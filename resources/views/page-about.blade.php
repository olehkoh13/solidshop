<?php /* Template Name: About Us */ ?>
{{--
  Template Name: About Us
  B2B About page — sharp corners, fluid spacing, minimalist grid.
  B2B сторінка «Про нас» — гострі кути, fluid spacing, мінімалістична сітка.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php(the_post())

    <div class="py-section max-w-7xl mx-auto px-4 sm:px-6">
      <header class="mb-10 md:mb-12 text-left">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
          {!! $title !!}
        </h1>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        {{-- Left column: copy, stats / Ліва колонка: текст, статистика --}}
        <div class="lg:col-span-7">
          <div class="text-gray-600 text-base leading-relaxed space-y-4 text-left">
            @if (trim(get_the_content()))
              @php(the_content())
            @else
              <p>
                {{ __('SolidShop — надійний B2B-партнер для бізнесу, який цінує якість, прозорість і швидкість. Ми постачаємо преміальні рішення для інтерʼєру та обладнання з повним супроводом від заявки до доставки.', 'solidshop') }}
              </p>
              <p>
                {{ __('Працюємо з оптовими клієнтами, дизайн-студіями та підрядниками по всій Україні. Наша команда допомагає підібрати асортимент, оформити замовлення та забезпечити стабільні поставки.', 'solidshop') }}
              </p>
            @endif
          </div>

          {{-- Company stats / Статистика компанії --}}
          <div class="grid grid-cols-2 gap-6 mt-10">
            <div class="border border-gray-200 rounded-none p-6">
              <p class="text-3xl font-bold text-gray-900">15+</p>
              <p class="text-sm text-gray-600 mt-1">{{ __('Років досвіду', 'solidshop') }}</p>
            </div>
            <div class="border border-gray-200 rounded-none p-6">
              <p class="text-3xl font-bold text-gray-900">10k+</p>
              <p class="text-sm text-gray-600 mt-1">{{ __('Клієнтів', 'solidshop') }}</p>
            </div>
            <div class="border border-gray-200 rounded-none p-6">
              <p class="text-3xl font-bold text-gray-900">500+</p>
              <p class="text-sm text-gray-600 mt-1">{{ __('SKU в асортименті', 'solidshop') }}</p>
            </div>
            <div class="border border-gray-200 rounded-none p-6">
              <p class="text-3xl font-bold text-gray-900">24/7</p>
              <p class="text-sm text-gray-600 mt-1">{{ __('Підтримка B2B', 'solidshop') }}</p>
            </div>
          </div>
        </div>

        {{-- Right column: image / Права колонка: зображення --}}
        <div class="lg:col-span-5">
          @if (has_post_thumbnail())
            <div class="aspect-square overflow-hidden border border-gray-200 rounded-none">
              {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full h-full object-cover']) !!}
            </div>
          @else
            <div
              class="aspect-square bg-gray-100 border border-gray-200 rounded-none"
              aria-hidden="true"
            ></div>
          @endif
        </div>
      </div>
    </div>
  @endwhile
@endsection
