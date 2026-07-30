<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionGroupService
{
    /**
     * Group permissions by module
     * Expected permission names: module.action (e.g., products.view, roles.create)
     *
     * @return array<string, array<string, mixed>>
     */
    public function groupPermissions(): array
    {
        $permissions = Permission::where('guard_name', 'web')->get();

        $grouped = [];

        foreach ($permissions as $permission) {
            [$module, $action] = $this->parsePermissionName($permission->name);

            if (! isset($grouped[$module])) {
                $grouped[$module] = [
                    'name' => $module,
                    'display_name' => $this->formatModuleName($module),
                    'permissions' => [],
                ];
            }

            $grouped[$module]['permissions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action,
                'display_name' => $this->formatActionName($action),
            ];
        }

        // Sort modules and permissions within each module
        ksort($grouped);
        foreach ($grouped as &$module) {
            usort($module['permissions'], fn ($a, $b) => $this->getActionOrder($a['action']) <=> $this->getActionOrder($b['action']));
        }

        return array_values($grouped);
    }

    /**
     * Parse permission name into module and action
     */
    private function parsePermissionName(string $permissionName): array
    {
        if (str_contains($permissionName, '.')) {
            $parts = explode('.', $permissionName);

            if (count($parts) === 2) {
                return $parts;
            }
        }

        if (str_contains($permissionName, ' ')) {
            $parts = explode(' ', $permissionName);

            if (count($parts) === 2) {
                return [$parts[1], $parts[0]];
            }
        }

        // Fallback for unexpected format
        return [$permissionName, 'other'];
    }

    /**
     * Format module name for display
     */
    private function formatModuleName(string $module): string
    {
        return ucfirst(str_replace('_', ' ', $module));
    }

    /**
     * Format action name for display
     */
    private function formatActionName(string $action): string
    {
        $map = [
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'list' => 'List',
            'show' => 'Show',
            'update' => 'Update',
        ];

        return $map[$action] ?? ucfirst($action);
    }

    /**
     * Get sort order for actions
     */
    private function getActionOrder(string $action): int
    {
        $order = [
            'view' => 1,
            'list' => 1,
            'create' => 2,
            'show' => 3,
            'edit' => 4,
            'update' => 4,
            'delete' => 5,
        ];

        return $order[$action] ?? 10;
    }
}
