<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Contracts;

use App\Models\SocialMediaAccount;
use App\Models\SocialPostTarget;

interface SocialMediaProviderInterface
{
    /**
     * Nama unik provider (contoh: 'meta', 'tiktok').
     */
    public function getProviderName(): string;

    /**
     * Cek apakah platform provider telah dikonfigurasi lengkap di Pengaturan Platform.
     */
    public function isConfigured(): bool;

    /**
     * Dapatkan URL dialog OAuth resmi provider untuk redirect browser/popup.
     */
    public function getAuthUrl(string $redirectUri, string $state): string;

    /**
     * Tangani otorisasi callback OAuth, tukar code dengan token permanen / long-lived token.
     *
     * @return array<string, mixed>
     */
    public function handleAuthCallback(string $code, string $redirectUri): array;

    /**
     * Refresh access token jika provider menggunakan token dengan masa kedaluwarsa (misal TikTok 24 jam).
     */
    public function refreshToken(SocialMediaAccount $account): SocialMediaAccount;

    /**
     * Publikasikan konten ke target saluran spesifik (Facebook, Instagram, Threads, TikTok).
     *
     * @param  list<array<string, mixed>>  $mediaItems  Daftar berkas media terurut (untuk Carousel/Single)
     * @return array<string, mixed> Data respons publikasi dari API resmi
     */
    public function publish(SocialMediaAccount $account, SocialPostTarget $target, array $mediaItems = []): array;

    /**
     * Ambil informasi profil / kapabilitas kreator akun (username, avatar, batas durasi, privasi).
     *
     * @return array<string, mixed>
     */
    public function getCreatorInfo(SocialMediaAccount $account): array;

    /**
     * Tarik metrik keterlibatan terkini (impressions, reach, likes, comments, shares).
     *
     * @return array<string, int>
     */
    public function syncMetrics(SocialMediaAccount $account, string $platformPostId): array;
}
