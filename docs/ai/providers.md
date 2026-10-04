# Multi-Provider BYOAI (Bring Your Own AI)

> **Layer:** AI Model Providers, Routing, Token Management, & Enkripsi API Key  
> **Source:** `App\Domain\Ai\Providers\` & `App\Models\AiProviderConfig`

---

## 1. Konsep Bring Your Own AI (BYOAI)

COOCA AI memberikan kebebasan penuh kepada pemilik usaha untuk memilih vendor kecerdasan buatan favorit mereka atau menggunakan kuota token bawaan COOCA.

### Penyedia Model yang Didukung:
1. **OpenAI:**
   - Model yang didukung: `gpt-4o`, `gpt-4o-mini`, `gpt-4-turbo`, `gpt-3.5-turbo`.
   - Endpoint: `https://api.openai.com/v1/chat/completions`.
2. **Google Gemini:**
   - Model yang didukung: `gemini-1.5-pro`, `gemini-1.5-flash`, `gemini-2.0-flash`.
   - Endpoint: Google AI Studio REST API.
3. **Anthropic Claude:**
   - Model yang didukung: `claude-3-5-sonnet-20241022`, `claude-3-haiku-20240307`.
   - Endpoint: `https://api.anthropic.com/v1/messages`.
4. **OpenRouter:**
   - Model yang didukung: Llama-3 70B/8B, Mistral Large, DeepSeek-V3, Qwen, dll.
   - Endpoint: `https://openrouter.ai/api/v1/chat/completions`.
5. **Rule-Based Fallback Engine (100% Offline):**
   - Tidak memerlukan koneksi internet atau kunci API pihak ketiga.
   - Menggunakan mesin inferensi bisnis deterministik berbasis data riil COOCA (analisis tren, perbandingan stok, kalkulasi margin) untuk menjamin sistem tetap dapat beroperasi dalam kondisi darurat atau tanpa kuota.

---

## 2. Enkripsi & Penyimpanan Kunci API

Kunci API pihak ketiga adalah rahasia berisiko tinggi. COOCA menerapkan standar enkripsi ketat:

1. **Enkripsi AES-256-CBC saat Tersimpan di Basis Data:**
   Model `AiProviderConfig` menggunakan mutator terenkripsi Laravel:
   ```php
   protected function casts(): array
   {
       return [
           'api_key' => 'encrypted',
           'is_active' => 'boolean',
           'is_default' => 'boolean',
       ];
   }
   ```
2. **Aksesor Bertopeng (Masked Accessor):**
   Untuk mencegah kebocoran kunci di antarmuka pengguna:
   ```php
   public function getMaskedApiKeyAttribute(): string
   {
       if (empty($this->api_key)) {
           return '••••••••';
       }
       return '••••' . substr((string) $this->api_key, -4);
   }
   ```
3. **Penyembunyian Serialisasi JSON:**
   Atribut `api_key` dimasukkan ke dalam daftar `$hidden`, sehingga tidak akan pernah terekspos saat model diubah menjadi array atau respons JSON HTTP.

---

## 3. Pengujian Koneksi (Provider Test Endpoint)

Sebelum menyimpan konfigurasi baru, pemilik bisnis dapat memvalidasi apakah kunci API dan model yang dimasukkan valid melalui tombol **"Uji Koneksi"** (`POST /ai/providers/test`).

Sistem akan mengirimkan pesan ping ringan ke endpoint vendor dan mengembalikan status latensi serta keberhasilan autentikasi ke antarmuka pengguna.
