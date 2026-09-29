<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Ai\TenantSopIngestionService;
use App\Http\Controllers\Controller;
use App\Models\TenantSopDocument;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class TenantSopWebController extends Controller
{
    public function __construct(
        private readonly TenantSopIngestionService $ingestionService = new TenantSopIngestionService(),
    ) {}

    /**
     * Display tenant SOP management center.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $documents = TenantSopDocument::where('business_id', $business->id)
            ->with('user')
            ->withCount('chunks')
            ->latest()
            ->paginate(15);

        $totalChunks = (int) $documents->sum('chunks_count');
        $totalPages = (int) $documents->sum('total_pages');
        $totalBytes = (int) $documents->sum('file_size');

        return view('app.settings.sop', compact(
            'business',
            'documents',
            'totalChunks',
            'totalPages',
            'totalBytes',
        ));
    }

    /**
     * Upload and ingest a new PDF SOP document.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'sop_file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // Max 20MB
        ]);

        $business = Context::requireBusiness();
        $user = $request->user();

        if (!$user) {
            return back()->with('error', 'Sesi login tidak valid.');
        }

        try {
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->file('sop_file');
            $doc = $this->ingestionService->ingestUploadedPdf(
                $business,
                $user,
                $file,
                $validated['title'],
            );

            return back()->with('success', "Dokumen SOP \"{$doc->title}\" berhasil diunggah dan diindeks ({$doc->total_chunks} bagian terindeks).");
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses dokumen SOP: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an SOP document and remove its chunks.
     */
    public function destroy(string $id): RedirectResponse
    {
        $business = Context::requireBusiness();

        $doc = TenantSopDocument::where('business_id', $business->id)->findOrFail($id);

        try {
            $title = $doc->title;
            $this->ingestionService->deleteDocument($business, $doc);

            return back()->with('success', "Dokumen SOP \"{$title}\" berhasil dihapus.");
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menghapus dokumen SOP: ' . $e->getMessage());
        }
    }
}
