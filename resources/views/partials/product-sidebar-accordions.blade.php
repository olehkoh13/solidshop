{{--
  Sidebar accordions — content from product fields
  Акордеони в правій колонці — контент з полів товару
--}}
@php
  /** @var \WC_Product $product */
  $sections = [];

  $attributes = $product->get_attributes();
  $attr_lines = [];
  foreach ($attributes as $attribute) {
    if (! $attribute->get_visible()) {
      continue;
    }
    $label = wc_attribute_label($attribute->get_name());
    $value = $product->get_attribute($attribute->get_name());
    if ($label && $value) {
      $attr_lines[] = '<strong>' . esc_html($label) . ':</strong> ' . esc_html($value);
    }
  }
  if ($attr_lines) {
    $sections[] = [
      'id'      => 'details',
      'title'   => __('Деталі', 'solidshop'),
      'content' => '<ul><li>' . implode('</li><li>', $attr_lines) . '</li></ul>',
      'is_html' => true,
    ];
  }

  $delivery = get_post_meta($product->get_id(), '_ss_delivery_returns', true);
  if (! $delivery) {
    // Dynamic threshold from WC shipping zones / Динамічний поріг із зон доставки WooCommerce
    $free_shipping_threshold = function_exists('\App\solidshop_free_shipping_threshold')
        ? \App\solidshop_free_shipping_threshold()
        : 0.0;

    if ($free_shipping_threshold > 0) {
      $delivery = sprintf(
        /* translators: %s: minimum order amount for free shipping (from WC zones) */
        __('Безкоштовна доставка від %s. Повернення протягом 14 днів без пояснень. Відправка у день замовлення до 16:00.', 'solidshop'),
        wp_strip_all_tags(wc_price($free_shipping_threshold, ['decimals' => 0]))
      );
    } else {
      $delivery = __('Безкоштовна доставка за умов магазину. Повернення протягом 14 днів без пояснень. Відправка у день замовлення до 16:00.', 'solidshop');
    }
  }
  $sections[] = [
    'id'      => 'delivery',
    'title'   => __('Доставка і повернення', 'solidshop'),
    'content' => wp_kses_post(wpautop($delivery)),
    'is_html' => true,
  ];

  $care = get_post_meta($product->get_id(), '_ss_care_guide', true);
  if (! $care) {
    $care = __('Дотримуйтесь інструкцій на етикетці. Не використовуйте агресивні засоби для прання та сушіння.', 'solidshop');
  }
  $sections[] = [
    'id'      => 'care',
    'title'   => __('Догляд', 'solidshop'),
    'content' => wp_kses_post(wpautop($care)),
    'is_html' => true,
  ];
@endphp

@if (! empty($sections))
  <div class="product-sidebar-accordions" data-sidebar-accordions>
    @foreach ($sections as $index => $section)
      <div class="product-sidebar-accordions__item {{ $index === 0 ? 'is-open' : '' }}" data-accordion-item>
        <button type="button" class="product-sidebar-accordions__trigger" data-accordion-trigger aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
          <span>{{ $section['title'] }}</span>
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div class="product-sidebar-accordions__panel" data-accordion-panel @if($index !== 0) hidden @endif>
          {!! $section['content'] !!}
        </div>
      </div>
    @endforeach
  </div>
@endif
