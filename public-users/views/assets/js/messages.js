document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-button');
    const chatContainer = document.getElementById('chat-container');
    const charCount = document.getElementById('char-count');

    // Live character count
    input.addEventListener('input', () => {
        charCount.textContent = input.value.length;
    });

    // Send message
    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        const formData = new FormData(form);

        fetch(form.action, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                sendBtn.disabled = false;
                if (data.success) {
                    appendMessage('You', message, true);
                    input.value = '';
                    charCount.textContent = '0';
                } else {
                    alert('Error: ' + (data.error || 'Failed to send'));
                }
            })
            .catch(err => {
                console.error('Send error:', err);
                sendBtn.disabled = false;
            });
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Append message to chat
    function appendMessage(sender, text, isMine) {
        const div = document.createElement('div');
        div.className = 'd-flex mb-3 ' + (isMine ? 'justify-content-end' : 'justify-content-start');
        div.innerHTML = `
            <div class="p-2 rounded" style="max-width:75%;background:${isMine ? '#d1e7dd' : '#fff'};">
                <div class="small text-muted mb-1">${sender}</div>
                <div>${text}</div>
                <div class="small text-muted mt-1 text-end">${new Date().toLocaleString()}</div>
            </div>`;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Real-time fetch every 1 second
    function fetchMessages() {
        fetch(`../controllers/MessagesController.php?action=fetch&sender_id=${currentUserId}&recipient_id=${recipientId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data.messages)) {
                chatContainer.innerHTML = '';
                data.messages.forEach(msg => {
                    appendMessage(
                        msg.sender_name,
                        msg.body,
                        msg.sender_id == currentUserId
                    );
                });
            }
        })
        .catch(err => console.error('Fetch error:', err));
    }

    // Auto refresh messages every second
    setInterval(fetchMessages, 1000);
});
