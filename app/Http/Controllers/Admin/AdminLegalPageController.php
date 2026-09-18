<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLegalPageController extends Controller
{
    /**
     * Display a listing of legal pages in Admin CMS.
     */
    public function index(): View
    {
        $pages = LegalPage::orderBy('id', 'asc')->get();

        return view('admin.legal-pages.index', compact('pages'));
    }

    /**
     * Show the form for editing the specified legal page.
     */
    public function edit(LegalPage $legalPage): View
    {
        return view('admin.legal-pages.edit', compact('legalPage'));
    }

    /**
     * Update the specified legal page in storage.
     */
    public function update(Request $request, LegalPage $legalPage): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'subtitle'         => ['nullable', 'string', 'max:1000'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'content_general'  => ['nullable', 'string'],
            'content_owner'    => ['nullable', 'string'],
            'content_customer' => ['nullable', 'string'],
            'version'          => ['required', 'string', 'max:20'],
            'effective_date'   => ['required', 'date'],
            'is_published'     => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $legalPage->update($validated);

        return redirect()
            ->route('admin.legal-pages.edit', $legalPage)
            ->with('success', "Halaman hukum '{$legalPage->title}' berhasil diperbarui dan dipublikasikan.");
    }

    /**
     * Toggle published status of the specified legal page.
     */
    public function toggle(LegalPage $legalPage): RedirectResponse
    {
        $legalPage->update([
            'is_published' => ! $legalPage->is_published,
        ]);

        $status = $legalPage->is_published ? 'dipublikasikan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.legal-pages.index')
            ->with('success', "Status '{$legalPage->title}' berhasil diubah menjadi {$status}.");
    }
}
