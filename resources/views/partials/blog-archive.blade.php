{{--
  Shared blog archive layout — breadcrumbs, grid, sidebar, pagination.
  Спільний layout архіву блогу.
--}}
<div class="blog-archive py-section max-w-7xl mx-auto px-4 sm:px-6">
  @if (! empty($breadcrumbItems))
    <nav class="solidshop-breadcrumbs text-sm text-gray-500 mb-3" aria-label="{{ __('Breadcrumb', 'solidshop') }}">
      @foreach ($breadcrumbItems as $index => $item)
        @if ($index > 0)
          <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
        @endif
        @if (! empty($item['url']))
          <a href="{{ esc_url($item['url']) }}" class="hover:text-gray-900 transition-colors no-underline">
            {{ $item['label'] }}
          </a>
        @else
          <span class="text-gray-900">{{ $item['label'] }}</span>
        @endif
      @endforeach
    </nav>
  @endif

  <header class="mb-10 md:mb-12 text-left">
    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
      {!! $title !!}
    </h1>
    @if (! empty($archiveSubtitle))
      <p class="text-gray-600 mt-4 leading-relaxed text-left">
        {{ $archiveSubtitle }}
      </p>
    @endif
  </header>

  @if (! have_posts())
    <div class="mt-10">
      <x-alert type="warning">
        {!! __('На жаль, записів не знайдено.', 'solidshop') !!}
      </x-alert>

      {!! get_search_form(false) !!}
    </div>
  @else
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">
      <div class="lg:col-span-8 order-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-12">
          @while(have_posts())
            @php(the_post())
            @include('partials.content-blog-card')
          @endwhile
        </div>

        @if (! empty($blogPagination))
          <nav class="blog-pagination mt-12" aria-label="{{ __('Навігація записів', 'solidshop') }}">
            {!! $blogPagination !!}
          </nav>
        @endif
      </div>

      <div class="lg:col-span-4 order-2">
        @include('partials.blog-sidebar')
      </div>
    </div>
  @endif
</div>
