<script>
function initializeChoices() {
    if (document.getElementById('election_type_category_id-field')) {
        const electionTypeCategorySelect = new Choices('#election_type_category_id-field', {
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Seleccione una combinación'
        });
        window.electionTypeCategoryChoices = electionTypeCategorySelect;
    }
    
    if (document.getElementById('type-field')) {
        const candidateTypeSelect = new Choices('#type-field', {
            searchEnabled: false,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Seleccione un tipo'
        });
        window.typeChoices = candidateTypeSelect;
    }
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
        });
        window.departmentChoices = departmentSelect;
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

function loadProvinces(departmentId, provinceSelect, municipalitySelect) {
    if (!provinceSelect || !municipalitySelect) return;
    
    if (!departmentId) {
        provinceSelect.innerHTML = '<option value="">Primero seleccione departamento</option>';
        provinceSelect.disabled = true;
        municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
        municipalitySelect.disabled = true;
        return;
    }

    provinceSelect.innerHTML = '<option value="">Cargando...</option>';
    provinceSelect.disabled = true;
    
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
            if (window.provinceChoices) {
                window.provinceChoices.destroy();
            }
            window.provinceChoices = new Choices(provinceSelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Seleccione una provincia',
                itemSelectText: ''
            });
            if (window.selectedProvinceId) {
                provinceSelect.value = window.selectedProvinceId;
                window.provinceChoices.setChoiceByValue(window.selectedProvinceId);
                window.selectedProvinceId = null;
            }
        })
        .catch(error => {
            console.error('Error loading provinces:', error);
            provinceSelect.innerHTML = '<option value="">Error al cargar</option>';
            provinceSelect.disabled = false;
        });
}

function loadMunicipalities(provinceId, municipalitySelect) {
    if (!municipalitySelect) return;
    
    if (!provinceId) {
        municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
        municipalitySelect.disabled = true;
        return;
    }

    municipalitySelect.innerHTML = '<option value="">Cargando...</option>';
    municipalitySelect.disabled = true;
    
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
            if (window.municipalityChoices) {
                window.municipalityChoices.destroy();
            }
            window.municipalityChoices = new Choices(municipalitySelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Seleccione un municipio',
                itemSelectText: ''
            });
            if (window.selectedMunicipalityId) {
                municipalitySelect.value = window.selectedMunicipalityId;
                window.municipalityChoices.setChoiceByValue(window.selectedMunicipalityId);
                window.selectedMunicipalityId = null;
            }
        })
        .catch(error => {
            console.error('Error loading municipalities:', error);
            municipalitySelect.innerHTML = '<option value="">Error al cargar</option>';
            municipalitySelect.disabled = false;
        });
}

function loadMunicipalities(provinceId, municipalitySelect) {
    if (!municipalitySelect) return;
    
    if (!provinceId) {
        municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
        municipalitySelect.disabled = true;
        return;
    }

    municipalitySelect.innerHTML = '<option value="">Cargando...</option>';
    municipalitySelect.disabled = true;
    
    fetch(`/candidates/municipalities/${provinceId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(municipalities => {
            municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
            municipalities.forEach(municipality => {
                municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
            });
            municipalitySelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading municipalities:', error);
            municipalitySelect.innerHTML = '<option value="">Error al cargar</option>';
            municipalitySelect.disabled = false;
        });
}

function setupGeographicSelects() {    
    const departmentSelect = document.getElementById('department_id-field');
    const provinceSelect = document.getElementById('province_id-field');
    const municipalitySelect = document.getElementById('municipality_id-field');

    if (!departmentSelect || !provinceSelect || !municipalitySelect) {
        console.error('No se encontraron los selectores geográficos en el DOM');
        return;
    }
    if (window.departmentChoices) window.departmentChoices.destroy();
    if (window.provinceChoices) window.provinceChoices.destroy();
    if (window.municipalityChoices) window.municipalityChoices.destroy();
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
    departmentSelect.addEventListener('change', function() {
        const departmentId = this.value;
        if (window.provinceChoices) window.provinceChoices.destroy();
        if (window.municipalityChoices) window.municipalityChoices.destroy();
        
        if (!departmentId) {
            provinceSelect.innerHTML = '<option value="">Primero seleccione departamento</option>';
            provinceSelect.disabled = true;
            municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
            municipalitySelect.disabled = true;
            
            window.provinceChoices = new Choices(provinceSelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione departamento',
                itemSelectText: '',
            });
            
            window.municipalityChoices = new Choices(municipalitySelect, {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Primero seleccione provincia',
                itemSelectText: '',
            });
            return;
        }
        fetch(`/candidates/provinces/${departmentId}`)
            .then(response => response.json())
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
                });
                if (window.selectedProvinceId) {
                    window.provinceChoices.setChoiceByValue(window.selectedProvinceId);
                    setTimeout(() => {
                        provinceSelect.value = window.selectedProvinceId;
                        const event = new Event('change', { bubbles: true });
                        provinceSelect.dispatchEvent(event);
                    }, 100);
                }
            })
            .catch(error => {
                console.error('Error loading provinces:', error);
            });
    });
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
            });
            return;
        }
        fetch(`/candidates/municipalities/${provinceId}`)
            .then(response => response.json())
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
                });
                if (window.selectedMunicipalityId) {
                    window.municipalityChoices.setChoiceByValue(window.selectedMunicipalityId);
                    window.selectedMunicipalityId = null;
                }
            })
            .catch(error => {
                console.error('Error loading municipalities:', error);
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
            const requiredFields = [
                'view-name', 'view-party', 'view-party-full-name', 'view-type',
                'view-list', 'view-election-type', 'view-election-category',
                'view-location', 'view-active', 'view-photo', 'view-party-logo',
                'view-color-preview'
            ];
            let missingFields = [];
            requiredFields.forEach(id => {
                if (!document.getElementById(id)) {
                    missingFields.push(id);
                }
            });
            
            if (missingFields.length > 0) {
                console.error('Elementos faltantes en el DOM:', missingFields);
                alert('Error: No se pueden cargar los detalles del candidato. Faltan elementos en el modal.');
                return;
            }
            document.getElementById('view-name').textContent = this.dataset.name || 'N/A';
            document.getElementById('view-party').textContent = this.dataset.party || 'N/A';
            document.getElementById('view-party-full-name').textContent = this.dataset.party_full_name || 'N/A';
            const typeText = this.dataset.type === 'candidato' ? 'Candidato' :
                            this.dataset.type === 'blank_votes' ? 'Votos en Blanco' :
                            this.dataset.type === 'null_votes' ? 'Votos Nulos' : this.dataset.type;
            document.getElementById('view-type').textContent = typeText;
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
            document.getElementById('view-election-type').textContent = this.dataset.election_type || 'N/A';
            document.getElementById('view-election-category').textContent = this.dataset.election_category || 'N/A';
            const locationParts = [];
            if (this.dataset.department_name) locationParts.push(this.dataset.department_name);
            if (this.dataset.province_name) locationParts.push(this.dataset.province_name);
            if (this.dataset.municipality_name) locationParts.push(this.dataset.municipality_name);
            document.getElementById('view-location').textContent = locationParts.length > 0 ? locationParts.join(' / ') : 'N/A';
            document.getElementById('view-active').innerHTML = this.dataset.active === '1' ? 
                '<span class="badge bg-success">Activo</span>' : 
                '<span class="badge bg-danger">Inactivo</span>';
            const photo = document.getElementById('view-photo');
            if (this.dataset.photoUrl && this.dataset.photoUrl !== 'null') {
                photo.src = this.dataset.photoUrl;
                photo.style.display = 'block';
            } else {
                photo.src = '<?php echo e(asset("build/images/default-candidate.jpg")); ?>';
                photo.style.display = 'block';
            }
            
            const partyLogo = document.getElementById('view-party-logo');
            if (this.dataset.partyLogoUrl && this.dataset.partyLogoUrl !== 'null') {
                partyLogo.src = this.dataset.partyLogoUrl;
                partyLogo.style.display = 'inline-block';
            } else {
                partyLogo.style.display = 'none';
            }
            const colorPreview = document.getElementById('view-color-preview');
            if (this.dataset.color) {
                colorPreview.style.backgroundColor = this.dataset.color;
                colorPreview.style.display = 'block';
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
            document.getElementById('name-field').value = this.dataset.name || '';
            document.getElementById('party-field').value = this.dataset.party || '';
            document.getElementById('party_full_name-field').value = this.dataset.party_full_name || '';
            document.getElementById('color-field').value = this.dataset.color || '#1b8af8';
            document.getElementById('color-hex').value = this.dataset.color || '#1b8af8';
            const electionField = document.getElementById('election_type_category_id-field');
            if (electionField) {
                electionField.value = this.dataset.election_type_category_id || '';
                if (window.electionTypeCategoryChoices) {
                    window.electionTypeCategoryChoices.setChoiceByValue(this.dataset.election_type_category_id || '');
                }
            }
            const typeField = document.getElementById('type-field');
            if (typeField) {
                typeField.value = this.dataset.type || '';
                if (window.typeChoices) {
                    window.typeChoices.setChoiceByValue(this.dataset.type || '');
                }
            }
            
            document.getElementById('list_order-field').value = this.dataset.list_order || '';
            document.getElementById('list_name-field').value = this.dataset.list_name || '';
            const candidateIdField = document.getElementById('candidate_id');
            if (candidateIdField && this.dataset.id) {
                candidateIdField.value = this.dataset.id;
            }
            const departmentField = document.getElementById('department_id-field');
            const provinceField = document.getElementById('province_id-field');
            const municipalityField = document.getElementById('municipality_id-field');
            if (departmentField) {
                window.selectedProvinceId = this.dataset.province_id;
                window.selectedMunicipalityId = this.dataset.municipality_id;
                departmentField.value = this.dataset.department_id || '';
                if (window.departmentChoices) {
                    window.departmentChoices.setChoiceByValue(this.dataset.department_id || '');
                }
                if (this.dataset.department_id) {
                    setTimeout(() => {
                        const event = new Event('change', { bubbles: true });
                        departmentField.dispatchEvent(event);
                    }, 100);
                }
            }
            updateImagePreviews(this.dataset);
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
    if (modalTitle) modalTitle.textContent = 'Editar Candidato';
    if (form) form.action = updateUrl;
    if (methodField) methodField.value = 'PUT';
}

function updateChoicesInstances(data) {
    if (window.electionTypeCategoryChoices) {
        try {
            window.electionTypeCategoryChoices.destroy();
            window.electionTypeCategoryChoices = new Choices('#election_type_category_id-field');
            if (data && data.election_type_category_id) {
                window.electionTypeCategoryChoices.setChoiceByValue(data.election_type_category_id);
            }
        } catch (e) {
            console.warn('Error actualizando Choices para election_type_category_id-field');
        }
    }
    if (window.typeChoices) {
        try {
            window.typeChoices.destroy();
            window.typeChoices = new Choices('#type-field');
            if (data && data.type) {
                window.typeChoices.setChoiceByValue(data.type);
            }
        } catch (e) {
            console.warn('Error actualizando Choices para type-field');
        }
    }
}

function updateImagePreviews(data) {
    const photoPreview = document.getElementById('photo-preview');
    const partyLogoPreview = document.getElementById('party-logo-preview');
    
    if (photoPreview) {
        if (data.photoUrl && data.photoUrl !== 'null') {
            photoPreview.src = data.photoUrl;
            photoPreview.style.display = 'block';
        } else {
            photoPreview.style.display = 'none';
        }
    }
    
    if (partyLogoPreview) {
        if (data.partyLogoUrl && data.partyLogoUrl !== 'null') {
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
            const form = document.getElementById('candidateForm');
            if (form) form.reset();            
            const colorField = document.getElementById('color-field');
            if (colorField) colorField.value = '#1b8af8';            
            const colorHex = document.getElementById('color-hex');
            if (colorHex) colorHex.value = '#1b8af8';
            const photoPreview = document.getElementById('photo-preview');
            if (photoPreview) photoPreview.style.display = 'none';
            const partyLogoPreview = document.getElementById('party-logo-preview');
            if (partyLogoPreview) partyLogoPreview.style.display = 'none';
            const provinceSelect = document.getElementById('province_id-field');
            const municipalitySelect = document.getElementById('municipality_id-field');            
            if (provinceSelect) {
                provinceSelect.innerHTML = '<option value="">Primero seleccione departamento</option>';
                provinceSelect.disabled = true;
            }
            if (municipalitySelect) {
                municipalitySelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
                municipalitySelect.disabled = true;
            }
            resetChoicesInstances();
            const activeStatusRow = document.getElementById('active-status-row');
            if (activeStatusRow) {
                activeStatusRow.style.display = 'none';
            }
        });
    }
}

function resetModalForCreate() {
    const modalTitle = document.getElementById('modalTitleText');
    const form = document.getElementById('candidateForm');
    const methodField = document.getElementById('method_field');
    const candidateId = document.getElementById('candidate_id');
    if (modalTitle) modalTitle.textContent = 'Agregar Nuevo Candidato';
    if (form) form.action = "<?php echo e(route('candidates.store')); ?>";
    if (methodField) methodField.value = '';
    if (candidateId) candidateId.value = '';
}

function resetChoicesInstances() {
    if (window.electionTypeCategoryChoices) {
        try {
            window.electionTypeCategoryChoices.destroy();
            window.electionTypeCategoryChoices = new Choices('#election_type_category_id-field');
        } catch (e) {
            console.warn('Error reiniciando Choices para election_type_category_id-field');
        }
    }
    if (window.typeChoices) {
        try {
            window.typeChoices.destroy();
            window.typeChoices = new Choices('#type-field');
        } catch (e) {
            console.warn('Error reiniciando Choices para type-field');
        }
    }
}

function setupDeleteButton() {
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const deleteForm = document.getElementById('deleteForm');
            const deleteMessage = document.getElementById('deleteMessage');
            if (deleteForm) deleteForm.action = this.dataset.deleteUrl;
            if (deleteMessage) deleteMessage.textContent = `¿Está seguro de que desea eliminar el candidato "${this.dataset.name}"?`;
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
                checkAll.checked = Array.from(childCheckboxes).every(cb => cb.checked);
                checkAll.indeterminate = Array.from(childCheckboxes).some(cb => cb.checked) && !checkAll.checked;
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
            text: 'Por favor seleccione al menos un candidato para exportar.'
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
            text: 'Por favor seleccione al menos un candidato para eliminar.'
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
            form.action = '<?php echo e(route("candidates.multiple-delete")); ?>';
            form.innerHTML = '<?php echo csrf_field(); ?> <?php echo method_field("DELETE"); ?>';
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

</script><?php /**PATH D:\_Mine\corporate\resources\views/candidates/scripts/candidates-js.blade.php ENDPATH**/ ?>