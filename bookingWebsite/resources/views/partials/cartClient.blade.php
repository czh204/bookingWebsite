{{--
    Shared Add-to-Cart helper used by the flight, hotel and attraction
    modals. Posts only the item id and the chosen option key - the server
    resolves the price itself, so nothing here can set what gets charged.
--}}
<script>
window.Voyagr = window.Voyagr || {};

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

            setTimeout(function () {
                button.innerHTML = original;
                button.disabled = false;
            }, 1500);
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
