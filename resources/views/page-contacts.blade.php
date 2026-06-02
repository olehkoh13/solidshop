<?php /* Template Name: Contact */ ?>
{{--
  Template Name: Contact
  Slug: contacts — matches header link /contacts/
  Slug: contacts — відповідає посиланню в шапці /contacts/
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php(the_post())
    @include('partials.contact-page')
  @endwhile
@endsection
