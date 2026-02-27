{{-- resources/views/voting-table-votes/partials/table-row.blade.php --}}
<div class="card mb-3 table-card {{ $table->status }}" id="table-{{ $table->id }}">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa {{ $table->number }} - {{ $table->institution->name }}
                    <small class="text-muted ms-2">{{ $table->code }}</small>
                </h5>
            </div>
            <div class="col-md-4">
                <span class="badge bg-{{ $table->status === 'cerrado' ? 'danger' : ($table->status === 'en_proceso' ? 'success' : 'warning') }} total-badge">
                    {{ $table->getStatusBadgeAttribute()|raw }}
                </span>
                <span class="ms-2 text-muted">
                    <i class="ri-group-line me-1"></i>
                    Habilitados: {{ number_format($table->registered_citizens ?? 0) }}
                </span>
            </div>
            <div class="col-md-4 text-end">
                <span class="text-muted me-3">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: {{ $table->votes->sum('quantity') }}
                </span>
                @if($table->status !== 'cerrado')
                <button class="btn btn-sm btn-success save-table" data-table-id="{{ $table->id }}">
                    <i class="ri-save-line me-1"></i>Guardar
                </button>
                <button class="btn btn-sm btn-warning close-table" data-table-id="{{ $table->id }}">
                    <i class="ri-lock-line me-1"></i>Cerrar
                </button>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">Candidato</th>
                        <th style="width: 40%">Partido</th>
                        <th style="width: 20%">Votos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidates as $candidate)
                        @php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: {{ $candidate->color ?? '#0ab39c' }}"></span>
                                    <span class="ms-2">{{ $candidate->name }}</span>
                                </div>
                            </td>
                            <td>{{ $candidate->party }}</td>
                            <td>
                                <input type="number" 
                                       class="form-control vote-input candidate-vote" 
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       value="{{ $quantity }}"
                                       min="0"
                                       {{ $table->status === 'cerrado' ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total Votos:</th>
                        <th>
                            <span id="total-{{ $table->id }}" class="total-votes">
                                {{ $table->votes->sum('quantity') }}
                            </span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>