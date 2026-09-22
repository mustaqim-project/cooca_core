$targets = @(
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
)
$pairs = @(
 'text-[8.5px];text-xs',
 'text-[8px];text-xs',
 'text-[9px] sm:text-[10px];text-[11px] sm:text-xs',
 'text-[10px] sm:text-[11px];text-[11px] sm:text-xs',
 'text-[10px] sm:text-xs;text-[11px] sm:text-xs',
 'text-[9px];text-xs',
 'text-[10px];text-xs',
 'text-[11.5px];text-xs',
 'font-mono text-sm focus:border-[#007AFF];font-mono text-[16px] sm:text-sm focus:border-[#007AFF]',
 'rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF];rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] sm:text-sm focus:border-[#007AFF]',
 'rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs placeholder-[#6E6E73]/60;rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] sm:text-sm placeholder-[#6E6E73]/60'
)
foreach ($rel in $targets) {
  $f = 'c:\laragon\www\cooca_core\' + $rel
  if (!(Test-Path $f)) { Write-Output ("MISSING: " + $rel); continue }
  $c = Get-Content -Path $f -Raw -Encoding UTF8
  $orig = $c
  foreach ($pp in $pairs) {
    $bits = $pp.Split(';')
    $c = $c.Replace($bits[0], $bits[1])
  }
  if ($c -ne $orig) {
    [IO.File]::WriteAllText($f, $c, 'utf-8')
    Write-Output ("UPDATED: " + $rel)
  } else {
    Write-Output ("NOCHANGE: " + $rel)
  }
}