<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    {{-- Головний контейнер Tailwind, що тримає футер внизу --}}
    <div id="app" class="flex flex-col min-h-screen bg-gray-50 font-sans antialiased">

      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'sage') }}
      </a>

      {{-- Наша наскрізна преміальна шапка --}}
      @include('sections.header')

      {{-- Контентний каркас --}}
      <main id="main" class="flex-grow">
        <div class="flex flex-col lg:flex-row gap-8">

          {{-- Основна зона виводу контенту сторінок --}}
          <div class="flex-grow">
            @yield('content')
          </div>

          {{-- Сайдбар виведеться ТІЛЬКИ там, де його явно попросять --}}
          @hasSection('sidebar')
            <aside class="sidebar w-full lg:w-1/4 shrink-0 px-4 py-8">
              @yield('sidebar')
            </aside>
          @endif

        </div>
      </main>

      {{-- Смуга довіри — завжди над футером на всіх сторінках / Trust bar above footer site-wide --}}
      <div class="site-trust-bar bg-gray-50 border-t border-gray-200 mt-auto">
        <div class="container mx-auto max-w-7xl px-4">
          @include('partials.product-trust-bar')
        </div>
      </div>

      {{-- Наш новий широкий преміальний футер --}}
      @include('sections.footer')

      {{-- Quick Buy modal — site-wide shell / Модалка Quick Buy — глобальна оболонка --}}
      @include('partials.quick-buy-modal')

      {{-- Плаваючий банер кошика / Floating sticky cart bar --}}
      @include('partials.floating-cart-bar')

    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
