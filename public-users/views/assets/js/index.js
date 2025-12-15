(function(){
    function init(){
        'use strict';
        // ================= MEDIA COMPOSER LOGIC =================
        const dropZone = document.getElementById('drop-zone');
        const input = document.getElementById('media');
        const selectBtn = document.getElementById('select-media-btn');
        let currentFiles = [];
        const objectURLs = new Map();
        function makeKey(file){ return (file.name||'')+'|'+(file.size||0)+'|'+(file.type||''); }
        function countKinds(entries){ let images=0,videos=0; for(const e of entries){ const f=e.file; if(!f||!f.type) continue; if(f.type.startsWith('image/')) images++; if(f.type.startsWith('video/')) videos++; } return {images,videos}; }
        function updateInputFiles(){ if(!input) return; const dt=new DataTransfer(); for(const e of currentFiles) dt.items.add(e.file); input.files=dt.files; }
        let previewContainer=document.getElementById('media-previews') || (dropZone?dropZone.querySelector('.media-previews'):null);
        if(dropZone){ if(!previewContainer){ previewContainer=document.createElement('div'); previewContainer.className='media-previews mt-3 d-flex flex-wrap gap-2'; dropZone.appendChild(previewContainer);} if(previewContainer && previewContainer.children.length===0){ previewContainer.innerHTML='<div class="text-muted small">No files selected</div>'; } }
        function renderPreviews(){ if(!previewContainer) return; previewContainer.innerHTML=''; if(currentFiles.length===0){ previewContainer.innerHTML='<div class="text-muted small">No files selected</div>'; return; } currentFiles.forEach((entry,idx)=>{ const file=entry.file; const key=entry.key; const wrapper=document.createElement('div'); wrapper.className='position-relative border rounded overflow-hidden'; Object.assign(wrapper.style,{width:'110px',height:'110px',display:'inline-block',background:'#f8f9fa'}); let url=objectURLs.get(key); if(!url){ url=URL.createObjectURL(file); objectURLs.set(key,url);} if(file.type && file.type.startsWith('image/')){ const img=document.createElement('img'); Object.assign(img.style,{width:'100%',height:'100%',objectFit:'cover'}); img.src=url; wrapper.appendChild(img);} else if(file.type && file.type.startsWith('video/')){ const vid=document.createElement('video'); Object.assign(vid.style,{width:'100%',height:'100%',objectFit:'cover'}); vid.src=url; vid.muted=true; vid.playsInline=true; vid.controls=false; wrapper.appendChild(vid); const play=document.createElement('div'); play.innerHTML='\u25BA'; Object.assign(play.style,{position:'absolute',left:'50%',top:'50%',transform:'translate(-50%,-50%)',color:'rgba(255,255,255,0.9)',fontSize:'24px'}); wrapper.appendChild(play);} const removeBtn=document.createElement('button'); removeBtn.type='button'; removeBtn.className='btn btn-sm btn-danger'; Object.assign(removeBtn.style,{position:'absolute',top:'4px',right:'4px',zIndex:'5'}); removeBtn.innerHTML='×'; removeBtn.onclick=function(e){ e.stopPropagation(); if(objectURLs.has(key)){ URL.revokeObjectURL(objectURLs.get(key)); objectURLs.delete(key);} currentFiles.splice(idx,1); renderPreviews(); updateInputFiles(); }; wrapper.appendChild(removeBtn); previewContainer.appendChild(wrapper); }); }
        function addFiles(filesList){ const incoming=Array.from(filesList||[]); if(incoming.length===0) return; const existing=countKinds(currentFiles); let toAdd=[]; for(const f of incoming){ const key=makeKey(f); if(currentFiles.some(e=>e.key===key)) continue; const isImage=f.type && f.type.startsWith('image/'); const isVideo=f.type && f.type.startsWith('video/'); if(isVideo && existing.videos + toAdd.filter(t=>t.file.type.startsWith('video/')).length >= 1) continue; if(isImage && existing.images + toAdd.filter(t=>t.file.type.startsWith('image/')).length >= 8) continue; toAdd.push({key,file:f}); } if(toAdd.length>0){ currentFiles=currentFiles.concat(toAdd); renderPreviews(); updateInputFiles(); } }
        if(dropZone && input){ dropZone.addEventListener('click', function(e){ if(e.target && (e.target.tagName.toLowerCase()==='button' || e.target.closest('button'))) return; input.click(); }); if(selectBtn){ selectBtn.addEventListener('click', function(e){ e.stopPropagation(); }); } input.addEventListener('change', function(){ if(!input.files || input.files.length===0) return; addFiles(input.files); }); dropZone.addEventListener('dragover', function(e){ e.preventDefault(); dropZone.classList.add('drag-over'); }); dropZone.addEventListener('dragleave', function(e){ e.preventDefault(); dropZone.classList.remove('drag-over'); }); dropZone.addEventListener('drop', function(e){ e.preventDefault(); dropZone.classList.remove('drag-over'); if(e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length>0) addFiles(e.dataTransfer.files); }); }
        window._postComposerFiles=currentFiles;
        // ================= CAPTION TOGGLE =================
        // Caption toggle (robust) for new .post-caption-toggle and legacy .expand-caption buttons
(function(){
    function toggleCaption(btn){
        var postId = btn.getAttribute('data-post');
        if(!postId) return;
        var caption = document.getElementById('caption-' + postId);
        if(!caption) return;
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        if(expanded){
            caption.textContent = caption.getAttribute('data-short');
            btn.setAttribute('aria-expanded','false');
            btn.textContent = 'See more';
        } else {
            caption.innerHTML = caption.getAttribute('data-full');
            btn.setAttribute('aria-expanded','true');
            btn.textContent = 'See less';
        }
    }
    function initCaptionButtons(){
        document.querySelectorAll('.post-caption-toggle, .expand-caption').forEach(function(btn){
            if(btn.dataset.captionInit==='1') return;
            btn.dataset.captionInit='1';
            var postId = btn.getAttribute('data-post');
            var caption = document.getElementById('caption-' + postId);
            if(!caption || caption.getAttribute('data-truncated') !== '1'){
                btn.style.display = 'none';
            }
        });
    }
    function bindDelegation(){
        document.addEventListener('click', function(e){
            var btn = e.target.closest('.post-caption-toggle, .expand-caption');
            if(!btn) return;
            toggleCaption(btn);
        });
        document.addEventListener('keydown', function(e){
            if(e.key !== 'Enter' && e.key !== ' ') return;
            var btn = document.activeElement && document.activeElement.closest('.post-caption-toggle, .expand-caption');
            if(!btn) return;
            e.preventDefault();
            toggleCaption(btn);
        });
    }
    function start(){ initCaptionButtons(); bindDelegation(); }
    if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
        // Fallback direct binding for See more buttons to ensure functionality even if previous handlers fail
(function(){
    function bindFallback(){
        document.querySelectorAll('.post-caption-toggle').forEach(function(btn){
            if(btn.dataset.fallbackBound==='1') return;
            btn.dataset.fallbackBound='1';
            btn.addEventListener('click', function(){
                var postId = btn.getAttribute('data-post');
                var caption = document.getElementById('caption-' + postId);
                if(!caption) return;
                var expanded = btn.getAttribute('aria-expanded') === 'true';
                if(expanded){
                    caption.textContent = caption.getAttribute('data-short');
                    btn.setAttribute('aria-expanded','false');
                    btn.textContent = 'See more';
                } else {
                    caption.innerHTML = caption.getAttribute('data-full');
                    btn.setAttribute('aria-expanded','true');
                    btn.textContent = 'See less';
                }
            });
        });
    }
    if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', bindFallback); } else { bindFallback(); }
})();
        // ================= FOCUS COMMENT INPUT ON COLLAPSE SHOW =================
        if(!window.__commentCollapseFocusBound){ window.__commentCollapseFocusBound=true; document.addEventListener('shown.bs.collapse', function(e){ if(e.target.classList.contains('comment-box')){ var inputField=e.target.querySelector('input[name="content"], textarea[name="content"]'); if(inputField) setTimeout(function(){ inputField.focus(); },50); } }); }
    }
    if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();

window.toggleCaption = function(btn){
    try {
        var postId = btn.getAttribute('data-post');
        if(!postId) return;
        var caption = document.getElementById('caption-' + postId);
        if(!caption) return;
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        if(expanded){
            caption.textContent = caption.getAttribute('data-short');
            btn.setAttribute('aria-expanded','false');
            btn.textContent = 'See more';
        } else {
            caption.innerHTML = caption.getAttribute('data-full');
            btn.setAttribute('aria-expanded','true');
            btn.textContent = 'See less';
        }
    } catch(err){ console.error('[toggleCaption] error', err); }
};
// Bind (in case inline onclick not present yet or JS added later)
(function(){
    function bind(){
        document.querySelectorAll('.post-caption-toggle').forEach(function(btn){
            if(btn.dataset.simpleBound==='1') return;
            btn.dataset.simpleBound='1';
            btn.addEventListener('click', function(e){ e.preventDefault(); window.toggleCaption(btn); });
        });
    }
    if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', bind); } else { bind(); }
})();
(function(){
  // Minimal reliable caption toggle (final fallback)
  function bindCaptionToggles(){
    document.querySelectorAll('.post-caption-toggle').forEach(function(btn){
      if(btn.dataset.finalBound==='1') return;
      btn.dataset.finalBound='1';
      var postId = btn.getAttribute('data-post');
      var caption = document.getElementById('caption-' + postId);
      if(!caption){ btn.style.display='none'; return; }
      if(caption.getAttribute('data-truncated')!=='1'){ btn.style.display='none'; return; }
      btn.addEventListener('click', function(e){
        e.preventDefault();
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        if(expanded){
          caption.textContent = caption.getAttribute('data-short');
          btn.setAttribute('aria-expanded','false');
          btn.textContent = 'See more';
        } else {
          caption.innerHTML = caption.getAttribute('data-full');
          btn.setAttribute('aria-expanded','true');
          btn.textContent = 'See less';
        }
      });
    });
  }
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', bindCaptionToggles); } else { bindCaptionToggles(); }
})();
(function(){
  // Like-only toggle
  function qsa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  function update(btn, liked, count){
    btn.dataset.liked = liked ? '1' : '0';
    btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
    btn.classList.remove('btn-light','btn-primary');
    btn.classList.add(liked ? 'btn-primary' : 'btn-light');
    btn.classList.toggle('active', !!liked);
    var badge = btn.querySelector('.like-count');
    if (badge){ var n = Number(count||0); badge.textContent = n; badge.style.display = n > 0 ? '' : 'none'; }
  }
  function toggle(postId){
    return fetch('../controllers/ToggleLikeController.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      credentials:'same-origin',
      body: JSON.stringify({action:'toggle', post_id: postId, reaction_type: 'like'})
    }).then(function(r){ return r.json(); });
  }
  function onClick(e){
    var btn = e.currentTarget;
    if (btn.dataset.auth !== '1'){ window.location.href = '../login.php'; return; }
    var postId = btn.dataset.postId;
    if (!postId) return;
    var liked = btn.dataset.liked === '1';
    var badge = btn.querySelector('.like-count');
    var current = Number(badge ? badge.textContent : '0');
    var optimisticLiked = !liked;
    var optimisticCount = current + (optimisticLiked ? 1 : -1);
    if (optimisticCount < 0) optimisticCount = 0;
    update(btn, optimisticLiked, optimisticCount);
    toggle(postId).then(function(res){
      if (res && res.success){ update(btn, !!res.liked, Number(res.count||0)); }
      else { update(btn, liked, current); }
    }).catch(function(){ update(btn, liked, current); });
  }
  function init(){ qsa('.post-like-btn').forEach(function(btn){ btn.addEventListener('click', onClick); }); }
  if (document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();