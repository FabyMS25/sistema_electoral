<script>
function initializeChoices() {
    // Initialize election type category select
    if (document.getElementById('election_type_category_id-field')) {
        const electionTypeCategorySelect = new Choices('#election_type_category_id-field', {
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Seleccione una combinación',
            itemSelectText: '',
            removeItemButton: false,
            allowHTML: true,
        });
        window.electionTypeCategoryChoices = electionTypeCategorySelect;
    }

    // Initialize department select
    const departmentField = document.getElementById('department_id-field');
    if (departmentField) {
        if (window.departmentChoices) {
            window.departmentChoices.destroy();
        }
        const departmentSelect = new Choices('#department_id-field', {
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Seleccione un departamento',
            itemSelectText: '',
            removeItemButton: false,
            allowHTML: true,
        });
        window.departmentChoices = departmentSelect;
    }

    // Initialize province select (initially disabled)
    const provinceField = document.getElementById('province_id-field');
    if (provinceField) {
        if (window.provinceChoices) {
            window.provinceChoices.destroy();
        }
        const provinceSelect = new Choices('#province_id-field', {
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Primero seleccione departamento',
            itemSelectText: '',
            allowHTML: true,
        });
        window.provinceChoices = provinceSelect;
    }

    // Initialize municipality select (initially disabled)
    const municipalityField = document.getElementById('municipality_id-field');
    if (municipalityField) {
        if (window.municipalityChoices) {
            window.municipalityChoices.destroy();
        }
        const municipalitySelect = new Choices('#municipality_id-field', {
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Primero seleccione provincia',
            itemSelectText: '',
            allowHTML: true,
        });
        window.municipalityChoices = municipalitySelect;
    }
}

function setupColorPicker() {
    const colorPicker = document.getElementById('color-field');
    const colorHex = document.getElementById('color-hex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });

        colorHex.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
    }
}

function setupGeographicSelects() {
    const departmentSelect = document.getElementById('department_id-field');
    const provinceSelect = document.getElementById('province_id-field');
    const municipalitySelect = document.getElementById('municipality_id-field');

    if (!departmentSelect || !provinceSelect || !municipalitySelect) {
        console.error('No se encontraron los selectores geográficos en el DOM');
        return;
    }

    // Destroy existing Choices instances if they exist
    if (window.departmentChoices) window.departmentChoices.destroy();
    if (window.provinceChoices) window.provinceChoices.destroy();
    if (window.municipalityChoices) window.municipalityChoices.destroy();

    // Reinitialize with proper configuration
    window.departmentChoices = new Choices(departmentSelect, {
        searchEnabled: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'Seleccione un departamento',
        itemSelectText: '',
        removeItemButton: false,
        allowHTML: true,
    });

    window.provinceChoices = new Choices(provinceSelect, {
        searchEnabled: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'Primero seleccione departamento',
        itemSelectText: '',
        allowHTML: true,
    });

    window.municipalityChoices = new Choices(municipalitySelect, {
        searchEnabled: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'Primero seleccione provincia',
        itemSelectText: '',
        allowHTML: true,
    });

    // Department change handler
    departmentSelect.addEventListener('change', function() {
        const departmentId = this.value;

        // Clear and disable dependent selects
        if (window.provinceChoices) {
            window.provinceChoices.destroy();
        }
        if (window.municipalityChoices) {
            window.municipalityChoices.destroy();
        }

        if (!departmentId) {
            // Reset province select
            provinceSelect.innerHTML = '<option value="">Primero seleccione departamento</option>';
            provinceSelect.disabled = true;

            // Reset municipality select
            municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
            municipalitySelect.disabled = true;

            // Reinitialize with disabled state
            window.provinceChoices = new Choices(provinceSelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione departamento',
                itemSelectText: '',
                allowHTML: true,
            });

            window.municipalityChoices = new Choices(municipalitySelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione provincia',
                itemSelectText: '',
                allowHTML: true,
            });
            return;
        }

        // Load provinces
        fetch(`/candidates/provinces/${departmentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(provinces => {
                let options = '<option value="">Seleccione una provincia</option>';
                provinces.forEach(province => {
                    options += `<option value="${province.id}">${province.name}</option>`;
                });
                provinceSelect.innerHTML = options;
                provinceSelect.disabled = false;

                window.provinceChoices = new Choices(provinceSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Seleccione una provincia',
                    itemSelectText: '',
                    allowHTML: true,
                });

                // If we have a selected province ID from edit mode, set it
                if (window.selectedProvinceId) {
                    setTimeout(() => {
                        window.provinceChoices.setChoiceByValue(window.selectedProvinceId);
                        // Trigger change event to load municipalities
                        const event = new Event('change', { bubbles: true });
                        provinceSelect.dispatchEvent(event);
                        window.selectedProvinceId = null;
                    }, 200);
                }
            })
            .catch(error => {
                console.error('Error loading provinces:', error);
                provinceSelect.innerHTML = '<option value="">Error al cargar provincias</option>';
                provinceSelect.disabled = false;

                window.provinceChoices = new Choices(provinceSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Error al cargar',
                    itemSelectText: '',
                    allowHTML: true,
                });
            });
    });

    // Province change handler
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;

        if (window.municipalityChoices) {
            window.municipalityChoices.destroy();
        }

        if (!provinceId) {
            municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
            municipalitySelect.disabled = true;

            window.municipalityChoices = new Choices(municipalitySelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione provincia',
                itemSelectText: '',
                allowHTML: true,
            });
            return;
        }

        // Load municipalities
        fetch(`/candidates/municipalities/${provinceId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(municipalities => {
                let options = '<option value="">Seleccione un municipio</option>';
                municipalities.forEach(municipality => {
                    options += `<option value="${municipality.id}">${municipality.name}</option>`;
                });
                municipalitySelect.innerHTML = options;
                municipalitySelect.disabled = false;

                window.municipalityChoices = new Choices(municipalitySelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Seleccione un municipio',
                    itemSelectText: '',
                    allowHTML: true,
                });

                // If we have a selected municipality ID from edit mode, set it
                if (window.selectedMunicipalityId) {
                    setTimeout(() => {
                        window.municipalityChoices.setChoiceByValue(window.selectedMunicipalityId);
                        window.selectedMunicipalityId = null;
                    }, 200);
                }
            })
            .catch(error => {
                console.error('Error loading municipalities:', error);
                municipalitySelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                municipalitySelect.disabled = false;

                window.municipalityChoices = new Choices(municipalitySelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Error al cargar',
                    itemSelectText: '',
                    allowHTML: true,
                });
            });
    });
}

function setupImagePreviews() {
    const photoField = document.getElementById('photo-field');
    const partyLogoField = document.getElementById('party_logo-field');
    const photoPreview = document.getElementById('photo-preview');
    const partyLogoPreview = document.getElementById('party-logo-preview');

    if (photoField && photoPreview) {
        photoField.addEventListener('change', function(e) {
            previewImage(this, photoPreview);
        });
    }

    if (partyLogoField && partyLogoPreview) {
        partyLogoField.addEventListener('change', function(e) {
            previewImage(this, partyLogoPreview);
        });
    }
}

function previewImage(input, previewElement) {
    if (input.files && input.files[0] && previewElement) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function setupViewButton() {
    const viewButtons = document.querySelectorAll('.view-item-btn');
    const modalElement = document.getElementById('viewCandidateModal');

    if (!modalElement) {
        console.error('ERROR: Modal viewCandidateModal no encontrado en el DOM');
        return;
    }

    if (viewButtons.length === 0) {
        console.warn('No se encontraron botones con clase .view-item-btn');
        return;
    }

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Check for required elements
            const requiredElements = [
                'view-name', 'view-party', 'view-party-full-name',
                'view-list', 'view-election-type', 'view-election-category',
                'view-election-code', 'view-ballot-order', 'view-votes-per-person',
                'view-location', 'view-active', 'view-photo', 'view-party-logo',
                'view-color-preview'
            ];

            let missingElements = [];
            requiredElements.forEach(id => {
                if (!document.getElementById(id)) {
                    missingElements.push(id);
                }
            });

            if (missingElements.length > 0) {
                console.error('Elementos faltantes en el DOM:', missingElements);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pueden cargar los detalles del candidato. Faltan elementos en el modal.'
                });
                return;
            }

            // Basic info
            document.getElementById('view-name').textContent = this.dataset.name || 'N/A';
            document.getElementById('view-party').textContent = this.dataset.party || 'N/A';
            document.getElementById('view-party-full-name').textContent = this.dataset.party_full_name || 'N/A';

            // List info
            const listOrder = this.dataset.list_order;
            const listName = this.dataset.list_name;
            if (listName && listOrder) {
                document.getElementById('view-list').textContent = `${listName} (Orden: ${listOrder})`;
            } else if (listName) {
                document.getElementById('view-list').textContent = listName;
            } else if (listOrder) {
                document.getElementById('view-list').textContent = `Orden: ${listOrder}`;
            } else {
                document.getElementById('view-list').textContent = 'N/A';
            }

            // Election info
            document.getElementById('view-election-type').textContent = this.dataset.election_type || 'N/A';
            document.getElementById('view-election-category').textContent = this.dataset.election_category || 'N/A';
            document.getElementById('view-election-code').textContent = this.dataset.election_category_code || 'N/A';
            document.getElementById('view-ballot-order').textContent = this.dataset.ballot_order || 'N/A';
            document.getElementById('view-votes-per-person').textContent = this.dataset.votes_per_person || '1';

            // Location
            const locationParts = [];
            if (this.dataset.department_name) locationParts.push(this.dataset.department_name);
            if (this.dataset.province_name) locationParts.push(this.dataset.province_name);
            if (this.dataset.municipality_name) locationParts.push(this.dataset.municipality_name);
            document.getElementById('view-location').textContent = locationParts.length > 0 ? locationParts.join(' / ') : 'N/A';

            // Status
            document.getElementById('view-active').innerHTML = this.dataset.active === '1' ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-danger">Inactivo</span>';

            // Photo
            const photo = document.getElementById('view-photo');
            if (this.dataset.photoUrl && this.dataset.photoUrl !== 'null' && this.dataset.photoUrl !== 'undefined') {
                photo.src = this.dataset.photoUrl;
                photo.style.display = 'block';
            } else {
                photo.src = '/build/images/default-candidate.jpg';
                photo.style.display = 'block';
            }

            // Party logo
            const partyLogo = document.getElementById('view-party-logo');
            if (this.dataset.partyLogoUrl && this.dataset.partyLogoUrl !== 'null' && this.dataset.partyLogoUrl !== 'undefined') {
                partyLogo.src = this.dataset.partyLogoUrl;
                partyLogo.style.display = 'inline-block';
            } else {
                partyLogo.style.display = 'none';
            }

            // Color preview
            const colorPreview = document.getElementById('view-color-preview');
            if (this.dataset.color) {
                colorPreview.style.backgroundColor = this.dataset.color;
                colorPreview.style.display = 'block';
                colorPreview.title = this.dataset.color;
            } else {
                colorPreview.style.display = 'none';
            }
        });
    });
}

function setupEditButton() {
    const editButtons = document.querySelectorAll('.edit-item-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.dataset || !this.dataset.updateUrl) {
                console.error('El botón de edición no tiene los atributos data necesarios');
                return;
            }

            resetModalForEdit(this.dataset.updateUrl);

            // Basic fields
            document.getElementById('name-field').value = this.dataset.name || '';
            document.getElementById('party-field').value = this.dataset.party || '';
            document.getElementById('party_full_name-field').value = this.dataset.party_full_name || '';
            document.getElementById('color-field').value = this.dataset.color || '#1b8af8';
            document.getElementById('color-hex').value = this.dataset.color || '#1b8af8';

            // Election type category
            const electionField = document.getElementById('election_type_category_id-field');
            if (electionField) {
                if (window.electionTypeCategoryChoices) {
                    window.electionTypeCategoryChoices.setChoiceByValue(this.dataset.election_type_category_id || '');
                } else {
                    electionField.value = this.dataset.election_type_category_id || '';
                }
            }

            // List fields
            document.getElementById('list_order-field').value = this.dataset.list_order || '';
            document.getElementById('list_name-field').value = this.dataset.list_name || '';

            // Candidate ID for form
            const candidateIdField = document.getElementById('candidate_id');
            if (candidateIdField && this.dataset.id) {
                candidateIdField.value = this.dataset.id;
            }

            // Geographic selects
            const departmentField = document.getElementById('department_id-field');
            const provinceField = document.getElementById('province_id-field');
            const municipalityField = document.getElementById('municipality_id-field');

            if (departmentField) {
                // Store selected IDs for later use
                window.selectedProvinceId = this.dataset.province_id;
                window.selectedMunicipalityId = this.dataset.municipality_id;

                // Set department
                if (window.departmentChoices) {
                    window.departmentChoices.setChoiceByValue(this.dataset.department_id || '');
                } else {
                    departmentField.value = this.dataset.department_id || '';
                }

                // Trigger change to load provinces if department is selected
                if (this.dataset.department_id) {
                    setTimeout(() => {
                        const event = new Event('change', { bubbles: true });
                        departmentField.dispatchEvent(event);
                    }, 200);
                }
            }

            // Update image previews
            updateImagePreviews(this.dataset);

            // Active status
            const activeRow = document.getElementById('active-status-row');
            if (activeRow) {
                activeRow.style.display = 'block';
                const activeField = document.getElementById('active-field');
                if (activeField) {
                    activeField.checked = this.dataset.active === '1' || this.dataset.active === 'true';
                }
            }
        });
    });
}

function resetModalForEdit(updateUrl) {
    const modalTitle = document.getElementById('modalTitleText');
    const form = document.getElementById('candidateForm');
    const methodField = document.getElementById('method_field');
    const formMethod = document.getElementById('form-method');

    if (modalTitle) modalTitle.textContent = 'Editar Candidato';
    if (form) form.action = updateUrl;
    if (methodField) methodField.value = 'PUT';
    if (formMethod) formMethod.value = 'PUT';
}

function updateImagePreviews(data) {
    const photoPreview = document.getElementById('photo-preview');
    const partyLogoPreview = document.getElementById('party-logo-preview');

    if (photoPreview) {
        if (data.photoUrl && data.photoUrl !== 'null' && data.photoUrl !== 'undefined') {
            photoPreview.src = data.photoUrl;
            photoPreview.style.display = 'block';
        } else {
            photoPreview.style.display = 'none';
        }
    }

    if (partyLogoPreview) {
        if (data.partyLogoUrl && data.partyLogoUrl !== 'null' && data.partyLogoUrl !== 'undefined') {
            partyLogoPreview.src = data.partyLogoUrl;
            partyLogoPreview.style.display = 'block';
        } else {
            partyLogoPreview.style.display = 'none';
        }
    }
}

function setupCreateButton() {
    const createBtn = document.getElementById('create-btn');

    if (createBtn) {
        createBtn.addEventListener('click', function() {
            resetModalForCreate();

            // Reset form
            const form = document.getElementById('candidateForm');
            if (form) form.reset();

            // Set default color
            const colorField = document.getElementById('color-field');
            if (colorField) colorField.value = '#1b8af8';

            const colorHex = document.getElementById('color-hex');
            if (colorHex) colorHex.value = '#1b8af8';

            // Hide previews
            const photoPreview = document.getElementById('photo-preview');
            if (photoPreview) photoPreview.style.display = 'none';

            const partyLogoPreview = document.getElementById('party-logo-preview');
            if (partyLogoPreview) partyLogoPreview.style.display = 'none';

            // Reset geographic selects
            resetGeographicSelects();

            // Reset Choices instances
            resetChoicesInstances();

            // Hide active status row
            const activeStatusRow = document.getElementById('active-status-row');
            if (activeStatusRow) {
                activeStatusRow.style.display = 'none';
            }
        });
    }
}

function resetGeographicSelects() {
    const provinceSelect = document.getElementById('province_id-field');
    const municipalitySelect = document.getElementById('municipality_id-field');
    const departmentSelect = document.getElementById('department_id-field');

    if (provinceSelect) {
        provinceSelect.innerHTML = '<option value="">Primero seleccione departamento</option>';
        provinceSelect.disabled = true;
        if (window.provinceChoices) {
            window.provinceChoices.destroy();
            window.provinceChoices = new Choices(provinceSelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione departamento',
                itemSelectText: '',
                allowHTML: true,
            });
        }
    }

    if (municipalitySelect) {
        municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
        municipalitySelect.disabled = true;
        if (window.municipalityChoices) {
            window.municipalityChoices.destroy();
            window.municipalityChoices = new Choices(municipalitySelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione provincia',
                itemSelectText: '',
                allowHTML: true,
            });
        }
    }

    if (departmentSelect && window.departmentChoices) {
        window.departmentChoices.setChoiceByValue('');
    }
}

function resetModalForCreate() {
    const modalTitle = document.getElementById('modalTitleText');
    const form = document.getElementById('candidateForm');
    const methodField = document.getElementById('method_field');
    const formMethod = document.getElementById('form-method');
    const candidateId = document.getElementById('candidate_id');

    if (modalTitle) modalTitle.textContent = 'Agregar Nuevo Candidato';
    if (form) form.action = '/candidates';
    if (methodField) methodField.value = '';
    if (formMethod) formMethod.value = 'POST';
    if (candidateId) candidateId.value = '';
}

function resetChoicesInstances() {
    if (window.electionTypeCategoryChoices) {
        try {
            window.electionTypeCategoryChoices.destroy();
            window.electionTypeCategoryChoices = new Choices('#election_type_category_id-field', {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Seleccione una combinación',
                itemSelectText: '',
                allowHTML: true,
            });
        } catch (e) {
            console.warn('Error reiniciando Choices para election_type_category_id-field');
        }
    }
}

function setupDeleteButton() {
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const deleteForm = document.getElementById('deleteForm');
            const deleteMessage = document.getElementById('deleteMessage');
            const candidateName = this.dataset.name || 'este candidato';

            if (deleteForm) deleteForm.action = this.dataset.deleteUrl;
            if (deleteMessage) deleteMessage.textContent = `¿Está seguro de que desea eliminar el candidato "${candidateName}"?`;
        });
    });
}

function setupCheckAll() {
    const checkAll = document.getElementById('checkAll');
    const childCheckboxes = document.querySelectorAll('.child-checkbox');
    const deleteMultipleBtn = document.getElementById('delete-multiple-btn');
    const exportSelectedBtn = document.getElementById('export-selected-btn');
    const selectedCountBadge = document.getElementById('selected-count-badge');

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            childCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedButtons(childCheckboxes, deleteMultipleBtn, exportSelectedBtn, selectedCountBadge);
        });
    }

    childCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (checkAll) {
                const allChecked = Array.from(childCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(childCheckboxes).some(cb => cb.checked);

                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            }
            updateSelectedButtons(childCheckboxes, deleteMultipleBtn, exportSelectedBtn, selectedCountBadge);
        });
    });
}

function updateSelectedButtons(childCheckboxes, deleteMultipleBtn, exportSelectedBtn, selectedCountBadge) {
    const selectedCount = Array.from(childCheckboxes).filter(cb => cb.checked).length;

    if (selectedCount > 0) {
        if (deleteMultipleBtn) {
            deleteMultipleBtn.style.display = 'inline-block';
            deleteMultipleBtn.innerHTML = `<i class="ri-delete-bin-2-line me-1"></i>Eliminar Seleccionados (${selectedCount})`;
        }
        if (exportSelectedBtn && selectedCountBadge) {
            exportSelectedBtn.disabled = false;
            selectedCountBadge.textContent = selectedCount;
            selectedCountBadge.style.display = 'inline-block';
        }
    } else {
        if (deleteMultipleBtn) deleteMultipleBtn.style.display = 'none';
        if (exportSelectedBtn && selectedCountBadge) {
            exportSelectedBtn.disabled = true;
            selectedCountBadge.style.display = 'none';
        }
    }
}

window.exportSelected = function() {
    const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
        .map(cb => cb.value);

    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Por favor seleccione al menos un candidato para exportar.',
            confirmButtonColor: '#1b8af8'
        });
        return;
    }

    const selectedIdsInput = document.getElementById('selected-ids-input');
    if (selectedIdsInput) {
        selectedIdsInput.value = JSON.stringify(selectedIds);
    }

    const exportForm = document.getElementById('export-selected-form');
    if (exportForm) exportForm.submit();
};

window.deleteMultiple = function() {
    const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
        .map(cb => cb.value);

    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Por favor seleccione al menos un candidato para eliminar.',
            confirmButtonColor: '#1b8af8'
        });
        return;
    }

    Swal.fire({
        title: '¿Está seguro?',
        text: `Se eliminarán ${selectedIds.length} candidato(s). Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#8590a5',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/candidates/multiple-delete';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken || ''}">
                <input type="hidden" name="_method" value="DELETE">
            `;

            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeChoices();
    setupColorPicker();
    setupGeographicSelects();
    setupImagePreviews();
    setupEditButton();
    setupViewButton();
    setupCreateButton();
    setupDeleteButton();
    setupCheckAll();
});
</script>
