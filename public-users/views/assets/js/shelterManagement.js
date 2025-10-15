// filepath: c:\xampp\htdocs\Hope4PetsOnlinePetAdoptionandRehomingSystem\public-users\views\assets\js\shelterManagement.js

// helper to build controller URLs reliably from APP_BASE
function controllerUrl(name) {
    var base = (typeof APP_BASE !== 'undefined' && APP_BASE) ? APP_BASE : '';
    // ensure leading slash
    if (base && base.indexOf('/') !== 0) base = '/' + base;
    return base + '/public-users/controllers/' + name;
}

// Auto-fetch data from controller API to populate page dynamically
(function() {
    var api = '../controllers/ShelterManagementController.php';

    function textOrDash(v) {
        return v || '—';
    }

    function setText(selector, val) {
        var el = document.querySelector(selector);
        if (el) el.textContent = val;
    }

    function clearChildren(el) {
        while (el.firstChild) el.removeChild(el.firstChild);
    }

    function buildDocumentsTable(docs) {
        var tbody = document.querySelector('#submittedDocumentsTbody');
        if (!tbody) return;
        clearChildren(tbody);
        if (!docs || docs.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'text-center text-muted py-4';
            td.textContent = 'No documents uploaded.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        docs.forEach(function(d) {
            var tr = document.createElement('tr');
            tr.id = d.doc_type ? String(d.doc_type) : '';
            var tdType = document.createElement('td');
            tdType.textContent = (d.doc_type ? d.doc_type.replace(/_/g, ' ').toUpperCase() : '');
            tr.appendChild(tdType);
            var tdStatus = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'badge bg-' + ({
                'pending': 'warning',
                'approved': 'success',
                'rejected': 'danger'
            } [d.status] || 'light');
            span.textContent = (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) :
                'Unknown');
            tdStatus.appendChild(span);
            tr.appendChild(tdStatus);
            var tdUploaded = document.createElement('td');
            tdUploaded.innerHTML = '<span class="small text-muted">' + (d.uploaded_at ? new Date(d
                .uploaded_at).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric'
            }) : '—') + '</span>';
            tr.appendChild(tdUploaded);
            var tdReviewed = document.createElement('td');
            tdReviewed.innerHTML = '<span class="small text-muted">' + (d.reviewed_at ? new Date(d
                .reviewed_at).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric'
            }) : '—') + '</span>';
            tr.appendChild(tdReviewed);
            var tdAction = document.createElement('td');
            tdAction.className = 'text-end';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-secondary';
            btn.textContent = 'View';
            btn.onclick = function() {
                openDocumentModal(d.file_path);
            };
            tdAction.appendChild(btn);
            // add Remove button for rejected documents
            if (d.status && String(d.status).toLowerCase() === 'rejected') {
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger ms-2';
                removeBtn.textContent = 'Remove';
                removeBtn.onclick = function() {
                    deleteDocument(d.id, removeBtn);
                };
                tdAction.appendChild(removeBtn);
            }
            tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    }

    function populate(data) {
        if (!data) return;
        // fill header
        var shelter = data.shelter || {};
        var stats = data.stats || {};
        // set both header and the detail fields
        setText('h3.mb-0.d-flex', shelter.shelter_name || 'My Shelter');
        setText('#shelterNameDisplay', shelter.shelter_name || '—');
        setText('#shelterAddressDisplay', shelter.address || '—');
        setText('#shelterContactDisplay', shelter.contact_number || '—');
        setText('#shelterSinceDisplay', shelter.created_at ? new Date(shelter.created_at).toLocaleDateString(
            undefined, {
                month: 'short',
                year: 'numeric'
            }) : '—');
        // simple placements: update counts
        setText('.card .card-body .small.text-muted + h4', String(stats.pets || 0));
        // better update using IDs: ensure elements have IDs — fallback to query selectors
        var statCards = document.querySelectorAll('.row.g-3.mb-3 .card .card-body h4');
        if (statCards && statCards.length >= 3) {
            statCards[0].textContent = String(stats.pets || 0);
            statCards[1].textContent = '₱' + (typeof stats.donations !== 'undefined' ? Number(stats.donations)
                .toFixed(2) : '0.00');
            statCards[2].textContent = String(stats.pending_docs || 0);
        }

        // shelter details
        var shelterNameEl = document.querySelector('[title="' + (shelter.shelter_name || '—') + '"]');
        // update address/contact/since more reliably
        var addressEls = document.querySelectorAll('div[title]');

        // set upload form shelter_id
        var hid = document.querySelector('input[name="shelter_id"]');
        if (hid) hid.value = shelter.id || '';

        buildDocumentsTable(data.documents || []);

        // overall document status badge
        var badge = document.querySelector('.badge.rounded-pill');
        if (badge && data.documents) {
            var overall = 'No documents';
            var type = 'light';
            var hasPending = false,
                hasApproved = false,
                hasRejected = false;
            data.documents.forEach(function(d) {
                var st = (d.status || '').toLowerCase();
                if (st === 'rejected') hasRejected = true;
                if (st === 'pending') hasPending = true;
                if (st === 'approved') hasApproved = true;
            });
            if (hasRejected) {
                overall = 'Rejected';
                type = 'danger';
            } else if (hasPending) {
                overall = 'Pending';
                type = 'warning';
            } else if (hasApproved) {
                overall = 'Approved';
                type = 'success';
            }
            badge.textContent = overall;
            badge.className = 'badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-' + type +
                ' text-dark shadow-sm';
        }
    }

    // fetch data
    // resolve api via helper
    try {
        api = controllerUrl('ShelterManagementController.php');
    } catch (e) {}
    fetch(api, {
        credentials: 'same-origin'
    }).then(function(res) {
        if (!res.ok) throw new Error('Network response not ok');
        return res.json();
    }).then(function(json) {
        try {
            populate(json);
        } catch (e) {
            console.error(e);
        }
    }).catch(function(err) {
        console.warn('Could not fetch remote data, falling back to server-side render if present.', err);
    });
})();

// helper to show notifications (optional wrapper — uses $.notify)
function notifyMessage(msg) {
    try {
        $.notify(String(msg || ''), {
            align: 'center',
            verticalAlign: 'top'
        });
    } catch (e) {
        try {
            alert(msg);
        } catch (e) {}
    }
}

// Modern, minimalist delete confirmation using SweetAlert2
window.deleteDocument = function(docId, btnEl) {
    if (!docId) {
        notifyMessage('Document id missing');
        return;
    }

    Swal.fire({
        title: 'Delete document?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        width: 350, // smaller width
        scrollbarPadding: false, // prevent page jump
        focusConfirm: false, // prevent auto scroll
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(docId, btnEl);
        }
    });
};

// performDelete: sends request and removes row on success
window.performDelete = function(docId, btnEl) {
    if (!docId) return;
    try {
        if (btnEl) btnEl.disabled = true;
    } catch (e) {}
    var origText = btnEl && btnEl.textContent || 'Remove';
    if (btnEl) btnEl.textContent = 'Removing...';

    var fd = new FormData();
    fd.append('document_id', String(docId));

    fetch(controllerUrl('DeleteDocumentController.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    }).then(function(res) {
        if (!res.ok) return res.text().then(function(text) {
            throw new Error('Server returned ' + res.status + '. ' + text);
        });
        var ct = res.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) return res.text().then(function(text) {
            throw new Error('Expected JSON response but got: ' + text);
        });
        return res.json();
    }).then(function(json) {
        if (json && json.success) {
            var tr = btnEl && btnEl.closest && btnEl.closest('tr');
            if (tr) tr.parentNode.removeChild(tr);

            // Minimalist success toast
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                timer: 1200,
                showConfirmButton: false,
                width: 300,
                scrollbarPadding: false,
                focusConfirm: false
            });
        } else {
            notifyMessage((json && json.error) ? json.error : 'Could not remove document');
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.textContent = origText;
            }
        }
    }).catch(function(err) {
        console.error('Delete error', err);
        notifyMessage('Request failed: ' + (err && err.message ? err.message : 'unknown'));
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.textContent = origText;
        }
    });
};

// Edit shelter modal
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('editShelterBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var nameEl = document.getElementById('shelterNameDisplay');
        var addrEl = document.getElementById('shelterAddressDisplay');
        var contactEl = document.getElementById('shelterContactDisplay');
        var hid = document.querySelector('input[name="shelter_id"]');
        var shelterId = hid ? hid.value : '';
        var curName = nameEl ? nameEl.textContent.trim() : '';
        var curAddr = addrEl ? addrEl.textContent.trim() : '';
        var curContact = contactEl ? addrEl.textContent.trim() : '';

        // Parse address parts
        var parts = curAddr.split(', ');
        document.querySelector('#editShelterModal input[name="shelter_name"]').value = curName;
        document.querySelector('#editShelterModal input[name="contact_number"]').value = curContact;
        document.querySelector('#editShelterModal input[name="shelter_unit"]').value = '';
        document.querySelector('#editShelterModal input[name="purok_subdivision"]').value = parts[0] || '';
        document.querySelector('#editShelterModal input[name="barangay"]').value = parts[1] || '';
        document.querySelector('#editShelterModal input[name="city"]').value = parts[2] || '';
        document.querySelector('#editShelterModal input[name="province"]').value = parts[3] || '';
        document.querySelector('#editShelterModal input[name="postal_code"]').value = parts[4] || '';
        document.querySelector('#editShelterModal input[name="shelter_id"]').value = shelterId;

        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('editShelterModal'));
        modal.show();
    });

    // Handle form submit
    document.getElementById('editShelterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // combine address
        const address = [
            document.querySelector('#editShelterModal input[name="shelter_unit"]').value.trim(),
            document.querySelector('#editShelterModal input[name="purok_subdivision"]').value.trim(),
            document.querySelector('#editShelterModal input[name="barangay"]').value.trim(),
            document.querySelector('#editShelterModal input[name="city"]').value.trim(),
            document.querySelector('#editShelterModal input[name="province"]').value.trim(),
            document.querySelector('#editShelterModal input[name="postal_code"]').value.trim()
        ].filter(v => v).join(', ');

        const data = {
            shelter_id: document.querySelector('#editShelterModal input[name="shelter_id"]').value,
            shelter_name: document.querySelector('#editShelterModal input[name="shelter_name"]').value,
            address: address,
            contact_number: document.querySelector('#editShelterModal input[name="contact_number"]').value
        };

        if (!data.shelter_id) {
            notifyMessage('Shelter ID is missing.');
            return;
        }
        if (!data.shelter_name.trim()) {
            notifyMessage('Shelter name is required.');
            return;
        }

        fetch(controllerUrl('EditShelterController.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        }).then(res => res.json()).then(json => {
            if (json.success) {
                // Update the page
                document.querySelector('h3.mb-0.d-flex').textContent = data.shelter_name || 'My Shelter';
                document.getElementById('shelterNameDisplay').textContent = data.shelter_name || '—';
                document.getElementById('shelterAddressDisplay').textContent = data.address || '—';
                document.getElementById('shelterContactDisplay').textContent = data.contact_number || '—';
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editShelterModal')).hide();
                notifyMessage('Shelter updated successfully.');
            } else {
                notifyMessage(json.error || 'Failed to update shelter.');
            }
        }).catch(error => {
            console.error('Error:', error);
            notifyMessage('An error occurred while updating the shelter.');
        });
    });
});