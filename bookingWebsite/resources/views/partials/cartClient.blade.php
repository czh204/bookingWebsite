{{--
    Shared Add-to-Cart helper used by the flight, hotel and attraction
    modals. Posts the item id, the chosen option key and the booking date -
    the server resolves the price itself, so nothing here can set what
    gets charged, and it re-checks the date so nothing here can backdate.
--}}
<script>
window.Voyagr = window.Voyagr || {};

/** Today as YYYY-MM-DD in the visitor's own timezone, not UTC. */
window.Voyagr.today = function () {
    const now = new Date();
    const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);

    return local.toISOString().slice(0, 10);
};

/**
 * Reads a modal's booking date, showing the inline error and returning
 * null when it's empty or in the past. Catches the two ways `min` can be
 * bypassed: typing into the field, and browsers without date support.
 *
 * The floor is the field's own `min`, which the server rendered, not the
 * browser's clock. Those disagree whenever the app timezone and the
 * visitor's differ — and the server is the one that decides, since it
 * re-checks the date on arrival.
 */
window.Voyagr.readBookingDate = function (input) {
    const wrap = input.closest('.booking-date-field');
    const error = wrap ? wrap.querySelector('.booking-date-error') : null;
    const value = (input.value || '').trim();
    const floor = input.min || window.Voyagr.today();
    const valid = value !== '' && value >= floor;

    input.classList.toggle('is-invalid', !valid);
    if (error) error.hidden = valid;

    return valid ? value : null;
};

window.Voyagr.addToCart = function (payload, button) {
    if (!button || button.disabled) return;

    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = 'Adding...';

    return fetch(@json(route('cart.store')), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    })
        .then(function (response) {
            return response.json().then(body => ({ ok: response.ok, body }));
        })
        .then(function (result) {
            if (!result.ok) {
                throw new Error(result.body.message || 'Could not add that to your cart.');
            }

            window.Voyagr.updateCartBadge(result.body.count);
            button.innerHTML = 'Added <i class="bi bi-check-lg"></i>';

            const restore = function () {
                button.innerHTML = original;
                button.disabled = false;
            };

            const modalEl = button.closest('.modal');

            if (!modalEl) {
                setTimeout(restore, 1500);
                return;
            }

            // The item is in the cart, so the modal has nothing left to do -
            // close it. The button is restored on hide rather than on a timer
            // so reopening the modal never shows a stale "Added" state, and
            // the short delay lets the checkmark register first.
            modalEl.addEventListener('hidden.bs.modal', restore, { once: true });

            setTimeout(function () {
                (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).hide();
            }, 600);
        })
        .catch(function (error) {
            button.innerHTML = original;
            button.disabled = false;
            alert(error.message);
        });
};

/** Keeps the navbar count in step without a page reload. */
window.Voyagr.updateCartBadge = function (count) {
    const link = document.querySelector('.site-nav a[href$="/cart"]');
    if (!link) return;

    let badge = link.querySelector('.cart-count-badge');

    if (!count) {
        if (badge) badge.remove();
        return;
    }

    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-count-badge';
        link.appendChild(badge);
    }

    badge.textContent = count;
};
</script>
