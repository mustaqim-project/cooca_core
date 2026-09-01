<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BugReport;
use App\Models\FeatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminFeedbackController extends Controller
{
    public function bugs(Request $request): View
    {
        $status = $request->get('status', 'all');
        $query = BugReport::with(['business', 'reporter', 'assignedAdmin'])->latest();
        if (in_array($status, BugReport::STATUSES, true)) $query->where('status', $status);
        $items = $query->paginate(20)->withQueryString();
        return view('admin.feedback.index', ['items' => $items, 'type' => 'bugs', 'status' => $status, 'statuses' => BugReport::STATUSES]);
    }

    public function showBug(BugReport $bugReport): View
    {
        $bugReport->load(['business', 'reporter', 'assignedAdmin', 'updates.user', 'updates.admin']);
        return view('admin.feedback.show', ['item' => $bugReport, 'type' => 'bugs', 'statuses' => BugReport::STATUSES, 'admins' => Admin::where('is_active', true)->orderBy('name')->get()]);
    }

    public function updateBug(Request $request, BugReport $bugReport): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', BugReport::STATUSES)],
            'priority' => ['required', 'in:low,normal,high,critical'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'assigned_admin_id' => ['nullable', 'exists:admins,id'],
            'comment' => ['nullable', 'string', 'max:10000'],
            'resolution' => ['nullable', 'string', 'max:10000'],
        ]);
        $bugReport->update([
            'status' => $validated['status'], 'priority' => $validated['priority'], 'progress_percent' => $validated['progress_percent'],
            'assigned_admin_id' => $validated['assigned_admin_id'] ?? null, 'resolution' => $validated['resolution'] ?? null,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null,
            'closed_at' => $validated['status'] === 'closed' ? now() : null,
        ]);
        $bugReport->updates()->create(['admin_id' => auth('admin')->id(), 'status' => $bugReport->status, 'progress_percent' => $bugReport->progress_percent, 'comment' => $validated['comment'] ?? null]);
        return back()->with('success', 'Laporan bug berhasil diperbarui.');
    }

    public function features(Request $request): View
    {
        $status = $request->get('status', 'all');
        $query = FeatureRequest::with(['business', 'requester', 'assignedAdmin'])->latest();
        if (in_array($status, FeatureRequest::STATUSES, true)) $query->where('status', $status);
        $items = $query->paginate(20)->withQueryString();
        return view('admin.feedback.index', ['items' => $items, 'type' => 'features', 'status' => $status, 'statuses' => FeatureRequest::STATUSES]);
    }

    public function showFeature(FeatureRequest $featureRequest): View
    {
        $featureRequest->load(['business', 'requester', 'assignedAdmin', 'updates.user', 'updates.admin']);
        return view('admin.feedback.show', ['item' => $featureRequest, 'type' => 'features', 'statuses' => FeatureRequest::STATUSES, 'admins' => Admin::where('is_active', true)->orderBy('name')->get()]);
    }

    public function updateFeature(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', FeatureRequest::STATUSES)],
            'priority' => ['required', 'in:low,normal,high'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'assigned_admin_id' => ['nullable', 'exists:admins,id'],
            'comment' => ['nullable', 'string', 'max:10000'],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
        ]);
        $featureRequest->update([
            'status' => $validated['status'], 'priority' => $validated['priority'], 'progress_percent' => $validated['progress_percent'],
            'assigned_admin_id' => $validated['assigned_admin_id'] ?? null, 'admin_notes' => $validated['admin_notes'] ?? null,
            'released_at' => $validated['status'] === 'released' ? now() : null,
        ]);
        $featureRequest->updates()->create(['admin_id' => auth('admin')->id(), 'status' => $featureRequest->status, 'progress_percent' => $featureRequest->progress_percent, 'comment' => $validated['comment'] ?? null]);
        return back()->with('success', 'Request fitur berhasil diperbarui.');
    }
}
