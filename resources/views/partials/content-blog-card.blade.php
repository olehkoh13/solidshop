{{--
  Blog archive card — image, title, date (reference layout).
  Картка архіву блогу — зображення, заголовок, дата.
--}}
<article @php(post_class('group'))>
  <a href="{{ get_permalink() }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
    @if (has_post_thumbnail())
      <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
        {!! get_the_post_thumbnail(null, 'medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300']) !!}
      </div>
    @else
      <div class="aspect-[4/3] bg-gray-100" aria-hidden="true"></div>
    @endif
  </a>

  <h2 class="entry-title text-xl font-bold mt-5 leading-snug">
    <a href="{{ get_permalink() }}" class="text-gray-900 no-underline hover:text-black transition-colors">
      {!! $title !!}
    </a>
  </h2>

  <time class="block mt-3 text-xs font-medium uppercase tracking-wider text-gray-500" datetime="{{ get_post_time('c', true) }}">
    {{ \App\View\Composers\Blog::formattedPostDate() }}
  </time>
</article>
