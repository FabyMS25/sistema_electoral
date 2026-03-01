<div class="modal fade" id="candidateModal" tabindex="-1" aria-labelledby="candidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="candidateModalLabel">
                    <i class="ri-user-star-line me-1"></i>
                    <span id="modalTitleText">Agregar Nuevo Candidato</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            
            <form id="candidateForm" method="POST" class="tablelist-form" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="method_field" name="_method" value="">
                <input type="hidden" id="candidate_id" name="id">
                
                <div class="modal-body">
                    <!-- Información Básica -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name-field" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name-field" name="name" class="form-control" 
                                    placeholder="Ej: Juan Pérez González" value="{{ old('name') }}" required />
                                <div class="invalid-feedback" id="name-error">El nombre es obligatorio y debe tener máximo 255 caracteres.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type-field" class="form-label">
                                    Tipo de Registro <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="type" id="type-field" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="candidato" {{ old('type') == 'candidato' ? 'selected' : '' }}>Candidato</option>
                                    <option value="blank_votes" {{ old('type') == 'blank_votes' ? 'selected' : '' }}>Votos en Blanco</option>
                                    <option value="null_votes" {{ old('type') == 'null_votes' ? 'selected' : '' }}>Votos Nulos</option>
                                </select>
                                <div class="invalid-feedback" id="type-error">Seleccione un tipo de registro válido.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Partido -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party-field" class="form-label">
                                    Sigla del Partido <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="party-field" name="party" class="form-control" 
                                    placeholder="Ej: UNE, FRI .." value="{{ old('party') }}" required 
                                    maxlength="50" />
                                <div class="invalid-feedback" id="party-error">La sigla del partido es obligatoria.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party_full_name-field" class="form-label">
                                    Nombre Completo del Partido
                                </label>
                                <input type="text" id="party_full_name-field" name="party_full_name" class="form-control" 
                                    placeholder="" value="{{ old('party_full_name') }}"
                                    maxlength="255" />
                                <div class="invalid-feedback" id="party-full-name-error">El nombre no puede exceder los 255 caracteres.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Lista -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="list_name-field" class="form-label">
                                    Nombre de la Lista
                                </label>
                                <input type="text" id="list_name-field" name="list_name" class="form-control" 
                                    placeholder="Ej: Lista 1, Alcandes, Concejales" value="{{ old('list_name') }}"
                                    maxlength="255" />
                                <div class="invalid-feedback" id="list-name-error">El nombre no puede exceder los 255 caracteres.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="list_order-field" class="form-label">
                                    Orden en la Lista
                                </label>
                                <input type="number" id="list_order-field" name="list_order" class="form-control" 
                                    placeholder="Ej: 1, 2, 3" value="{{ old('list_order') }}" 
                                    min="1" step="1" />
                                <div class="invalid-feedback" id="list-order-error">El orden debe ser un número positivo.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Color y Categoría -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color-field" class="form-label">
                                    Color Representativo <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color w-25 me-2" 
                                        id="color-field" name="color" value="{{ old('color', '#1b8af8') }}" 
                                        style="height: 38px; padding: 2px;" required />
                                    <input type="text" class="form-control" id="color-hex" 
                                        value="{{ old('color', '#1b8af8') }}" placeholder="#RRGGBB" 
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" />
                                </div>
                                <div class="invalid-feedback" id="color-error">Seleccione un color válido en formato hexadecimal (#RRGGBB).</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="election_type_category_id-field" class="form-label">
                                    Elección / Categoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="election_type_category_id" id="election_type_category_id-field" required>
                                    <option value="">Seleccione una combinación</option>
                                    @foreach($electionTypeCategories as $etc)
                                        <option value="{{ $etc->id }}" 
                                            {{ old('election_type_category_id', $candidate->election_type_category_id ?? '') == $etc->id ? 'selected' : '' }}>
                                            {{ $etc->electionType->name }} - {{ $etc->electionCategory->name }} 
                                            ({{ $etc->electionCategory->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="election-type-category-error">Seleccione una combinación de elección y categoría.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación Geográfica -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="department_id-field" class="form-label">Departamento</label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        name="department_id" id="department_id-field">
                                    <option value="">Seleccione un departamento</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                            {{ old('department_id', $candidate->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="province_id-field" class="form-label">Provincia</label>
                                <select class="form-select @error('province_id') is-invalid @enderror" 
                                        name="province_id" id="province_id-field" disabled>
                                    <option value="">Primero seleccione un departamento</option>
                                    @if(isset($provinces) && count($provinces) > 0)
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" 
                                                {{ old('province_id', $candidate->province_id ?? '') == $province->id ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="municipality_id-field" class="form-label">Municipio</label>
                                <select class="form-select @error('municipality_id') is-invalid @enderror" 
                                        name="municipality_id" id="municipality_id-field" disabled>
                                    <option value="">Primero seleccione una provincia</option>
                                    @if(isset($municipalities) && count($municipalities) > 0)
                                        @foreach($municipalities as $municipality)
                                            <option value="{{ $municipality->id }}" 
                                                {{ old('municipality_id', $candidate->municipality_id ?? '') == $municipality->id ? 'selected' : '' }}>
                                                {{ $municipality->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('municipality_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Imágenes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo-field" class="form-label">Foto del Candidato</label>
                                <input type="file" id="photo-field" name="photo" class="form-control" 
                                    accept="image/jpeg,image/png,image/jpg,image/gif" />
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máximo 2MB</small>
                                <div class="invalid-feedback" id="photo-error"></div>
                                <div class="image-preview-container mt-2" id="photo-preview-container">
                                    <img id="photo-preview" class="image-preview img-thumbnail" src="" style="display: none; max-height: 100px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party_logo-field" class="form-label">Logo del Partido</label>
                                <input type="file" id="party_logo-field" name="party_logo" class="form-control" 
                                    accept="image/jpeg,image/png,image/jpg,image/gif" />
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máximo 2MB</small>
                                <div class="invalid-feedback" id="party-logo-error"></div>
                                <div class="image-preview-container mt-2" id="party-logo-preview-container">
                                    <img id="party-logo-preview" class="image-preview img-thumbnail" src="" style="display: none; max-height: 100px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="save-btn">
                            <i class="ri-save-line me-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@section('script')
    @parent
    @include('candidates.scripts.geographic-scripts')
@endsection