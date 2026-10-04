<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentAvatar extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'ai_agent_avatars';

    protected $fillable = [
        'business_id',
        'agent_role',
        'custom_name',
        'preset',
        'suit_color',
        'skin_tone',
        'hair_color',
        'hair_style',
        'accessory',
        'avatar_icon',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Predefined archetype presets with balanced aesthetic styling.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getPresets(): array
    {
        return [
            'executive_male' => [
                'label' => 'Pria Eksekutif Formal',
                'description' => 'Jas formal midnight navy dengan potongan tajam dan jam tangan.',
                'suit_color' => '#0f172a',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'none',
                'icon' => 'user',
            ],
            'executive_female' => [
                'label' => 'Wanita Eksekutif Formal',
                'description' => 'Blazer elegan plum/charcoal dengan gaya profesional modern.',
                'suit_color' => '#1e1b4b',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#292524',
                'hair_style' => 'long_bob',
                'accessory' => 'glasses',
                'icon' => 'user-check',
            ],
            'cyber_innovator' => [
                'label' => 'Cyber Innovator / Tech',
                'description' => 'Jaket cyber violet dengan glowing visor neon cyan & headset neural.',
                'suit_color' => '#6d28d9',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#06b6d4',
                'hair_style' => 'cyber_fade',
                'accessory' => 'cyber_visor',
                'icon' => 'cpu',
            ],
            'creative_director' => [
                'label' => 'Kreatif Studio Modern',
                'description' => 'Turtleneck terracotta hangat dengan kacamata berbingkai tipis.',
                'suit_color' => '#c2410c',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#451a03',
                'hair_style' => 'pixie',
                'accessory' => 'glasses',
                'icon' => 'sparkles',
            ],
            'operations_tactical' => [
                'label' => 'Operasional & Taktikal',
                'description' => 'Rompi navy industri dengan communicator earpiece dan tablet.',
                'suit_color' => '#1e3a8a',
                'skin_tone' => '#fcd34d',
                'hair_color' => '#1c1917',
                'hair_style' => 'buzz',
                'accessory' => 'headset',
                'icon' => 'boxes',
            ],
            'android_core' => [
                'label' => 'AI Android Hologram',
                'description' => 'Chassis titanium putih mutiara dengan glowing cyan neural circuits.',
                'suit_color' => '#0284c7',
                'skin_tone' => '#cbd5e1',
                'hair_color' => '#38bdf8',
                'hair_style' => 'cyber_fade',
                'accessory' => 'cyber_visor',
                'icon' => 'bot',
            ],
            'financial_strategist' => [
                'label' => 'Analis Keuangan Presisi',
                'description' => 'Setelan jas onyx gelap dengan kacamata arsitektural.',
                'suit_color' => '#18181b',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#27272a',
                'hair_style' => 'classic',
                'accessory' => 'glasses',
                'icon' => 'coins',
            ],
            'growth_hunter' => [
                'label' => 'Sales & Growth Specialist',
                'description' => 'Blazer hijau zamrud energik dengan headset nirkabel ultra-ringan.',
                'suit_color' => '#047857',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'headset',
                'icon' => 'trending-up',
            ],
        ];
    }

    /**
     * Default avatar configurations per agent role.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefaultAvatars(): array
    {
        return [
            'ceo' => [
                'role' => 'ceo',
                'default_name' => 'Aditya Pratama',
                'title' => 'Chief Executive Officer',
                'preset' => 'executive_male',
                'suit_color' => '#0f172a',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'none',
                'icon' => 'crown',
            ],
            'cfo' => [
                'role' => 'cfo',
                'default_name' => 'Hendrawan',
                'title' => 'Chief Financial Officer',
                'preset' => 'financial_strategist',
                'suit_color' => '#18181b',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#27272a',
                'hair_style' => 'classic',
                'accessory' => 'glasses',
                'icon' => 'coins',
            ],
            'coo' => [
                'role' => 'coo',
                'default_name' => 'Bambang Wijaya',
                'title' => 'Chief Operating Officer',
                'preset' => 'operations_tactical',
                'suit_color' => '#1e3a8a',
                'skin_tone' => '#fcd34d',
                'hair_color' => '#1c1917',
                'hair_style' => 'buzz',
                'accessory' => 'headset',
                'icon' => 'boxes',
            ],
            'cmo' => [
                'role' => 'cmo',
                'default_name' => 'Nadia Safitri',
                'title' => 'Chief Marketing Officer',
                'preset' => 'creative_director',
                'suit_color' => '#7c3aed',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#292524',
                'hair_style' => 'long_bob',
                'accessory' => 'glasses',
                'icon' => 'sparkles',
            ],
            'sales_director' => [
                'role' => 'sales_director',
                'default_name' => 'Faisal Akbar',
                'title' => 'Commercial & Sales Director',
                'preset' => 'growth_hunter',
                'suit_color' => '#047857',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'headset',
                'icon' => 'trending-up',
            ],
            'hr_lead' => [
                'role' => 'hr_lead',
                'default_name' => 'Dewi Anggraini',
                'title' => 'People & Culture Lead',
                'preset' => 'executive_female',
                'suit_color' => '#be185d',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#27272a',
                'hair_style' => 'long_bob',
                'accessory' => 'none',
                'icon' => 'users',
            ],
            'inventory' => [
                'role' => 'inventory',
                'default_name' => 'Dimas',
                'title' => 'Inventory & Warehouse Specialist',
                'preset' => 'operations_tactical',
                'suit_color' => '#1e3a8a',
                'skin_tone' => '#fcd34d',
                'hair_color' => '#1c1917',
                'hair_style' => 'buzz',
                'accessory' => 'headset',
                'icon' => 'package',
            ],
            'purchasing' => [
                'role' => 'purchasing',
                'default_name' => 'Siti',
                'title' => 'Procurement & PO Analyst',
                'preset' => 'financial_strategist',
                'suit_color' => '#0f766e',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#292524',
                'hair_style' => 'long_bob',
                'accessory' => 'glasses',
                'icon' => 'shopping-cart',
            ],
            'marketplace' => [
                'role' => 'marketplace',
                'default_name' => 'Kevin',
                'title' => 'Multi-Channel Marketplace Specialist',
                'preset' => 'cyber_innovator',
                'suit_color' => '#ea580c',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#18181b',
                'hair_style' => 'cyber_fade',
                'accessory' => 'cyber_visor',
                'icon' => 'store',
            ],
            'sales' => [
                'role' => 'sales',
                'default_name' => 'Rifky',
                'title' => 'Revenue & Sales Hunter',
                'preset' => 'growth_hunter',
                'suit_color' => '#047857',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'headset',
                'icon' => 'trending-up',
            ],
            'customer' => [
                'role' => 'customer',
                'default_name' => 'Ayu',
                'title' => 'CRM & Customer Retention',
                'preset' => 'executive_female',
                'suit_color' => '#0284c7',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#27272a',
                'hair_style' => 'long_bob',
                'accessory' => 'headset',
                'icon' => 'user-check',
            ],
            'finance' => [
                'role' => 'finance',
                'default_name' => 'Lukman',
                'title' => 'Cashflow & Costing Analyst',
                'preset' => 'financial_strategist',
                'suit_color' => '#1e293b',
                'skin_tone' => '#fcd34d',
                'hair_color' => '#1c1917',
                'hair_style' => 'classic',
                'accessory' => 'glasses',
                'icon' => 'coins',
            ],
            'reporting' => [
                'role' => 'reporting',
                'default_name' => 'Budi',
                'title' => 'BI & Executive Reporting Specialist',
                'preset' => 'financial_strategist',
                'suit_color' => '#334155',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'glasses',
                'icon' => 'file-text',
            ],
            'marketing' => [
                'role' => 'marketing',
                'default_name' => 'Laras',
                'title' => 'Campaign & Growth Strategist',
                'preset' => 'creative_director',
                'suit_color' => '#4f46e5',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#292524',
                'hair_style' => 'long_bob',
                'accessory' => 'none',
                'icon' => 'target',
            ],
            'content' => [
                'role' => 'content',
                'default_name' => 'Zahra',
                'title' => 'Creative Copywriter & Scriptwriter',
                'preset' => 'creative_director',
                'suit_color' => '#be123c',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#18181b',
                'hair_style' => 'long_bob',
                'accessory' => 'glasses',
                'icon' => 'pen-tool',
            ],
            'social_media' => [
                'role' => 'social_media',
                'default_name' => 'Farhan',
                'title' => 'Social Media & Engagement Specialist',
                'preset' => 'cyber_innovator',
                'suit_color' => '#7c3aed',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#06b6d4',
                'hair_style' => 'cyber_fade',
                'accessory' => 'cyber_visor',
                'icon' => 'share-2',
            ],
            'hr' => [
                'role' => 'hr',
                'default_name' => 'Mega',
                'title' => 'People Operations Analyst',
                'preset' => 'executive_female',
                'suit_color' => '#9333ea',
                'skin_tone' => '#fed7aa',
                'hair_color' => '#27272a',
                'hair_style' => 'classic',
                'accessory' => 'none',
                'icon' => 'users',
            ],
            'business' => [
                'role' => 'business',
                'default_name' => 'Aditya Pratama',
                'title' => 'Chief Business Analyst',
                'preset' => 'executive_male',
                'suit_color' => '#0f172a',
                'skin_tone' => '#fbcfe8',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'none',
                'icon' => 'line-chart',
            ],
            'business_analyst' => [
                'role' => 'business_analyst',
                'default_name' => 'Tariq',
                'title' => 'Strategic Business Analyst',
                'preset' => 'executive_male',
                'suit_color' => '#1e293b',
                'skin_tone' => '#fcd34d',
                'hair_color' => '#18181b',
                'hair_style' => 'classic',
                'accessory' => 'glasses',
                'icon' => 'bar-chart-2',
            ],
        ];
    }

    /**
     * Get merged avatar configurations for a given business ID.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAvatarsForBusiness(string $businessId): array
    {
        $defaults = self::getDefaultAvatars();
        $customs = self::where('business_id', $businessId)->get()->keyBy('agent_role');

        $result = [];
        foreach ($defaults as $role => $config) {
            if ($custom = $customs->get($role)) {
                $result[$role] = array_merge($config, [
                    'name' => $custom->custom_name ?: $config['default_name'],
                    'custom_name' => $custom->custom_name,
                    'preset' => $custom->preset,
                    'suit_color' => $custom->suit_color,
                    'skin_tone' => $custom->skin_tone,
                    'hair_color' => $custom->hair_color,
                    'hair_style' => $custom->hair_style,
                    'accessory' => $custom->accessory,
                    'is_customized' => true,
                ]);
            } else {
                $result[$role] = array_merge($config, [
                    'name' => $config['default_name'],
                    'custom_name' => null,
                    'is_customized' => false,
                ]);
            }
        }

        return $result;
    }

    /**
     * Update or create avatar customization for an agent in a business.
     *
     * @param array<string, mixed> $data
     */
    public static function saveAvatar(string $businessId, string $agentRole, array $data): self
    {
        return self::updateOrCreate(
            [
                'business_id' => $businessId,
                'agent_role' => $agentRole,
            ],
            [
                'custom_name' => $data['custom_name'] ?? $data['name'] ?? null,
                'preset' => $data['preset'] ?? 'executive_male',
                'suit_color' => $data['suit_color'] ?? '#0f172a',
                'skin_tone' => $data['skin_tone'] ?? '#fbcfe8',
                'hair_color' => $data['hair_color'] ?? '#18181b',
                'hair_style' => $data['hair_style'] ?? 'classic',
                'accessory' => $data['accessory'] ?? 'none',
                'avatar_icon' => $data['avatar_icon'] ?? null,
            ]
        );
    }
}
