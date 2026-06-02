<?php

/**
 * Store JSON-LD schema builder for Contact page.
 * Побудова Store JSON-LD schema для сторінки контактів.
 *
 * @package App\Support
 */

declare(strict_types=1);

namespace App\Support;

class ContactSchema
{
    /** @var array<string, string> */
    private const DAY_MAP = [
        'пн' => 'Monday',
        'mon' => 'Monday',
        'monday' => 'Monday',
        'вт' => 'Tuesday',
        'tue' => 'Tuesday',
        'tuesday' => 'Tuesday',
        'ср' => 'Wednesday',
        'wed' => 'Wednesday',
        'wednesday' => 'Wednesday',
        'чт' => 'Thursday',
        'thu' => 'Thursday',
        'thursday' => 'Thursday',
        'пт' => 'Friday',
        'fri' => 'Friday',
        'friday' => 'Friday',
        'сб' => 'Saturday',
        'sat' => 'Saturday',
        'saturday' => 'Saturday',
        'нд' => 'Sunday',
        'sun' => 'Sunday',
        'sunday' => 'Sunday',
    ];

    /** @var array<int, string> Ordered weekdays for range expansion / Порядок днів для діапазонів */
    private const WEEKDAY_ORDER = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    /**
     * Build Store schema array from normalized contact data.
     * Будує масив Store schema з нормалізованих contact-даних.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function build(array $data): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Store',
            'name'     => (string) ($data['name'] ?? get_bloginfo('name')),
            'url'      => (string) ($data['url'] ?? home_url('/')),
        ];

        if (! empty($data['telephone'])) {
            $schema['telephone'] = (string) $data['telephone'];
        }

        if (! empty($data['email'])) {
            $schema['email'] = (string) $data['email'];
        }

        $address = self::buildAddress($data);
        if ($address !== []) {
            $schema['address'] = $address;
        }

        $hours = self::buildOpeningHours($data['working_hours'] ?? []);
        if ($hours !== []) {
            $schema['openingHoursSpecification'] = $hours;
        }

        $geo = self::buildGeo($data);
        if ($geo !== null) {
            $schema['geo'] = $geo;
        }

        return $schema;
    }

    /**
     * Encode schema as JSON for ld+json output.
     * Кодує schema у JSON для ld+json.
     *
     * @param  array<string, mixed>  $data
     */
    public static function toJson(array $data): string
    {
        $schema = self::build($data);

        if (count($schema) <= 3) {
            return '';
        }

        $json = wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function buildAddress(array $data): array
    {
        $street  = trim((string) ($data['location_street'] ?? ''));
        $city    = trim((string) ($data['location_city'] ?? ''));
        $zip     = trim((string) ($data['location_zip'] ?? ''));
        $country = trim((string) ($data['location_country'] ?? ''));

        if ($street === '' && $city === '' && $zip === '' && $country === '') {
            return [];
        }

        $address = [
            '@type' => 'PostalAddress',
        ];

        if ($street !== '') {
            $address['streetAddress'] = $street;
        }

        if ($city !== '') {
            $address['addressLocality'] = $city;
        }

        if ($zip !== '') {
            $address['postalCode'] = $zip;
        }

        if ($country !== '') {
            $address['addressCountry'] = $country;
        }

        return $address;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private static function buildOpeningHours(array $rows): array
    {
        $specs = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $days  = trim((string) ($row['days'] ?? ''));
            $opens = self::normalizeTime((string) ($row['open_time'] ?? ''));
            $closes = self::normalizeTime((string) ($row['close_time'] ?? ''));

            if ($days === '' || $opens === '' || $closes === '') {
                continue;
            }

            $dayOfWeek = self::parseDays($days);
            if ($dayOfWeek === []) {
                continue;
            }

            $specs[] = [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => $dayOfWeek,
                'opens'     => $opens,
                'closes'    => $closes,
            ];
        }

        return $specs;
    }

    /**
     * Parse UA/EN day labels into schema.org dayOfWeek values.
     * Парсить UA/EN позначення днів у значення schema.org dayOfWeek.
     *
     * @return array<int, string>
     */
    public static function parseDays(string $days): array
    {
        $days = trim($days);

        if ($days === '') {
            return [];
        }

        if (str_contains($days, '-')) {
            return self::expandDayRange($days);
        }

        if (str_contains($days, ',')) {
            $parsed = [];
            foreach (explode(',', $days) as $part) {
                $parsed = array_merge($parsed, self::parseDays(trim($part)));
            }

            return self::uniqueOrderedDays($parsed);
        }

        $key = mb_strtolower($days, 'UTF-8');

        if (isset(self::DAY_MAP[$key])) {
            return [self::DAY_MAP[$key]];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private static function expandDayRange(string $range): array
    {
        [$startRaw, $endRaw] = array_map('trim', explode('-', $range, 2));
        $start = self::DAY_MAP[mb_strtolower($startRaw, 'UTF-8')] ?? null;
        $end   = self::DAY_MAP[mb_strtolower($endRaw, 'UTF-8')] ?? null;

        if ($start === null || $end === null) {
            return [];
        }

        $startIndex = array_search($start, self::WEEKDAY_ORDER, true);
        $endIndex   = array_search($end, self::WEEKDAY_ORDER, true);

        if ($startIndex === false || $endIndex === false) {
            return [];
        }

        if ($startIndex <= $endIndex) {
            return array_slice(self::WEEKDAY_ORDER, (int) $startIndex, $endIndex - $startIndex + 1);
        }

        return array_merge(
            array_slice(self::WEEKDAY_ORDER, (int) $startIndex),
            array_slice(self::WEEKDAY_ORDER, 0, $endIndex + 1)
        );
    }

    /**
     * @param  array<int, string>  $days
     * @return array<int, string>
     */
    private static function uniqueOrderedDays(array $days): array
    {
        $unique = array_values(array_unique($days));

        return array_values(array_filter(
            self::WEEKDAY_ORDER,
            static fn (string $day): bool => in_array($day, $unique, true)
        ));
    }

    private static function normalizeTime(string $time): string
    {
        $time = trim($time);

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches) !== 1) {
            return '';
        }

        $hour   = (int) $matches[1];
        $minute = (int) $matches[2];

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return '';
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private static function buildGeo(array $data): ?array
    {
        $lat = $data['geo_latitude'] ?? null;
        $lng = $data['geo_longitude'] ?? null;

        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return null;
        }

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $lat,
            'longitude' => (float) $lng,
        ];
    }
}
