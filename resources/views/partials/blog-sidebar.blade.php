{{--
  Blog archive sidebar — author, shop blocks, archives, categories, tags.
  Sidebar архіву блогу — автор, магазин, архів, категорії, теги.
--}}
<aside class="blog-sidebar lg:sticky lg:top-24 lg:self-start space-y-10" aria-label="{{ __('Sidebar блогу', 'solidshop') }}">
  {{-- About blog / Про блог --}}
  <section class="blog-sidebar__widget">
    @if ($blogAuthorAvatar !== '')
      <img
        src="{{ esc_url($blogAuthorAvatar) }}"
        alt=""
        width="80"
        height="80"
        class="w-20 h-20 rounded-full object-cover mb-4"
        loading="lazy"
      >
    @endif
    <h2 class="blog-sidebar__title text-lg font-bold text-gray-900 mb-3">
      {{ __('Блог', 'solidshop') }} {{ get_bloginfo('name') }}
    </h2>
    <p class="text-sm text-gray-600 leading-relaxed">
      {{ $blogAuthorBio }}
    </p>
  </section>

  {{-- B2B: featured products / Рекомендовані товари --}}
  @if (! empty($sidebarFeaturedProducts))
    <section class="blog-sidebar__widget">
      <h2 class="blog-sidebar__heading">{{ __('Рекомендовані товари', 'solidshop') }}</h2>
      <ul class="space-y-4">
        @foreach ($sidebarFeaturedProducts as $product)
          <li>
            <a href="{{ esc_url($product['url']) }}" class="flex gap-3 no-underline group">
              @if ($product['image'] !== '')
                <img
                  src="{{ esc_url($product['image']) }}"
                  alt=""
                  width="64"
                  height="64"
                  class="w-16 h-16 object-cover border border-gray-200 shrink-0"
                  loading="lazy"
                >
              @else
                <span class="w-16 h-16 bg-gray-100 border border-gray-200 shrink-0" aria-hidden="true"></span>
              @endif
              <span class="min-w-0">
                <span class="block text-sm font-bold text-gray-900 group-hover:text-black transition-colors line-clamp-2">
                  {{ $product['name'] }}
                </span>
                <span class="block text-sm text-gray-600 mt-1">{!! $product['price_html'] !!}</span>
              </span>
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  {{-- B2B: shop categories / Категорії магазину --}}
  @if (! empty($sidebarProductCats))
    <section class="blog-sidebar__widget">
      <h2 class="blog-sidebar__heading">{{ __('Каталог', 'solidshop') }}</h2>
      <ul class="blog-sidebar__list">
        @foreach ($sidebarProductCats as $cat)
          <li>
            <a href="{{ esc_url($cat['url']) }}" class="blog-sidebar__link">
              {{ $cat['name'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  {{-- B2B CTA / Зв'язок з менеджером --}}
  <section class="blog-sidebar__widget border border-gray-200 p-6">
    <h2 class="blog-sidebar__heading mb-2">{{ __('B2B-підтримка', 'solidshop') }}</h2>
    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
      {{ __('Потрібна консультація щодо оптових замовлень?', 'solidshop') }}
    </p>
    <a
      href="{{ esc_url(home_url('/contacts/')) }}"
      class="inline-block text-sm font-bold uppercase tracking-wider text-gray-900 no-underline hover:text-black transition-colors"
    >
      {{ __('Звʼязатися з менеджером', 'solidshop') }} →
    </a>
  </section>

  {{-- Archive / Архів --}}
  @if (! empty($sidebarArchives))
    <section class="blog-sidebar__widget">
      <h2 class="blog-sidebar__heading">{{ __('Архів', 'solidshop') }}</h2>
      <ul class="blog-sidebar__list">
        @foreach ($sidebarArchives as $archive)
          <li>
            <a href="{{ esc_url($archive['url']) }}" class="blog-sidebar__link">
              {{ $archive['label'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  {{-- Categories / Категорії --}}
  @if (! empty($sidebarCategories))
    <section class="blog-sidebar__widget">
      <h2 class="blog-sidebar__heading">{{ __('Категорії', 'solidshop') }}</h2>
      <ul class="blog-sidebar__list">
        @foreach ($sidebarCategories as $category)
          <li>
            <a href="{{ esc_url($category['url']) }}" class="blog-sidebar__link">
              {{ $category['name'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  {{-- Tags / Теги --}}
  @if (! empty($sidebarTags))
    <section class="blog-sidebar__widget">
      <h2 class="blog-sidebar__heading">{{ __('Теги', 'solidshop') }}</h2>
      <ul class="blog-sidebar__tags flex flex-wrap gap-2">
        @foreach ($sidebarTags as $tag)
          <li>
            <a href="{{ esc_url(get_tag_link($tag->term_id)) }}" class="blog-sidebar__tag">
              {{ $tag->name }}
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  @endif
</aside>
