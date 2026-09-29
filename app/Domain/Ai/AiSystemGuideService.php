<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Business;
use App\Models\TenantSopChunk;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class AiSystemGuideService
{
    public function __construct(
        private readonly MarkdownKnowledgeService $knowledgeService = new MarkdownKnowledgeService(),
    ) {}

    /**
     * Process a question from a user with multi-tenant SOP and sector-aware system guidance.
     *
     * @return array{
     *     conversation_id: string,
     *     message_id: string,
     *     role: string,
     *     content: string,
     *     sources: array<int, array{type: string, title: string, subtitle: string}>,
     *     action_buttons: array<int, array{label: string, url: string, icon: string}>,
     *     scope: string
     * }
     */
    public function ask(
        Business $business,
        User $user,
        string $query,
        string $scope = 'all',
        ?string $conversationId = null,
    ): array {
        $cleanQuery = trim($query);
        if (empty($cleanQuery)) {
            $cleanQuery = 'Halo, apa saja yang bisa dibantu?';
        }

        // 1. Get or create Conversation
        $conversation = null;
        if ($conversationId) {
            $conversation = AiConversation::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->find($conversationId);
        }

        if (!$conversation) {
            $conversation = AiConversation::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'title' => Str::limit($cleanQuery, 40),
                'scope' => $scope,
                'last_activity_at' => now(),
            ]);
        } else {
            $conversation->update([
                'scope' => $scope,
                'last_activity_at' => now(),
            ]);
        }

        // 2. Record User Message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => AiMessage::ROLE_USER,
            'content' => $cleanQuery,
            'tokens_used' => str_word_count($cleanQuery),
        ]);

        // 3. Detect Sector & User Role
        $sectorCode = (string) ($business->template_code ?? 'general');
        $userRoles = $this->resolveUserRoles($user, $business);

        // 4. Retrieve Context Chunks
        $sopChunks = [];
        $guideChunks = [];
        $sources = [];
        $actionButtons = [];

        // 4A. Search Tenant SOPs (strictly scoped to business_id)
        if ($scope === 'all' || $scope === 'sop') {
            $sopChunks = $this->searchTenantSopChunks($business, $cleanQuery);
            foreach ($sopChunks as $chunk) {
                $docTitle = $chunk->document?->title ?? 'SOP Internal';
                $sources[] = [
                    'type' => 'sop',
                    'title' => $docTitle,
                    'subtitle' => "Hal {$chunk->page_number} - {$chunk->section_title}",
                ];
            }
        }

        // 4B. Search System Guides
        if ($scope === 'all' || $scope === 'system') {
            $guideChunks = $this->searchSystemGuideChunks($cleanQuery, $sectorCode, $userRoles);
            foreach ($guideChunks as $gc) {
                $sources[] = [
                    'type' => 'system_guide',
                    'title' => $gc['title'],
                    'subtitle' => "Modul {$gc['module']}",
                ];
                foreach ($gc['actions'] as $act) {
                    $actionButtons[$act['url']] = $act;
                }
            }
        }

        // 4C. Resolve additional contextual action buttons from user query
        $queryActions = $this->knowledgeService->resolveActionLinks($cleanQuery, $userRoles);
        foreach ($queryActions as $act) {
            $actionButtons[$act['url']] = $act;
        }

        // 5. Generate Answer (Hybrid: Gemini AI if configured, or Local High-Precision Engine)
        $aiResult = $this->generateHybridResponse(
            $cleanQuery,
            $business,
            $user,
            $sectorCode,
            $sopChunks,
            $guideChunks,
        );

        $replyContent = $aiResult['content'];
        $tokensUsed = $aiResult['tokens_used'];

        // 6. Record Assistant Message
        $actionButtonsList = array_values($actionButtons);
        $assistantMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => AiMessage::ROLE_ASSISTANT,
            'content' => $replyContent,
            'sources' => $sources,
            'action_buttons' => $actionButtonsList,
            'tokens_used' => $tokensUsed,
        ]);

        return [
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'role' => 'assistant',
            'content' => $replyContent,
            'sources' => $sources,
            'action_buttons' => $actionButtonsList,
            'scope' => $scope,
        ];
    }

    /**
     * Get tailored quick prompt suggestions for the business sector and user.
     *
     * @return array<int, array{text: string, icon: string, scope: string}>
     */
    public function getQuickPrompts(Business $business, User $user): array
    {
        $sector = strtolower((string) ($business->template_code ?? 'general'));

        $prompts = [
            ['text' => 'Bagaimana cara rekap kasir & tutup shift (Blind Count)?', 'icon' => 'clock', 'scope' => 'system'],
            ['text' => 'Cara menghubungkan WhatsApp Meta Cloud API', 'icon' => 'message-circle', 'scope' => 'system'],
        ];

        if (str_contains($sector, 'workshop') || str_contains($sector, 'bengkel')) {
            $prompts[] = ['text' => 'Cara membuat Work Order & assign mekanik', 'icon' => 'wrench', 'scope' => 'system'];
            $prompts[] = ['text' => 'SOP pengecekan kendaraan masuk & servis', 'icon' => 'file-text', 'scope' => 'sop'];
        } elseif (str_contains($sector, 'fnb') || str_contains($sector, 'resto') || str_contains($sector, 'cafe')) {
            $prompts[] = ['text' => 'Cara mengatur Manajemen Meja Dine-In & KOT Dapur', 'icon' => 'grid', 'scope' => 'system'];
            $prompts[] = ['text' => 'Cara input resep BOM bahan baku menu', 'icon' => 'layers', 'scope' => 'system'];
        } elseif (str_contains($sector, 'retail') || str_contains($sector, 'minimarket')) {
            $prompts[] = ['text' => 'Cara scan barcode cepat di kasir toko', 'icon' => 'barcode', 'scope' => 'system'];
            $prompts[] = ['text' => 'Cara input stok opname berkala', 'icon' => 'package', 'scope' => 'system'];
        } elseif (str_contains($sector, 'laundry')) {
            $prompts[] = ['text' => 'Alur tracking cucian (Diterima -> Dicuci -> Siap Ambil)', 'icon' => 'check-circle', 'scope' => 'system'];
        } elseif (str_contains($sector, 'manufacturing') || str_contains($sector, 'konveksi')) {
            $prompts[] = ['text' => 'Cara hitung HPP potong bahan & labor rate mesin', 'icon' => 'calculator', 'scope' => 'system'];
        } else {
            $prompts[] = ['text' => 'Bagaimana cara input produk & varian baru?', 'icon' => 'package', 'scope' => 'system'];
            $prompts[] = ['text' => 'Cara melihat laporan laba rugi bulanan', 'icon' => 'bar-chart-2', 'scope' => 'system'];
        }

        // Check if tenant has SOPs uploaded
        $hasSop = TenantSopChunk::where('business_id', $business->id)->exists();
        if ($hasSop) {
            $prompts[] = ['text' => 'Apa aturan SOP pembukaan & penutupan toko kami?', 'icon' => 'file-check', 'scope' => 'sop'];
        } else {
            $prompts[] = ['text' => 'Bagaimana cara upload dokumen PDF SOP usaha kami?', 'icon' => 'upload', 'scope' => 'system'];
        }

        return $prompts;
    }

    /**
     * Search tenant SOP chunks strictly within business_id.
     *
     * @return array<int, TenantSopChunk>
     */
    private function searchTenantSopChunks(Business $business, string $query): array
    {
        $keywords = preg_split('/[\s,\.\?\!]+/', strtolower($query));
        if (empty($keywords)) {
            return [];
        }

        $queryBuilder = TenantSopChunk::with('document')
            ->where('business_id', $business->id);

        $queryBuilder->where(function ($sub) use ($keywords, $query): void {
            $sub->where('content_text', 'like', "%{$query}%")
                ->orWhere('section_title', 'like', "%{$query}%");

            foreach ($keywords as $kw) {
                if (strlen($kw) >= 3) {
                    $sub->orWhere('keywords', 'like', "%{$kw}%")
                        ->orWhere('content_text', 'like', "%{$kw}%");
                }
            }
        });

        return $queryBuilder->limit(4)->get()->all();
    }

    /**
     * Search system guide chunks filtered by sector and role.
     *
     * @param array<string> $userRoles
     * @return array<int, array{
     *     source_file: string,
     *     module: string,
     *     title: string,
     *     content: string,
     *     sectors: array<string>,
     *     roles: array<string>,
     *     keywords: string,
     *     actions: array<array{label: string, url: string, icon: string}>
     * }>
     */
    private function searchSystemGuideChunks(string $query, string $sectorCode, array $userRoles): array
    {
        $all = $this->knowledgeService->getSystemKnowledge();
        $queryLower = strtolower($query);
        $keywords = array_filter(preg_split('/[\s,\.\?\!]+/', $queryLower), fn($k) => strlen($k) >= 3);

        $scored = [];

        foreach ($all as $chunk) {
            // 1. Check Sector compatibility
            $sectors = $chunk['sectors'];
            $sectorMatch = in_array('all', $sectors, true) || in_array($sectorCode, $sectors, true);
            if (!$sectorMatch) {
                continue;
            }

            // 2. Check Role compatibility
            $roles = $chunk['roles'];
            $roleMatch = in_array('all', $roles, true) || in_array('all', $userRoles, true);
            if (!$roleMatch) {
                foreach ($userRoles as $ur) {
                    if (in_array($ur, $roles, true)) {
                        $roleMatch = true;
                        break;
                    }
                }
            }
            if (!$roleMatch) {
                continue;
            }

            // 3. Calculate match score
            $score = 0;
            $titleLower = strtolower($chunk['title']);
            $contentLower = strtolower($chunk['content']);

            if (str_contains($titleLower, $queryLower)) {
                $score += 50;
            }
            if (str_contains($contentLower, $queryLower)) {
                $score += 30;
            }

            foreach ($keywords as $kw) {
                if (str_contains($titleLower, $kw)) {
                    $score += 15;
                }
                if (str_contains($chunk['keywords'], $kw)) {
                    $score += 10;
                }
                if (str_contains($contentLower, $kw)) {
                    $score += 5;
                }
            }

            if ($score > 0) {
                $scored[] = ['score' => $score, 'chunk' => $chunk];
            }
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(fn($item) => $item['chunk'], array_slice($scored, 0, 4));
    }

    /**
     * Generate response via Gemini AI if available or local structured engine.
     *
     * @param array<int, TenantSopChunk> $sopChunks
     * @param array<int, array<string, mixed>> $guideChunks
     * @return array{content: string, tokens_used: int}
     */
    private function generateHybridResponse(
        string $query,
        Business $business,
        User $user,
        string $sectorCode,
        array $sopChunks,
        array $guideChunks,
    ): array {
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if (!empty($geminiKey)) {
            try {
                $geminiReply = $this->callGemini(
                    $geminiKey,
                    $query,
                    $business->name,
                    $sectorCode,
                    $user->name,
                    $sopChunks,
                    $guideChunks,
                );
                if (!empty($geminiReply)) {
                    return [
                        'content' => $geminiReply,
                        'tokens_used' => str_word_count($geminiReply) + 200,
                    ];
                }
            } catch (Throwable $e) {
                Log::warning('Gemini AI fallback triggered: ' . $e->getMessage());
            }
        }

        // Local High-Precision Formatter
        $localContent = $this->formatLocalResponse($query, $business, $sopChunks, $guideChunks);
        return [
            'content' => $localContent,
            'tokens_used' => str_word_count($localContent),
        ];
    }

    /**
     * Call Google Gemini API with grounded RAG context.
     *
     * @param array<int, TenantSopChunk> $sopChunks
     * @param array<int, array<string, mixed>> $guideChunks
     */
    private function callGemini(
        string $apiKey,
        string $query,
        string $businessName,
        string $sector,
        string $userName,
        array $sopChunks,
        array $guideChunks,
    ): string {
        $systemPrompt = "Anda adalah COOCA Smart Assistant, asisten cerdas untuk platform ERP UMKM COOCA.
Nama Bisnis Pengguna: {$businessName}
Sektor Usaha: {$sector}
Pengguna yang bertanya: {$userName}

PRINSIP JAWABAN:
1. Berikan jawaban dalam Bahasa Indonesia yang santun, jelas, ringkas, dan profesional (gaya Apple HIG / Zero-Clutter).
2. Jika ada data SOP Internal Usaha, jelaskan sesuai aturan SOP yang tertera pada dokumen usaha mereka.
3. Jika ada panduan fitur COOCA, jelaskan langkah 1, 2, 3 yang mudah dipahami kasir/staf operasional.
4. JANGAN PERNAH mengarang fitur yang tidak ada di konteks. Jika tidak ditemukan, sarankan untuk cek ke Supervisor/Owner.
5. Format jawaban menggunakan Markdown yang rapi dengan bullet points.";

        $contextText = "=== KONTEKS PENGETAHUAN ===\n";

        if (!empty($sopChunks)) {
            $contextText .= "\n--- [DOKUMEN SOP INTERNAL USAHA MILIK TENANT] ---\n";
            foreach ($sopChunks as $sc) {
                $docTitle = $sc->document?->title ?? 'SOP';
                $contextText .= "Dokumen: {$docTitle} (Hal {$sc->page_number} - {$sc->section_title}):\n{$sc->content_text}\n\n";
            }
        }

        if (!empty($guideChunks)) {
            $contextText .= "\n--- [PANDUAN FITUR SISTEM COOCA] ---\n";
            foreach ($guideChunks as $gc) {
                $contextText .= "Modul: {$gc['title']} ({$gc['module']}):\n{$gc['content']}\n\n";
            }
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(10)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $contextText . "\n\nPertanyaan Pengguna: " . $query],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 800,
            ],
        ]);

        if ($response->successful()) {
            $json = $response->json();
            return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        return '';
    }

    /**
     * Synthesize a structured response locally without external AI APIs.
     *
     * @param array<int, TenantSopChunk> $sopChunks
     * @param array<int, array<string, mixed>> $guideChunks
     */
    private function formatLocalResponse(
        string $query,
        Business $business,
        array $sopChunks,
        array $guideChunks,
    ): string {
        $output = '';

        // Case 1: Matching SOP Chunks
        if (!empty($sopChunks)) {
            $output .= "### 📋 Berdasarkan SOP Usaha {$business->name}\n\n";
            foreach ($sopChunks as $index => $sc) {
                $docTitle = $sc->document?->title ?? 'Dokumen SOP';
                $output .= "**" . ($index + 1) . ". {$sc->section_title}** *(Sumber: {$docTitle} - Hal {$sc->page_number})*\n";
                $output .= Str::limit($sc->content_text, 350) . "\n\n";
            }
        }

        // Case 2: Matching System Guides
        if (!empty($guideChunks)) {
            if (!empty($output)) {
                $output .= "---\n\n";
            }
            $output .= "### ⚙️ Panduan Penggunaan Fitur COOCA\n\n";
            foreach ($guideChunks as $index => $gc) {
                $output .= "**" . ($index + 1) . ". {$gc['title']}** *(Modul: {$gc['module']})*\n";
                $snippet = Str::limit($gc['content'], 400);
                $output .= $snippet . "\n\n";
            }
        }

        // Case 3: No Chunks Matched
        if (empty($sopChunks) && empty($guideChunks)) {
            $output .= "Maaf, panduan spesifik mengenai *\"{$query}\"* belum ditemukan di basis data SOP usaha Anda maupun ringkasan modul COOCA saat ini.\n\n";
            $output .= "**Saran Tindakan:**\n";
            $output .= "1. Gunakan kata kunci yang lebih spesifik seperti *kasir*, *stok*, *printer*, *jurnal*, *whatsapp*, atau *shift*.\n";
            $output .= "2. Business Owner dapat mengunggah file SOP internal usaha dalam bentuk PDF di menu **Pusat SOP Usaha** agar bot dapat mempelajari aturan khusus usaha Anda.\n";
            $output .= "3. Hubungi Supervisor atau tim Dukungan COOCA jika Anda memerlukan bantuan teknis mendalam.";
        }

        return trim($output);
    }

    /**
     * Resolve list of roles for current user.
     *
     * @return array<string>
     */
    private function resolveUserRoles(User $user, Business $business): array
    {
        $roles = ['cashier']; // default baseline

        if ($business->user_id === $user->id) {
            $roles[] = 'owner';
            $roles[] = 'admin';
        }

        if (method_exists($user, 'roles')) {
            foreach ($user->roles as $r) {
                $roles[] = strtolower((string) ($r->slug ?? $r->name ?? ''));
            }
        }

        return array_unique(array_filter($roles));
    }
}
