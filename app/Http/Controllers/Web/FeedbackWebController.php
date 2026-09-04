<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BugReport;
use App\Models\FeatureRequest;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FeedbackWebController extends Controller
{
    public function bugs(): View
    {
        $business = Context::requireBusiness();
        $items = BugReport::where('business_id', $business->id)->latest()->paginate(12);
        return view('app.feedback.index', ['business' => $business, 'items' => $items, 'type' => 'bugs']);
    }

    public function createBug(): View
    {
        return view('app.feedback.create', ['type' => 'bugs', 'categories' => BugReport::CATEGORIES]);
    }

    public function storeBug(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        abort_unless($user !== null, 403);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:' . implode(',', BugReport::CATEGORIES)],
            'severity' => ['required', 'in:' . implode(',', BugReport::SEVERITIES)],
            'description' => ['required', 'string', 'max:10000'],
            'steps_to_reproduce' => ['nullable', 'string', 'max:10000'],
            'expected_behavior' => ['nullable', 'string', 'max:5000'],
            'actual_behavior' => ['nullable', 'string', 'max:5000'],
            'environment' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,txt,log', 'max:5120'],
        ]);
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $storageQuota = app(\App\Domain\Storage\OwnerStorageQuotaService::class);
            if (! $storageQuota->canUpload($user, (int) $file->getSize())) {
                return back()->withInput()->withErrors(['attachment' => 'Kuota storage Anda tidak mencukupi untuk mengunggah lampiran ini.']);
            }
            $validated['attachment_path'] = $file->store('feedback/bugs', 'public');
        }
        $validated['business_id'] = $business->id;
        $validated['reporter_id'] = $user->getAuthIdentifier();
        $validated['status'] = 'open';
        $validated['progress_percent'] = 0;
        $report = BugReport::create($validated);
        $report->updates()->create(['user_id' => $user->getAuthIdentifier(), 'status' => $report->status, 'progress_percent' => 0, 'comment' => 'Laporan bug dibuat.']);
        return redirect()->route('feedback.bugs.show', $report)->with('success', 'Laporan bug berhasil dikirim.');
    }

    public function showBug(BugReport $bugReport): View
    {
        $business = Context::requireBusiness();
        abort_unless($bugReport->business_id === $business->id, 403);
        $bugReport->load(['reporter', 'assignedAdmin', 'updates.user', 'updates.admin']);
        return view('app.feedback.show', ['business' => $business, 'item' => $bugReport, 'type' => 'bugs']);
    }

    public function features(): View
    {
        $business = Context::requireBusiness();
        $items = FeatureRequest::where('business_id', $business->id)->latest()->paginate(12);
        return view('app.feedback.index', ['business' => $business, 'items' => $items, 'type' => 'features']);
    }

    public function createFeature(): View
    {
        return view('app.feedback.create', ['type' => 'features', 'categories' => FeatureRequest::CATEGORIES]);
    }

    public function storeFeature(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        abort_unless($user !== null, 403);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:' . implode(',', FeatureRequest::CATEGORIES)],
            'description' => ['required', 'string', 'max:10000'],
            'business_value' => ['required', 'string', 'max:5000'],
            'use_case' => ['required', 'string', 'max:5000'],
            'proposed_solution' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high'],
        ]);
        $validated['business_id'] = $business->id;
        $validated['requester_id'] = $user->getAuthIdentifier();
        $validated['status'] = 'submitted';
        $validated['progress_percent'] = 0;
        $feature = FeatureRequest::create($validated);
        $feature->updates()->create(['user_id' => $user->getAuthIdentifier(), 'status' => $feature->status, 'progress_percent' => 0, 'comment' => 'Request fitur dibuat.']);
        return redirect()->route('feedback.features.show', $feature)->with('success', 'Request fitur berhasil dikirim.');
    }

    public function showFeature(FeatureRequest $featureRequest): View
    {
        $business = Context::requireBusiness();
        abort_unless($featureRequest->business_id === $business->id, 403);
        $featureRequest->load(['requester', 'assignedAdmin', 'updates.user', 'updates.admin']);
        return view('app.feedback.show', ['business' => $business, 'item' => $featureRequest, 'type' => 'features']);
    }
}
