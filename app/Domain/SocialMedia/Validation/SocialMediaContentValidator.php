<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Validation;

class SocialMediaContentValidator
{
    /**
     * COOCA Business Rule: Maksimal 5 hashtag unik per postingan / channel.
     */
    public const MAX_COOCA_HASHTAGS = 5;

    /**
     * Batas karakter caption resmi per saluran media sosial.
     */
    public const CAPTION_LIMITS = [
        'facebook'  => 63206,
        'instagram' => 2200,
        'threads'   => 500,
        'tiktok'    => 2200,
    ];

    /**
     * Ekstrak dan normalisasi hashtag dari caption teks (case-insensitive deduplication).
     *
     * @return list<string> Daftar hashtag unik ternormalisasi (lowercase tanpa tanda #)
     */
    public function extractHashtags(string $caption): array
    {
        if (trim($caption) === '') {
            return [];
        }

        preg_match_all('/#([a-zA-Z0-9_\x{0080}-\x{FFFF}]+)/u', $caption, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $normalized = [];
        foreach ($matches[1] as $tag) {
            $lower = mb_strtolower(trim($tag), 'UTF-8');
            if ($lower !== '' && ! in_array($lower, $normalized, true)) {
                $normalized[] = $lower;
            }
        }

        return $normalized;
    }

    /**
     * Hitung total hashtag unik.
     */
    public function countUniqueHashtags(string $caption): int
    {
        return count($this->extractHashtags($caption));
    }

    /**
     * Daftar tipe konten yang didukung secara resmi per saluran.
     */
    public const SUPPORTED_CONTENT_TYPES = [
        'facebook'  => ['feed', 'photo', 'video', 'reel'],
        'instagram' => ['photo', 'carousel', 'reel', 'story'],
        'threads'   => ['text', 'image', 'video'],
        'tiktok'    => ['video', 'photo'],
    ];

    /**
     * Cek apakah tipe konten didukung oleh saluran.
     */
    public function isContentTypeSupported(string $channel, string $contentType): bool
    {
        $channel = strtolower(trim($channel));
        $contentType = strtolower(trim($contentType));

        $allowed = self::SUPPORTED_CONTENT_TYPES[$channel] ?? [];

        return in_array($contentType, $allowed, true);
    }

    /**
     * Validasi aturan hashtag COOCA (Maksimal 5 hashtag unik per post).
     *
     * @return array{is_valid: bool, valid: bool, count: int, max: int, tags: list<string>, hashtags: list<string>, error: string|null}
     */
    public function validateHashtags(string $caption): array
    {
        $tags = $this->extractHashtags($caption);
        $count = count($tags);
        $withHash = array_map(fn ($t) => '#' . $t, $tags);

        if ($count > self::MAX_COOCA_HASHTAGS) {
            $tagList = implode(', ', $withHash);

            return [
                'is_valid' => false,
                'valid'    => false,
                'count'    => $count,
                'max'      => self::MAX_COOCA_HASHTAGS,
                'tags'     => $tags,
                'hashtags' => $withHash,
                'error'    => "Jumlah hashtag ({$count}) melebihi aturan maksimal COOCA (" . self::MAX_COOCA_HASHTAGS . " hashtag per postingan). Hashtag ditemukan: {$tagList}",
            ];
        }

        return [
            'is_valid' => true,
            'valid'    => true,
            'count'    => $count,
            'max'      => self::MAX_COOCA_HASHTAGS,
            'tags'     => $tags,
            'hashtags' => $withHash,
            'error'    => null,
        ];
    }

    /**
     * Validasi konten postingan untuk saluran dan tipe konten tertentu.
     *
     * @param  string  $channel      'facebook', 'instagram', 'threads', 'tiktok'
     * @param  string  $contentType  'feed', 'photo', 'video', 'reel', 'story', 'carousel', 'text'
     * @param  string  $caption      Teks caption
     * @param  list<array<string, mixed>>  $mediaItems  Daftar berkas media
     * @return array{valid: bool, is_valid: bool, errors: list<string>, warnings: list<string>}
     */
    public function validateForChannel(
        string $channel,
        string $contentType,
        string $caption,
        array $mediaItems = []
    ): array {
        $errors = [];
        $warnings = [];
        $channel = strtolower($channel);
        $contentType = strtolower($contentType);

        // 1. Validasi Hashtag COOCA Rule (Maksimal 5 Hashtag Unik)
        $uniqueHashtagCount = $this->countUniqueHashtags($caption);
        if ($uniqueHashtagCount > self::MAX_COOCA_HASHTAGS) {
            $errors[] = "Jumlah hashtag ({$uniqueHashtagCount}) melebihi aturan maksimal COOCA (" . self::MAX_COOCA_HASHTAGS . " hashtag per postingan).";
        }

        // 2. Validasi Batas Panjang Karakter Caption
        $maxChars = self::CAPTION_LIMITS[$channel] ?? 2200;
        $captionLength = mb_strlen($caption, 'UTF-8');

        if ($captionLength > $maxChars) {
            $errors[] = "Panjang caption ({$captionLength} karakter) melebihi batas resmi {$channel} (maks. {$maxChars} karakter).";
        }

        if ($channel === 'facebook' && $captionLength > 5000) {
            $warnings[] = 'Rekomendasi COOCA: Caption Facebook sebaiknya di bawah 5.000 karakter agar tingkat keterlibatan (engagement) optimal.';
        }

        // 3. Validasi Berkas Media per Content Type
        $mediaCount = count($mediaItems);

        if ($contentType === 'carousel') {
            if ($mediaCount < 2) {
                $errors[] = 'Instagram Carousel membutuhkan minimal 2 berkas media (foto/video).';
            }
            if ($mediaCount > 10) {
                $errors[] = 'Instagram Carousel hanya mendukung maksimal 10 berkas media sesuai batas API Meta.';
            }
        } elseif ($contentType === 'photo') {
            if ($channel === 'tiktok') {
                if ($mediaCount < 2) {
                    $errors[] = 'TikTok Photo Mode membutuhkan minimal 2 gambar.';
                }
                if ($mediaCount > 35) {
                    $errors[] = 'TikTok Photo Mode mendukung maksimal 35 gambar.';
                }
            } else {
                if ($mediaCount === 0 && $channel === 'instagram') {
                    $errors[] = 'Instagram mewajibkan minimal 1 media foto.';
                }
            }
        } elseif ($contentType === 'video' || $contentType === 'reel') {
            if ($mediaCount === 0) {
                $errors[] = "Format {$contentType} pada {$channel} mewajibkan berkas video.";
            } elseif ($mediaCount > 1) {
                $warnings[] = "Format {$contentType} hanya mempublikasikan video pertama. Berkas lainnya akan diabaikan.";
            }
        }

        return [
            'valid'    => empty($errors),
            'is_valid' => empty($errors),
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }
}
