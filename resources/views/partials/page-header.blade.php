@php
  $page_header_classes = 'page-header';

  if (function_exists('is_account_page') && is_account_page()) {
      $page_header_classes .= ' max-w-7xl mx-auto px-4 sm:px-6 pt-10 pb-4 text-center';
  }
@endphp

<div class="{{ $page_header_classes }}">
  <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">{!! $title !!}</h1>
</div>
