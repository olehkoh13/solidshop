{{--
  SKU + categories footer below product tabs
  SKU та категорії під табами товару
--}}
@php
  /** @var \WC_Product $product */
  $sku = $product->get_sku();
  $cats = wc_get_product_category_list($product->get_id(), ', ');
@endphp

@if ($sku || $cats)
  <footer class="product-meta-footer">
    @if ($sku)
      <span>{{ __('SKU', 'solidshop') }}: {{ $sku }}</span>
    @endif
    @if ($sku && $cats)
      <span class="product-meta-footer__sep">·</span>
    @endif
    @if ($cats)
      <span>{!! wp_kses_post($cats) !!}</span>
    @endif
  </footer>
@endif
