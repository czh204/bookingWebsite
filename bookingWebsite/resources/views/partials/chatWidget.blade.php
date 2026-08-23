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
            <div class="chat-bubble">Hello! I'm here to help with any questions about your bookings. Click a quick question or type your prompt below.</div>
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
    const endpoint  = @json(route('chat.send'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Returned by the server on the first reply, then echoed back so the
    // agent keeps the thread instead of starting over each message.
    let conversationId = null;
    let sending = false;

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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // The assistant naturally formats its results link as markdown
    // ([text](url)) — this is the only markdown syntax it's expected to
    // use, so rather than pull in a markdown library, just turn that one
    // pattern into a real link. Text is HTML-escaped first, and only
    // http(s) URLs are linkified, so nothing in the model's output (or a
    // malicious tool result) can inject markup or a javascript: URL.
    function renderBotText(text) {
        const escaped = escapeHtml(text);

        return escaped.replace(
            /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>'
        );
    }

    function addBotMessage(text) {
        const row = document.createElement('div');
        row.className = 'chat-message chat-message-bot';
        row.innerHTML = '<span class="chat-avatar"><i class="bi bi-stars"></i></span><div class="chat-bubble"></div>';
        row.querySelector('.chat-bubble').innerHTML = renderBotText(text);
        messages.appendChild(row);
        scrollToBottom();
    }

    function addTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'chat-message chat-message-bot';
        row.id = 'chatTyping';
        row.innerHTML = '<span class="chat-avatar"><i class="bi bi-stars"></i></span>'
            + '<div class="chat-bubble chat-typing"><span></span><span></span><span></span></div>';
        messages.appendChild(row);
        scrollToBottom();
        return row;
    }

    function setSending(state) {
        sending = state;
        input.disabled = state;
        form.querySelector('.chat-send-btn').disabled = state;
        quickBtns.forEach(function (btn) { btn.disabled = state; });
    }

    async function respondTo(question) {
        if (sending) return;

        addUserMessage(question);
        setSending(true);
        const typing = addTypingIndicator();

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: question,
                    conversation_id: conversationId,
                }),
            });

            const data = await res.json().catch(function () { return {}; });
            typing.remove();

            if (data.conversation_id) {
                conversationId = data.conversation_id;
            }

            if (res.status === 429) {
                addBotMessage('Too many messages — please wait a moment before sending another.');
            } else {
                addBotMessage(data.reply || 'Sorry — I could not get a response. Please try again.');
            }
        } catch (error) {
            typing.remove();
            addBotMessage('Sorry — I could not reach the server. Please check your connection and try again.');
        } finally {
            setSending(false);
            input.focus();
        }
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
