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
// Show preloader helper
function showPreloader() {
	var pre = document.getElementById('preloader');
	if (pre) pre.style.display = 'flex';
}
+
// Hide preloader helper (with small delay to allow navigation to start)
function hidePreloader(timeout) {
	timeout = typeof timeout === 'number' ? timeout : 0;
	setTimeout(function() {
		var pre = document.getElementById('preloader');
		if (pre) pre.style.display = 'none';
	}, timeout);
}

// Attach handlers: links, forms, and buttons that navigate
+
document.addEventListener('DOMContentLoaded', function() {
	// Delegate click on anchors and buttons with data-href
+    document.body.addEventListener('click', function(e) {
+        var el = e.target;
+        // walk up to find actionable element
+        while (el && el !== document.body) {
+            if (el.matches && el.matches('a[href]')) {
+                // opt-out via class or data attribute
+                if (el.classList.contains('no-preloader') || el.dataset.preloader === 'false') return;
+                // allow same-page hashes without showing preloader
+                var href = el.getAttribute('href');
+                if (href && href.indexOf('#') === 0) return;
+                showPreloader();
+                return;
+            }
+            if (el.matches && (el.matches('button[data-href], [data-href].btn') || el.matches('[data-toggle-navigate]'))) {
+                if (el.classList.contains('no-preloader') || el.dataset.preloader === 'false') return;
+                showPreloader();
+                return;
+            }
+            el = el.parentNode;
+        }
+    }, true);
+
+    // Attach to form submissions
+    document.body.addEventListener('submit', function(e) {
+        var form = e.target;
+        if (!form) return;
+        if (form.classList && (form.classList.contains('no-preloader') || form.dataset.preloader === 'false')) return;
+        showPreloader();
+    }, true);
+
+    // jQuery global handlers if jQuery present
+    if (window.jQuery) {
+        (function($){
+            $(document).ajaxStart(function(){
+                showPreloader();
+            }).ajaxStop(function(){
+                // small delay so UX isn't jarring
+                hidePreloader(250);
+            }).ajaxError(function(){
+                hidePreloader(250);
+            });
+        })(window.jQuery);
+    }
+
+    // Wrap window.fetch to show preloader for fetch calls
+    if (window.fetch) {
+        var _origFetch = window.fetch;
+        window.fetch = function(){
+            try { showPreloader(); } catch (e) {}
+            return _origFetch.apply(this, arguments).then(function(res){
+                // hide after small delay
+                hidePreloader(200);
+                return res;
+            }).catch(function(err){
+                hidePreloader(200);
+                throw err;
+            });
+        };
+    }
+
+});
+
+// Optional: hide preloader after X seconds as a safety (in case of navigation failure)
+setTimeout(function(){
+    var pre = document.getElementById('preloader');
+    if (pre && pre.style.display !== 'none') pre.style.display = 'none';
+}, 15000);
+</script>
+<?php } ?>