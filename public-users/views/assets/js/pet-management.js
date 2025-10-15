(function(){
  const searchInput = document.getElementById('pet-search');
  const statusFilter = document.getElementById('pet-filter-status');
  const grid = document.getElementById('pet-grid');
  const countEl = document.getElementById('pet-count');

  function filterCards(){
    const q = (searchInput.value || '').trim().toLowerCase();
    const st = (statusFilter.value || '').trim();
    const cards = grid.querySelectorAll('.pet-card');
    let shown = 0;
    cards.forEach(card => {
      const name = card.getAttribute('data-name') || '';
      const breed = card.getAttribute('data-breed') || '';
      const species = card.getAttribute('data-species') || '';
      const status = card.getAttribute('data-status') || '';
      const matchQ = q === '' || name.includes(q) || breed.includes(q) || species.includes(q);
      const matchStatus = st === '' || status === st;
      const wrapper = card.closest('.col-12');
      if (matchQ && matchStatus) { wrapper.style.display = ''; shown++; } else { wrapper.style.display = 'none'; }
    });
    if (countEl) countEl.textContent = shown;
  }

  searchInput && searchInput.addEventListener('input', filterCards);
  statusFilter && statusFilter.addEventListener('change', filterCards);

  // Modal view handler
  const modalEl = document.getElementById('petDetailModal');
  const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;
  grid.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-view');
    if (!btn) return;
    const data = btn.getAttribute('data-pet');
    try{
      const pet = JSON.parse(data);
      document.getElementById('petDetailTitle').textContent = pet.name || 'Pet';
      document.getElementById('petDetailImg').src = pet.photo || '/public-assets/img/pet-placeholder.png';
      document.getElementById('petDetailBreed').textContent = pet.breed || '-';
      document.getElementById('petDetailAge').textContent = pet.age || '-';
      document.getElementById('petDetailSpecies').textContent = pet.species ? capitalize(pet.species) : '-';
      document.getElementById('petDetailGender').textContent = pet.gender ? capitalize(pet.gender) : '-';
      document.getElementById('petDetailSize').textContent = pet.size ? capitalize(pet.size) : '-';
      const stEl = document.getElementById('petDetailStatus');
      stEl.textContent = pet.status ? capitalize(pet.status) : 'Available';
      stEl.className = 'badge ' + ((pet.status==='available')? 'bg-success' : ((pet.status==='adopted')? 'bg-secondary' : 'bg-warning'));
      document.getElementById('petDetailVaccine').textContent = pet.vaccine_status || '-';
      document.getElementById('petDetailHealth').textContent = pet.health_status || '-';
      document.getElementById('petDetailLocation').textContent = pet.location || '-';
      document.getElementById('petDetailAdded').textContent = pet.created_at ? new Date(pet.created_at).toLocaleDateString() : '-';
      document.getElementById('petDetailDesc').textContent = pet.description || '';
      document.getElementById('petDetailLink').setAttribute('href', './pet_view.php?id=' + (pet.id || ''));
      bsModal && bsModal.show();
    } catch(err){ console.error(err); }
  });

  // Modal edit handler
  const editModalEl = document.getElementById('editPetModal');
  const editBsModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  grid.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;
    const data = btn.getAttribute('data-pet');
    try{
      const pet = JSON.parse(data);
      document.getElementById('editPetId').value = pet.id || '';
      document.getElementById('editPetName').value = pet.name || '';
      document.getElementById('editPetSpecies').value = pet.species || 'other';
      document.getElementById('editPetBreed').value = pet.breed || '';
      document.getElementById('editPetAge').value = pet.age || '';
      document.getElementById('editPetGender').value = pet.gender || 'unknown';
      document.getElementById('editPetSize').value = pet.size || 'medium';
      document.getElementById('editPetVaccine').value = pet.vaccine_status || '';
      document.getElementById('editPetHealth').value = pet.health_status || '';
      document.getElementById('editPetLocation').value = pet.location || '';
      document.getElementById('editPetDescription').value = pet.description || '';
      // Set status radio
      if (pet.status === 'adopted') {
        document.getElementById('editStatusAdopted').checked = true;
      } else {
        document.getElementById('editStatusAvailable').checked = true;
      }
      // Clear photo preview
      document.getElementById('editPetPhotoPreview').innerHTML = '';
      editBsModal && editBsModal.show();
    } catch(err){ console.error(err); }
  });

  function capitalize(s){ return typeof s === 'string' && s.length ? s.charAt(0).toUpperCase() + s.slice(1) : s; }
})();

// image preview for add pet
(function(){
  const input = document.getElementById('addPetPhotos');
  const preview = document.getElementById('addPetPhotoPreview');
  if (!input || !preview) return;
  input.addEventListener('change', function(){
    preview.innerHTML = '';
    const files = Array.from(input.files || []);
    files.slice(0,6).forEach(f => {
      if (!f.type.startsWith('image/')) return;
      const r = new FileReader();
      r.onload = function(ev){
        const img = document.createElement('img');
        img.src = ev.target.result;
        img.style.width = '80px';
        img.style.height = '80px';
        img.className = 'rounded border';
        img.style.objectFit = 'cover';
        preview.appendChild(img);
      };
      r.readAsDataURL(f);
    });
  });
})();

// image preview for edit pet
(function(){
  const input = document.getElementById('editPetPhotos');
  const preview = document.getElementById('editPetPhotoPreview');
  if (!input || !preview) return;
  input.addEventListener('change', function(){
    preview.innerHTML = '';
    const files = Array.from(input.files || []);
    files.slice(0,6).forEach(f => {
      if (!f.type.startsWith('image/')) return;
      const r = new FileReader();
      r.onload = function(ev){
        const img = document.createElement('img');
        img.src = ev.target.result;
        img.style.width = '80px';
        img.style.height = '80px';
        img.className = 'rounded border';
        img.style.objectFit = 'cover';
        preview.appendChild(img);
      };
      r.readAsDataURL(f);
    });
  });
})();

// Modern, minimalist delete confirmation using SweetAlert2
window.deletePet = function(petId, btnEl) {
    if (!petId) {
        Swal.fire('Error', 'Pet id missing', 'error');
        return;
    }

    Swal.fire({
        title: 'Delete pet?',
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
            performDeletePet(petId, btnEl);
        }
    });
};

// performDeletePet: sends request and removes card on success
window.performDeletePet = function(petId, btnEl) {
    if (!petId) return;
    try {
        if (btnEl) btnEl.disabled = true;
    } catch (e) {}
    var origText = btnEl && btnEl.textContent || 'Delete';
    if (btnEl) btnEl.textContent = 'Deleting...';

    var fd = new FormData();
    fd.append('pet_id', String(petId));

    fetch('../controllers/DeletePetManagementController.php', {
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
            var card = btnEl && btnEl.closest && btnEl.closest('.col-12');
            if (card) card.parentNode.removeChild(card);

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
            Swal.fire('Error', (json && json.error) ? json.error : 'Could not delete pet', 'error');
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.textContent = origText;
            }
        }
    }).catch(function(err) {
        console.error('Delete error', err);
        Swal.fire('Error', 'Request failed: ' + (err && err.message ? err.message : 'unknown'), 'error');
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.textContent = origText;
        }
    });
};