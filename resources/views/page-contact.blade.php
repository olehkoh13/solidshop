<?php /* Template Name: Contact */ ?>
{{--
  Template Name: Contact
  Slug: contact — B2B contact page with map, FAQ, and AJAX form.
  Slug: contact — B2B контакти з картою, FAQ та AJAX формою.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php(the_post())
    @include('partials.contact-page')
  @endwhile
@endsection
