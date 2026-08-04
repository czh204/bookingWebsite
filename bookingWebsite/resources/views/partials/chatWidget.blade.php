<div class="chat-bubble-btn" id="chatBubbleBtn" role="button" aria-label="Open support chat">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="chat-bubble-dot"></span>
</div>

<div class="chat-panel d-none" id="chatPanel">
    <div class="chat-panel-header">
        <div class="d-flex align-items-center gap-2">
            <span class="chat-header-icon"><i class="bi bi-stars"></i></span>
            <div>
                <div class="chat-header-title">Support Assistant</div>
                <div class="chat-header-subtitle">Usually replies instantly</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="chat-icon-btn" id="chatMinimizeBtn" aria-label="Minimize"><i class="bi bi-dash-lg"></i></button>
            <button type="button" class="chat-icon-btn" id="chatCloseBtn" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <div class="chat-quick-questions">
        <div class="chat-quick-questions-label">Quick Questions</div>
        <div class="d-flex flex-column gap-2">
            <button type="button" class="chat-quick-btn" data-question="How do I cancel my booking?">How do I cancel my booking?</button>
            <button type="button" class="chat-quick-btn" data-question="What is your refund policy?">What is your refund policy?</button>
            <button type="button" class="chat-quick-btn" data-question="How do I modify my reservation?">How do I modify my reservation?</button>
            <button type="button" class="chat-quick-btn" data-question="What payment methods do you accept?">What payment methods do you accept?</button>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <div class="chat-message chat-message-bot">
            <span class="chat-avatar"><i class="bi bi-stars"></i></span>
            <div class="chat-bubble">Hello! I'm here to help with any questions about your bookings. Click a quick question or type below.</div>
        </div>
    </div>

    <form class="chat-input-row" id="chatForm">
        <input type="text" class="chat-input" id="chatInput" placeholder="Type a question..." autocomplete="off">
        <button type="submit" class="chat-send-btn" aria-label="Send"><i class="bi bi-send-fill"></i></button>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bubbleBtn   = document.getElementById('chatBubbleBtn');
    const panel       = document.getElementById('chatPanel');
    const minimizeBtn = document.getElementById('chatMinimizeBtn');
    const closeBtn    = document.getElementById('chatCloseBtn');
    const messages    = document.getElementById('chatMessages');
    const form        = document.getElementById('chatForm');
    const input       = document.getElementById('chatInput');
    const quickBtns   = document.querySelectorAll('.chat-quick-btn');

    const cannedReplies = {
        'How do I cancel my booking?': 'You can cancel a booking from My Trips — open the booking and select "Cancel Reservation". Refund eligibility depends on the fare rules.',
        'What is your refund policy?': 'Most bookings can be refunded in full within 24 hours of purchase, and partially refunded after that based on the fare type.',
        'How do I modify my reservation?': 'Go to My Trips, open the booking, and choose "Modify Reservation" to change dates, room type, or passenger details.',
        'What payment methods do you accept?': 'We accept all major credit/debit cards, PayPal, and popular e-wallets at checkout.'
    };
    const fallbackReply = "Thanks for your message! One of our support agents will follow up shortly. In the meantime, feel free to try one of the quick questions above.";

    function openPanel() {
        panel.classList.remove('d-none');
        bubbleBtn.classList.add('d-none');
        input.focus();
    }

    function closePanel() {
        panel.classList.add('d-none');
        bubbleBtn.classList.remove('d-none');
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addUserMessage(text) {
        const row = document.createElement('div');
        row.className = 'chat-message chat-message-user';
        row.innerHTML = '<div class="chat-bubble chat-bubble-user"></div>';
        row.querySelector('.chat-bubble-user').textContent = text;
        messages.appendChild(row);
        scrollToBottom();
    }

    function addBotMessage(text) {
        const row = document.createElement('div');
        row.className = 'chat-message chat-message-bot';
        row.innerHTML = '<span class="chat-avatar"><i class="bi bi-stars"></i></span><div class="chat-bubble"></div>';
        row.querySelector('.chat-bubble').textContent = text;
        messages.appendChild(row);
        scrollToBottom();
    }

    function respondTo(question) {
        addUserMessage(question);
        setTimeout(function () {
            addBotMessage(cannedReplies[question] || fallbackReply);
        }, 500);
    }

    bubbleBtn.addEventListener('click', openPanel);
    minimizeBtn.addEventListener('click', closePanel);
    closeBtn.addEventListener('click', closePanel);

    quickBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            respondTo(btn.dataset.question);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        respondTo(text);
        input.value = '';
    });
});
</script>
@endpush
