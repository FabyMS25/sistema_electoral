{{-- resources/views/institutions/scripts/institution-js.blade.php --}}
<script>
    // Variables globales para Choices.js
    let choicesInstances = {};

    document.addEventListener('DOMContentLoaded', function() {
        initializeChoices();
        initializeLocationSelects();
        initializeListJs();
        initializeCheckboxes();
        initializeModals();
        initializeDeleteMultiple();
    });

    function initializeChoices() {
        if (typeof Choices === 'undefined') return;
        
        const selectors = [
            '#department-field', '#province-field', '#municipality-field', 
            '#locality-field', '#district-field', '#zone-field', '#status-field'
        ];
        
        selectors.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                try {
                    choicesInstances[selector] = new Choices(element, {
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: element.querySelector('option')?.textContent || 'Seleccionar',
                    });
                } catch (e) {
                    console.warn('Error initializing Choices for', selector, e);
                }
            }
        });
    }

    function initializeLocationSelects() {
        const selects = {
            department: '#department-field',
            province: '#province-field',
            municipality: '#municipality-field',
            locality: '#locality-field',
            district: '#district-field',
            zone: '#zone-field'
        };

        const deptSelect = document.querySelector(selects.department);
        if (deptSelect) {
            deptSelect.addEventListener('change', function() {
                const deptId = this.value;
                resetDependentSelects([
                    selects.province, selects.municipality, 
                    selects.locality, selects.district, selects.zone
                ]);
                if (deptId) {
                    const url = `${this.dataset.url}/${deptId}`;
                    loadOptions(url, selects.province, 'Seleccione Provincia');
                }
            });
        }

        // Province change
        const provSelect = document.querySelector(selects.province);
        if (provSelect) {
            provSelect.addEventListener('change', function() {
                const provinceId = this.value;
                resetDependentSelects([selects.municipality, selects.locality, selects.district, selects.zone]);
                if (provinceId) {
                    const url = `${this.dataset.url}/${provinceId}`;
                    loadOptions(url, selects.municipality, 'Seleccione Municipio');
                }
            });
        }

        // Municipality change
        const munSelect = document.querySelector(selects.municipality);
        if (munSelect) {
            munSelect.addEventListener('change', function() {
                const municipalityId = this.value;
                resetDependentSelects([selects.locality, selects.district, selects.zone]);
                if (municipalityId) {
                    const url = `${this.dataset.url}/${municipalityId}`;
                    loadOptions(url, selects.locality, 'Seleccione Localidad');
                }
            });
        }

        // Locality change
        const locSelect = document.querySelector(selects.locality);
        if (locSelect) {
            locSelect.addEventListener('change', function() {
                const localityId = this.value;
                resetDependentSelects([selects.district, selects.zone]);
                if (localityId) {
                    const url = `${this.dataset.url}/${localityId}`;
                    loadOptions(url, selects.district, 'Seleccione Distrito');
                }
            });
        }

        // District change
        const distSelect = document.querySelector(selects.district);
        if (distSelect) {
            distSelect.addEventListener('change', function() {
                const districtId = this.value;
                resetDependentSelects([selects.zone]);
                if (districtId) {
                    const url = `${this.dataset.url}/${districtId}`;
                    loadOptions(url, selects.zone, 'Seleccione Zona');
                }
            });
        }
    }

    function resetDependentSelects(selectors) {
        selectors.forEach(selector => {
            const select = document.querySelector(selector);
            if (!select) return;

            // Destruir Choices
            if (choicesInstances[selector]) {
                try {
                    choicesInstances[selector].destroy();
                } catch (e) {
                    console.warn('Error destroying Choices for', selector, e);
                }
                choicesInstances[selector] = null;
            }

            // Resetear opciones
            const placeholder = select.querySelector('option')?.textContent || 'Seleccionar';
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = true;
        });
    }

    async function loadOptions(url, selector, placeholder) {
        const target = document.querySelector(selector);
        if (!target) return;

        target.innerHTML = `<option value="">Cargando...</option>`;
        target.disabled = true;

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            if (data.error) throw new Error(data.error);

            target.innerHTML = `<option value="">${placeholder}</option>`;
            
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(item => {
                    target.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                });
            } else {
                target.innerHTML += `<option value="" disabled>No hay datos disponibles</option>`;
            }

            target.disabled = false;
            
            // Re-inicializar Choices
            if (typeof Choices !== 'undefined') {
                try {
                    choicesInstances[selector] = new Choices(target, {
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: placeholder,
                    });
                } catch (e) {
                    console.warn('Error re-initializing Choices for', selector, e);
                }
            }

        } catch (error) {
            console.error('Error loading data:', error);
            target.innerHTML = `<option value="">${placeholder}</option>`;
            target.disabled = false;
            
            showToast('error', 'Error al cargar datos');
        }
    }

    function initializeListJs() {
        if (document.querySelectorAll('#customerTable tbody tr').length === 0) return;
        
        try {
            if (typeof List !== 'undefined' && document.getElementById('institutionList')) {
                window.institutionList = new List('institutionList', {
                    valueNames: [
                        'institution_code',
                        'institution_name',
                        { name: 'location', attr: 'data-location' },
                        { name: 'citizens', attr: 'data-citizens' },
                        { name: 'mesas', attr: 'data-mesas' },
                        { name: 'status', attr: 'data-status' }
                    ],
                    page: 10,
                    pagination: true
                });
            }
        } catch (e) {
            console.warn('List.js could not initialize:', e);
        }
    }

    function initializeCheckboxes() {
        const checkAll = document.getElementById('checkAll');
        const childCheckboxes = document.querySelectorAll('.child-checkbox');
        const deleteMultipleBtn = document.getElementById('delete-multiple-btn');

        if (!checkAll || !childCheckboxes.length) return;

        checkAll.addEventListener('change', function() {
            childCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleDeleteButton();
        });

        childCheckboxes.forEach(cb => {
            cb.addEventListener('change', toggleDeleteButton);
        });

        function toggleDeleteButton() {
            const checkedCount = document.querySelectorAll('.child-checkbox:checked').length;
            if (deleteMultipleBtn) {
                deleteMultipleBtn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
            }
        }
    }

    function initializeModals() {
        // Delete modal
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const deleteUrl = this.dataset.deleteUrl;
                const name = this.dataset.name;
                document.getElementById('deleteForm').action = deleteUrl;
                document.getElementById('delete-institution-name').textContent = name;
            });
        });

        // Form validation (si existe el formulario)
        const institutionForm = document.getElementById('institutionForm');
        if (institutionForm) {
            institutionForm.addEventListener('submit', validateForm);
        }

        // Auto-cerrar alertas
        setTimeout(function() {
            document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    }

    function initializeDeleteMultiple() {
        window.deleteMultiple = function() {
            const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);

            if (ids.length === 0) {
                showToast('info', 'Seleccione al menos un registro');
                return;
            }

            Swal.fire({
                title: '¿Está seguro?',
                text: `¿Desea eliminar ${ids.length} recinto(s) seleccionado(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route("institutions.deleteMultiple") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message); });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value?.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        window.location.reload();
                    });
                } else if (result.value && !result.value.success) {
                    showToast('error', result.value.message || 'Error al eliminar');
                }
            });
        };
    }

    function validateForm(event) {
        let isValid = true;
        const requiredFields = ['name-field', 'department-field', 'province-field', 'municipality-field', 'locality-field'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value) {
                field.classList.add('is-invalid');
                if (field.closest('.choices')) {
                    field.closest('.choices').classList.add('is-invalid');
                }
                isValid = false;
            } else if (field) {
                field.classList.remove('is-invalid');
                if (field.closest('.choices')) {
                    field.closest('.choices').classList.remove('is-invalid');
                }
            }
        });

        if (!isValid) {
            event.preventDefault();
            showToast('warning', 'Complete todos los campos obligatorios');
        }
    }

    function showToast(type, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: type === 'success' ? 'Éxito' : type === 'warning' ? 'Advertencia' : 'Error',
                text: message,
                icon: type,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }

    // Función para resetear formulario (útil para create/edit)
    window.resetInstitutionForm = function() {
        const form = document.getElementById('institutionForm');
        if (form) {
            form.reset();
            
            // Resetear selects dependientes
            resetDependentSelects([
                '#province-field', '#municipality-field', 
                '#locality-field', '#district-field', '#zone-field'
            ]);
            
            // Limpiar clases de error
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.choices.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        }
    };

    // Exponer funciones necesarias globalmente
    window.showToast = showToast;
</script>