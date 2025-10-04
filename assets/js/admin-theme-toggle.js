(function(){
  var THEME_KEY = 'admin_theme';
  var COOKIE_NAME = 'admin_theme';
  var root = document.body;

  function setCookie(name,value,days){
    try {
      var d = new Date();
      d.setTime(d.getTime() + (days*24*60*60*1000));
      document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    } catch(e) {}
  }

  function applyTheme(theme){
    if(!root) return;
    if(theme !== 'dark' && theme !== 'light') theme = 'light';
    root.classList.remove('theme-dark','theme-light');
    root.classList.add('theme-' + theme);
    // Update toggle button tooltip / aria
    var btn = document.getElementById('themeToggle');
    if(btn){
      var next = theme === 'dark' ? 'light' : 'dark';
      btn.setAttribute('aria-label','Switch to ' + next + ' mode');
      btn.title = 'Switch to ' + next + ' mode';
    }
  }

  function getStoredTheme(){
    try { return localStorage.getItem(THEME_KEY) || null; } catch(e) { return null; }
  }

  function storeTheme(theme){
    try { localStorage.setItem(THEME_KEY, theme); } catch(e) {}
    setCookie(COOKIE_NAME, theme, 365);
  }

  function init(){
    var current = getStoredTheme();
    if(!current){
      // Fallback to cookie if localStorage missing or system preference
      var cookieMatch = document.cookie.match(/(?:^|; )admin_theme=(dark|light)/);
      if(cookieMatch) current = cookieMatch[1];
      else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) current = 'dark';
      else current = 'light';
    }
    applyTheme(current);
    var btn = document.getElementById('themeToggle');
    if(btn){
      btn.addEventListener('click', function(){
        var nowDark = root.classList.contains('theme-dark');
        var newTheme = nowDark ? 'light' : 'dark';
        applyTheme(newTheme);
        storeTheme(newTheme);
      });
    }
    // Respond to system preference changes if user never manually selected
    try {
      if(!localStorage.getItem(THEME_KEY) && window.matchMedia){
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e){
          if(!localStorage.getItem(THEME_KEY)){
            applyTheme(e.matches ? 'dark' : 'light');
          }
        });
      }
    } catch(e) {}
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();