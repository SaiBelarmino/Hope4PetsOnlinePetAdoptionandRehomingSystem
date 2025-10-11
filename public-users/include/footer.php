</div>
</div>
</div> <!-- /.body-wrapper -->
</div> <!-- /.page-wrapper -->
<script src="../../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
<script src="../../assets/libs/simplebar/dist/simplebar.min.js"></script>
<script src="../../assets/js/sidebarmenu.js"></script>
<script src="../../assets/js/app.min.js"></script>
<script src="../../assets/js/dashboard.js"></script>
</body>

</html>
<?php if (empty($pageTitle) || strtolower($pageTitle) !== 'messages') { ?>
<script>
	// Hide preloader when page is fully loaded
	window.addEventListener('load', function() {
		var preloader = document.getElementById('preloader');
		if (preloader) {
			preloader.style.display = 'none';
		}
	});
</script>
<?php } ?>
<?php if (empty($pageTitle) || strtolower($pageTitle) !== 'messages') { ?>
<script>
(function(){
	function qs(sel, el){ return (el||document).querySelector(sel); }
	function showPreloader(){ var p = qs('#preloader'); if (p) p.style.display = 'flex'; }
	function hidePreloader(timeout){ timeout = Number(timeout)||0; setTimeout(function(){ var p = qs('#preloader'); if (p) p.style.display = 'none'; }, timeout); }

	// Ensure preloader hidden on full page load (non-SPA navigation)
	window.addEventListener('load', function(){ hidePreloader(0); });

	// Delegate clicks: only show for same-origin navigations that open in same tab
	document.body.addEventListener('click', function(e){
		var el = e.target;
		while(el && el !== document.body){
			if (el.matches && el.matches('a[href]')){
				if (el.classList.contains('no-preloader') || el.dataset.preloader === 'false') return;
				var href = el.getAttribute('href');
				if (!href) return;
				if (href.indexOf('#') === 0 || href.indexOf('javascript:') === 0) return;
				if (el.target && el.target !== '') return; // don't show for _blank etc.
				try {
					var url = new URL(href, location.href);
					if (url.origin !== location.origin) return; // external link -> don't show
				} catch(err) { /* ignore parse errors */ }
				showPreloader();
				return;
			}
			el = el.parentNode;
		}
	}, true);

	// Show on form submits (unless opted out)
	document.body.addEventListener('submit', function(e){
		var f = e.target;
		if (!f) return;
		if (f.classList && (f.classList.contains('no-preloader') || f.dataset.preloader === 'false')) return;
		showPreloader();
	}, true);

	// jQuery: use counters to avoid race conditions
	if (window.jQuery){
		(function($){
			var ajaxCount = 0;
			$(document).ajaxStart(function(){ ajaxCount++; showPreloader(); })
				.ajaxStop(function(){ ajaxCount = 0; hidePreloader(200); })
				.ajaxError(function(){ ajaxCount = 0; hidePreloader(200); });
		})(window.jQuery);
	}

	// Fetch wrapper with counter and finally handling (modern browsers)
	if (window.fetch){
		var _fetch = window.fetch;
		var fetchCounter = 0;
		window.fetch = function(){
			fetchCounter++;
			showPreloader();
			return _fetch.apply(this, arguments).finally(function(){
				fetchCounter--;
				if (fetchCounter <= 0) { fetchCounter = 0; hidePreloader(150); }
			});
		};
	}

	// Safety fallback: ensure preloader doesn't stay visible forever
	setTimeout(function(){ var p = qs('#preloader'); if (p && p.style.display !== 'none') p.style.display = 'none'; }, 15000);

})();
</script>
<?php } ?>