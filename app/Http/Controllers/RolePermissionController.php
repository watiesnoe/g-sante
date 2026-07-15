<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:roles.view');
        // $this->middleware('permission:roles.create')->only(['create', 'store']);
        // $this->middleware('permission:roles.edit')->only(['edit', 'update']);
        // $this->middleware('permission:roles.delete')->only(['destroy']);
        // $this->middleware('permission:roles.assign_permissions')->only(['editPermissions', 'updatePermissions']);
    }

    /**
     * Liste des rôles
     */
    public function index()
    {
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
        $user = \App\Models\User::findOrFail($userId);
        $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $allPermissions    = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'direct_permissions' => $directPermissions,
            'all_permissions'    => $allPermissions,
        ]);
    }
}