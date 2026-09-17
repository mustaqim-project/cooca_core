<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi\Templates;

/**
 * Class WhatsAppTemplateBuilder
 *
 * Fluent builder untuk mengonstruksi payload komponen template resmi Meta Graph API
 * (Header, Body, Buttons, Footer).
 */
class WhatsAppTemplateBuilder
{
    protected string $templateName;
    protected string $languageCode = 'id';
    protected array $headerParameters = [];
    protected ?string $headerType = null;
    protected array $bodyParameters = [];
    protected array $buttonParameters = [];

    public function __construct(string $templateName, string $languageCode = 'id')
    {
        $this->templateName = $templateName;
        $this->languageCode = $languageCode;
    }

    public static function make(string $templateName, string $languageCode = 'id'): self
    {
        return new self($templateName, $languageCode);
    }

    /**
     * Set header berupa gambar.
     */
    public function setHeaderImage(string $imageUrl): self
    {
        $this->headerType = 'image';
        $this->headerParameters = [
            [
                'type'  => 'image',
                'image' => ['link' => $imageUrl],
            ],
        ];

        return $this;
    }

    /**
     * Set header berupa dokumen PDF.
     */
    public function setHeaderDocument(string $documentUrl, ?string $filename = null): self
    {
        $this->headerType = 'document';
        $doc = ['link' => $documentUrl];
        if ($filename !== null) {
            $doc['filename'] = $filename;
        }

        $this->headerParameters = [
            [
                'type'     => 'document',
                'document' => $doc,
            ],
        ];

        return $this;
    }

    /**
     * Set header teks dengan parameter dinamis.
     */
    public function setHeaderTextParameter(string $text): self
    {
        $this->headerType = 'text';
        $this->headerParameters = [
            [
                'type' => 'text',
                'text' => $text,
            ],
        ];

        return $this;
    }

    /**
     * Tambahkan parameter teks biasa ke Body template ({{1}}, {{2}}, dst).
     */
    public function addBodyText(string $text): self
    {
        $this->bodyParameters[] = [
            'type' => 'text',
            'text' => $text,
        ];

        return $this;
    }

    /**
     * Tambahkan parameter mata uang resmi Meta ke Body template.
     */
    public function addBodyCurrency(int|float $amount, string $currency = 'IDR'): self
    {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');

        $this->bodyParameters[] = [
            'type'     => 'currency',
            'currency' => [
                'fallback_value' => $formatted,
                'code'           => $currency,
                'amount_1000'    => (int) round($amount * 1000),
            ],
        ];

        return $this;
    }

    /**
     * Tambahkan parameter tanggal/waktu resmi Meta ke Body template.
     */
    public function addBodyDateTime(\DateTimeInterface $dateTime, string $format = 'd/m/Y H:i'): self
    {
        $this->bodyParameters[] = [
            'type'      => 'date_time',
            'date_time' => [
                'fallback_value' => $dateTime->format($format),
            ],
        ];

        return $this;
    }

    /**
     * Tambahkan parameter dinamis untuk tombol tipe URL (dynamic suffix).
     */
    public function addButtonUrl(int $buttonIndex, string $urlSuffix): self
    {
        $this->buttonParameters[] = [
            'type'       => 'button',
            'sub_type'   => 'url',
            'index'      => (string) $buttonIndex,
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $urlSuffix,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Tambahkan parameter untuk tombol Quick Reply payload.
     */
    public function addButtonPayload(int $buttonIndex, string $payload): self
    {
        $this->buttonParameters[] = [
            'type'       => 'button',
            'sub_type'   => 'quick_reply',
            'index'      => (string) $buttonIndex,
            'parameters' => [
                [
                    'type'    => 'payload',
                    'payload' => $payload,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Rangkai struktur komponen Meta WhatsApp Template.
     */
    public function buildComponents(): array
    {
        $components = [];

        // 1. Header component
        if ($this->headerType !== null && ! empty($this->headerParameters)) {
            $components[] = [
                'type'       => 'header',
                'parameters' => $this->headerParameters,
            ];
        }

        // 2. Body component
        if (! empty($this->bodyParameters)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => $this->bodyParameters,
            ];
        }

        // 3. Button components
        foreach ($this->buttonParameters as $button) {
            $components[] = $button;
        }

        return $components;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
}
