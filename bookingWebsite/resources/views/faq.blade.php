@extends('layouts.homeApp')

@section('title', 'FAQ')

@push('styles')
<style>
    .faq-hero {
        background: var(--navy);
        color: #fff;
        padding: 2.25rem 1.5rem;
    }

    .faq-hero h1 { font-size: 2rem; margin-bottom: .2rem; }
    .faq-hero p { opacity: .8; margin: 0; font-size: .9rem; }

    .faq-body { max-width: 760px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    .faq-item {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1.1rem 1.25rem;
    }

    .faq-item + .faq-item { margin-top: .75rem; }
    .faq-item h2 { font-size: 1.05rem; font-weight: 700; color: var(--navy-dark); margin-bottom: .35rem; }
    .faq-item p { margin: 0; color: #4b5563; font-size: .9rem; line-height: 1.55; }

    .faq-note {
        margin-top: 1.5rem;
        font-size: .85rem;
        color: var(--text-muted);
        text-align: center;
    }

</style>
@endpush

@section('content')

<section class="faq-hero">
    <div class="mx-auto" style="max-width: 760px;">
        <h1 class="font-serif fw-bold">Frequently Asked Questions</h1>
        <p>Booking, payment and cancellation, answered</p>
    </div>
</section>

<div class="faq-body">
    @foreach ($faqs as $faq)
        <div class="faq-item">
            <h2>{{ $faq['question'] }}</h2>
            <p>{{ $faq['answer'] }}</p>
        </div>
    @endforeach

    <div class="faq-note">
        Still stuck? Ask the assistant in the chat bubble at the bottom of any page.
    </div>
</div>

@endsection
