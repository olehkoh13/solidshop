<article @php(post_class('group flex flex-col h-full border border-transparent hover:border-gray-200 transition bg-white rounded-none'))>
  <a href="{{ get_permalink() }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
    @if (has_post_thumbnail())
      <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
        {!! get_the_post_thumbnail(null, 'medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300']) !!}
      </div>
    @else
      <div class="aspect-[16/9] bg-gray-100" aria-hidden="true"></div>
    @endif
  </a>

  <div class="p-6 flex flex-col flex-grow">
    <div class="text-xs text-gray-500">
      @include('partials.entry-meta')
    </div>

    <h2 class="entry-title text-lg font-bold mt-4">
      <a href="{{ get_permalink() }}" class="text-gray-900 no-underline hover:text-black transition-colors">
        {!! $title !!}
      </a>
    </h2>

    <div class="entry-summary text-gray-600 text-sm mt-2 flex-grow leading-relaxed">
      @php(the_excerpt())
    </div>

    <a
      href="{{ get_permalink() }}"
      class="mt-4 text-sm font-bold uppercase tracking-wider text-gray-900 no-underline hover:text-black transition-colors inline-flex items-center gap-1"
    >
      {{ __('Читати далі', 'solidshop') }}
      <span aria-hidden="true">→</span>
    </a>
  </div>
</article>
