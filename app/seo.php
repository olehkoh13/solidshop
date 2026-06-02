<?php

/**
 * Rank Math SEO — strict SERP limits and clean DOM output.
 * Rank Math SEO — жорсткі ліміти для SERP і чистий DOM без слідів плагіна.
 *
 * @package App
 */

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/** Max SEO title length for SERP / Макс. довжина SEO title для SERP */
const RANK_MATH_TITLE_MAX = 60;

/** Max meta description length for SERP / Макс. довжина meta description для SERP */
const RANK_MATH_DESCRIPTION_MAX = 160;

/**
 * Truncate multibyte-safe text; append ellipsis only when truncated.
 * Обрізати текст з підтримкою UTF-8; «...» лише якщо рядок реально скорочено.
 */
function solidshop_rank_math_truncate(string $value, int $maxLength): string
{
    $value = trim($value);

    if ($value === '') {
        return $value;
    }

    if (mb_strlen($value) <= $maxLength) {
        return $value;
    }

    return mb_substr($value, 0, $maxLength - 3) . '...';
}

// Register hooks only when Rank Math is active / Хуки лише якщо Rank Math активний
if (! defined('RANK_MATH_VERSION')) {
    return;
}

/**
 * Enforce max 60 characters on frontend SEO title.
 * Обмежити SEO title на фронтенді до 60 символів.
 */
add_filter('rank_math/frontend/title', function (string $title): string {
    return solidshop_rank_math_truncate($title, RANK_MATH_TITLE_MAX);
}, 20);

/**
 * Enforce max 160 characters on meta description.
 * Обмежити meta description до 160 символів.
 */
add_filter('rank_math/frontend/description', function (string $description): string {
    return solidshop_rank_math_truncate($description, RANK_MATH_DESCRIPTION_MAX);
}, 20);

/**
 * Remove Rank Math HTML credit comment from page source.
 * Прибрати HTML-коментар Rank Math із вихідного коду сторінки.
 */
add_filter('rank_math/frontend/remove_credit_notice', '__return_true', 10);
