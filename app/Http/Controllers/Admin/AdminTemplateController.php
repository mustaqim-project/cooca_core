<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExcelTemplate;
use App\Models\TemplateLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminTemplateController extends Controller
{
    /**
     * Display a listing of Excel templates with metrics & search.
     */
    public function index(Request $request): View
    {
        ExcelTemplate::seedDefaultTemplatesIfEmpty();

        $query = ExcelTemplate::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = trim((string) $request->query('category', ''))) {
            $query->where('category', $category);
        }

        $templates = $query->orderBy('sort_order', 'asc')->latest()->paginate(15)->withQueryString();

        $totalTemplates = ExcelTemplate::count();
        $activeTemplates = ExcelTemplate::where('is_active', true)->count();
        $totalDownloads = (int) ExcelTemplate::sum('downloads_count');
        $totalLeads = TemplateLead::where('template_slug', '!=', 'contact-inquiry')->count();
        $categories = ExcelTemplate::availableCategories();

        return view('admin.templates.index', compact(
            'templates',
            'totalTemplates',
            'activeTemplates',
            'totalDownloads',
            'totalLeads',
            'categories',
            'search',
            'category'
        ));
    }

    /**
     * Show the form for uploading a new Excel template.
     */
    public function create(): View
    {
        $categories = ExcelTemplate::availableCategories();

        return view('admin.templates.create', compact('categories'));
    }

    /**
     * Store a newly created Excel template and its uploaded file.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:excel_templates,slug'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'highlights' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:25600'], // max 25MB
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Check if slug generated from name is already taken
        $originalSlug = $slug;
        $counter = 1;
        while (ExcelTemplate::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $highlights = [];
        if (!empty($validated['highlights'])) {
            $highlights = array_values(array_filter(
                array_map('trim', explode("\n", str_replace("\r", "", $validated['highlights']))),
                fn ($line) => $line !== ''
            ));
        }

        $file = $request->file('excel_file');
        $originalExtension = strtolower($file->getClientOriginalExtension());
        $format = strtoupper($originalExtension);
        $safeFileName = 'Template_' . Str::studly($slug) . '_COOCA.' . $originalExtension;

        // Store file in public storage disk under templates/
        $storedPath = $file->storeAs('templates', $safeFileName, 'public');
        $fileSize = $file->getSize();

        ExcelTemplate::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'format' => $format,
            'description' => $validated['description'] ?? null,
            'highlights' => $highlights,
            'file_path' => $storedPath,
            'file_name' => $safeFileName,
            'file_size' => $fileSize,
            'downloads_count' => 0,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template Excel berhasil diunggah dan dipublikasikan.');
    }

    /**
     * Show the form for editing an existing Excel template.
     */
    public function edit(ExcelTemplate $template): View
    {
        $categories = ExcelTemplate::availableCategories();
        $highlightsText = is_array($template->highlights) ? implode("\n", $template->highlights) : '';

        return view('admin.templates.edit', compact('template', 'categories', 'highlightsText'));
    }

    /**
     * Update template details and optionally replace the uploaded Excel file.
     */
    public function update(Request $request, ExcelTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('excel_templates', 'slug')->ignore($template->id)],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'highlights' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'excel_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv', 'max:25600'],
        ]);

        $slug = Str::slug($validated['slug']);

        $highlights = [];
        if (!empty($validated['highlights'])) {
            $highlights = array_values(array_filter(
                array_map('trim', explode("\n", str_replace("\r", "", $validated['highlights']))),
                fn ($line) => $line !== ''
            ));
        }

        $dataToUpdate = [
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'highlights' => $highlights,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];

        if ($request->hasFile('excel_file')) {
            // Delete old file if exists in storage
            if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            $file = $request->file('excel_file');
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $safeFileName = 'Template_' . Str::studly($slug) . '_COOCA.' . $originalExtension;
            $storedPath = $file->storeAs('templates', $safeFileName, 'public');

            $dataToUpdate['file_path'] = $storedPath;
            $dataToUpdate['file_name'] = $safeFileName;
            $dataToUpdate['file_size'] = $file->getSize();
            $dataToUpdate['format'] = strtoupper($originalExtension);
        }

        $template->update($dataToUpdate);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template Excel "' . $template->name . '" berhasil diperbarui.');
    }

    /**
     * Toggle active/published status of a template.
     */
    public function toggle(ExcelTemplate $template): RedirectResponse
    {
        $template->update([
            'is_active' => !$template->is_active,
        ]);

        $status = $template->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status template {$template->name} berhasil {$status}.");
    }

    /**
     * Delete an Excel template and its physical file.
     */
    public function destroy(ExcelTemplate $template): RedirectResponse
    {
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }

        $name = $template->name;
        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', "Template Excel \"{$name}\" berhasil dihapus.");
    }

    /**
     * Download the file directly for administrator inspection.
     */
    public function download(ExcelTemplate $template): StreamedResponse|BinaryFileResponse
    {
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            return Storage::disk('public')->download($template->file_path, $template->file_name);
        }

        // Fallback to public/downloads
        $fallback = public_path('downloads/' . $template->file_name);
        if (file_exists($fallback)) {
            return response()->download($fallback, $template->file_name);
        }

        abort(404, 'File template fisik tidak ditemukan di server.');
    }
}
