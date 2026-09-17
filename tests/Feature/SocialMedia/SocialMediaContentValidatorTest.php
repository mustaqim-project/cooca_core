<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Validation\SocialMediaContentValidator;
use Tests\TestCase;

class SocialMediaContentValidatorTest extends TestCase
{
    protected SocialMediaContentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SocialMediaContentValidator();
    }

    /**
     * Test exactly 5 unique hashtags passes COOCA business rule.
     */
    public function test_five_unique_hashtags_is_valid(): void
    {
        $caption = "Promo spesial Ramadhan! Dapatkan diskon 30% hari ini. #umkm #bisnis #kuliner #cooca #indonesia";
        $result = $this->validator->validateHashtags($caption);

        $this->assertTrue($result['is_valid']);
        $this->assertSame(5, $result['count']);
        $this->assertNull($result['error']);
        $this->assertEqualsCanonicalizing(['#umkm', '#bisnis', '#kuliner', '#cooca', '#indonesia'], $result['hashtags']);
    }

    /**
     * Test more than 5 unique hashtags fails with descriptive error message.
     */
    public function test_more_than_five_hashtags_fails_validation(): void
    {
        $caption = "Promo kopi susu kekinian. #kopi #ngopi #cafe #jakarta #promo #diskon";
        $result = $this->validator->validateHashtags($caption);

        $this->assertFalse($result['is_valid']);
        $this->assertSame(6, $result['count']);
        $this->assertStringContainsString('melebihi aturan maksimal COOCA', $result['error']);
        $this->assertStringContainsString('5 hashtag per postingan', $result['error']);
        $this->assertStringContainsString('Jumlah hashtag (6)', $result['error']);
    }

    /**
     * Test case-insensitive hashtag deduplication: #UMKM and #umkm counted once.
     */
    public function test_case_insensitive_hashtag_deduplication(): void
    {
        $caption = "Mari dukung #UMKM lokal! Bangga produk #umkm dan #Umkm kita bersama #Kopi #kopi!";
        $result = $this->validator->validateHashtags($caption);

        $this->assertTrue($result['is_valid']);
        // Only two unique normalized hashtags: #umkm, #kopi
        $this->assertSame(2, $result['count']);
        $this->assertEqualsCanonicalizing(['#umkm', '#kopi'], $result['hashtags']);
    }

    /**
     * Test zero hashtags passes validation.
     */
    public function test_caption_without_hashtags_passes_validation(): void
    {
        $caption = "Beli 1 gratis 1 hanya untuk hari ini. Silakan kunjungi outlet kami.";
        $result = $this->validator->validateHashtags($caption);

        $this->assertTrue($result['is_valid']);
        $this->assertSame(0, $result['count']);
        $this->assertEmpty($result['hashtags']);
    }

    /**
     * Test multi-byte UTF-8 string counting for platform limits.
     */
    public function test_caption_length_supports_multibyte_utf8(): void
    {
        // 500 emojis/Japanese characters should be counted accurately using mb_strlen
        $japaneseText = str_repeat('こんにちは', 100); // 500 characters
        $this->assertSame(500, mb_strlen($japaneseText, 'UTF-8'));

        // Threads limit is 500 characters
        $threadsResult = $this->validator->validateForChannel('threads', 'text', $japaneseText);
        $this->assertTrue($threadsResult['is_valid']);

        // 501 characters should fail for Threads
        $tooLongThreads = $japaneseText . 'あ';
        $threadsFailResult = $this->validator->validateForChannel('threads', 'text', $tooLongThreads);
        $this->assertFalse($threadsFailResult['is_valid']);
        $this->assertStringContainsString('melebihi batas resmi threads', $threadsFailResult['errors'][0]);
    }

    /**
     * Test platform specific caption limits.
     */
    public function test_platform_caption_limits(): void
    {
        $text2300 = str_repeat('A', 2300);

        // Instagram limit 2,200
        $igResult = $this->validator->validateForChannel('instagram', 'photo', $text2300);
        $this->assertFalse($igResult['is_valid']);
        $this->assertStringContainsString('melebihi batas resmi instagram', $igResult['errors'][0]);

        // TikTok limit 2,200
        $tiktokResult = $this->validator->validateForChannel('tiktok', 'video', $text2300);
        $this->assertFalse($tiktokResult['is_valid']);
        $this->assertStringContainsString('melebihi batas resmi tiktok', $tiktokResult['errors'][0]);

        // Facebook limit 63,206 (2,300 is well within limits)
        $fbResult = $this->validator->validateForChannel('facebook', 'feed', $text2300);
        $this->assertTrue($fbResult['is_valid']);
    }

    /**
     * Test Instagram carousel count constraints (2 - 10 items).
     */
    public function test_instagram_carousel_media_count_rules(): void
    {
        // 1 media item is invalid for carousel
        $singleMedia = [
            ['media_type' => 'image', 'media_url' => 'https://example.com/1.jpg'],
        ];
        $result1 = $this->validator->validateForChannel('instagram', 'carousel', 'Caption', $singleMedia);
        $this->assertFalse($result1['is_valid']);
        $this->assertStringContainsString('Instagram Carousel membutuhkan minimal 2 berkas media', $result1['errors'][0]);

        // 5 media items is valid
        $fiveMedia = array_fill(0, 5, ['media_type' => 'image', 'media_url' => 'https://example.com/img.jpg']);
        $result5 = $this->validator->validateForChannel('instagram', 'carousel', 'Caption', $fiveMedia);
        $this->assertTrue($result5['is_valid']);

        // 11 media items exceeds maximum of 10
        $elevenMedia = array_fill(0, 11, ['media_type' => 'image', 'media_url' => 'https://example.com/img.jpg']);
        $result11 = $this->validator->validateForChannel('instagram', 'carousel', 'Caption', $elevenMedia);
        $this->assertFalse($result11['is_valid']);
        $this->assertStringContainsString('maksimal 10 berkas media', $result11['errors'][0]);
    }

    /**
     * Test TikTok photo mode count constraints (min 2 items).
     */
    public function test_tiktok_photo_mode_media_count_rules(): void
    {
        $singleImage = [
            ['media_type' => 'image', 'media_url' => 'https://example.com/1.jpg'],
        ];
        $resultSingle = $this->validator->validateForChannel('tiktok', 'photo', 'Caption', $singleImage);
        $this->assertFalse($resultSingle['is_valid']);
        $this->assertStringContainsString('TikTok Photo Mode membutuhkan minimal 2 gambar', $resultSingle['errors'][0]);

        $threeImages = array_fill(0, 3, ['media_type' => 'image', 'media_url' => 'https://example.com/img.jpg']);
        $resultThree = $this->validator->validateForChannel('tiktok', 'photo', 'Caption', $threeImages);
        $this->assertTrue($resultThree['is_valid']);
    }

    /**
     * Test content type compatibility for channels.
     */
    public function test_channel_supported_content_types(): void
    {
        $this->assertTrue($this->validator->isContentTypeSupported('facebook', 'feed'));
        $this->assertTrue($this->validator->isContentTypeSupported('facebook', 'video'));
        $this->assertTrue($this->validator->isContentTypeSupported('facebook', 'reel'));

        $this->assertTrue($this->validator->isContentTypeSupported('instagram', 'photo'));
        $this->assertTrue($this->validator->isContentTypeSupported('instagram', 'carousel'));
        $this->assertTrue($this->validator->isContentTypeSupported('instagram', 'reel'));
        $this->assertTrue($this->validator->isContentTypeSupported('instagram', 'story'));

        $this->assertTrue($this->validator->isContentTypeSupported('threads', 'text'));
        $this->assertTrue($this->validator->isContentTypeSupported('threads', 'image'));
        $this->assertTrue($this->validator->isContentTypeSupported('threads', 'video'));

        $this->assertTrue($this->validator->isContentTypeSupported('tiktok', 'video'));
        $this->assertTrue($this->validator->isContentTypeSupported('tiktok', 'photo'));
        $this->assertFalse($this->validator->isContentTypeSupported('tiktok', 'story'));
    }
}
