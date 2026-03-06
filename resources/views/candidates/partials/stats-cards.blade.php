@php
    use App\Models\Candidate;
    use App\Models\ElectionTypeCategory;

    $totalCandidates = $candidates->total();

    // Get counts by election type category (pivot)
    try {
        $byElectionTypeCategory = Candidate::where('active', true)
            ->select('election_type_category_id', \DB::raw('count(*) as total'))
            ->whereNotNull('election_type_category_id')
            ->groupBy('election_type_category_id')
            ->with('electionTypeCategory.electionType', 'electionTypeCategory.electionCategory')
            ->get();
    } catch (\Exception $e) {
        $byElectionTypeCategory = collect();
        \Log::warning('Could not load candidates by election type category: ' . $e->getMessage());
    }

    // Get counts by department
    try {
        $byDepartment = Candidate::where('active', true)
            ->select('department_id', \DB::raw('count(*) as total'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->with('department')
            ->get();
    } catch (\Exception $e) {
        $byDepartment = collect();
        \Log::warning('Could not load candidates by department: ' . $e->getMessage());
    }

    // Get counts by election type
    try {
        $byElectionType = Candidate::where('active', true)
            ->select('election_type_category_id', \DB::raw('count(*) as total'))
            ->whereNotNull('election_type_category_id')
            ->groupBy('election_type_category_id')
            ->with('electionTypeCategory.electionType')
            ->get()
            ->groupBy(function($item) {
                return $item->electionTypeCategory?->electionType?->name ?? 'Sin tipo';
            })
            ->map(function($group) {
                return $group->sum('total');
            });
    } catch (\Exception $e) {
        $byElectionType = collect();
        \Log::warning('Could not load candidates by election type: ' . $e->getMessage());
    }

    // Get counts by geographic scope
    try {
        $nationalCandidates = Candidate::where('active', true)
            ->whereNull('department_id')
            ->whereNull('province_id')
            ->whereNull('municipality_id')
            ->count();

        $departmentalCandidates = Candidate::where('active', true)
            ->whereNotNull('department_id')
            ->whereNull('province_id')
            ->whereNull('municipality_id')
            ->count();

        $provincialCandidates = Candidate::where('active', true)
            ->whereNotNull('province_id')
            ->whereNull('municipality_id')
            ->count();

        $municipalCandidates = Candidate::where('active', true)
            ->whereNotNull('municipality_id')
            ->count();
    } catch (\Exception $e) {
        $nationalCandidates = $departmentalCandidates = $provincialCandidates = $municipalCandidates = 0;
        \Log::warning('Could not load candidates by geographic scope: ' . $e->getMessage());
    }
@endphp

<div class="row g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                                <i class="ri-user-star-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Candidatos</p>
                        <h4 class="mb-0">{{ $totalCandidates }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                                <i class="ri-stack-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Categorías</p>
                        <h4 class="mb-0">{{ $byElectionTypeCategory->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info-subtle text-info rounded fs-3">
                                <i class="ri-map-pin-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Departamentos</p>
                        <h4 class="mb-0">{{ $byDepartment->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                                <i class="ri-government-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Tipos de Elección</p>
                        <h4 class="mb-0">{{ $byElectionType->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($byElectionTypeCategory->isNotEmpty())
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Candidatos por Tipo de Elección y Categoría</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo de Elección</th>
                                <th>Categoría</th>
                                <th>Código</th>
                                <th>Franja</th>
                                <th>Votos por Persona</th>
                                <th class="text-center">Candidatos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byElectionTypeCategory->sortByDesc('total') as $item)
                                <tr>
                                    <td>{{ $item->electionTypeCategory?->electionType?->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $item->electionTypeCategory?->electionCategory?->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td><code>{{ $item->electionTypeCategory?->electionCategory?->code ?? 'N/A' }}</code></td>
                                    <td class="text-center">{{ $item->electionTypeCategory?->ballot_order ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $item->electionTypeCategory?->votes_per_person ?? 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $item->total }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($byDepartment->isNotEmpty())
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Candidatos por Departamento</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Departamento</th>
                                <th class="text-center">Candidatos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byDepartment->sortByDesc('total') as $item)
                                <tr>
                                    <td>{{ $item->department->name ?? 'Sin departamento' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $item->total }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Candidatos por Ámbito Geográfico</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ámbito</th>
                                <th class="text-center">Candidatos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nacional</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $nationalCandidates }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Departamental</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $departmentalCandidates }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Provincial</td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $provincialCandidates }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Municipal</td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $municipalCandidates }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
