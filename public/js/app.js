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
        document.getElementById('donor_name').value = data.donor_name;
        document.getElementById('donor_whatsapp').value = data.donor_whatsapp;
        document.getElementById('donor_address').value = data.donor_address || '';
        document.getElementById('quantity').value = data.quantity;
        document.getElementById('description').value = data.description || '';

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
                        <div class="autocomplete-item" onclick="selectDonor('${escapeHtml(donor.donor_name)}', '${escapeHtml(donor.donor_whatsapp)}', '${escapeHtml(donor.donor_address || '')}')">
                            <div style="font-weight: 500;">${escapeHtml(donor.donor_name)}</div>
                            <div style="font-size: 0.85rem; color: #666;">${escapeHtml(donor.donor_whatsapp)} - ${escapeHtml(donor.donor_address || '-')}</div>
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
                    <td style="font-weight:500">${d.donor_name}</td>
                    <td>${d.donor_whatsapp}</td>
                    <td class="col-alamat" style="font-size:0.9em; color:#64748B">${d.donor_address || '-'}</td>
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
function showDonorDetails(donorName) {
    fetch(`/donations/donor-donations?donor_name=${encodeURIComponent(donorName)}`)
        .then(response => response.json())
        .then(data => {
            // Get donor info from first record
            const donorAddress = data.length > 0 ? (data[0].donor_address || '-') : '-';
            const donorWhatsapp = data.length > 0 ? data[0].donor_whatsapp : '-';

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
    const formattedDate = formatDate(date);
    const dateObj = new Date(date);
    const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });

    // Create a temporary container for PDF content
    const pdfContent = document.createElement('div');
    pdfContent.style.padding = '20px';
    pdfContent.style.fontFamily = 'Arial, sans-serif';

    pdfContent.innerHTML = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #0F766E; margin-bottom: 5px;">Daftar Donatur</h2>
            <h3 style="color: #64748B; margin-top: 5px;">Tanggal: ${formattedDate}</h3>
            <p style="font-size: 12px; color: #9CA3AF;">Masjid An-Nur - Takjil Manager</p>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #0F766E; color: white;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Nama</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">WhatsApp</th>
                    <th class="col-alamat" style="padding: 10px; border: 1px solid #ddd; text-align: left;">Alamat</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Tipe</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Jumlah</th>
                    <th class="col-keterangan" style="padding: 10px; border: 1px solid #ddd; text-align: left;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                ${currentDateDonations.map(d => `
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">${d.donor_name}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">${d.donor_whatsapp}</td>
                        <td class="col-alamat" style="padding: 8px; border: 1px solid #ddd; font-size: 11px;">${d.donor_address || '-'}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center; background-color: ${d.type === 'nasi' ? '#E6FFEA' : '#E0F2FF'}; font-weight: bold;">${d.type.toUpperCase()}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${d.quantity}</td>
                        <td class="col-keterangan" style="padding: 8px; border: 1px solid #ddd; font-size: 11px;">${d.description || '-'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div style="margin-top: 20px; font-size: 11px; color: #64748B;">
            <p><strong>Total Donatur:</strong> ${currentDateDonations.length} orang</p>
            <p><strong>Total Nasi:</strong> ${currentDateDonations.filter(d => d.type === 'nasi').reduce((sum, d) => sum + d.quantity, 0)} porsi</p>
            <p><strong>Total Snack:</strong> ${currentDateDonations.filter(d => d.type === 'snack').reduce((sum, d) => sum + d.quantity, 0)} porsi</p>
        </div>
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #9CA3AF;">
            <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
        </div>
    `;

    const opt = {
        margin: 10,
        filename: `Donatur_${date}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(pdfContent).save();
}

function downloadDonorDetailsPDF(donorName) {
    fetch(`/donations/donor-donations?donor_name=${encodeURIComponent(donorName)}`)
        .then(response => response.json())
        .then(data => {
            const donorAddress = data.length > 0 ? (data[0].donor_address || '-') : '-';
            const donorWhatsapp = data.length > 0 ? data[0].donor_whatsapp : '-';

            // Create a temporary container for PDF content
            const pdfContent = document.createElement('div');
            pdfContent.style.padding = '20px';
            pdfContent.style.fontFamily = 'Arial, sans-serif';

            pdfContent.innerHTML = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #0F766E; margin-bottom: 5px;">Riwayat Donasi</h2>
                    <h3 style="color: #64748B; margin-top: 5px;">${donorName}</h3>
                    <p style="font-size: 12px; color: #9CA3AF;">Mas jid An-Nur - Takjil Manager</p>
                </div>
                <div style="background-color: #F9FAFB; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 5px 0;"><strong>WhatsApp:</strong> ${donorWhatsapp}</p>
                    <p style="margin: 5px 0;"><strong>Alamat:</strong> ${donorAddress}</p>
                </div>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background-color: #0F766E; color: white;">
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Tanggal</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Tipe</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Jumlah</th>
                            <th class="col-keterangan" style="padding: 10px; border: 1px solid #ddd; text-align: left;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(d => {
                // const dateFormatted = d.date ? new Date(d.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Belum dijadwalkan';
                if (!d.date) {
                    return 'Belum dijadwalkan';
                }
                const dateObj = new Date(d.date);
                const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                const dateFormatted = `${dayName}, ${dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}`;
                return `
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">${dateFormatted}</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center; background-color: ${d.type === 'nasi' ? '#E6FFEA' : '#E0F2FF'}; font-weight: bold;">${d.type.toUpperCase()}</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-weight: bold;">${d.quantity} Box</td>
                                    <td class="col-keterangan" style="padding: 8px; border: 1px solid #ddd;">${d.description || '-'}</td>
                                </tr>
                            `;
            }).join('')}
                    </tbody>
                </table>
                <div style="margin-top: 20px; font-size: 11px; color: #64748B;">
                    <p><strong>Total Donasi:</strong> ${data.length} kali</p>
                   <p><strong>Total Nasi:</strong> ${data.filter(d => d.type === 'nasi').reduce((sum, d) => sum + d.quantity, 0)} Box</p>
                    <p><strong>Total Snack:</strong> ${data.filter(d => d.type === 'snack').reduce((sum, d) => sum + d.quantity, 0)} Box</p>
                </div>
                <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #9CA3AF;">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                </div>
            `;

            const opt = {
                margin: 10,
                filename: `Riwayat_Donasi_${donorName.replace(/\s+/g, '_')}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(pdfContent).save();
        });
}
