<?php
$pageTitle = 'Settings';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';
?>

<div class="container mt-5">
	<div class="row">
		<!-- Sidebar -->
		<div class="col-md-4 col-lg-3 mb-4">
			<div class="card shadow-sm">
				<div class="card-body p-3">
					<h5 class="mb-3">Settings & Privacy</h5>
					<input type="text" class="form-control mb-3" placeholder="Search settings" id="settings-search">
					<div class="list-group" id="settings-sidebar">
						<a href="#account" class="list-group-item list-group-item-action active" data-section="account">Account Settings</a>
						<a href="#notifications" class="list-group-item list-group-item-action" data-section="notifications">Notification Settings</a>
						<a href="#privacy" class="list-group-item list-group-item-action" data-section="privacy">Privacy Settings</a>
						<a href="#language" class="list-group-item list-group-item-action" data-section="language">Language Settings</a>
						<a href="#system" class="list-group-item list-group-item-action" data-section="system">System Settings</a>
						<a href="#about" class="list-group-item list-group-item-action" data-section="about">About / Info</a>
					</div>
				</div>
			</div>
		</div>
		<!-- Main Content -->
		<div class="col-md-8 col-lg-9">
			<div id="account" class="card mb-4 shadow-sm">
				<div class="card-body">
					<h5 class="card-title">Account Settings</h5>
					<form>
						<div class="mb-3">
							<label for="username" class="form-label">Edit Username</label>
							<input type="text" class="form-control" id="username" value="JohnDoe">
						</div>
						<div class="mb-3">
							<label for="changePassword" class="form-label">Change Password</label>
							<input type="password" class="form-control" id="changePassword" placeholder="New Password">
							<input type="password" class="form-control mt-2" id="confirmPassword" placeholder="Confirm New Password">
						</div>

						<button type="submit" class="btn btn-success btn-sm">Save Changes</button>
					</form>
				</div>
			</div>
			<div id="notifications" class="card mb-4 shadow-sm">
				<div class="card-body">
					<h5 class="card-title">Notification Settings</h5>
					<form>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="soundNotif" checked>
							<label class="form-check-label" for="soundNotif">Sound Notifications</label>
						</div>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="popupNotif">
							<label class="form-check-label" for="popupNotif">Pop-up Notifications</label>
						</div>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="msgAlert" checked>
							<label class="form-check-label" for="msgAlert">Message Alerts</label>
						</div>
					</form>
				</div>
			</div>
			<div id="privacy" class="card mb-4 shadow-sm">
				<div class="card-body">
					<h5 class="card-title">Privacy Settings</h5>
					<form>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="locationToggle">
							<label class="form-check-label" for="locationToggle">Allow Location</label>
						</div>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="cookiesToggle" checked>
							<label class="form-check-label" for="cookiesToggle">Allow Cookies</label>
						</div>
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="onlineStatusToggle" checked>
							<label class="form-check-label" for="onlineStatusToggle">Show Online Status</label>
						</div>
					</form>
				</div>
			</div>
			<div id="language" class="card mb-4 shadow-sm">
				<div class="card-body">
					<h5 class="card-title">Language Settings</h5>
					<form>
						<div class="mb-3">
							<label for="appLanguage" class="form-label">Choose App Language</label>
							<select class="form-select" id="appLanguage">
								<option value="en">English</option>
								<option value="fil">Filipino</option>
							</select>
						</div>
					</form>
				</div>
			</div>
			<div id="system" class="card mb-4 shadow-sm">
				<div class="card-body">
					<h5 class="card-title">System Settings</h5>
					<form>
						<button type="button" class="btn btn-warning btn-sm mb-2">Reset All Data</button>
						<button type="button" class="btn btn-secondary btn-sm mb-2">Clear Results / History</button>
						<button type="button" class="btn btn-info btn-sm mb-2">Restore Default Settings</button>
					</form>
				</div>
			</div>
			<div id="about" class="card shadow-sm">
				<div class="card-body">
					<h5 class="card-title">About / Info</h5>
					<ul class="list-unstyled mb-2">
						<li><strong>App Version:</strong> 1.0.0</li>
						<li><strong>Developer:</strong> Hope4Pets Team</li>
						<li><strong>Description:</strong> Online Pet Adoption and Rehoming System for shelters and pet lovers.</li>
						<li><strong>Contact:</strong> hope4pets@email.com</li>
					</ul>
				</div>
			</div>
			<!-- Activity Log Section -->
			<div id="activity-log" class="card shadow-sm mt-4">
				<div class="card-body">
					<h5 class="card-title">Activity Log</h5>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead class="table-light">
								<tr>
									<th>Date & Time</th>
									<th>Activity</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<!-- Activity rows will be loaded here dynamically -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	// Sidebar tab logic
	const sidebarLinks = document.querySelectorAll('#settings-sidebar a[data-section]');
	const sections = document.querySelectorAll('[id^="account"], [id^="notifications"], [id^="privacy"], [id^="language"], [id^="system"], [id^="about"], #activity-log');
	sidebarLinks.forEach(link => {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			sidebarLinks.forEach(l => l.classList.remove('active'));
			this.classList.add('active');
			sections.forEach(sec => sec.style.display = 'none');
			const sectionId = this.getAttribute('data-section');
			const section = document.getElementById(sectionId);
			if(section) section.style.display = '';
			if(document.getElementById('activity-log'))
				document.getElementById('activity-log').style.display = (sectionId === 'account') ? '' : 'none';
		});
	});

	// Search bar logic
	const searchInput = document.getElementById('settings-search');
	searchInput.addEventListener('keydown', function(e) {
		if(e.key === 'Enter') {
			const query = this.value.trim().toLowerCase();
			let foundSection = false;
			sidebarLinks.forEach(link => {
				const sectionId = link.getAttribute('data-section');
				const section = document.getElementById(sectionId);
				const labelText = link.textContent.trim().toLowerCase();
				let contentText = '';
				if (section) {
					contentText = section.textContent.trim().toLowerCase();
				}
				// Show sidebar link if label or section content matches
				if (query === '' || labelText.includes(query) || contentText.includes(query)) {
					link.style.display = '';
					// Show only the first matching section
					if (!foundSection && section) {
						sidebarLinks.forEach(l => l.classList.remove('active'));
						link.classList.add('active');
						sections.forEach(sec => sec.style.display = 'none');
						section.style.display = '';
						if(document.getElementById('activity-log'))
							document.getElementById('activity-log').style.display = (sectionId === 'account') ? '' : 'none';
						foundSection = true;
					}
				} else {
					link.style.display = 'none';
				}
			});
			// If no match, hide all sections
			if (!foundSection) {
				sections.forEach(sec => sec.style.display = 'none');
				if(document.getElementById('activity-log'))
					document.getElementById('activity-log').style.display = 'none';
			}
		}
	});
});
</script>
