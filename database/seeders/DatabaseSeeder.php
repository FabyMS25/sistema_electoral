<?php
namespace Database\Seeders;

use App\Models\Dashboard;
use App\Models\ElectionCategory;
use App\Models\ElectionType;
use App\Models\ElectionTypeCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ProvincesMunicipalitiesSeeder::class,
            LocalitiesSeeder::class,
            PermissionRoleSeeder::class,
            AdminUserSeeder::class,
            ElectionTypeSeeder::class,
            Candidates2026Seeder::class,
            Concejales2026Seeder::class,
            QuillacolloInstitutionsSeeder::class,
            QuillacolloTablesSeeder::class,
        ]);

        $defaultCat = ElectionCategory::where('code', 'ALC')->where('active', true)->first();
        $defaultElectionType = null;
        if ($defaultCat) {
            $link = ElectionTypeCategory::where('election_category_id', $defaultCat->id)
                ->with('electionType')
                ->first();
            $defaultElectionType = $link?->electionType;
        }
        $defaultElectionType ??= ElectionType::where('active', true)->first();
        Dashboard::updateOrCreate(
            ['id' => 1],
            [
                'title'                    => 'Resultados Electorales 2026',
                'is_public'                => false,
                'default_election_type_id' => $defaultElectionType?->id,
                'default_category_id'      => $defaultCat?->id,
                'show_election_switcher'   => true,
                'show_category_filter'     => true,
                'auto_refresh_seconds'     => 60,
            ]
        );
    }
}
