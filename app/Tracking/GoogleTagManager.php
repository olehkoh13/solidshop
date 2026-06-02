<?php

/**
 * Google Tag Manager container snippets (head + body).
 * Сніпети контейнера Google Tag Manager (head + body).
 *
 * @package App\Tracking
 */

declare(strict_types=1);

namespace App\Tracking;

final class GoogleTagManager
{
    public function register(): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        add_action('wp_head', [$this, 'renderHeadScript'], 1);
        add_action('wp_body_open', [$this, 'renderBodyNoscript'], 1);
    }

    /**
     * Valid GTM container ID is stored.
     * Збережено валідний ID контейнера GTM.
     */
    public function isConfigured(): bool
    {
        return $this->containerId() !== '';
    }

    /**
     * GTM <script> in <head> (priority 1).
     * GTM <script> у <head> (пріоритет 1).
     */
    public function renderHeadScript(): void
    {
        $id = $this->containerId();

        if ($id === '') {
            return;
        }

        printf(
            "<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\nnew Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,'script','dataLayer','%s');</script>\n<!-- End Google Tag Manager -->\n",
            esc_js($id)
        );
    }

    /**
     * GTM <noscript> iframe after <body> open (priority 1).
     * GTM <noscript> iframe одразу після відкриття <body> (пріоритет 1).
     */
    public function renderBodyNoscript(): void
    {
        $id = $this->containerId();

        if ($id === '') {
            return;
        }

        printf(
            "<!-- Google Tag Manager (noscript) -->\n<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=%s\"\nheight=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n<!-- End Google Tag Manager (noscript) -->\n",
            esc_attr($id)
        );
    }

    /**
     * Sanitized container ID from wp_options.
     * Санітизований ID контейнера з wp_options.
     */
    private function containerId(): string
    {
        $raw = (string) get_option('ss_gtm_id', '');

        return self::sanitizeContainerId($raw);
    }

    /**
     * Accept only GTM-XXXXXXX format.
     * Приймає лише формат GTM-XXXXXXX.
     */
    public static function sanitizeContainerId(string $value): string
    {
        $value = strtoupper(sanitize_text_field(trim($value)));

        if ($value === '') {
            return '';
        }

        if (preg_match('/^GTM-[A-Z0-9]+$/', $value) !== 1) {
            return '';
        }

        return $value;
    }
}
