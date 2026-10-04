<?php

declare(strict_types=1);

namespace App\Domain\Ai\Organization;

final class CompanyHierarchy
{
    /**
     * Get the full organizational hierarchy structure.
     *
     * @return array<string, mixed>
     */
    public static function getStructure(): array
    {
        return [
            'governance' => [
                'authority' => 'Business Owner (Human-in-the-Loop)',
                'rule' => 'AI Bekerja untuk Bisnis. Manusia Tetap Pemegang Keputusan Akhir.',
            ],
            'executives' => [
                'ceo' => [
                    'role' => ExecutiveRole::CEO->value,
                    'label' => ExecutiveRole::CEO->label(),
                    'title' => ExecutiveRole::CEO->title(),
                    'icon' => ExecutiveRole::CEO->icon(),
                    'focus' => 'Company-wide business health, strategic priorities, KPI, major risks, opportunity detection',
                ],
                'coo' => [
                    'role' => ExecutiveRole::COO->value,
                    'label' => ExecutiveRole::COO->label(),
                    'title' => ExecutiveRole::COO->title(),
                    'icon' => ExecutiveRole::COO->icon(),
                    'focus' => 'Operational orchestration, work division, task delegation, action proposal aggregation',
                ],
            ],
            'departments' => [
                Department::MARKETING->value => [
                    'label' => Department::MARKETING->label(),
                    'lead' => ExecutiveRole::CMO->value,
                    'lead_label' => ExecutiveRole::CMO->label(),
                    'icon' => Department::MARKETING->icon(),
                    'agents' => [
                        AgentRole::MARKETING->value => self::formatAgent(AgentRole::MARKETING),
                        AgentRole::CONTENT->value => self::formatAgent(AgentRole::CONTENT),
                        AgentRole::SOCIAL_MEDIA->value => self::formatAgent(AgentRole::SOCIAL_MEDIA),
                    ],
                ],
                Department::SALES->value => [
                    'label' => Department::SALES->label(),
                    'lead' => ExecutiveRole::SALES_DIRECTOR->value,
                    'lead_label' => ExecutiveRole::SALES_DIRECTOR->label(),
                    'icon' => Department::SALES->icon(),
                    'agents' => [
                        AgentRole::SALES->value => self::formatAgent(AgentRole::SALES),
                        AgentRole::CUSTOMER->value => self::formatAgent(AgentRole::CUSTOMER),
                    ],
                ],
                Department::FINANCE->value => [
                    'label' => Department::FINANCE->label(),
                    'lead' => ExecutiveRole::CFO->value,
                    'lead_label' => ExecutiveRole::CFO->label(),
                    'icon' => Department::FINANCE->icon(),
                    'agents' => [
                        AgentRole::FINANCE->value => self::formatAgent(AgentRole::FINANCE),
                        AgentRole::REPORTING->value => self::formatAgent(AgentRole::REPORTING),
                    ],
                ],
                Department::OPERATIONS->value => [
                    'label' => Department::OPERATIONS->label(),
                    'lead' => ExecutiveRole::COO->value,
                    'lead_label' => ExecutiveRole::COO->label(),
                    'icon' => Department::OPERATIONS->icon(),
                    'agents' => [
                        AgentRole::INVENTORY->value => self::formatAgent(AgentRole::INVENTORY),
                        AgentRole::PURCHASING->value => self::formatAgent(AgentRole::PURCHASING),
                        AgentRole::MARKETPLACE->value => self::formatAgent(AgentRole::MARKETPLACE),
                    ],
                ],
                Department::PEOPLE->value => [
                    'label' => Department::PEOPLE->label(),
                    'lead' => ExecutiveRole::HR_LEAD->value,
                    'lead_label' => ExecutiveRole::HR_LEAD->label(),
                    'icon' => Department::PEOPLE->icon(),
                    'agents' => [
                        AgentRole::HR->value => self::formatAgent(AgentRole::HR),
                    ],
                ],
            ],
            'offices' => [
                AiOffice::EXECUTIVE->value => [
                    'label' => AiOffice::EXECUTIVE->label(),
                    'tagline' => AiOffice::EXECUTIVE->tagline(),
                    'icon' => AiOffice::EXECUTIVE->icon(),
                    'leads' => [ExecutiveRole::CEO->label(), ExecutiveRole::CFO->label()],
                    'route' => AiOffice::EXECUTIVE->routeName(),
                ],
                AiOffice::OPERATIONS->value => [
                    'label' => AiOffice::OPERATIONS->label(),
                    'tagline' => AiOffice::OPERATIONS->tagline(),
                    'icon' => AiOffice::OPERATIONS->icon(),
                    'leads' => [ExecutiveRole::COO->label()],
                    'route' => AiOffice::OPERATIONS->routeName(),
                ],
                AiOffice::GROWTH->value => [
                    'label' => AiOffice::GROWTH->label(),
                    'tagline' => AiOffice::GROWTH->tagline(),
                    'icon' => AiOffice::GROWTH->icon(),
                    'leads' => [ExecutiveRole::CMO->label(), ExecutiveRole::SALES_DIRECTOR->label()],
                    'route' => AiOffice::GROWTH->routeName(),
                ],
            ],
            'executive_agents' => [
                AgentRole::BUSINESS->value => self::formatAgent(AgentRole::BUSINESS),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function formatAgent(AgentRole $agent): array
    {
        return [
            'id' => $agent->value,
            'name' => $agent->label(),
            'department' => $agent->department()->value,
            'lead' => $agent->executiveLead()->value,
            'description' => $agent->description(),
            'icon' => $agent->icon(),
        ];
    }

    /**
     * Get the defined business teams working within the unified virtual office.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getTeams(): array
    {
        return [
            'executive' => [
                'id' => 'executive',
                'name' => 'Executive Suite',
                'title' => 'Direksi & Pengambilan Keputusan Strategis',
                'icon' => 'crown',
                'color' => '#f59e0b',
                'badge' => 'Executive',
                'is_executive' => true,
                'leads' => [ExecutiveRole::CEO->value, ExecutiveRole::COO->value, ExecutiveRole::CFO->value],
                'lead_labels' => ['AI CEO', 'AI COO', 'AI CFO'],
                'agents' => [AgentRole::BUSINESS],
                'agent_slugs' => [AgentRole::BUSINESS->value],
                'room' => 'Executive Suite & Boardroom',
                'room_id' => 'executive',
                'scope' => 'Kesehatan bisnis menyeluruh, evaluasi KPI, mitigasi risiko utama, eskalasi anggaran, dan keputusan strategis.',
            ],
            'marketing' => [
                'id' => 'marketing',
                'name' => 'Tim Marketing',
                'title' => 'Pemasaran & Pertumbuhan Brand',
                'icon' => 'sparkles',
                'color' => '#8b5cf6',
                'badge' => 'Marketing Team',
                'is_executive' => false,
                'leads' => [ExecutiveRole::CMO->value],
                'lead_labels' => ['AI CMO'],
                'agents' => [AgentRole::MARKETING, AgentRole::CONTENT, AgentRole::SOCIAL_MEDIA],
                'agent_slugs' => [AgentRole::MARKETING->value, AgentRole::CONTENT->value, AgentRole::SOCIAL_MEDIA->value],
                'room' => 'Marketing & Creative Studio',
                'room_id' => 'marketing',
                'scope' => 'Strategi kampanye promosi, perumusan diskon berkala, pembuatan naskah copywriting, dan penjadwalan media sosial.',
            ],
            'sales' => [
                'id' => 'sales',
                'name' => 'Tim Sales',
                'title' => 'Penjualan & Retensi Pelanggan',
                'icon' => 'trending-up',
                'color' => '#10b981',
                'badge' => 'Sales Team',
                'is_executive' => false,
                'leads' => [ExecutiveRole::SALES_DIRECTOR->value],
                'lead_labels' => ['AI Sales Director'],
                'agents' => [AgentRole::SALES, AgentRole::CUSTOMER],
                'agent_slugs' => [AgentRole::SALES->value, AgentRole::CUSTOMER->value],
                'room' => 'Sales & CRM Command',
                'room_id' => 'sales',
                'scope' => 'Konversi prospek penjualan, follow-up piutang pelanggan, aktivasi pelanggan tidak aktif (dormant), dan relasi konsumen.',
            ],
            'operations' => [
                'id' => 'operations',
                'name' => 'Tim Operasional',
                'title' => 'Rantai Pasok & Multi-Kanal',
                'icon' => 'boxes',
                'color' => '#3b82f6',
                'badge' => 'Operations Team',
                'is_executive' => false,
                'leads' => [ExecutiveRole::COO->value],
                'lead_labels' => ['AI COO'],
                'agents' => [AgentRole::INVENTORY, AgentRole::PURCHASING, AgentRole::MARKETPLACE],
                'agent_slugs' => [AgentRole::INVENTORY->value, AgentRole::PURCHASING->value, AgentRole::MARKETPLACE->value],
                'room' => 'Operations & Logistics Bay',
                'room_id' => 'operations',
                'scope' => 'Pemantauan stok kritis gudang, persiapan purchase order (PO) supplier, dan sinkronisasi omnichannel marketplace.',
            ],
            'finance' => [
                'id' => 'finance',
                'name' => 'Tim Keuangan',
                'title' => 'Arus Kas, Laba Rugi & Audit',
                'icon' => 'wallet',
                'color' => '#06b6d4',
                'badge' => 'Finance Team',
                'is_executive' => false,
                'leads' => [ExecutiveRole::CFO->value],
                'lead_labels' => ['AI CFO'],
                'agents' => [AgentRole::FINANCE, AgentRole::REPORTING],
                'agent_slugs' => [AgentRole::FINANCE->value, AgentRole::REPORTING->value],
                'room' => 'Finance & Audit Chamber',
                'room_id' => 'finance',
                'scope' => 'Analisis arus kas harian, rekonsiliasi buku besar, evaluasi laba kotor, dan penyusunan laporan keuangan.',
            ],
            'people' => [
                'id' => 'people',
                'name' => 'Tim People & HR',
                'title' => 'Pengelolaan SDM & Shift',
                'icon' => 'users',
                'color' => '#ec4899',
                'badge' => 'People Team',
                'is_executive' => false,
                'leads' => [ExecutiveRole::HR_LEAD->value],
                'lead_labels' => ['AI HR Lead'],
                'agents' => [AgentRole::HR],
                'agent_slugs' => [AgentRole::HR->value],
                'room' => 'People & HR Hub',
                'room_id' => 'people',
                'scope' => 'Monitoring absensi staf, jadwal shift kasir, dan evaluasi produktivitas kerja tim fisik.',
            ],
        ];
    }

    /**
     * Resolve incoming business topic or task to the appropriate Team.
     * Executive tasks are handled by CEO, COO, or CFO. All other tasks are assigned to Teams.
     *
     * @return array{
     *     team: string,
     *     team_name: string,
     *     is_executive: bool,
     *     executive_lead: string,
     *     lead_labels: array<int, string>,
     *     agents: array<int, AgentRole>,
     *     agent_slugs: array<int, string>,
     *     primary_agent: AgentRole,
     *     room: string,
     *     room_id: string
     * }
     */
    public static function routeTopicToTeam(string $topic, ?string $preferredTeam = null): array
    {
        $teams = self::getTeams();

        // 1. If explicit preferred team provided and exists
        if ($preferredTeam !== null && isset($teams[$preferredTeam])) {
            $t = $teams[$preferredTeam];
            return [
                'team' => $preferredTeam,
                'team_name' => $t['name'],
                'is_executive' => $t['is_executive'],
                'executive_lead' => $t['leads'][0],
                'lead_labels' => $t['lead_labels'],
                'agents' => $t['agents'],
                'agent_slugs' => $t['agent_slugs'],
                'primary_agent' => $t['agents'][0],
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        $q = strtolower(trim($topic));

        // 2. Executive tasks (Company-wide diagnosis, strategic priorities, high-level governance)
        if (str_contains($q, 'kesehatan') || str_contains($q, 'diagnosa') || str_contains($q, 'ceo') || str_contains($q, 'coo') || str_contains($q, 'cfo') || str_contains($q, 'eksekutif') || str_contains($q, 'strategi') || str_contains($q, 'arah bisnis') || str_contains($q, 'anjlok') || str_contains($q, 'turun drastis') || str_contains($q, 'rugi besar') || str_contains($q, 'kenapa')) {
            $t = $teams['executive'];
            // In executive diagnosis, cross-functional agents can join
            return [
                'team' => 'executive',
                'team_name' => $t['name'],
                'is_executive' => true,
                'executive_lead' => ExecutiveRole::CEO->value,
                'lead_labels' => $t['lead_labels'],
                'agents' => [AgentRole::BUSINESS, AgentRole::SALES, AgentRole::INVENTORY, AgentRole::FINANCE],
                'agent_slugs' => [AgentRole::BUSINESS->value, AgentRole::SALES->value, AgentRole::INVENTORY->value, AgentRole::FINANCE->value],
                'primary_agent' => AgentRole::BUSINESS,
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        // 3. Marketing Team tasks
        if (str_contains($q, 'promo') || str_contains($q, 'diskon') || str_contains($q, 'iklan') || str_contains($q, 'kampanye') || str_contains($q, 'konten') || str_contains($q, 'caption') || str_contains($q, 'medsos') || str_contains($q, 'posting') || str_contains($q, 'sosial') || str_contains($q, 'instagram') || str_contains($q, 'tiktok') || str_contains($q, 'marketing')) {
            $t = $teams['marketing'];
            return [
                'team' => 'marketing',
                'team_name' => $t['name'],
                'is_executive' => false,
                'executive_lead' => $t['leads'][0],
                'lead_labels' => $t['lead_labels'],
                'agents' => $t['agents'],
                'agent_slugs' => $t['agent_slugs'],
                'primary_agent' => $t['agents'][0],
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        // 4. Operations Team tasks
        if (str_contains($q, 'stok') || str_contains($q, 'habis') || str_contains($q, 'gudang') || str_contains($q, 'reorder') || str_contains($q, 'rop') || str_contains($q, 'beli') || str_contains($q, 'supplier') || str_contains($q, 'pemasok') || str_contains($q, 'po') || str_contains($q, 'pengadaan') || str_contains($q, 'marketplace') || str_contains($q, 'shopee') || str_contains($q, 'tokopedia') || str_contains($q, 'operasional')) {
            $t = $teams['operations'];
            return [
                'team' => 'operations',
                'team_name' => $t['name'],
                'is_executive' => false,
                'executive_lead' => $t['leads'][0],
                'lead_labels' => $t['lead_labels'],
                'agents' => $t['agents'],
                'agent_slugs' => $t['agent_slugs'],
                'primary_agent' => $t['agents'][0],
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        // 5. Finance Team tasks
        if (str_contains($q, 'uang') || str_contains($q, 'kas') || str_contains($q, 'cashflow') || str_contains($q, 'profit') || str_contains($q, 'laba') || str_contains($q, 'biaya') || str_contains($q, 'rugi') || str_contains($q, 'laporan') || str_contains($q, 'rekap') || str_contains($q, 'keuangan') || str_contains($q, 'neraca') || str_contains($q, 'jurnal') || str_contains($q, 'pajak') || str_contains($q, 'audit')) {
            $t = $teams['finance'];
            return [
                'team' => 'finance',
                'team_name' => $t['name'],
                'is_executive' => false,
                'executive_lead' => $t['leads'][0],
                'lead_labels' => $t['lead_labels'],
                'agents' => $t['agents'],
                'agent_slugs' => $t['agent_slugs'],
                'primary_agent' => $t['agents'][0],
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        // 6. People & HR Team tasks
        if (str_contains($q, 'karyawan') || str_contains($q, 'absensi') || str_contains($q, 'staf') || str_contains($q, 'kasir') || str_contains($q, 'gaji') || str_contains($q, 'shift') || str_contains($q, 'sdm') || str_contains($q, 'hr')) {
            $t = $teams['people'];
            return [
                'team' => 'people',
                'team_name' => $t['name'],
                'is_executive' => false,
                'executive_lead' => $t['leads'][0],
                'lead_labels' => $t['lead_labels'],
                'agents' => $t['agents'],
                'agent_slugs' => $t['agent_slugs'],
                'primary_agent' => $t['agents'][0],
                'room' => $t['room'],
                'room_id' => $t['room_id'],
            ];
        }

        // 7. Default to Sales Team
        $t = $teams['sales'];
        return [
            'team' => 'sales',
            'team_name' => $t['name'],
            'is_executive' => false,
            'executive_lead' => $t['leads'][0],
            'lead_labels' => $t['lead_labels'],
            'agents' => $t['agents'],
            'agent_slugs' => $t['agent_slugs'],
            'primary_agent' => $t['agents'][0],
            'room' => $t['room'],
            'room_id' => $t['room_id'],
        ];
    }

    /**
     * Resolve which specialized agents should be summoned for a business goal or topic (legacy compatibility).
     *
     * @return array<int, AgentRole>
     */
    public static function routeTopicToAgents(string $topic): array
    {
        $routing = self::routeTopicToTeam($topic);
        return $routing['agents'];
    }
}

