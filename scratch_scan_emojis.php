<?php
$files = [
    'resources/views/landing.blade.php',
    'resources/views/layouts/public_marketing.blade.php'
];

foreach (glob('resources/views/public/**/*.blade.php') as $f) {
    $files[] = $f;
}

$emojiRegex = '/[\x{1F300}-\x{1F5FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F270}\x{2388}\x{2B05}\x{2B06}\x{2B07}\x{2B1B}\x{2B1C}\x{2B50}\x{2B55}\x{261D}\x{26F9}\x{26FA}\x{26FD}\x{0266B}\x{266A}\x{2669}\x{266C}]/u';

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    if (preg_match_all($emojiRegex, $content, $matches, PREG_OFFSET_CAPTURE)) {
        echo "Found emojis in $file:\n";
        foreach ($matches[0] as $m) {
            $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
            echo "  Line $line: " . bin2hex($m[0]) . " (" . $m[0] . ")\n";
        }
    }
}
echo "Scan complete.\n";
