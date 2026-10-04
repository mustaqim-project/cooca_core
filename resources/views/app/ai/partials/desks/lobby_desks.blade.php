{{-- Lobby HQ Campus 2D Isometric Desks --}}
<!-- Executive Wing Representative -->
<g transform="translate(500, 180)" class="cursor-pointer group" @click="inspectAgent('ceo')">
    <ellipse cx="0" cy="0" rx="40" ry="20" fill="#2d1e18" stroke="#f59e0b" stroke-width="1.8" />
    <circle cx="0" cy="18" r="11" fill="#0f172a" stroke="#eab308" stroke-width="1.2" />
    <circle cx="0" cy="12" r="7.5" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-85" y="32" width="170" height="22" rx="7" fill="#090d16" stroke="rgba(245,158,11,0.4)" stroke-width="1.2" />
    <circle cx="-70" cy="43" r="3.5" fill="#10b981" />
    <text x="5" y="47" fill="#fef08a" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI Chief Executive Officer</text>
</g>

<!-- Operations Wing Representative -->
<g transform="translate(280, 260)" class="cursor-pointer group" @click="inspectAgent('coo')">
    <polygon points="0,-20 40,0 0,20 -40,0" fill="#1e293b" stroke="#f59e0b" stroke-width="1.6" />
    <circle cx="0" cy="18" r="10" fill="#0f172a" stroke="#f59e0b" stroke-width="1" />
    <circle cx="0" cy="12" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-85" y="32" width="170" height="22" rx="7" fill="#090d16" stroke="rgba(245,158,11,0.3)" stroke-width="1" />
    <circle cx="-70" cy="43" r="3.5" fill="#10b981" />
    <text x="5" y="47" fill="#fde68a" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI Chief Operating Officer</text>
</g>

<!-- Growth Wing Representative -->
<g transform="translate(720, 260)" class="cursor-pointer group" @click="inspectAgent('cmo')">
    <polygon points="0,-20 40,0 0,20 -40,0" fill="#1e293b" stroke="#a855f7" stroke-width="1.6" />
    <circle cx="0" cy="18" r="10" fill="#0f172a" stroke="#a855f7" stroke-width="1" />
    <circle cx="0" cy="12" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-85" y="32" width="170" height="22" rx="7" fill="#090d16" stroke="rgba(168,85,247,0.3)" stroke-width="1" />
    <circle cx="-70" cy="43" r="3.5" fill="#10b981" />
    <text x="5" y="47" fill="#e9d5ff" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI Chief Marketing Officer</text>
</g>

<!-- Finance Representative -->
<g transform="translate(320, 390)" class="cursor-pointer group" @click="inspectAgent('finance')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-70" y="30" width="140" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-57" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">AI Finance Officer</text>
</g>

<!-- Marketing Representative -->
<g transform="translate(680, 390)" class="cursor-pointer group" @click="inspectAgent('marketing')">
    <polygon points="0,-18 36,0 0,18 -36,0" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
    <circle cx="0" cy="16" r="10" fill="#0f172a" stroke="#38bdf8" stroke-width="1" />
    <circle cx="0" cy="11" r="7" fill="#fbcfe8" stroke="#ffffff" stroke-width="1.5" />
    <rect x="-65" y="30" width="130" height="22" rx="7" fill="#090d16" stroke="rgba(255,255,255,0.15)" stroke-width="1" />
    <circle cx="-52" cy="41" r="3.5" fill="#38bdf8" />
    <text x="5" y="45" fill="#f8fafc" font-size="10" font-family="Inter, sans-serif" font-weight="bold" text-anchor="middle">Marketing Agent</text>
</g>
