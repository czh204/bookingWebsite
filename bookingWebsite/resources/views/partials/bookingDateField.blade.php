{{--
    Booking date picker, shared by the flight, hotel and attraction modals.

    A native date input rather than a hand-rolled widget: it opens the
    browser's own mini calendar, works on touch, is keyboard accessible,
    and enforces `min` without a JS date library — which the
    Laravel-and-Bootstrap-only rule rules out anyway.

    `min` is today, so past dates can't be picked. The server re-checks it
    (Cart::add) because `min` only constrains the picker, not a POST.

    Expects: $id — unique element id for the page it's included on.
--}}
@php
    $today = now()->toDateString();
@endphp

<div class="booking-date-field">
    <label class="booking-date-label" for="{{ $id }}">
        <i class="bi bi-calendar3"></i> Booking date
    </label>
    <input type="date"
           id="{{ $id }}"
           class="form-control booking-date-input js-booking-date"
           value="{{ $today }}"
           min="{{ $today }}"
           required>
    <div class="booking-date-error" hidden>Pick a date from today onwards.</div>
</div>
