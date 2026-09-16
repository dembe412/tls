@extends('layouts.app')

@section('title', 'All TSL products')
@section('description', 'The full TSL catalogue — smart locks plus VIP marketing benefits.')

@section('content')
@php $tab = 'products'; @endphp

<header class="page-head">
    <p class="kicker">Catalogue</p>
    <h1>All products</h1>
</header>

<section class="catalog">
    <div class="lock-grid">
        @foreach ($products as $product)
            @include('partials.lock-card', ['product' => $product])
        @endforeach
    </div>
</section>

@if ($vips->isNotEmpty())
    @include('partials.vip-board', ['vips' => $vips])
@endif
@endsection
