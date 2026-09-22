import re, glob, os
base = r'c:/laragon/www/cooca_core/resources/views'
targets = ['landing.blade.php', 'layouts/public_marketing.blade.php', 'public/blog/index.blade.php', 'public/blog/show.blade.php', 'public/calculators/index.blade.php', 'public/calculators/bep.blade.php', 'public/calculators/gaji_karyawan.blade.php', 'public/calculators/harga_jual.blade.php', 'public/calculators/hpp.blade.php', 'public/calculators/laba_bersih.blade.php', 'public/calculators/omzet_harian.blade.php', 'public/calculators/pph_final.blade.php', 'public/calculators/simulasi_what_if.blade.php', 'public/contact/index.blade.php', 'public/discovery/index.blade.php', 'public/marketplace/index.blade.php', 'public/marketplace/search.blade.php', 'public/solutions/show.blade.php', 'public/templates/index.blade.php', 'public/templates/show.blade.php']
emoji_pat = re.compile('[\U0001F300-\U0001FAFF\U00002600-\U000027BF\U0000FE0F\U00002190-\U000021FF]')
tiny_pat = re.compile(r'text-\[(?:6|7|8|9|10)px\]')
pill_pat = re.compile(r'text-\[(?:10|11)px\].*?rounded-full|px-3\.5 py-1\.5 rounded-full|px-3 py-1 rounded-full|px-2\.5 py-1 rounded-full')
for rel in targets:
    p = os.path.join(base, rel.replace('/', os.sep))
    if not os.path.exists(p):
        print('MISSING', rel); continue
    lines = open(p, encoding='utf-8').read().splitlines()
    emos, tiny, pills, inputs = [], [], [], []
    for i, ln in enumerate(lines, 1):
        if emoji_pat.search(ln): emos.append((i, ln.strip()[:110]))
        if tiny_pat.search(ln): tiny.append((i, ln.strip()[:110]))
        if pill_pat.search(ln): pills.append((i, ln.strip()[:110]))
        if re.search(r'<input[^>]*?(?:text|tel|email|number|search)"[^>]*>', ln) and 'text-[16px]' not in ln:
            if 'class="' in ln and ('text-sm' in ln or 'text-xs' in ln): inputs.append((i, ln.strip()[:110]))
    flag = ''
    if emos: flag += ' EMOJI=%d' % len(emos)
    if tiny: flag += ' TINY=%d' % len(tiny)
    if pills: flag += ' PILLS=%d' % len(pills)
    if inputs: flag += ' INPUT<%d' % len(inputs)
    print('%-42s%s' % (rel, flag or ' OK'))
