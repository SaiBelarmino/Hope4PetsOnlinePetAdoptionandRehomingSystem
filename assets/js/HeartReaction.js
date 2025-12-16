// HeartReaction.js
// Handles heart (like) button logic for posts

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.heart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (btn.hasAttribute('disabled')) return;
            var postId = btn.getAttribute('data-post-id');
            var userHeart = btn.getAttribute('data-user-heart') === '1';
            var heartCountEl = btn.querySelector('.heart-count');
            var count = parseInt(heartCountEl.textContent) || 0;
            // Optimistic UI update
            if (userHeart) {
                btn.classList.remove('hearted');
                btn.setAttribute('data-user-heart', '0');
                heartCountEl.textContent = Math.max(0, count - 1);
            } else {
                btn.classList.add('hearted');
                btn.setAttribute('data-user-heart', '1');
                heartCountEl.textContent = count + 1;
            }
            // Send AJAX request to server
            fetch('../api/heart_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, action: userHeart ? 'unheart' : 'heart' })
            })
            .then(r => r.json())
            .then(data => {
                if (typeof data.heart_count !== 'undefined') {
                    heartCountEl.textContent = data.heart_count;
                }
                if (typeof data.user_hearted !== 'undefined') {
                    if (data.user_hearted) {
                        btn.classList.add('hearted');
                        btn.setAttribute('data-user-heart', '1');
                    } else {
                        btn.classList.remove('hearted');
                        btn.setAttribute('data-user-heart', '0');
                    }
                }
            })
            .catch(() => {
                // Revert UI on error
                if (userHeart) {
                    btn.classList.add('hearted');
                    btn.setAttribute('data-user-heart', '1');
                    heartCountEl.textContent = count;
                } else {
                    btn.classList.remove('hearted');
                    btn.setAttribute('data-user-heart', '0');
                    heartCountEl.textContent = count;
                }
                alert('Failed to update heart.');
            });
        });
    });
});
