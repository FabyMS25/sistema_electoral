<?php
    use App\Models\Candidate;
    use App\Models\ElectionTypeCategory;
    
    $totalCandidates = $candidates->total();
    
    // Count by type
    $candidateCount = Candidate::where('type', 'candidato')->where('active', true)->count();
    $blankVotesCount = Candidate::where('type', 'blank_votes')->where('active', true)->count();
    $nullVotesCount = Candidate::where('type', 'null_votes')->where('active', true)->count();
    
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
?>

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
                        <h4 class="mb-0"><?php echo e($totalCandidates); ?></h4>
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
                                <i class="ri-user-2-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Candidatos</p>
                        <h4 class="mb-0"><?php echo e($candidateCount); ?></h4>
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
                                <i class="ri-file-blank-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Votos en Blanco</p>
                        <h4 class="mb-0"><?php echo e($blankVotesCount); ?></h4>
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
                            <span class="avatar-title bg-danger-subtle text-danger rounded fs-3">
                                <i class="ri-close-circle-line"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Votos Nulos</p>
                        <h4 class="mb-0"><?php echo e($nullVotesCount); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($byElectionTypeCategory->isNotEmpty()): ?>
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Candidatos por Tipo y Categoría</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $byElectionTypeCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info me-2"><?php echo e($item->total); ?></span>
                                <div>
                                    <strong><?php echo e($item->electionTypeCategory?->electionType?->name ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo e($item->electionTypeCategory?->electionCategory?->name ?? 'Sin categoría'); ?>

                                        (<?php echo e($item->electionTypeCategory?->electionCategory?->code ?? ''); ?>)
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH D:\_Mine\corporate\resources\views/candidates/partials/stats-cards.blade.php ENDPATH**/ ?>