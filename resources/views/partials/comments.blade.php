@if (! post_password_required())
  <section id="comments" class="comments blog-comments">
    @if ($responses())
      <h2 class="blog-comments__list-title">
        {!! $title !!}
      </h2>

      <ol class="comment-list blog-comments__list">
        {!! $responses !!}
      </ol>

      @if ($paginated())
        <nav aria-label="{{ __('Коментарі', 'solidshop') }}">
          <ul class="blog-comments__pager">
            @if ($previous())
              <li class="previous">
                {!! $previous !!}
              </li>
            @endif

            @if ($next())
              <li class="next">
                {!! $next !!}
              </li>
            @endif
          </ul>
        </nav>
      @endif
    @endif

    @if ($closed())
      <x-alert type="warning">
        {!! __('Comments are closed.', 'sage') !!}
      </x-alert>
    @endif

    @php(comment_form())
  </section>
@endif
