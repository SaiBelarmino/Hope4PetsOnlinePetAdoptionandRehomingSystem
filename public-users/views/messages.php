<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pageTitle = 'Messages';
?>

<div class="container-fluid">
	<div class="row g-3 py-3">
		<!-- Left: Search / Contacts -->
		<div class="col-12 col-lg-4">
			<div class="card">
				<div class="card-body">
					<h6 class="mb-3">Search people</h6>
					<div class="mb-3">
						<input id="contact-search" type="search" class="form-control" placeholder="Search by name or email..." aria-label="Search people">
					</div>
					<div id="search-results" class="list-group" style="max-height:520px; overflow-y:auto;">
						<!-- Live results inserted here -->
						<div class="text-muted text-center py-4" id="no-results">Start typing to find someone to chat with</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Right: Conversation / Placeholder -->
		<div class="col-12 col-lg-8">
			<div class="card">
				<div class="card-body text-center py-5" id="conversation-placeholder">
					<i class="ti ti-mail" style="font-size:48px;color:#666;"></i>
					<h5 class="mt-3">Messages</h5>
					<p class="text-muted">No  Conversation Yet</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
// Minimal client-side search with debounce. Expects ../controllers/search_user.php?q=... to return JSON array of users
(() => {
	const input = document.getElementById('contact-search');
	const resultsContainer = document.getElementById('search-results');
	const noResultsEl = document.getElementById('no-results');

	function escapeHtml(str) {
		return str.replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
	}

	function renderResults(users) {
		resultsContainer.innerHTML = '';
		if (!users || users.length === 0) {
			resultsContainer.appendChild(noResultsEl);
			noResultsEl.textContent = 'No users found';
			return;
		}

		users.forEach(user => {
			const a = document.createElement('a');
			a.href = './chat.php?user_id=' + encodeURIComponent(user.id);
			a.className = 'list-group-item list-group-item-action d-flex align-items-center';
			a.setAttribute('role', 'button');

			const img = document.createElement('img');
			img.src = user.profile_photo ? ('../../' + user.profile_photo) : '../../assets/images/profile/user-1.jpg';
			img.alt = user.full_name || 'User';
			img.width = 44; img.height = 44;
			img.className = 'rounded-circle me-3 object-fit-cover';
			img.style.objectFit = 'cover';

			const div = document.createElement('div');
			const name = document.createElement('div');
			name.className = 'fw-bold';
			name.textContent = user.full_name || user.email || 'Unknown';
			const meta = document.createElement('div');
			meta.className = 'small text-muted';
			meta.textContent = user.email || '';

			div.appendChild(name);
			div.appendChild(meta);

			a.appendChild(img);
			a.appendChild(div);

			resultsContainer.appendChild(a);
		});
	}

	let timeout = null;
	input.addEventListener('input', (e) => {
		const q = e.target.value.trim();
		if (timeout) clearTimeout(timeout);
		if (q.length === 0) {
			resultsContainer.innerHTML = '';
			resultsContainer.appendChild(noResultsEl);
			noResultsEl.textContent = 'Start typing to find someone to chat with';
			return;
		}

		timeout = setTimeout(() => {
			// Fetch search results
			fetch('../controllers/search_user.php?q=' + encodeURIComponent(q), { method: 'GET', credentials: 'same-origin' })
				.then(r => r.ok ? r.json() : Promise.reject(new Error('Network response was not ok')))
				.then(data => {
					// Expecting data to be an array of {id, full_name, email, profile_photo}
					renderResults(Array.isArray(data) ? data : []);
				})
				.catch(err => {
					resultsContainer.innerHTML = '';
					const errEl = document.createElement('div');
					errEl.className = 'text-danger small p-2';
					errEl.textContent = 'Not Found';
					resultsContainer.appendChild(errEl);
					console.error('Search error', err);
				});
		}, 300); // debounce 300ms
	});

})();
</script>

