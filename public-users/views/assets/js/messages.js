document.addEventListener('DOMContentLoaded', function() {
    // --- Global Element Variables ---
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-button');
    const chatContainer = document.getElementById('chat-container');
    const charCount = document.getElementById('char-count');
    
    // --- PHP-Defined Global Variables ---
    // Siguraduhin na ang mga ito ay tama ang pagkakadeklara sa iyong PHP file
    const currentUserId = window.CURRENT_USER_ID;
    const recipientId = window.RECIPIENT_ID;
    let lastMessageId = window.INITIAL_LAST_MESSAGE_ID || 0; 
    
    const CURRENT_USER_AVATAR = window.CURRENT_USER_AVATAR;
    // Use server-provided recipient avatar when available, otherwise fallback to DOM or default
    const RECIPIENT_AVATAR = (typeof window.RECIPIENT_AVATAR !== 'undefined' && window.RECIPIENT_AVATAR) ? window.RECIPIENT_AVATAR : (document.querySelector('.card-header img.rounded-circle.me-1')?.src || '/assets/img/default-avatar.png');


    // --- Helper Function: Forced Scroll ---
    function scrollToBottom() {
        // Tinitiyak na ang scroll ay nasa pinakababa
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // --- Append Message Function (Final na format para sa picture) ---
    function appendMessage(text, isMine, shouldAutoScroll, imageUrl) {
        const div = document.createElement('div');
        
        let imgHtml = '';
        // Magpapakita lang ng image kung ang mensahe ay HINDI galing sa iyo
        if (!isMine) {
            imgHtml = imageUrl ? 
                `<img src="${imageUrl}" alt="User Image" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin: 0 5px;">` : 
                `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white me-2 ms-2" style="min-width: 40px; height: 40px; font-size: 1.2rem;">R</div>`;
        }
            
        // Ang chat bubble content
        const messageContent = `
            <div class="p-2 rounded" style="max-width:75%;background:${isMine ? '#d1e7dd' : '#fff'};">
                <div>${text.replace(/\n/g, '<br>')}</div>
            </div>`;
        
        // Structure: Walang larawan para sa sarili mo (isMine = true)
        if (isMine) {
            div.className = 'd-flex mb-3 justify-content-end align-items-end';
            div.innerHTML = messageContent;
        } else {
            div.className = 'd-flex mb-3 justify-content-start align-items-end';
            div.innerHTML = imgHtml + messageContent;
        }

        chatContainer.appendChild(div);
        
        // Auto-scroll logic:
        if (shouldAutoScroll === true) {
            scrollToBottom();
        } else {
            const isNearBottom = chatContainer.scrollTop + chatContainer.clientHeight >= chatContainer.scrollHeight - 100;
            if (isNearBottom) {
                scrollToBottom();
            }
        }
    }

    // --- Send Message Function (Kasama ang Auto-Clear at Auto-Focus) ---
    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        const formData = new FormData(form);

    fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData })
            .then(res => res.json())
            .then(data => {
                sendBtn.disabled = false;
                if (data.success) {
                    // APPEND MESSAGE SA SARILI MONG VIEW AGAD
                    appendMessage(message, true, true, CURRENT_USER_AVATAR); 
                    
                    // 🎯 Auto-Clear at Auto-Focus:
                    input.value = '';
                    charCount.textContent = '0';
                    input.focus(); 
                    
                    // Update lastMessageId kung ibinalik ito ng server
                    if (data.message_id) {
                        lastMessageId = data.message_id;
                    } else {
                         // Fallback: Force update the fetcher
                         fetchMessages();
                    }
                } else {
                    alert('Error: ' + (data.error || 'Failed to send'));
                }
            })
            .catch(err => {
                console.error('Send error:', err);
                sendBtn.disabled = false;
                // Optional: I-clear pa rin ang input kahit may error, pero mananatili ang focus
                input.value = ''; 
                charCount.textContent = '0';
                input.focus();
            });
    }

    // --- Real-time fetch for current conversation (Live Chat Polling) ---
    function fetchMessages() {
        // I-assume na ang PHP mo ay walang filtering function, kaya ito ang safest URL.
        const url = `../controllers/MessageController.php?action=conversation&other=${recipientId}`;
        
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.messages)) {
                
                const latestMessage = data.messages[data.messages.length - 1];
                const latestId = latestMessage ? (latestMessage.id || 0) : 0;
                
                // Kung ang pinakabagong ID mula sa server ay mas mataas sa huling nakita natin
                if (latestId > lastMessageId) {
                    
                    // Determine if user is currently scrolled near bottom
                    let isUserScrolledDown = chatContainer.scrollTop + chatContainer.clientHeight >= chatContainer.scrollHeight - 100;
                    
                    data.messages.forEach(msg => {
                        // CLIENT-SIDE FILTERING: Only append messages with a higher ID
                        if (msg.id > lastMessageId) {
                            const isMine = msg.sender_id == currentUserId;
                            const imageUrl = isMine ? CURRENT_USER_AVATAR : RECIPIENT_AVATAR;

                            appendMessage(msg.message, isMine, isUserScrolledDown, imageUrl);
                            lastMessageId = msg.id; // I-update ang huling ID
                        }
                    });
                } else if (latestId > 0 && lastMessageId === 0) {
                     // Initial Load fallback (kung hindi na-initialize nang tama ang PHP)
                     chatContainer.innerHTML = '';
                     data.messages.forEach(msg => {
                         const isMine = msg.sender_id == currentUserId;
                         const imageUrl = isMine ? CURRENT_USER_AVATAR : RECIPIENT_AVATAR;
                         appendMessage(msg.message, isMine, false, imageUrl);
                         if (msg.id > lastMessageId) lastMessageId = msg.id;
                     });
                     scrollToBottom();
                }
            }
        })
        .catch(err => {
            console.error('fetchMessages error:', err);
        });
    }

    // --- Event Listeners and Auto refresh setup ---
    
    // Submit form handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });

    // Click handler for the Send button (the button is type="button" in the markup)
    // Ensure clicking the visible button triggers the same send flow as pressing Enter
    if (sendBtn) {
        sendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    // Character count 
    if (charCount) {
        input.addEventListener('input', function() {
            charCount.textContent = input.value.length;
        });
    }
    
    // Enter key submit
    input.addEventListener('keydown', function(e) {
        // Nagse-send ng mensahe kapag pinindot ang ENTER, pero hindi ang Shift+Enter
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Auto refresh setup (Polling for new messages)
    if (recipientId > 0) {
        // Polling: 1 second for live chat
        setInterval(fetchMessages, 1000); 
        
        // Initial call kung hindi na-set ang ID (for initial sync)
        if (lastMessageId === 0) {
             fetchMessages();
        }
    }
    
    // 🎯 Final: Initial focus on load for convenience
    input.focus();
});