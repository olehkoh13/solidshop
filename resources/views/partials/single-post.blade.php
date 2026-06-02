{{--
  Single blog post — two-column layout with sidebar (Shoptimizer-style).
  Окремий запис блогу — двоколонковий layout з sidebar.
--}}
<div class="blog-single py-section max-w-7xl mx-auto px-4 sm:px-6">
  @if (! empty($breadcrumbItems))
    <nav class="solidshop-breadcrumbs text-sm text-gray-500 mb-6" aria-label="{{ __('Breadcrumb', 'solidshop') }}">
      @foreach ($breadcrumbItems as $index => $item)
        @if ($index > 0)
          <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
        @endif
        @if (! empty($item['url']))
          <a href="{{ esc_url($item['url']) }}" class="hover:text-gray-900 transition-colors no-underline uppercase tracking-wide">
            {{ $item['label'] }}
          </a>
        @else
          <span class="text-gray-900 uppercase tracking-wide">{{ $item['label'] }}</span>
        @endif
      @endforeach
    </nav>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">
    <div class="lg:col-span-8 order-1">
      <article @php(post_class('blog-single__article'))>
        @if (has_post_thumbnail())
          <div class="blog-single__hero aspect-[16/10] overflow-hidden bg-gray-100 mb-8 border border-gray-200">
            {!! get_the_post_thumbnail(null, 'large', ['class' => 'w-full h-full object-cover']) !!}
          </div>
        @endif

        <header class="mb-8 text-left">
          <h1 class="blog-single__title text-3xl md:text-4xl lg:text-[2.75rem] font-bold leading-tight text-gray-900 mb-4">
            {!! $title !!}
          </h1>

          @if ($postFormattedDate !== '')
            <time class="block text-xs font-semibold uppercase tracking-wider text-gray-500" datetime="{{ get_post_time('c', true) }}">
              {{ $postFormattedDate }}
            </time>
          @endif
        </header>

        <div class="blog-prose blog-single__content text-gray-800">
          @php(the_content())
        </div>

        @if ($pagination())
          <nav class="mt-10 pt-8 border-t border-gray-200" aria-label="{{ __('Сторінки запису', 'solidshop') }}">
            {!! $pagination !!}
          </nav>
        @endif

        @if (! empty($mentionedProducts))
          @include('partials.blog-mentioned-products')
        @endif

        @if ($postAuthorBio !== '' || $postAuthorName !== '')
          <section class="blog-single__author mt-12 pt-10 border-t border-gray-200" aria-label="{{ __('Про автора', 'solidshop') }}">
            <div class="flex gap-5 items-start">
              @if ($postAuthorAvatar !== '')
                <img
                  src="{{ esc_url($postAuthorAvatar) }}"
                  alt=""
                  width="72"
                  height="72"
                  class="w-[72px] h-[72px] rounded-full object-cover shrink-0"
                  loading="lazy"
                >
              @endif
              <div class="min-w-0">
                @if ($postAuthorName !== '')
                  <p class="text-base font-bold text-gray-900 mb-2">
                    @if ($postAuthorUrl !== '')
                      <a href="{{ esc_url($postAuthorUrl) }}" class="text-gray-900 no-underline hover:text-black transition-colors">
                        {{ $postAuthorName }}
                      </a>
                    @else
                      {{ $postAuthorName }}
                    @endif
                  </p>
                @endif
                @if ($postAuthorBio !== '')
                  <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $postAuthorBio }}
                  </p>
                @endif
              </div>
            </div>
          </section>
        @endif

        @if (! empty($postCategories) || ! empty($postTags))
          <footer class="blog-single__meta mt-8 space-y-2 text-sm text-gray-600">
            @if (! empty($postCategories))
              <p class="m-0">
                <span class="font-semibold text-gray-900">{{ __('Опубліковано в:', 'solidshop') }}</span>
                @foreach ($postCategories as $index => $category)
                  @if ($index > 0)
                    <span aria-hidden="true">, </span>
                  @endif
                  <a href="{{ esc_url($category['url']) }}" class="text-gray-600 hover:text-gray-900 no-underline transition-colors">
                    {{ $category['name'] }}
                  </a>
                @endforeach
              </p>
            @endif

            @if (! empty($postTags))
              <p class="m-0">
                <span class="font-semibold text-gray-900">{{ __('Позначено тегами:', 'solidshop') }}</span>
                @foreach ($postTags as $index => $tag)
                  @if ($index > 0)
                    <span aria-hidden="true">, </span>
                  @endif
                  <a href="{{ esc_url($tag['url']) }}" class="text-gray-600 hover:text-gray-900 no-underline transition-colors">
                    {{ $tag['name'] }}
                  </a>
                @endforeach
              </p>
            @endif
          </footer>
        @endif

        <div class="blog-single__comments mt-12">
          @php(comments_template())
        </div>
      </article>
    </div>

    <div class="lg:col-span-4 order-2">
      @include('partials.blog-sidebar')
    </div>
  </div>
</div>
