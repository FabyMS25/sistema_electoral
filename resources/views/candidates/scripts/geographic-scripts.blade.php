<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    const provinceSelect = document.getElementById('province_id');
    const municipalitySelect = document.getElementById('municipality_id');
    
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;
            
            // Resetear selects dependientes
            provinceSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
            municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
            provinceSelect.disabled = true;
            municipalitySelect.disabled = true;
            
            if (!departmentId) return;
            
            // Cargar provincias
            fetch(`/candidates/provinces/${departmentId}`)
                .then(response => response.json())
                .then(provinces => {
                    provinceSelect.disabled = false;
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading provinces:', error));
        });
    }
    
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const provinceId = this.value;
            
            // Resetear municipios
            municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
            municipalitySelect.disabled = true;
            
            if (!provinceId) return;
            
            // Cargar municipios
            fetch(`/candidates/municipalities/${provinceId}`)
                .then(response => response.json())
                .then(municipalities => {
                    municipalitySelect.disabled = false;
                    municipalities.forEach(municipality => {
                        const option = document.createElement('option');
                        option.value = municipality.id;
                        option.textContent = municipality.name;
                        municipalitySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading municipalities:', error));
        });
    }
});
</script>