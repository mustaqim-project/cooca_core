<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Models\Business;
use App\Models\TenantSopChunk;
use App\Models\TenantSopDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class TenantSopIngestionService
{
    /**
     * Ingest an uploaded PDF SOP document for a specific tenant business.
     */
    public function ingestUploadedPdf(Business $business, User $user, UploadedFile $file, string $title): TenantSopDocument
    {
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $safeName = Str::uuid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';
        
        $storageDir = 'tenants/' . $business->id . '/sops';
        $path = $file->storeAs($storageDir, $safeName, 'local');

        if (!$path) {
            throw new RuntimeException('Gagal menyimpan file PDF ke storage tenant.');
        }

        $fullPath = Storage::disk('local')->path($path);

        return DB::transaction(function () use ($business, $user, $title, $originalName, $path, $fileSize, $fullPath): TenantSopDocument {
            $doc = TenantSopDocument::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'title' => $title,
                'file_name' => $originalName,
                'file_path' => $path,
                'file_size' => $fileSize,
                'total_pages' => 0,
                'total_chunks' => 0,
                'status' => TenantSopDocument::STATUS_PROCESSING,
            ]);

            try {
                $pages = $this->extractTextFromPdf($fullPath);
                $totalPages = count($pages);
                $chunksCreated = 0;

                foreach ($pages as $pageIndex => $pageContent) {
                    $pageNumber = $pageIndex + 1;
                    $pageChunks = $this->splitIntoChunks($pageContent, $title, $pageNumber);

                    foreach ($pageChunks as $chunkData) {
                        TenantSopChunk::create([
                            'business_id' => $business->id,
                            'document_id' => $doc->id,
                            'page_number' => $pageNumber,
                            'section_title' => $chunkData['section_title'],
                            'content_text' => $chunkData['content_text'],
                            'keywords' => $chunkData['keywords'],
                        ]);
                        $chunksCreated++;
                    }
                }

                $doc->update([
                    'total_pages' => max(1, $totalPages),
                    'total_chunks' => $chunksCreated,
                    'status' => TenantSopDocument::STATUS_READY,
                ]);

                return $doc;
            } catch (Throwable $e) {
                $doc->update([
                    'status' => TenantSopDocument::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Delete an SOP document and all associated chunks from database and storage.
     */
    public function deleteDocument(Business $business, TenantSopDocument $document): bool
    {
        if ($document->business_id !== $business->id) {
            throw new RuntimeException('Akses ditolak: Dokumen bukan milik bisnis ini.');
        }

        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        return (bool) $document->delete();
    }

    /**
     * Extract text from PDF file by parsing PDF stream objects and text blocks.
     *
     * @return array<int, string> List of page texts
     */
    public function extractTextFromPdf(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File PDF tidak ditemukan: {$filePath}");
        }

        $content = (string) file_get_contents($filePath);
        if (empty($content)) {
            throw new RuntimeException("File PDF kosong.");
        }

        $pagesText = [];

        // 1. Try to extract decompressed text streams
        $streams = [];
        preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/is', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $rawStream) {
                $decompressed = @gzuncompress($rawStream);
                if ($decompressed === false) {
                    $decompressed = $rawStream;
                }

                $text = $this->parsePdfStreamText($decompressed);
                if (!empty(trim($text))) {
                    $streams[] = $text;
                }
            }
        }

        // 2. If streams yielded structured text
        if (!empty($streams)) {
            // Group stream texts into pages if multiple streams exist
            $combined = implode("\n\n", $streams);
            $pages = explode("\f", $combined); // Form feed character for page breaks
            if (count($pages) > 1) {
                foreach ($pages as $p) {
                    if (!empty(trim($p))) {
                        $pagesText[] = trim($p);
                    }
                }
            } else {
                // If single page or no form feeds, chunk into ~500 word pages
                $paragraphs = explode("\n\n", $combined);
                $currentPage = '';
                foreach ($paragraphs as $para) {
                    if (str_word_count($currentPage . ' ' . $para) > 400 && !empty($currentPage)) {
                        $pagesText[] = trim($currentPage);
                        $currentPage = $para;
                    } else {
                        $currentPage .= "\n\n" . $para;
                    }
                }
                if (!empty(trim($currentPage))) {
                    $pagesText[] = trim($currentPage);
                }
            }
        }

        // 3. Fallback: Parse visible text directly with regex if streams were compressed with proprietary filters
        if (empty($pagesText)) {
            $fallbackText = $this->parseFallbackText($content);
            if (!empty($fallbackText)) {
                $pagesText[] = $fallbackText;
            } else {
                $pagesText[] = "Dokumen SOP: " . pathinfo($filePath, PATHINFO_FILENAME);
            }
        }

        return $pagesText;
    }

    /**
     * Parse text operators in a single PDF stream.
     */
    private function parsePdfStreamText(string $stream): string
    {
        $result = '';

        // Match BT ... ET blocks
        if (preg_match_all('/BT[\r\n\s]+(.*?)[\r\n\s]+ET/is', $stream, $textBlocks)) {
            foreach ($textBlocks[1] as $block) {
                // Handle Tj (single string)
                if (preg_match_all('/\((.*?)\)\s*Tj/s', $block, $tjMatches)) {
                    foreach ($tjMatches[1] as $str) {
                        $result .= $this->unescapePdfString($str) . ' ';
                    }
                    $result .= "\n";
                }

                // Handle TJ (array of strings and kerning offsets)
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArrayMatches)) {
                    foreach ($tjArrayMatches[1] as $arrayContent) {
                        if (preg_match_all('/\((.*?)\)/s', $arrayContent, $stringMatches)) {
                            foreach ($stringMatches[1] as $str) {
                                $result .= $this->unescapePdfString($str);
                            }
                            $result .= ' ';
                        }
                    }
                    $result .= "\n";
                }

                // Handle ' and " operators
                if (preg_match_all('/\((.*?)\)\s*[\'"]/s', $block, $quoteMatches)) {
                    foreach ($quoteMatches[1] as $str) {
                        $result .= $this->unescapePdfString($str) . "\n";
                    }
                }
            }
        }

        return trim($result);
    }

    /**
     * Unescape standard PDF string escape sequences.
     */
    private function unescapePdfString(string $str): string
    {
        $search = ['\\\\', '\\(', '\\)', '\\n', '\\r', '\\t', '\\b', '\\f'];
        $replace = ['\\', '(', ')', "\n", "\r", "\t", "\x08", "\x0C"];
        return str_replace($search, $replace, $str);
    }

    /**
     * Extract human-readable text from binary buffer as fallback.
     */
    private function parseFallbackText(string $content): string
    {
        // Extract all ASCII and UTF-8 readable words
        preg_match_all('/[\x20-\x7E\xA0-\xFF]{4,}/', $content, $matches);
        if (empty($matches[0])) {
            return '';
        }

        $filtered = [];
        foreach ($matches[0] as $str) {
            $str = trim($str);
            // Skip PDF commands, hashes, and XML tags
            if (preg_match('/^(\/|%|\d+\s+\d+\s+obj|<<|>>|endobj|xref|trailer|startxref)/i', $str)) {
                continue;
            }
            if (strlen($str) >= 3 && !preg_match('/^[0-9A-F]{16,}$/i', $str)) {
                $filtered[] = $str;
            }
        }

        return implode("\n", array_slice($filtered, 0, 100));
    }

    /**
     * Split a page's text into logical knowledge chunks.
     *
     * @return array<int, array{section_title: string, content_text: string, keywords: string}>
     */
    private function splitIntoChunks(string $pageContent, string $docTitle, int $pageNumber): array
    {
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $pageContent)));
        if (empty($paragraphs)) {
            $paragraphs = [$pageContent];
        }

        $chunks = [];
        $currentChunkText = '';
        $currentSection = "{$docTitle} - Halaman {$pageNumber}";

        foreach ($paragraphs as $para) {
            // Check if paragraph looks like a heading (short length or starts with numbers/caps)
            if (strlen($para) < 60 && (preg_match('/^(BAB|[0-9]+\.|\bSOP\b|PASAL|ATURAN|STANDAR)/i', $para) || ctype_upper(str_replace(' ', '', $para)))) {
                if (!empty(trim($currentChunkText))) {
                    $chunks[] = [
                        'section_title' => $currentSection,
                        'content_text' => trim($currentChunkText),
                        'keywords' => $this->extractKeywords($currentChunkText . ' ' . $currentSection),
                    ];
                    $currentChunkText = '';
                }
                $currentSection = $para;
            } else {
                if (str_word_count($currentChunkText . ' ' . $para) > 350 && !empty($currentChunkText)) {
                    $chunks[] = [
                        'section_title' => $currentSection,
                        'content_text' => trim($currentChunkText),
                        'keywords' => $this->extractKeywords($currentChunkText . ' ' . $currentSection),
                    ];
                    $currentChunkText = $para;
                } else {
                    $currentChunkText .= ($currentChunkText ? "\n\n" : '') . $para;
                }
            }
        }

        if (!empty(trim($currentChunkText))) {
            $chunks[] = [
                'section_title' => $currentSection,
                'content_text' => trim($currentChunkText),
                'keywords' => $this->extractKeywords($currentChunkText . ' ' . $currentSection),
            ];
        }

        if (empty($chunks)) {
            $chunks[] = [
                'section_title' => "{$docTitle} - Bagian {$pageNumber}",
                'content_text' => trim($pageContent),
                'keywords' => $this->extractKeywords($pageContent),
            ];
        }

        return $chunks;
    }

    /**
     * Extract searchable keywords from text for fast indexing.
     */
    private function extractKeywords(string $text): string
    {
        $words = preg_split('/[\s,\.\(\)\[\]\{\}\"\'\:\;\-\_\/\?\!\+\=]+/', strtolower($text));
        if (!is_array($words)) {
            return '';
        }

        $stopwords = [
            'dan', 'atau', 'yang', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'adalah', 'ini', 'itu',
            'sebagai', 'oleh', 'dalam', 'akan', 'harus', 'bisa', 'dapat', 'sudah', 'telah', 'agar', 'supaya',
            'maka', 'jika', 'bila', 'saat', 'ketika', 'setelah', 'sebelum', 'karena', 'sebab', 'the', 'and',
            'is', 'in', 'to', 'for', 'of', 'with', 'on', 'at', 'by', 'from', 'an', 'a',
        ];

        $stopwordMap = array_flip($stopwords);
        $unique = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 3 && !isset($stopwordMap[$w]) && !is_numeric($w)) {
                $unique[$w] = true;
            }
        }

        return implode(' ', array_slice(array_keys($unique), 0, 40));
    }
}
