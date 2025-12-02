let currentDateDonations = [];
let debounceTimer;

if ('scrollRestoration' in history) {
    // Memberitahu browser untuk tidak mengembalikan posisi scroll otomatis
    history.scrollRestoration = 'manual';
}

// Opsional: Paksa scroll ke atas saat load
window.scrollTo(0, 0);

function openDonationModal(data = null) {
    const modal = document.getElementById('donationModal');
    const form = document.getElementById('donationForm');
    const title = document.getElementById('modalTitle');
    const dateInput = document.getElementById('date');
    const methodInput = document.getElementById('formMethod');

    modal.classList.add('active');

    // Reset form first
    form.reset();

    if (typeof data === 'object' && data !== null) {
        // Edit Mode
        form.action = `/donations/${data.id}`;
        methodInput.value = 'PUT';
        title.innerText = 'Edit Donasi';

        // Fill fields
        dateInput.value = data.date || '';
        document.getElementById('donor_name').value = data.donor.name;
        document.getElementById('donor_whatsapp').value = data.donor.whatsapp;
        document.getElementById('donor_address').value = data.donor.address || '';
        document.getElementById('quantity').value = data.quantity;
        document.getElementById('description').value = data.description || '';

        // Make donor fields readonly in edit mode
        const nameField = document.getElementById('donor_name');
        const whatsappField = document.getElementById('donor_whatsapp');
        const addressField = document.getElementById('donor_address');

        nameField.setAttribute('readonly', 'readonly');
        nameField.style.backgroundColor = '#f5f5f5';
        nameField.style.cursor = 'not-allowed';

        whatsappField.setAttribute('readonly', 'readonly');
        whatsappField.style.backgroundColor = '#f5f5f5';
        whatsappField.style.cursor = 'not-allowed';
        document.getElementById('whatsapp_readonly_note').style.display = 'block';

        addressField.setAttribute('readonly', 'readonly');
        addressField.style.backgroundColor = '#f5f5f5';
        addressField.style.cursor = 'not-allowed';
        document.getElementById('address_readonly_note').style.display = 'block';

        // Handle flexible date checkbox
        const flexibleCheckbox = document.getElementById('is_flexible_date');
        if (data.is_flexible_date) {
            flexibleCheckbox.checked = true;
            dateInput.removeAttribute('required');
            document.getElementById('dateInputContainer').style.display = 'none';
        } else {
            flexibleCheckbox.checked = false;
            dateInput.setAttribute('required', 'required');
            document.getElementById('dateInputContainer').style.display = 'block';
        }

        // Radio buttons
        const radios = document.getElementsByName('type');
        for (let r of radios) {
            if (r.value === data.type) r.checked = true;
        }
    } else {
        // Create Mode
        form.action = '/donations';
        methodInput.value = 'POST';

        // Reset readonly state for create mode
        const nameField = document.getElementById('donor_name');
        const whatsappField = document.getElementById('donor_whatsapp');
        const addressField = document.getElementById('donor_address');

        nameField.removeAttribute('readonly');
        nameField.style.backgroundColor = '';
        nameField.style.cursor = '';

        whatsappField.removeAttribute('readonly');
        whatsappField.style.backgroundColor = '';
        whatsappField.style.cursor = '';
        document.getElementById('whatsapp_readonly_note').style.display = 'none';

        addressField.removeAttribute('readonly');
        addressField.style.backgroundColor = '';
        addressField.style.cursor = '';
        document.getElementById('address_readonly_note').style.display = 'none';

        if (typeof data === 'string') {
            dateInput.value = data;
            title.innerText = `Input Donasi - ${formatDate(data)}`;
        } else {
            title.innerText = 'Input Donasi Baru';
        }
    }

    // Initialize autocomplete
    initDonorAutocomplete();
}

function initDonorAutocomplete() {
    const nameInput = document.getElementById('donor_name');
    const suggestionsContainer = document.getElementById('donor-suggestions');

    if (!nameInput || !suggestionsContainer) return;

    // Remove any existing listeners
    nameInput.removeEventListener('input', handleDonorInput);
    nameInput.addEventListener('input', handleDonorInput);

    // Close suggestions on click outside
    document.addEventListener('click', function (e) {
        if (!nameInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
        }
    });
}

function handleDonorInput(e) {
    const query = e.target.value;
    const suggestionsContainer = document.getElementById('donor-suggestions');

    clearTimeout(debounceTimer);

    if (query.length < 2) {
        suggestionsContainer.innerHTML = '';
        suggestionsContainer.style.display = 'none';
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`/donations/donor-suggestions?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(donors => {
                if (donors.length === 0) {
                    suggestionsContainer.innerHTML = '';
                    suggestionsContainer.style.display = 'none';
                    return;
                }

                let html = '';
                donors.forEach(donor => {
                    html += `
                        <div class="autocomplete-item" onclick="selectDonor('${escapeHtml(donor.name)}', '${escapeHtml(donor.whatsapp)}', '${escapeHtml(donor.address || '')}')">
                            <div style="font-weight: 500;">${escapeHtml(donor.name)}</div>
                            <div style="font-size: 0.85rem; color: #666;">${escapeHtml(donor.whatsapp)} - ${escapeHtml(donor.address || '-')}</div>
                        </div>
                    `;
                });

                suggestionsContainer.innerHTML = html;
                suggestionsContainer.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
            });
    }, 300);
}

function selectDonor(name, whatsapp, address) {
    document.getElementById('donor_name').value = name;
    document.getElementById('donor_whatsapp').value = whatsapp;
    document.getElementById('donor_address').value = address;

    const suggestionsContainer = document.getElementById('donor-suggestions');
    suggestionsContainer.innerHTML = '';
    suggestionsContainer.style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeDonationModal() {
    document.getElementById('donationModal').classList.remove('active');
}

function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    const overlay = document.getElementById('mobileMenuOverlay');

    navLinks.classList.toggle('active');

    if (navLinks.classList.contains('active')) {
        overlay.style.display = 'block';
    } else {
        overlay.style.display = 'none';
    }
}

function formatDate(dateString) {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Close modal on Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeDonationModal();
    }
});

// Date Detail Modal Logic
let currentSort = { key: 'donor_name', direction: 'asc' };
let currentModalDate = null;

function showDateDetails(date) {
    currentModalDate = date;
    const dateObj = new Date(date);
    const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
    // Fetch data via AJAX
    fetch(`/donations/donors?date=${date}`)
        .then(response => response.json())
        .then(data => {
            currentDateDonations = data; // Store for editing and sorting

            // Initial sort
            sortData('donor_name', 'asc');

            const viewModalHtml = `
                <div id="viewModal" class="modal active">
                    <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
                    <div class="modal-content" style="max-width: 900px;">
                        <div class="modal-header">
                            <h3>Donatur Tanggal ${dayName}, ${formatDate(date)}</h3>
                            <button class="close-modal" onclick="this.parentElement.parentElement.parentElement.remove()">&times;</button>
                        </div>
                        <div class="modal-body" id="donationTableContainer" style="max-height: 60vh; overflow-y: auto;">
                            ${renderDonationTable()}
                        </div>
                        <div class="modal-footer">
                              <button class="btn-secondary" onclick="this.parentElement.parentElement.parentElement.remove()">Tutup</button>
                              <button class="btn-secondary" onclick="downloadDateDetailsPDF('${date}')"><i class="ri-download-line"></i> Download PDF</button>
                              ${window.canEdit ? `<button class="btn-primary" onclick="this.parentElement.parentElement.parentElement.remove(); openDonationModal('${date}')">Tambah Donasi</button>` : ''}
                        </div>
                    </div>
                </div>
            `;

            // Remove existing viewModal if any
            const existingModal = document.getElementById('viewModal');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML('beforeend', viewModalHtml);
        });
}

function sortData(key, forceDirection = null) {
    if (forceDirection) {
        currentSort.key = key;
        currentSort.direction = forceDirection;
    } else {
        if (currentSort.key === key) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.key = key;
            currentSort.direction = 'asc';
        }
    }

    currentDateDonations.sort((a, b) => {
        let valA = a[key];
        let valB = b[key];

        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();

        if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
        if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });

    const container = document.getElementById('donationTableContainer');
    if (container) {
        container.innerHTML = renderDonationTable();
    }
}

function renderDonationTable() {
    const getSortIcon = (key) => {
        if (currentSort.key !== key) return '<i class="ri-arrow-up-down-line" style="font-size: 0.8em; opacity: 0.3;"></i>';
        return currentSort.direction === 'asc'
            ? '<i class="ri-arrow-up-line" style="color: var(--primary);"></i>'
            : '<i class="ri-arrow-down-line" style="color: var(--primary);"></i>';
    };

    const headerStyle = 'cursor: pointer; user-select: none; white-space: nowrap;';

    let listHtml = `
        <div class="table-container" style="margin-top: 1rem;">
            <table>
                <thead>
                    <tr>
                        <th style="${headerStyle}" onclick="sortData('donor_name')">Nama ${getSortIcon('donor_name')}</th>
                        <th style="${headerStyle}" onclick="sortData('donor_whatsapp')">WhatsApp ${getSortIcon('donor_whatsapp')}</th>
                        <th class="col-alamat" style="${headerStyle}" onclick="sortData('donor_address')">Alamat ${getSortIcon('donor_address')}</th>
                        <th style="${headerStyle}" onclick="sortData('type')">Tipe ${getSortIcon('type')}</th>
                        <th style="${headerStyle}" onclick="sortData('quantity')">Jml ${getSortIcon('quantity')}</th>
                        <th class="col-keterangan" style="${headerStyle}" onclick="sortData('description')">Ket ${getSortIcon('description')}</th>
                        ${window.canEdit ? '<th>Aksi</th>' : ''}
                    </tr>
                </thead>
                <tbody>
    `;

    if (currentDateDonations.length === 0) {
        listHtml += `<tr><td colspan="${window.canEdit ? 7 : 6}" style="text-align:center">Belum ada donatur.</td></tr>`;
    } else {
        currentDateDonations.forEach((d, index) => {
            listHtml += `
                <tr>
                    <td style="font-weight:500">${d.donor.name}</td>
                    <td>${d.donor.whatsapp}</td>
                    <td class="col-alamat" style="font-size:0.9em; color:#64748B">${d.donor.address || '-'}</td>
                    <td><span class="stat-badge ${d.type === 'nasi' ? 'stat-nasi' : 'stat-snack'}">${d.type}</span></td>
                    <td>${d.quantity}</td>
                    <td class="col-keterangan">${d.description || '-'}</td>
                    ${window.canEdit ? `
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <button onclick="editDonation(${index})" class="btn-warning" title="Edit">
                                Edit
                            </button>
                            <button onclick="deleteDonation(${d.id})" class="btn-danger" title="Hapus">
                                Hapus
                            </button>
                        </div>
                    </td>
                    ` : ''}
                </tr>
            `;
        });
    }

    listHtml += `</tbody></table></div>`;
    return listHtml;
}

function editDonation(index) {
    // Close view modal
    document.getElementById('viewModal').remove();
    // Open edit modal
    const donation = currentDateDonations[index];
    openDonationModal(donation);
}

function deleteDonation(id) {
    showConfirmModal('Yakin ingin menghapus donasi ini?', 'Hapus Donasi', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/donations/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (response.ok) {
                    return response.json().then(data => {
                        alert(data.message);
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
    });
}

// Donor Detail Modal Logic
function showDonorDetails(donorId) {
    fetch(`/donations/donor-donations?donor_id=${donorId}`)
        .then(response => response.json())
        .then(data => {
            // Get donor info from first record
            const donorName = data.length > 0 && data[0].donor ? data[0].donor.name : '-';
            const donorAddress = data.length > 0 && data[0].donor ? (data[0].donor.address || '-') : '-';
            const donorWhatsapp = data.length > 0 && data[0].donor ? data[0].donor.whatsapp : '-';

            // Check if user can edit (has access to window.canEdit variable)
            const canEdit = window.canEdit || false;
            const colspanCount = canEdit ? 5 : 4;

            let listHtml = `
                <div class="table-container" style="margin-top: 1rem;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th class="col-keterangan">Keterangan</th>
                                ${canEdit ? '<th>Aksi</th>' : ''}
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (data.length === 0) {
                listHtml += `<tr><td colspan="${colspanCount}" style="text-align:center">Tidak ada data donasi.</td></tr>`;
            } else {
                data.forEach((d) => {
                    let dateFormatted = null;
                    if (!d.date) {
                        dateFormatted = 'Belum dijadwalkan';
                    } else {
                        const dateObj = new Date(d.date);
                        const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                        dateFormatted = `${dayName}, ${dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}`;
                    }
                    listHtml += `
                        <tr>
                            <td>${dateFormatted}</td>
                            <td><span class="stat-badge ${d.type === 'nasi' ? 'stat-nasi' : 'stat-snack'}">${d.type}</span></td>
                            <td style="font-weight:700">${d.quantity} Box</td>
                            <td class="col-keterangan">${d.description || '-'}</td>
                            ${canEdit ? `
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick='closeDonorDetailModal(); openDonationModal(${JSON.stringify(d).replace(/'/g, "\\'")})'class="btn-warning" title="Edit">
                                            Edit
                                        </button>
                                        <button onclick="deleteDonation(${d.id})" class="btn-danger" title="Hapus">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            ` : ''}
                        </tr>
                    `;
                });
            }

            listHtml += `</tbody></table></div>`;

            // Remove existing modal if any
            const existingModal = document.getElementById('donorDetailModal');
            if (existingModal) existingModal.remove();

            const modalHtml = `
                <div id="donorDetailModal" class="modal active">
                    <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
                    <div class="modal-content" style="max-width: 900px;">
                        <div class="modal-header">
                            <div>
                                <h3>Detail Donasi - ${donorName}</h3>
                                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;  line-height: 1.5;">
                                    <div><strong>WhatsApp:</strong> ${donorWhatsapp}</div>
                                    <div><strong>Alamat:</strong> ${donorAddress}</div>
                                </div>
                            </div>
                            <button class="close-modal" onclick="this.parentElement.parentElement.parentElement.remove()">&times;</button>
                        </div>
                        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                            ${listHtml}
                        </div>
                        <div class="modal-footer">
                              <button class="btn-secondary" onclick="this.parentElement.parentElement.parentElement.remove()">Tutup</button>
                              <button class="btn-secondary" onclick="downloadDonorDetailsPDF('${donorName}')"><i class="ri-download-line"></i> Download PDF</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
        });
}

function closeDonorDetailModal() {
    const modal = document.getElementById('donorDetailModal');
    if (modal) modal.remove();
}

//  PDF Download Functions
function downloadDateDetailsPDF(date) {
    window.location.href = `/donations/date-pdf/${date}`;
}
function downloadDonorDetailsPDF(donorName) {
    window.location.href = `/donations/donor-pdf/${encodeURIComponent(donorName)}`;
}
