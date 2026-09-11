<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Liste des rôles
     */
    public function index()
    {
        abort_unless(Auth::user()->can('roles.view'), 403, 'Accès non autorisé.');

        $roles = Role::with(['permissions', 'users'])->paginate(15);
        $allPermissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        });
        return view('application.parametre.roles.index', compact('roles', 'allPermissions'));
    }

    /**
     * Formulaire de création de rôle
     */
    public function create()
    {
        abort_unless(Auth::user()->can('roles.create'), 403, 'Accès non autorisé.');

        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        });
        return view('application.parametre.roles.create', compact('permissions'));
    }

    /**
     * Enregistrer un nouveau rôle
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('roles.create'), 403, 'Accès non autorisé.');

        $request->validate([
            'name'        => 'required|unique:roles,name',
            'libelle'     => 'required|string|max:255',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'libelle'    => $request->libelle,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès');
    }

    /**
     * Formulaire d'édition de rôle (route model binding par UUID)
     */
    public function edit(Role $role)
    {
        abort_unless(Auth::user()->can('roles.edit'), 403, 'Accès non autorisé.');

        $permissions    = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('application.parametre.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Mettre à jour un rôle (route model binding par UUID)
     */
    public function update(Request $request, Role $role)
    {
        abort_unless(Auth::user()->can('roles.edit'), 403, 'Accès non autorisé.');

        $request->validate([
            'name'        => 'required|unique:roles,name,' . $role->id,
            'libelle'     => 'required|string|max:255',
            'permissions' => 'array',
        ]);

        $role->update([
            'name'    => $request->name,
            'libelle' => $request->libelle,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès');
    }

    /**
     * Mettre à jour les permissions d'un rôle (depuis le modal index)
     */
    public function updatePermissions(Request $request, Role $role)
    {
        abort_unless(Auth::user()->can('roles.assign_permissions'), 403, 'Accès non autorisé.');

        $request->validate([
            'permissions' => 'array',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Permissions du rôle « ' . ($role->libelle ?? $role->name) . ' » mises à jour avec succès.');
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role)
    {
        abort_unless(Auth::user()->can('roles.delete'), 403, 'Accès non autorisé.');

        // Empêcher la suppression des rôles système
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle système ne peut pas être supprimé',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès',
        ]);
    }

    /**
     * Page de gestion des permissions
     */
    public function permissions()
    {
        abort_unless(Auth::user()->can('roles.view'), 403, 'Accès non autorisé.');

        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        });

        return view('application.parametre.roles.permissions', compact('permissions'));
    }

    /**
     * Assigner des permissions à un utilisateur
     */
    public function assignUserPermissions(Request $request, $userId)
    {
        abort_unless(Auth::user()->can('roles.assign_permissions'), 403, 'Accès non autorisé.');

        $user = \App\Models\User::findOrFail($userId);

        $request->validate([
            'permissions' => 'array',
        ]);

        $user->syncPermissions($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assignées avec succès',
        ]);
    }

    /**
     * Obtenir les permissions d'un utilisateur
     */
    public function getUserPermissions($userId)
    {
        abort_unless(Auth::user()->can('roles.view'), 403, 'Accès non autorisé.');

        $user = \App\Models\User::findOrFail($userId);
        $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $allPermissions    = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'direct_permissions' => $directPermissions,
            'all_permissions'    => $allPermissions,
        ]);
    }
}