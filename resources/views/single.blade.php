@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php(the_post())
    @include('partials.single-post')
  @endwhile
@endsection
