<?php
// Temp helper: bulk Tailwind token normalization for Apple HIG Bento compliance.
// Deletes (no BOM, UTF-8 safe). Remove after use.
$base = __DIR__ . DIRECTORY_SEPARATOR;
$targets = [
    'resources/views/layouts/public_marketing.blade.php',
    'resources/views/landing.blade.php',
    'resources/views/public/blog/index.blade.php',
    'resources/views/public/blog/show.blade.php',
    'resources/views/public/calculators/bep.blade.php',
    'resources/views/public/calculators/gaji_karyawan.blade.php',
    'resources/views/public/calculators/harga_jual.blade.php',
    'resources/views/public/calculators/hpp.blade.php',
    'resources/views/public/calculators/laba_bersih.blade.php',
    'resources/views/public/calculators/omzet_harian.blade.php',
    'resources/views/public/calculators/pph_final.blade.php',
    'resources/views/public/calculators/simulasi_what_if.blade.php',
    'resources/views/public/calculators/index.blade.php',
    'resources/views/public/contact/index.blade.php',
    'resources/views/public/discovery/index.blade.php',
    'resources/views/public/marketplace/index.blade.php',
    'resources/views/public/solutions/show.blade.php',
    'resources/views/public/templates/index.blade.php',
    'resources/views/public/templates/show.blade.php'
];
// Longest/most-specific patterns FIRST so standalone text-[10px] never eats longer combos.
$pairs = [
    ['text-[8.5px]', 'text-xs'],
    ['text-[8px]', 'text-xs'],
    ['text-[9px] sm:text-[10px]', 'text-[11px] sm:text-xs'],
    ['text-[10px] sm:text-[11px]', 'text-[11px] sm:text-xs'],
    ['text-[10px] sm:text-xs', 'text-[11px] sm:text-xs'],
    ['text-[10.5px] sm:text-xs', 'text-xs'],
    ['text-[9px]', 'text-xs'],
    ['text-[10px]', 'text-xs'],
    ['text-[11.5px]', 'text-xs'],
    ['font-mono text-sm focus:border-[#007AFF]', 'font-mono text-[16px] sm:text-sm focus:border-[#007AFF]'],
    ['rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF]', 'rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] sm:text-sm focus:border-[#007AFF]'],
    ['rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs placeholder-[#6E6E73]/60', 'rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] sm:text-sm placeholder-[#6E6E73]/60']
];
$global = 0;
foreach ($targets as $rel) {
    $f = $base . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (!is_file($f)) {
        fwrite(STDERR, "MISSING: " . $rel . "\n");
        continue;
    }
    $c = file_get_contents($f);
    $o = $c;
    $n = 0;
    foreach ($pairs as $p) {
        $cnt = substr_count($c, $p[0]);
        if ($cnt > 0) {
            $c = str_replace($p[0], $p[1], $c);
            $n += $cnt;
        }
    }
    if ($c !== $o) {
        file_put_contents($f, $c);
        echo "UPDATED ($n): " . $rel . "\n";
        $global += $n;
    } else {
        echo "NOCHANGE: " . $rel . "\n";
    }
}
echo "TOTAL REPLACEMENTS: " . $global . "\n";