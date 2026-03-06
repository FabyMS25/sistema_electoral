<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
class PermissionRoleSeeder extends Seeder
{
    protected $permissionStructure = [
        'Usuarios' => [
            'global' => [
                ['name' => 'view_users',     'display_name' => 'Ver Usuarios'],
                ['name' => 'create_users',   'display_name' => 'Crear Usuarios'],
                ['name' => 'edit_users',     'display_name' => 'Editar Usuarios'],
                ['name' => 'delete_users',   'display_name' => 'Eliminar/Desactivar Usuarios'],
                ['name' => 'activate_users', 'display_name' => 'Activar Usuarios'],
            ]
        ],
        'Roles y Permisos' => [
            'global' => [
                ['name' => 'view_roles',         'display_name' => 'Ver Roles'],
                ['name' => 'create_roles',       'display_name' => 'Crear Roles'],
                ['name' => 'edit_roles',         'display_name' => 'Editar Roles'],
                ['name' => 'delete_roles',       'display_name' => 'Eliminar Roles'],
                ['name' => 'assign_roles',       'display_name' => 'Asignar Roles a Usuarios'],
                ['name' => 'view_permissions',   'display_name' => 'Ver Permisos'],
                ['name' => 'assign_permissions', 'display_name' => 'Asignar Permisos Directos'],
            ]
        ],
        'Recintos (Instituciones)' => [
            'global' => [
                ['name' => 'view_recintos',  'display_name' => 'Ver Recintos'],
                ['name' => 'create_recinto', 'display_name' => 'Crear Recinto'],
                ['name' => 'edit_recinto',   'display_name' => 'Editar Recinto'],
                ['name' => 'delete_recinto', 'display_name' => 'Eliminar Recinto'],
            ],
            'recinto' => [
                ['name' => 'view_recintos', 'display_name' => 'Ver Recintos'],
                ['name' => 'edit_recinto',  'display_name' => 'Editar Recinto'],
            ]
        ],
        'Mesas de Votación' => [
            'global' => [
                ['name' => 'view_mesas',    'display_name' => 'Ver Mesas'],
                ['name' => 'create_mesa',   'display_name' => 'Crear Mesa'],
                ['name' => 'edit_mesa',     'display_name' => 'Editar Mesa'],
                ['name' => 'delete_mesa',   'display_name' => 'Eliminar Mesa'],
                ['name' => 'configure_mesa','display_name' => 'Configurar Mesa'],
            ],
            'recinto' => [
                ['name' => 'view_mesas',    'display_name' => 'Ver Mesas'],
                ['name' => 'create_mesa',   'display_name' => 'Crear Mesa'],
                ['name' => 'edit_mesa',     'display_name' => 'Editar Mesa'],
                ['name' => 'configure_mesa','display_name' => 'Configurar Mesa'],
            ],
            'mesa' => [
                ['name' => 'view_mesas', 'display_name' => 'Ver Mesas'],
                ['name' => 'edit_mesa',  'display_name' => 'Editar Mesa'],
            ]
        ],
        'Votos' => [
            'global' => [
                ['name' => 'view_votes',    'display_name' => 'Ver Votos'],
                ['name' => 'register_votes','display_name' => 'Registrar Votos'],
                ['name' => 'observe_votes', 'display_name' => 'Observar Votos'],
                ['name' => 'correct_votes', 'display_name' => 'Corregir Votos'],
                ['name' => 'validate_votes','display_name' => 'Validar Votos'],
                ['name' => 'export_votes',  'display_name' => 'Exportar Votos'],
            ],
            'recinto' => [
                ['name' => 'view_votes',    'display_name' => 'Ver Votos'],
                ['name' => 'register_votes','display_name' => 'Registrar Votos'],
                ['name' => 'observe_votes', 'display_name' => 'Observar Votos'],
                ['name' => 'correct_votes', 'display_name' => 'Corregir Votos'],
                ['name' => 'validate_votes','display_name' => 'Validar Votos'],
            ],
            'mesa' => [
                ['name' => 'view_votes',    'display_name' => 'Ver Votos'],
                ['name' => 'register_votes','display_name' => 'Registrar Votos'],
                ['name' => 'observe_votes', 'display_name' => 'Observar Votos'],
            ]
        ],
        'Actas' => [
            'global' => [
                ['name' => 'view_actas',   'display_name' => 'Ver Actas'],
                ['name' => 'upload_acta',  'display_name' => 'Subir Acta'],
                ['name' => 'verify_actas', 'display_name' => 'Verificar Actas'],
                ['name' => 'approve_actas','display_name' => 'Aprobar Actas'],
            ],
            'recinto' => [
                ['name' => 'view_actas',   'display_name' => 'Ver Actas'],
                ['name' => 'upload_acta',  'display_name' => 'Subir Acta'],
                ['name' => 'verify_actas', 'display_name' => 'Verificar Actas'],
            ],
            'mesa' => [
                ['name' => 'view_actas',  'display_name' => 'Ver Actas'],
                ['name' => 'upload_acta', 'display_name' => 'Subir Acta'],
            ]
        ],
        'Observaciones' => [
            'global' => [
                ['name' => 'view_observations',   'display_name' => 'Ver Observaciones'],
                ['name' => 'create_observation',  'display_name' => 'Crear Observación'],
                ['name' => 'resolve_observation', 'display_name' => 'Resolver Observación'],
            ],
            'recinto' => [
                ['name' => 'view_observations',   'display_name' => 'Ver Observaciones'],
                ['name' => 'create_observation',  'display_name' => 'Crear Observación'],
                ['name' => 'resolve_observation', 'display_name' => 'Resolver Observación'],
            ],
            'mesa' => [
                ['name' => 'view_observations',  'display_name' => 'Ver Observaciones'],
                ['name' => 'create_observation', 'display_name' => 'Crear Observación'],
            ]
        ],
        'Delegaciones (Asignaciones)' => [
            'global' => [
                ['name' => 'view_assignments',   'display_name' => 'Ver Asignaciones'],
                ['name' => 'assign_delegates',   'display_name' => 'Asignar Delegados'],
                ['name' => 'manage_assignments', 'display_name' => 'Gestionar Asignaciones'],
            ],
            'recinto' => [
                ['name' => 'view_assignments',   'display_name' => 'Ver Asignaciones'],
                ['name' => 'manage_assignments', 'display_name' => 'Gestionar Asignaciones'],
                ['name' => 'assign_delegates',   'display_name' => 'Asignar Delegados'],
            ]
        ],
        'Auditoría' => [
            'global' => [
                ['name' => 'view_audit_logs',         'display_name' => 'Ver Logs de Auditoría'],
                ['name' => 'view_validation_history', 'display_name' => 'Ver Historial de Validaciones'],
            ]
        ],
        'Configuración' => [
            'global' => [
                ['name' => 'view_settings',         'display_name' => 'Ver Configuración'],
                ['name' => 'manage_settings',       'display_name' => 'Gestionar Configuración'],
                ['name' => 'manage_election_types', 'display_name' => 'Gestionar Tipos de Elección'],
                ['name' => 'manage_categories',     'display_name' => 'Gestionar Categorías'],
            ]
        ],
        'Dashboard' => [
            'global'  => [['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard']],
            'recinto' => [['name' => 'view_dashboard', 'display_name' => 'Ver Dashboard']],
        ],
        'Mesas (Acciones Operativas)' => [
            'global' => [
                ['name' => 'close_table',  'display_name' => 'Cerrar Mesa'],
                ['name' => 'reopen_table', 'display_name' => 'Reabrir Mesa'],
            ],
            'recinto' => [
                ['name' => 'close_table',  'display_name' => 'Cerrar Mesa'],
                ['name' => 'reopen_table', 'display_name' => 'Reabrir Mesa'],
            ],
            'mesa' => [
                ['name' => 'close_table', 'display_name' => 'Cerrar Mesa'],
            ]
        ],
    ];

    protected $roles = [
        'administrador' => [
            'display_name'  => 'Administrador del Sistema',
            'description'   => 'Control total del sistema sin restricciones',
            'default_scope' => 'global',
            'permissions'   => 'ALL',
        ],
        'delegado_recinto' => [
            'display_name'  => 'Delegado de Recinto',
            'description'   => 'Encargado de un recinto electoral - puede gestionar todas las mesas del recinto',
            'default_scope' => 'recinto',
            'permissions'   => [
                'view_recintos', 'edit_recinto',
                'view_mesas', 'create_mesa', 'edit_mesa', 'configure_mesa',
                'view_votes', 'register_votes', 'observe_votes', 'correct_votes', 'validate_votes',
                'view_actas', 'upload_acta', 'verify_actas',
                'view_observations', 'create_observation', 'resolve_observation',
                'view_assignments', 'assign_delegates', 'manage_assignments',
                'close_table', 'reopen_table',
                'view_dashboard',
            ]
        ],
        'delegado_mesa' => [
            'display_name'  => 'Delegado de Mesa',
            'description'   => 'Encargado de una mesa de votación específica',
            'default_scope' => 'mesa',
            'permissions'   => [
                'view_mesas', 'edit_mesa',
                'view_votes', 'register_votes', 'observe_votes',
                'view_actas', 'upload_acta',
                'view_observations', 'create_observation',
                'close_table',
            ]
        ],
        'revisor' => [
            'display_name'  => 'Revisor',
            'description'   => 'Revisa y valida votos y actas',
            'default_scope' => 'recinto',
            'permissions'   => [
                'view_recintos', 'view_mesas',
                'view_votes', 'validate_votes', 'observe_votes',
                'view_actas', 'verify_actas', 'approve_actas',
                'view_observations', 'resolve_observation',
            ]
        ],
        'modificador' => [
            'display_name'  => 'Modificador',
            'description'   => 'Corrige votos observados',
            'default_scope' => 'recinto',
            'permissions'   => [
                'view_recintos', 'view_mesas',
                'view_votes', 'correct_votes',
                'view_observations',
            ]
        ],
        'observador' => [
            'display_name'  => 'Observador',
            'description'   => 'Solo puede ver resultados, no modificar',
            'default_scope' => 'global',
            'permissions'   => [
                'view_recintos', 'view_mesas', 'view_votes',
                'view_actas', 'view_observations', 'view_dashboard',
            ]
        ],
        'tecnico' => [
            'display_name'  => 'Técnico/Soporte',
            'description'   => 'Soporte técnico del sistema',
            'default_scope' => 'recinto',
            'permissions'   => [
                'view_recintos', 'view_mesas', 'view_votes',
                'view_assignments', 'reopen_table', 'view_dashboard',
            ]
        ],
        'registrador' => [
            'display_name'  => 'Registrador',
            'description'   => 'Solo puede registrar votos, no validar',
            'default_scope' => 'mesa',
            'permissions'   => [
                'view_mesas',
                'view_votes', 'register_votes', 'observe_votes',
                'view_actas', 'upload_acta',
            ]
        ],
        'validador' => [
            'display_name'  => 'Validador',
            'description'   => 'Valida votos y actas',
            'default_scope' => 'recinto',
            'permissions'   => [
                'view_recintos', 'view_mesas',
                'view_votes', 'validate_votes',
                'view_actas', 'verify_actas', 'approve_actas',
                'view_observations', 'resolve_observation',
            ]
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->createPermissions();
            $this->createRoles();
        });
    }

    private function createPermissions(): void
    {
        foreach ($this->permissionStructure as $group => $scopes) {
            foreach ($scopes as $scope => $permissions) {
                foreach ($permissions as $permData) {
                    Permission::updateOrCreate(
                        ['name' => $permData['name']],
                        [
                            'display_name' => $permData['display_name'],
                            'group'        => $group,
                            'scope'        => $scope,
                            'description'  => $this->generateDescription($permData['name'], $group, $scope),
                        ]
                    );
                }
            }
        }
    }

    private function createRoles(): void
    {
        foreach ($this->roles as $name => $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name'  => $roleData['display_name'],
                    'description'   => $roleData['description'],
                    'default_scope' => $roleData['default_scope'],
                ]
            );

            if ($roleData['permissions'] === 'ALL') {
                $role->permissions()->sync(Permission::all()->pluck('id'));
            } else {
                $permissionIds    = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
                $foundNames       = Permission::whereIn('name', $roleData['permissions'])->pluck('name')->toArray();
                $missingNames     = array_diff($roleData['permissions'], $foundNames);
                if (!empty($missingNames)) {
                    $this->command->warn("  ⚠️  Role [{$name}] missing permissions: " . implode(', ', $missingNames));
                }

                $role->permissions()->sync($permissionIds);
            }
        }
    }

    private function generateDescription(string $name, string $group, string $scope): string
    {
        $scopeText = [
            'global'  => 'sin restricciones',
            'recinto' => 'limitado al recinto asignado',
            'mesa'    => 'limitado a la mesa asignada',
        ];
        return "Permiso para {$name} en {$group} - " . ($scopeText[$scope] ?? $scope);
    }
}
