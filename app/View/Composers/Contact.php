<?php

/**
 * Contact page view composer — ACF data + Store JSON-LD.
 * View composer сторінки контактів — ACF дані + Store JSON-LD.
 *
 * @package App\View\Composers
 */

declare(strict_types=1);

namespace App\View\Composers;

use App\Fields\ContactPage;
use App\Support\ContactSchema;
use Roots\Acorn\View\Composer;

class Contact extends Composer
{
    /**
     * @var array<int, string>
     */
    protected static $views = [
        'partials.contact-page',
        'page-contact',
        'page-contacts',
    ];

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        $data = $this->contactData();

        return [
            'contactPhone'            => $data['telephone'],
            'contactEmail'            => $data['email'],
            'locationStreet'          => $data['location_street'],
            'locationCity'            => $data['location_city'],
            'locationZip'             => $data['location_zip'],
            'locationCountry'         => $data['location_country'],
            'workingHours'            => $data['working_hours'],
            'faqItems'                => $this->normalizeFaqItems($this->acfRepeater('faq_items')),
            'mapIframe'               => $data['map_iframe'],
            'mapFallbackSrc'          => $data['map_fallback_src'],
            'geoLatitude'             => $data['geo_latitude'],
            'geoLongitude'            => $data['geo_longitude'],
            'localBusinessSchemaJson' => ContactSchema::toJson($data),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{question: string, answer: string, open: bool}>
     */
    private function normalizeFaqItems(array $rows): array
    {
        if ($rows === []) {
            $rows = ContactPage::defaultFaqItems();
        }

        $items = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $question = trim((string) ($row['question'] ?? ''));

            if ($question === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer'   => trim((string) ($row['answer'] ?? '')),
                'open'     => ! empty($row['is_open']),
            ];
        }

        if ($items === []) {
            return $this->normalizeFaqItems(ContactPage::defaultFaqItems());
        }

        return $items;
    }

    /**
     * Normalize ACF values with theme fallbacks.
     * Нормалізує ACF значення з fallback теми.
     *
     * @return array<string, mixed>
     */
    private function contactData(): array
    {
        $phone   = $this->acfString('contact_phone', '+38 (050) 123-45-67');
        $email   = $this->acfString('contact_email', 'support@solidwebcraft.com');
        $street  = $this->acfString('location_street');
        $city    = $this->acfString('location_city', 'Івано-Франківськ');
        $zip     = $this->acfString('location_zip');
        $country = $this->acfString('location_country', 'UA');
        $hours   = $this->acfRepeater('working_hours');
        $mapRaw  = $this->acfString('map_iframe');
        $lat     = $this->acfFloat('geo_latitude');
        $lng     = $this->acfFloat('geo_longitude');

        return [
            'name'             => get_bloginfo('name'),
            'url'              => home_url('/'),
            'telephone'        => $phone,
            'email'            => $email,
            'location_street'  => $street,
            'location_city'    => $city,
            'location_zip'     => $zip,
            'location_country' => $country,
            'working_hours'    => $hours,
            'map_iframe'       => self::sanitizeMapIframe($mapRaw),
            'map_fallback_src' => 'https://maps.google.com/maps?q=' . rawurlencode('Ivano-Frankivsk, Ukraine') . '&z=14&output=embed',
            'geo_latitude'     => $lat,
            'geo_longitude'    => $lng,
        ];
    }

    private function acfString(string $field, string $fallback = ''): string
    {
        if (! function_exists('get_field')) {
            return $fallback;
        }

        $value = get_field($field);

        if (! is_string($value) && ! is_numeric($value)) {
            return $fallback;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function acfRepeater(string $field): array
    {
        if (! function_exists('get_field')) {
            return [];
        }

        $value = get_field($field);

        return is_array($value) ? $value : [];
    }

    private function acfFloat(string $field): ?float
    {
        if (! function_exists('get_field')) {
            return null;
        }

        $value = get_field($field);

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Allow only safe iframe embed markup.
     * Дозволяє лише безпечну iframe embed-розмітку.
     */
    public static function sanitizeMapIframe(string $raw): string
    {
        $raw = trim($raw);

        if ($raw === '') {
            return '';
        }

        $allowed = [
            'iframe' => [
                'src'             => true,
                'width'           => true,
                'height'          => true,
                'class'           => true,
                'style'           => true,
                'loading'         => true,
                'referrerpolicy'  => true,
                'allowfullscreen' => true,
                'title'           => true,
                'frameborder'     => true,
                'aria-hidden'     => true,
            ],
        ];

        $clean = wp_kses($raw, $allowed);

        if (! str_contains($clean, '<iframe')) {
            return '';
        }

        // Strip fixed dimensions so responsive container controls sizing.
        // Прибираємо фіксовані розміри — контейнер керує масштабом.
        $clean = preg_replace('/\s(width|height)=["\'][^"\']*["\']/i', '', $clean) ?? $clean;

        return $clean;
    }
}
