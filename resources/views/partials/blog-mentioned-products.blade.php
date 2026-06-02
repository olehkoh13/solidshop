{{--
  Mentioned products grid on single post.
  Сітка згаданих товарів у single post.
--}}
<section class="blog-mentioned mt-12 pt-10 border-t border-gray-200" aria-labelledby="blog-mentioned-heading">
  <h2 id="blog-mentioned-heading" class="blog-mentioned__heading text-xl font-bold text-gray-900 mb-6">
    {{ __('Згадано в цій статті', 'solidshop') }}
  </h2>

  <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 list-none m-0 p-0">
    @foreach ($mentionedProducts as $product)
      <li>
        <article class="blog-mentioned__card group h-full flex flex-col">
          <a href="{{ esc_url($product['url']) }}" class="block relative overflow-hidden bg-gray-50 border border-gray-200 aspect-square no-underline">
            @if ($product['on_sale'])
              <span class="blog-mentioned__badge absolute top-2 left-2 z-10">
                {{ __('Sale', 'solidshop') }}
              </span>
            @endif

            @if ($product['image'] !== '')
              <img
                src="{{ esc_url($product['image']) }}"
                alt=""
                width="320"
                height="320"
                class="w-full h-full object-cover object-center transition-transform duration-300 group-hover:scale-[1.02]"
                loading="lazy"
              >
            @else
              <span class="block w-full h-full bg-gray-100" aria-hidden="true"></span>
            @endif
          </a>

          <div class="pt-4 flex flex-col flex-grow">
            @if ($product['category'] !== '')
              <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                {{ $product['category'] }}
              </span>
            @endif

            <h3 class="text-sm font-bold text-gray-900 mb-2 leading-snug">
              <a href="{{ esc_url($product['url']) }}" class="text-gray-900 no-underline hover:text-black transition-colors">
                {{ $product['name'] }}
              </a>
            </h3>

            @if ($product['rating_html'] !== '')
              <div class="blog-mentioned__rating mb-2">
                {!! $product['rating_html'] !!}
              </div>
            @endif

            <div class="text-sm font-black text-gray-950 mt-auto">
              {!! $product['price_html'] !!}
            </div>
          </div>
        </article>
      </li>
    @endforeach
  </ul>
</section>
