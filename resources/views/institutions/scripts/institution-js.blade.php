{{-- resources/views/institutions/scripts/institution-js.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Institution JS loaded');

    // Initialize Choices.js for select elements
    if (typeof Choices !== 'undefined') {
        const selectElements = document.querySelectorAll('.form-select');
        selectElements.forEach(select => {
            if (!select._choices) {
                new Choices(select, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholder: true,
                });
            }
        });
    }

    // Location cascading selects
    const departmentSelect = document.getElementById('department-field');
    const provinceSelect = document.getElementById('province-field');
    const municipalitySelect = document.getElementById('municipality-field');
    const localitySelect = document.getElementById('locality-field');
    const districtSelect = document.getElementById('district-field');
    const zoneSelect = document.getElementById('zone-field');

    // Department change
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;

            // Reset and disable dependent selects
            resetSelect(provinceSelect, '-- Seleccione Provincia --');
            resetSelect(municipalitySelect, '-- Seleccione Municipio --');
            resetSelect(localitySelect, '-- Seleccione Localidad --');
            resetSelect(districtSelect, '-- Seleccione Distrito (opcional) --');
            resetSelect(zoneSelect, '-- Seleccione Zona (opcional) --');

            if (departmentId) {
                const url = this.dataset.url.replace(/\/$/, '') + '/' + departmentId;
                fetchOptions(url, provinceSelect, 'provincia');
            }
        });
    }

    // Province change
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const provinceId = this.value;

            resetSelect(municipalitySelect, '-- Seleccione Municipio --');
            resetSelect(localitySelect, '-- Seleccione Localidad --');
            resetSelect(districtSelect, '-- Seleccione Distrito (opcional) --');
            resetSelect(zoneSelect, '-- Seleccione Zona (opcional) --');

            if (provinceId) {
                const url = this.dataset.url.replace(/\/$/, '') + '/' + provinceId;
                fetchOptions(url, municipalitySelect, 'municipio');
            }
        });
    }

    // Municipality change
    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;

            resetSelect(localitySelect, '-- Seleccione Localidad --');
            resetSelect(districtSelect, '-- Seleccione Distrito (opcional) --');
            resetSelect(zoneSelect, '-- Seleccione Zona (opcional) --');

            if (municipalityId) {
                const url = this.dataset.url.replace(/\/$/, '') + '/' + municipalityId;
                fetchOptions(url, localitySelect, 'localidad');
            }
        });
    }

    // Locality change
    if (localitySelect) {
        localitySelect.addEventListener('change', function() {
            const localityId = this.value;

            resetSelect(districtSelect, '-- Seleccione Distrito (opcional) --');
            resetSelect(zoneSelect, '-- Seleccione Zona (opcional) --');

            if (localityId) {
                const url = this.dataset.url.replace(/\/$/, '') + '/' + localityId;
                fetchOptions(url, districtSelect, 'distrito', true);
            }
        });
    }

    // District change
    if (districtSelect) {
        districtSelect.addEventListener('change', function() {
            const districtId = this.value;

            resetSelect(zoneSelect, '-- Seleccione Zona (opcional) --');

            if (districtId) {
                const url = this.dataset.url.replace(/\/$/, '') + '/' + districtId;
                fetchOptions(url, zoneSelect, 'zona', true);
            }
        });
    }

    function resetSelect(select, placeholder) {
        if (!select) return;

        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;

        if (select._choices) {
            select._choices.destroy();
            select._choices = null;
        }
    }

    function fetchOptions(url, targetSelect, type, optional = false) {
        if (!targetSelect) return;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                targetSelect.innerHTML = `<option value="">-- Seleccione ${type.charAt(0).toUpperCase() + type.slice(1)} ${optional ? '(opcional)' : ''} --</option>`;

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(item => {
                        targetSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                    targetSelect.disabled = false;
                } else {
                    targetSelect.innerHTML += `<option value="" disabled>No hay ${type}s disponibles</option>`;
                    targetSelect.disabled = true;
                }

                if (typeof Choices !== 'undefined') {
                    if (targetSelect._choices) {
                        targetSelect._choices.destroy();
                    }
                    targetSelect._choices = new Choices(targetSelect, {
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: '',
                        placeholder: true,
                    });
                }
            })
            .catch(error => {
                console.error('Error loading data:', error);
                targetSelect.innerHTML = `<option value="">-- Error al cargar ${type}s --</option>`;
                targetSelect.disabled = true;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error',
                        text: `No se pudieron cargar los ${type}s.`,
                        icon: 'error',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
    }

    // Check All functionality
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.child-checkbox');
    const deleteMultipleBtn = document.getElementById('delete-multiple-btn');
    const exportSelectedBtn = document.getElementById('export-selected-btn');
    const selectedCountBadge = document.getElementById('selected-count-badge');

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateActionButtons();
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCheckAllState();
            updateActionButtons();
        });
    });

    function updateCheckAllState() {
        if (checkboxes.length === 0) return;
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (checkAll) {
            checkAll.checked = allChecked;
            checkAll.indeterminate = !allChecked && anyChecked;
        }
    }

    function updateActionButtons() {
        const selectedCount = document.querySelectorAll('.child-checkbox:checked').length;

        if (deleteMultipleBtn) {
            if (selectedCount > 0) {
                deleteMultipleBtn.style.display = 'inline-block';
                deleteMultipleBtn.innerHTML = `<i class="ri-delete-bin-2-line me-1"></i>Eliminar Seleccionados (${selectedCount})`;
            } else {
                deleteMultipleBtn.style.display = 'none';
            }
        }

        if (exportSelectedBtn) {
            if (selectedCount > 0) {
                exportSelectedBtn.disabled = false;
                exportSelectedBtn.innerHTML = `<i class="ri-file-excel-line me-2"></i>Exportar Seleccionados`;
                if (selectedCountBadge) {
                    selectedCountBadge.style.display = 'inline-block';
                    selectedCountBadge.textContent = selectedCount;
                }
            } else {
                exportSelectedBtn.disabled = true;
                exportSelectedBtn.innerHTML = `<i class="ri-file-excel-line me-2"></i>Exportar Seleccionados`;
                if (selectedCountBadge) {
                    selectedCountBadge.style.display = 'none';
                }
            }
        }
    }
});

function exportSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
        .map(cb => cb.value);

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'Sin selección',
            text: 'Por favor seleccione al menos un recinto para exportar.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    document.getElementById('selected-ids-input').value = JSON.stringify(selectedIds);
    document.getElementById('export-selected-form').submit();
}

function deleteMultiple() {
    const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
        .map(cb => cb.value);

    if (selectedIds.length === 0) {
        return;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar ${selectedIds.length} recinto(s) seleccionado(s)? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route("institutions.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminados',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar los recintos.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}
</script>
