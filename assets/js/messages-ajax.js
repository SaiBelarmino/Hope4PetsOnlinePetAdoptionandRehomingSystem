(function(){
    // messages-ajax.js
    if (typeof window.MESSAGES_CONFIG === 'undefined') return;
    var cfg = window.MESSAGES_CONFIG || {};
    var otherId = parseInt(cfg.otherId || 0);
    var authUserId = parseInt(cfg.authUserId || 0);
    var fetchUrl = cfg.fetchUrl || './ajax/messages_fetch.php';
    var sendUrl = cfg.sendUrl || './ajax/messages_send.php';
    if (!otherId || !authUserId) return;

    var chatList = document.getElementById('chatList');
    var chatContainer = document.getElementById('chatContainer');
    var form = document.getElementById('sendMessageForm');
    var bodyInput = document.getElementById('messageBody');
    var recipientInput = document.getElementById('recipient_id');

    // Initialize last id
    function initLastId() {
        var existing = chatList ? chatList.querySelectorAll('li[data-msg-id]') : [];
        var lastId = 0;
        if (existing.length) {
            lastId = parseInt(existing[existing.length-1].getAttribute('data-msg-id')) || 0;
        }
        if (chatList) chatList.setAttribute('data-last-id', lastId);
    }
    initLastId();

    function scrollToBottom() {
        if (!chatContainer) return;
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function renderMessage(m) {
        var isOutgoing = (parseInt(m.sender_id || m.senderId || 0) === authUserId) || (m.is_outgoing === 1);
        var li = document.createElement('li');
        li.className = 'mb-3 d-flex ' + (isOutgoing ? 'flex-row-reverse text-end' : '');
        li.setAttribute('data-msg-id', m.id || m.ID || 0);

        var img = document.createElement('img');
        img.src = isOutgoing ? 'https://bootdey.com/img/Content/user_1.jpg' : 'https://bootdey.com/img/Content/user_3.jpg';
        img.className = 'rounded-circle ' + (isOutgoing ? 'ms-2' : 'me-2');
        img.width = 40; img.height = 40;

        var wrapper = document.createElement('div');
        var meta = document.createElement('div');
        meta.className = 'small text-muted';
        var name = isOutgoing ? 'You' : (m.sender_name || ('User #' + (m.sender_id||m.senderId||0)));
        var time = '';
        try { if (m.created_at) time = ' • ' + new Date(m.created_at).toLocaleString(); } catch(e){}
        meta.textContent = name + time;

        var bubble = document.createElement('div');
        bubble.className = 'p-2 rounded ' + (isOutgoing ? 'bg-primary text-white' : 'bg-light');
        bubble.innerHTML = (m.body ? (''+m.body).replace(/\n/g, '<br/>') : '');

        wrapper.appendChild(meta);
        wrapper.appendChild(bubble);

        li.appendChild(img);
        li.appendChild(wrapper);
        return li;
    }

    function poll() {
        if (!chatList) return;
        var after = parseInt(chatList.getAttribute('data-last-id')) || 0;
        fetch(fetchUrl + '?u=' + otherId + '&after_id=' + after, {cache: 'no-store'})
            .then(function(res){ return res.json(); })
            .then(function(json){
                if (!json || !json.success) return;
                var msgs = json.messages || [];
                if (!msgs.length) return;
                msgs.forEach(function(m){
                    var li = renderMessage(m);
                    chatList.appendChild(li);
                    var mid = parseInt(m.id || m.ID || 0);
                    if (mid && mid > (parseInt(chatList.getAttribute('data-last-id'))||0)) {
                        chatList.setAttribute('data-last-id', mid);
                    }
                });
                scrollToBottom();
            }).catch(function(err){
                // silent
            });
    }

    // Start polling
    setInterval(poll, 2000);
    setTimeout(poll, 500);

    // Send form handler
    if (form) {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var body = bodyInput.value.trim();
            var recipient = parseInt(recipientInput.value) || 0;
            if (!body || !recipient) return;

            bodyInput.disabled = true;
            var btn = document.getElementById('sendBtn'); if (btn) btn.disabled = true;

            var formData = new FormData();
            formData.append('recipient_id', recipient);
            formData.append('body', body);

            fetch(sendUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(function(res){ return res.json(); })
                .then(function(json){
                    if (json && json.success) {
                        var m = json.message || {};
                        var last = parseInt(chatList.getAttribute('data-last-id')) || 0;
                        var mid = parseInt(m.id || m.message_id || 0) || (last + 1);
                        var msgObj = { id: mid, sender_id: authUserId, body: body, created_at: m.created_at || new Date().toISOString() };
                        var li = renderMessage(msgObj);
                        chatList.appendChild(li);
                        chatList.setAttribute('data-last-id', mid);
                        bodyInput.value = '';
                        scrollToBottom();
                    } else {
                        alert((json && json.message) ? json.message : 'Failed to send');
                    }
                }).catch(function(){
                    alert('Network error');
                }).finally(function(){
                    bodyInput.disabled = false;
                    if (btn) btn.disabled = false;
                    bodyInput.focus();
                });
        });
    }

    // ensure recipient id from URL
    (function(){
        try {
            var url = new URL(window.location.href);
            var u = url.searchParams.get('u');
            var rid = document.getElementById('recipient_id');
            if (u && rid && (!rid.value || rid.value === '0')) {
                rid.value = u;
            }
        } catch(e){}
    })();
})();
