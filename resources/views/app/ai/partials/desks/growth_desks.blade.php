{{-- Growth Studio 2D Isometric Desks --}}
<!-- CMO Desk -->
<g transform="translate(500, 180)" class="cursor-pointer group" @click="inspectAgent('cmo')">
    <polygon points="0,-22 44,0 0,22 -44,0" fill="#1e293b" stroke="#a855f7" stroke-width="1.8" />
    <polygon points="-14,-28 14,-14 14,-8 -14,-22" fill="#10b981" opacity="0.85" />
    <circle cx="0" cy="18" r="11" fill="#0f172a" stroke="#a855f7" stroke-width="1.2" />
    <circle cx="0" cy="12" r="7.5" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <line x1="-5" y1="13" x2="-2" y2="4" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
    <line x1="5" y1="13" x2="2" y2="4" stroke="#ffffff" stroke-width="2" stroke-linecap="round" />
    <rect x="-85" y="32" width="170" height="22" rx="7" fill="#090d16" stroke="rgba(168,85,247,0.4)" stroke-width="1.2" />
    <circle cx="-70" cy="43" r="3.5" fill="#10b981" />
    <text x="5" y="47" fill="#e9d5ff" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI Chief Marketing Officer</text>
</g>

<!-- Sales Director Desk -->
<g transform="translate(500, 380)" class="cursor-pointer group" @click="inspectAgent('sales_director')">
    <polygon points="0,-20 40,0 0,20 -40,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.6" />
    <circle cx="0" cy="18" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="12" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-65" y="32" width="130" height="22" rx="7" fill="#090d16" stroke="rgba(56,189,248,0.3)" stroke-width="1" />
    <circle cx="-52" cy="43" r="3.5" fill="#38bdf8" />
    <text x="5" y="47" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Sales Director</text>
</g>

<!-- Marketing Agent Desk -->
<g transform="translate(260, 220)" class="cursor-pointer group" @click="inspectAgent('marketing')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-65" y="30" width="130" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-52" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Marketing Agent</text>
</g>

<!-- Content Agent Desk -->
<g transform="translate(740, 220)" class="cursor-pointer group" @click="inspectAgent('content')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-60" y="30" width="120" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-47" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Content Agent</text>
</g>

<!-- Social Media Agent Desk -->
<g transform="translate(260, 350)" class="cursor-pointer group" @click="inspectAgent('social_media')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-70" y="30" width="140" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-57" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Social Media Agent</text>
</g>

<!-- Sales Agent Desk -->
<g transform="translate(740, 350)" class="cursor-pointer group" @click="inspectAgent('sales')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-55" y="30" width="110" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-42" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Sales Agent</text>
</g>

<!-- Customer Agent Desk -->
<g transform="translate(500, 450)" class="cursor-pointer group" @click="inspectAgent('customer')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <polygon points="-10,-24 10,-14 10,-8 -10,-18" fill="#10b981" opacity="0.8" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-65" y="30" width="130" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-52" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Customer Agent</text>
</g>
