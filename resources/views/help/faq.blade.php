@extends('layouts.app')

@section('title', 'FAQ — TSL')
@section('description', 'Answers about TSL locks, daily interest, VIP levels, payments and bonus codes.')

@section('content')
<header class="page-head">
    <p class="kicker">Help</p>
    <h1>Frequently asked questions</h1>
    <p class="sub">Short answers about buying a lock, cashing out, VIP levels and bonus codes.</p>
</header>

<section class="faq-list">
    @foreach ($questions as $item)
        <details class="faq-item">
            <summary>{{ $item['q'] }}</summary>
            <p>{{ $item['a'] }}</p>
        </details>
    @endforeach
</section>
@endsection
