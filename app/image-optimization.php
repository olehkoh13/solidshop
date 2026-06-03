<?php

/**
 * Convert JPEG/PNG uploads to WebP via GD (no third-party plugins).
 * Конвертація JPEG/PNG у WebP через GD при завантаженні.
 *
 * @package App
 */

declare(strict_types=1);

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/** WebP output quality (0-100) / Якість WebP (0-100) */
const SOLIDSHOP_WEBP_QUALITY = 85;

/**
 * Image upload optimization module.
 * Модуль оптимізації завантажених зображень.
 */
final class ImageOptimization
{
    public static function boot(): void
    {
        add_filter('wp_handle_upload', [self::class, 'convertUploadToWebp'], 10, 2);
        add_filter('upload_mimes', [self::class, 'allowWebpMimeType']);
    }

    /**
     * Allow WebP in Media Library after conversion.
     * Дозволити WebP у медіатеці після конвертації.
     *
     * @param array<string, string> $mimes
     * @return array<string, string>
     */
    public static function allowWebpMimeType(array $mimes): array
    {
        $mimes['webp'] = 'image/webp';

        return $mimes;
    }

    /**
     * Intercept upload and replace JPEG/PNG with WebP.
     * Перехопити upload і замінити JPEG/PNG на WebP.
     *
     * @param array{file?: string, url?: string, type?: string} $upload
     * @return array{file?: string, url?: string, type?: string}
     */
    public static function convertUploadToWebp(array $upload, string $context): array
    {
        unset($context);

        if (! self::serverSupportsWebp()) {
            return $upload;
        }

        $file = isset($upload['file']) ? (string) $upload['file'] : '';
        $type = isset($upload['type']) ? strtolower((string) $upload['type']) : '';

        if ($file === '' || ! is_file($file) || ! is_readable($file)) {
            return $upload;
        }

        if (! in_array($type, ['image/jpeg', 'image/png'], true)) {
            return $upload;
        }

        $image = self::loadImage($file, $type);

        if ($image === false) {
            return $upload;
        }

        $webpPath = self::replaceImageExtension($file, 'webp');

        if ($webpPath === $file || ! imagewebp($image, $webpPath, SOLIDSHOP_WEBP_QUALITY)) {
            imagedestroy($image);

            return $upload;
        }

        imagedestroy($image);

        if (! is_file($webpPath)) {
            return $upload;
        }

        if (! @unlink($file)) {
            @unlink($webpPath);

            return $upload;
        }

        $upload['file'] = $webpPath;
        $upload['type'] = 'image/webp';

        if (isset($upload['url']) && is_string($upload['url']) && $upload['url'] !== '') {
            $upload['url'] = self::replaceImageExtension($upload['url'], 'webp');
        }

        return $upload;
    }

    /**
     * GD with WebP encode support available.
     * GD з підтримкою кодування WebP доступний.
     */
    private static function serverSupportsWebp(): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        if (! function_exists('imagecreatefromjpeg')
            || ! function_exists('imagecreatefrompng')
            || ! function_exists('imagewebp')
        ) {
            return false;
        }

        if (defined('IMG_WEBP')) {
            return (bool) (imagetypes() & IMG_WEBP);
        }

        return true;
    }

    /**
     * Load JPEG or PNG resource from disk.
     * Завантажити JPEG або PNG з диска.
     */
    private static function loadImage(string $file, string $mime): \GdImage|false
    {
        if ($mime === 'image/jpeg') {
            $image = @imagecreatefromjpeg($file);

            return $image instanceof \GdImage ? $image : false;
        }

        $image = @imagecreatefrompng($file);

        if (! $image instanceof \GdImage) {
            return false;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Replace .jpg/.jpeg/.png extension (case-insensitive).
     * Замінити розширення .jpg/.jpeg/.png (без урахування регістру).
     */
    private static function replaceImageExtension(string $path, string $extension): string
    {
        $replaced = preg_replace('/\.(jpe?g|png)$/i', '.' . $extension, $path);

        return is_string($replaced) ? $replaced : $path;
    }
}

ImageOptimization::boot();
