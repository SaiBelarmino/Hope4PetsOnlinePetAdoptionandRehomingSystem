// HeartReaction.js
// Standalone JS for heart reaction button (for PHP/HTML integration)
// Usage: <button class="heart-btn" data-post-id="123"><span class="heart-icon"></span><span class="heart-count">0</span></button>
(function(){
  const HEART_COLOR = '#f91880';
  const STORAGE_KEY = 'heart_reactions';
  // SVG heart icon (filled/outline)
  function getHeartSVG(filled) {
    return filled
      ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="'+HEART_COLOR+'" stroke="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-6.7-5.2-9.2-8.1C-1.2 8.2 2.2 3 7 3c2.1 0 4.1 1.2 5 3.1C13.9 4.2 15.9 3 18 3c4.8 0 8.2 5.2 4.2 9.9C18.7 15.8 12 21 12 21z"/></svg>'
      : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="'+HEART_COLOR+'" stroke-width="2" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-6.7-5.2-9.2-8.1C-1.2 8.2 2.2 3 7 3c2.1 0 4.1 1.2 5 3.1C13.9 4.2 15.9 3 18 3c4.8 0 8.2 5.2 4.2 9.9C18.7 15.8 12 21 12 21z"/></svg>';
  }
  // LocalStorage helpers
  function getLocalHearts() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}'); } catch { return {}; }
  }
  function setLocalHearts(obj) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
  }
  function isHearted(postId) {
    const hearts = getLocalHearts();
    return !!hearts[postId];
  }
  function setHearted(postId, val) {
    const hearts = getLocalHearts();
    if(val) hearts[postId]=1; else delete hearts[postId];
    setLocalHearts(hearts);
  }
  // AJAX helpers
  function sendHeart(postId, liked, cb) {
    fetch('../api/heart_reaction.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({post_id: postId, liked: liked?1:0})
    })
    .then(r=>r.json())
    .then(data => {
      cb && cb(data);
      // Always refresh count after a reaction
      refreshHeartCount(postId);
    })
    .catch(()=>{
      cb && cb({success:false});
      refreshHeartCount(postId);
    });
  }

  // Always fetch the latest heart count from the server
  function refreshHeartCount(postId) {
    // Find all heart buttons for this postId (in case of multiple on page)
    document.querySelectorAll('.heart-btn[data-post-id="'+postId+'"]').forEach(function(btn){
      const icon = btn.querySelector('.heart-icon');
      const countSpan = btn.querySelector('.heart-count');
      fetch('../api/heart_reaction.php?post_id='+encodeURIComponent(postId))
        .then(r=>r.json())
        .then(data => {
          if(data && typeof data.count==='number') {
            countSpan.textContent = data.count;
            // Update hearted state if returned
            if(typeof data.liked==='boolean') {
              icon.innerHTML = getHeartSVG(data.liked);
              btn.classList.toggle('hearted', data.liked);
            }
          }
        });
    });
  }
  // Main binding
  function bindHearts(){
    document.querySelectorAll('.heart-btn').forEach(function(btn){
      if(btn.dataset.heartBound==='1') return;
      btn.dataset.heartBound='1';
      const postId = btn.getAttribute('data-post-id');
      const icon = btn.querySelector('.heart-icon');
      const countSpan = btn.querySelector('.heart-count');
      // Always show outline heart and count by default
      function updateUI(newCount, forceLiked) {
        const isLiked = typeof forceLiked==='boolean' ? forceLiked : isHearted(postId);
        icon.innerHTML = getHeartSVG(isLiked);
        btn.classList.toggle('hearted', isLiked);
        if(newCount!==undefined) countSpan.textContent = newCount;
      }
      // Initial UI: always outline (not colored) unless user hearted
      updateUI(parseInt(countSpan.textContent,10)||0, false);
      btn.addEventListener('click', function(){
        // Prevent double click
        if(btn.classList.contains('heart-anim')) return;
        btn.classList.add('heart-anim');
        setTimeout(()=>btn.classList.remove('heart-anim'), 250);
        let liked = isHearted(postId);
        // Toggle hearted state
        liked = !liked;
        setHearted(postId, liked);
        // Optimistic UI: update count and color
        let count = parseInt(countSpan.textContent,10)||0;
        count = liked ? count+1 : Math.max(0,count-1);
        updateUI(count, liked);
        // After server responds, update UI to match server (responsive to unreact)
        sendHeart(postId, liked, function(data){
          if(data && typeof data.count==='number') {
            // Use server count and liked state
            updateUI(data.count, typeof data.liked==='boolean' ? data.liked : liked);
          } else {
            // fallback: refresh from server
            refreshHeartCount(postId);
          }
        });
      });
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', bindHearts);
  else bindHearts();

  // Periodically refresh all heart counts every 10 seconds
  function refreshAllHeartCounts() {
    document.querySelectorAll('.heart-btn[data-post-id]').forEach(function(btn){
      const postId = btn.getAttribute('data-post-id');
      refreshHeartCount(postId);
    });
  }
  setInterval(refreshAllHeartCounts, 10000); // 10 seconds
})();
