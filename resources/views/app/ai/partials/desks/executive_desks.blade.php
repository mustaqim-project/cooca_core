{{-- Executive Office 2D Isometric Desks --}}
<!-- CEO Desk -->
<g transform="translate(500, 180)" class="cursor-pointer group" @click="inspectAgent('ceo')">
    <ellipse cx="0" cy="0" rx="42" ry="22" fill="#2d1e18" stroke="#f59e0b" stroke-width="1.8" />
    <polygon points="-16,-26 0,-16 0,-8 -16,-18" fill="#0f172a" stroke="#64748b" stroke-width="1" />
    <polygon points="0,-16 16,-26 16,-18 0,-8" fill="#0f172a" stroke="#64748b" stroke-width="1" />
    <polygon points="-14,-24 -2,-16 -2,-10 -14,-18" fill="#10b981" opacity="0.85" />
    <polygon points="2,-16 14,-24 14,-18 2,-10" fill="#10b981" opacity="0.85" />
    <circle cx="0" cy="18" r="11" fill="#0f172a" stroke="#eab308" stroke-width="1.2" />
    <circle cx="0" cy="12" r="7.5" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <line x1="-5" y1="13" x2="-2" y2="4" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
    <line x1="5" y1="13" x2="2" y2="4" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
    <!-- Label -->
    <rect x="-85" y="32" width="170" height="22" rx="7" fill="#090d16" stroke="rgba(245,158,11,0.4)" stroke-width="1.2" />
    <circle cx="-70" cy="43" r="3.5" fill="#10b981" />
    <text x="5" y="47" fill="#fef08a" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI CEO // AI Chief Executive Officer</text>
</g>

<!-- CFO Desk -->
<g transform="translate(280, 220)" class="cursor-pointer group" @click="inspectAgent('cfo')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-80" y="30" width="160" height="22" rx="7" fill="#090d16" stroke="rgba(56,189,248,0.3)" stroke-width="1" />
    <circle cx="-66" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI CFO // AI Chief Financial Officer</text>
</g>

<!-- Business Analyst Desk -->
<g transform="translate(720, 220)" class="cursor-pointer group" @click="inspectAgent('business_analyst')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-75" y="30" width="150" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-62" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Business Agent // AI Business Analyst</text>
</g>

<!-- Finance Officer Desk -->
<g transform="translate(280, 370)" class="cursor-pointer group" @click="inspectAgent('finance')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-70" y="30" width="140" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-57" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Finance Agent // AI Finance Officer</text>
</g>

<!-- Reporting Specialist Desk -->
<g transform="translate(720, 370)" class="cursor-pointer group" @click="inspectAgent('reporting')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-75" y="30" width="150" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-62" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Reporting Agent // AI Reporting Specialist</text>
</g>
