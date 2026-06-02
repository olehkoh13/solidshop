<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Blog single — comment form markup and field layout.
 * Single post блогу — розмітка форми коментарів.
 */
add_filter('comment_form_defaults', function (array $defaults): array {
    if (! is_singular('post')) {
        return $defaults;
    }

    $defaults['class_form']           = 'blog-comment-form';
    $defaults['class_submit']         = 'blog-comment-form__submit';
    $defaults['title_reply']          = __('Залишити відповідь', 'solidshop');
    $defaults['title_reply_before']   = '<h3 id="reply-title" class="blog-comment-form__title comment-reply-title">';
    $defaults['title_reply_after']    = '</h3>';
    $defaults['comment_notes_before'] = '';
    $defaults['submit_button']        = '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>';
    $defaults['submit_field']         = '<p class="form-submit blog-comment-form__submit-wrap">%1$s %2$s</p>';

    return $defaults;
}, 20);

add_filter('comment_form_fields', function (array $fields): array {
    if (! is_singular('post')) {
        return $fields;
    }

    foreach ($fields as $key => $field) {
        if (! is_string($field)) {
            continue;
        }

        $fields[$key] = str_replace(
            '<p class="',
            '<p class="blog-comment-form__field blog-comment-form__field--' . esc_attr($key) . ' ',
            $field
        );
    }

    return $fields;
}, 20);

add_filter('comment_form_field_comment', function (string $field): string {
    if (! is_singular('post')) {
        return $field;
    }

    return str_replace(
        'comment-form-comment',
        'comment-form-comment blog-comment-form__field blog-comment-form__field--comment',
        $field
    );
}, 20);
