@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php
      the_post();
      $solidshop_hide_page_header = (function_exists('is_checkout') && is_checkout())
        || (function_exists('is_cart') && is_cart())
        || (function_exists('is_account_page') && is_account_page() && is_user_logged_in());
    @endphp

    @if (function_exists('is_account_page') && is_account_page() && ! is_user_logged_in())
      @include('partials.account-guest-header')
    @elseif (! $solidshop_hide_page_header)
      @include('partials.page-header')
    @endif

    @includeFirst(['partials.content-page', 'partials.content'])
  @endwhile
@endsection
