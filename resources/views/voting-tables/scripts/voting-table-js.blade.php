{{-- resources/views/voting-tables/scripts/voting-table-js.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            text: 'Por favor seleccione al menos una mesa para exportar.',
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
        text: `¿Deseas eliminar ${selectedIds.length} mesa(s) seleccionada(s)? Esta acción no se puede deshacer.`,
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
            fetch('{{ route("voting-tables.deleteMultiple") }}', {
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
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar las mesas.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}
</script>
