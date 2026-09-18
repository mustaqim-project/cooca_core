<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExcelTemplate;
use App\Models\TemplateLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicTemplateController extends Controller
{
    /**
     * Index of all free downloadable Excel templates.
     */
    public function index(): View
    {
        ExcelTemplate::seedDefaultTemplatesIfEmpty();

        $templates = ExcelTemplate::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('public.templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Detail & lead capture page for a specific template.
     */
    public function show(string $slug): View
    {
        ExcelTemplate::seedDefaultTemplatesIfEmpty();

        $template = ExcelTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $otherTemplates = ExcelTemplate::where('is_active', true)
            ->where('slug', '!=', $slug)
            ->orderBy('sort_order', 'asc')
            ->take(3)
            ->get();

        return view('public.templates.show', [
            'template' => $template,
            'otherTemplates' => $otherTemplates,
        ]);
    }

    /**
     * Submit lead capture form and deliver template download link.
     */
    public function captureLead(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        ExcelTemplate::seedDefaultTemplatesIfEmpty();

        $template = ExcelTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:120',
            'business_name' => 'nullable|string|max:120',
        ]);

        TemplateLead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'template_slug' => $slug,
            'template_name' => $template->name,
            'ip_address' => $request->ip(),
        ]);

        $template->increment('downloads_count');

        $downloadUrl = route('template.file', $template->slug);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! File Excel siap diunduh.',
                'download_url' => $downloadUrl,
                'file_name' => $template->file_name,
                'file_size' => $template->formatted_file_size,
            ]);
        }

        return back()->with('download_success', true)
            ->with('download_url', $downloadUrl)
            ->with('file_name', $template->file_name);
    }

    /**
     * Direct file download route for visitors.
     */
    public function downloadFile(string $slug): StreamedResponse|BinaryFileResponse
    {
        $template = ExcelTemplate::where('slug', $slug)->firstOrFail();

        // 1. Try public storage disk
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            return Storage::disk('public')->download($template->file_path, $template->file_name);
        }

        // 2. Try direct public path
        $directPublic = public_path($template->file_path);
        if ($template->file_path && file_exists($directPublic)) {
            return response()->download($directPublic, $template->file_name);
        }

        // 3. Fallback to public/downloads/
        $fallback = public_path('downloads/' . $template->file_name);
        if (file_exists($fallback)) {
            return response()->download($fallback, $template->file_name);
        }

        abort(404, 'File template fisik tidak ditemukan di server.');
    }
}
