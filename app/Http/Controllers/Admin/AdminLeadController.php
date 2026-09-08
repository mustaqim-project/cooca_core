<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateLead;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminLeadController extends Controller
{
    /**
     * Display listing of template download leads.
     */
    public function index(Request $request): View
    {
        $query = TemplateLead::latest();

        if ($template = $request->get('template')) {
            $query->where('template_slug', $template);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(20)->withQueryString();

        $templatesCount = TemplateLead::select('template_slug', 'template_name')
            ->selectRaw('count(*) as count')
            ->groupBy('template_slug', 'template_name')
            ->get();

        return view('admin.leads.index', [
            'leads' => $leads,
            'totalLeads' => TemplateLead::count(),
            'todayLeads' => TemplateLead::whereDate('created_at', today())->count(),
            'templatesCount' => $templatesCount,
        ]);
    }

    /**
     * Export leads to CSV file.
     */
    public function export(): StreamedResponse
    {
        $leads = TemplateLead::latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_template_' . date('Y-m-d_His') . '.csv"',
        ];

        return response()->stream(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Nama', 'No WhatsApp/HP', 'Email', 'Nama Usaha', 'Template Diunduh', 'Tanggal Download']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->name,
                    $lead->phone,
                    $lead->email ?? '-',
                    $lead->business_name ?? '-',
                    $lead->template_name,
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
