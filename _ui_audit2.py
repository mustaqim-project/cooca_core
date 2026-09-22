import re, os
base = r'c:/laragon/www/cooca_core/resources/views'
targets = ['layouts/public_marketing.blade.php', 'public/blog/index.blade.php', 'public/blog/show.blade.php', 'public/calculators/index.blade.php', 'public/calculators/bep.blade.php', 'public/calculators/hpp.blade.php', 'public/contact/index.blade.php', 'public/discovery/index.blade.php', 'public/solutions/show.blade.php', 'public/templates/index.blade.php', 'landing.blade.php']
emoji_pat = re.compile('[\U0001F300-\U0001FAFF\U00002600-\U000027BF\U0000FE0F\U00002190-\U000021FF]')
for rel in targets:
    p = os.path.join(base, rel)
    lines = open(p, encoding='utf-8').read().splitlines()
    print('=== ' + rel + ' ===')
    for i, ln in enumerate(lines, 1):
        if emoji_pat.search(ln):
            print('  EMOJI L%d: %s' % (i, ln.strip()[:150]))
        if ('rounded-full' in ln and ('inline-flex items-center gap-' in ln or 'px-3.5 py-1.5' in ln) and 'bg-[' in ln):
            print('  PILL   L%d: %s' % (i, ln.strip()[:150]))
