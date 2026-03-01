{{-- resources/views/voting-tables/assign-delegates.blade.php --}}
@extends('layouts.master')

@section('title', 'Asignar Delegados a Mesa')

@section('css')
    <style>
        .user-select-card {
            transition: all 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .user-select-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #0ab39c;
        }
        .user-select-card.selected {
            border-color: #0ab39c;
            background-color: #f0f9f8;
        }
        .role-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        .current-assignment {
            background-color: #e8f5e9;
            border-left: 4px solid #0ab39c;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('voting-tables.index') }}">Mesas</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('voting-tables.show', $votingTable->id) }}">
                Mesa {{ $votingTable->oep_code ?? $votingTable->internal_code }}
            </a>
        @endslot
        @slot('title')
            Asignar Delegados
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-1"></i>
                        Asignar Delegados - Mesa N° {{ $votingTable->number }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        <strong>Información de la mesa:</strong> 
                        {{ $votingTable->institution->name }} | 
                        Código OEP: {{ $votingTable->oep_code ?? 'N/A' }} | 
                        Código Interno: {{ $votingTable->internal_code ?? 'N/A' }}
                    </div>

                    <!-- Asignaciones Actuales -->
                    <div class="current-assignment">
                        <h6 class="mb-3">
                            <i class="ri-user-settings-line me-1"></i>
                            Delegados Actualmente Asignados
                        </h6>
                        <div class="row">
                            @php
                                $currentDelegates = [
                                    'president_id' => ['label' => 'Presidente', 'color' => 'primary'],
                                    'secretary_id' => ['label' => 'Secretario', 'color' => 'success'],
                                    'vocal1_id' => ['label' => 'Vocal 1', 'color' => 'info'],
                                    'vocal2_id' => ['label' => 'Vocal 2', 'color' => 'warning'],
                                    'vocal3_id' => ['label' => 'Vocal 3', 'color' => 'secondary'],
                                ];
                            @endphp
                            @foreach($currentDelegates as $field => $info)
                                @php
                                    $userId = $votingTable->$field;
                                    $user = $userId ? \App\Models\User::find($userId) : null;
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-{{ $info['color'] }} role-badge me-1">{{ $info['label'] }}:</span>
                                    @if($user)
                                        <strong>{{ $user->name }} {{ $user->last_name }}</strong>
                                        <small class="text-muted d-block">{{ $user->email }}</small>
                                    @else
                                        <span class="text-muted">No asignado</span>
                                    @endif
                                </div>
                            @endforeach
                            @if($votingTable->vocal4_name)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-dark role-badge me-1">Vocal 4 (Externo):</span>
                                    <strong>{{ $votingTable->vocal4_name }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('voting-tables.assign-delegates.store', $votingTable->id) }}" method="POST">
                        @csrf
                        
                        <h5 class="mb-3">Seleccionar Nuevos Delegados</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Presidente de Mesa</label>
                                <select class="form-select @error('president_id') is-invalid @enderror" name="president_id">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('president_id', $votingTable->president_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('president_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Secretario</label>
                                <select class="form-select @error('secretary_id') is-invalid @enderror" name="secretary_id">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('secretary_id', $votingTable->secretary_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('secretary_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Vocal 1</label>
                                <select class="form-select @error('vocal1_id') is-invalid @enderror" name="vocal1_id">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('vocal1_id', $votingTable->vocal1_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vocal1_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Vocal 2</label>
                                <select class="form-select @error('vocal2_id') is-invalid @enderror" name="vocal2_id">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('vocal2_id', $votingTable->vocal2_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vocal2_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Vocal 3</label>
                                <select class="form-select @error('vocal3_id') is-invalid @enderror" name="vocal3_id">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('vocal3_id', $votingTable->vocal3_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vocal3_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Vocal 4 (Externo)</label>
                                <input type="text" class="form-control @error('vocal4_name') is-invalid @enderror" 
                                       name="vocal4_name" value="{{ old('vocal4_name', $votingTable->vocal4_name) }}"
                                       placeholder="Nombre completo del vocal externo">
                                @error('vocal4_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Para personas no registradas en el sistema</small>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('voting-tables.show', $votingTable->id) }}" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Guardar Asignaciones
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection