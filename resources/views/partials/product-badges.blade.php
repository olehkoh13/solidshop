{{--
  Глобальні бейджі товару: Розпродаж / Новинка
  Global product badges: Sale / New
  @var \WC_Product|null $product
--}}
@php
  $product = $product ?? ($GLOBALS['product'] ?? null);

  if ($product instanceof \WC_Product) {
      $is_on_sale = $is_on_sale ?? $product->is_on_sale();

      if (! isset($is_new)) {
          $created = $product->get_date_created();
          $is_new = $created && $created->getTimestamp() >= strtotime('-30 days');
      }
  } else {
      $is_on_sale = $is_on_sale ?? false;
      $is_new = $is_new ?? false;
  }
@endphp

@if ($product instanceof \WC_Product && ($is_on_sale || $is_new))
  @php
    $sale_percent = 0;

    if ($is_on_sale && $product instanceof \WC_Product && ! $product->is_type('variable')) {
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();

        if ($regular > 0 && $sale > 0 && $sale < $regular) {
            $sale_percent = (int) round((($regular - $sale) / $regular) * 100);
        }
    }
  @endphp
  <div class="ss-product-badges" aria-hidden="true">
    @if ($is_on_sale)
      @if ($sale_percent > 0)
        <span class="ss-product-badge ss-product-badge--percent">-{{ $sale_percent }}%</span>
      @else
        <span class="ss-product-badge ss-product-badge--sale">{{ __('Розпродаж', 'solidshop') }}</span>
      @endif
    @endif
    @if ($is_new)
      <span class="ss-product-badge ss-product-badge--new">{{ __('Новинка', 'solidshop') }}</span>
    @endif
  </div>
@endif
