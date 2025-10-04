(function(){
  var PRELOADER_ID='preloader';
  var MIN_DISPLAY=400; // minimum ms to show spinner to avoid flash
  var start=Date.now();
  function hide(){
    var el=document.getElementById(PRELOADER_ID);
    if(!el || el.classList.contains('hidden')) return;
    var left=Date.now()-start;
    var delay=Math.max(0, MIN_DISPLAY-left);
    setTimeout(function(){
      el.classList.add('hidden');
      // Remove from DOM after transition
      setTimeout(function(){ if(el && el.parentNode) el.parentNode.removeChild(el); }, 500);
    }, delay);
  }
  // Safety fallback (e.g., if load event never fires due to cached resources) after 8s
  setTimeout(hide, 8000);
  if(document.readyState==='complete') hide(); else window.addEventListener('load', hide);
})();
