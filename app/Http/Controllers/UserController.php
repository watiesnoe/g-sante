<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 👈 ajoute ceci
class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        $this->authorize('viewAny', User::class);

        // Si la requête est AJAX (pour DataTables)
        if ($request->ajax()) {
            $users = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))->latest()->get();

            return datatables()->of($users)
                ->addIndexColumn()
                ->addColumn('photo', function ($user) {
                    $photo = $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->prenom.' '.$user->nom).'&background=random';
                    return '<img src="' . $photo . '" class="rounded-circle" width="40" height="40" style="object-fit:cover;">';
                })
                ->addColumn('utilisateur', function ($user) {
                    return "<strong>{$user->prenom} {$user->nom}</strong><br><small>{$user->email}</small>";
                })
                ->addColumn('contact', fn($user) => $user->telephone ?? '-')
                ->addColumn('role', function ($user) {
                    $role = $user->roles->first();
                    return $role ? ucfirst($role->libelle ?? $role->name) : '-';
                })
                ->addColumn('statut', function ($user) {
                    $badge = $user->statut === 'actif' ? 'success' : 'danger';
                    return "<span class='badge bg-{$badge} text-uppercase'>{$user->statut}</span>";
                })
                ->addColumn('date_creation', fn($user) => $user->created_at ? $user->created_at->format('d-m-Y') : '-')
                ->addColumn('actions', function ($user) {
                    $statusIcon  = $user->statut === 'actif' ? 'fa-toggle-on' : 'fa-toggle-off';
                    $statusClass = $user->statut === 'actif' ? 'btn-outline-success' : 'btn-outline-warning';
                    
                    $edit   = '<a href="'.route('users.edit', $user->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $status = '<button type="button" class="btn btn-sm '.$statusClass.' toggle-status" data-id="'.$user->uuid.'" title="Changer statut"><i class="fa '.$statusIcon.'"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete-user" data-id="'.$user->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $edit . $status . $delete . '</div>';
                })

                ->rawColumns(['photo', 'utilisateur', 'statut', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => User::count(),
            'actifs' => User::where('statut', 'actif')->count(),
            'inactifs' => User::where('statut', 'inactif')->count(),
        ];

        $roles = \Spatie\Permission\Models\Role::all();
        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });

        return view('auth.index', compact('stats', 'roles', 'permissions'));
    }

    public function getData(Request $request)
    {
        $users = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))->latest()->get();

        return datatables()->of($users)
            ->addColumn('photo', function($user){
                $photo = $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->prenom.' '.$user->nom).'&background=random';
                return '<img src="' . $photo . '" width="40" height="40" class="rounded-circle" style="object-fit:cover;">';
            })
            ->addColumn('contact', function($user){
                return $user->email.'<br>'.$user->telephone;
            })
            ->addColumn('actions', function ($user) {
                $statusIcon  = $user->statut === 'actif' ? 'fa-toggle-on' : 'fa-toggle-off';
                $statusClass = $user->statut === 'actif' ? 'btn-outline-success' : 'btn-outline-warning';
                
                $edit   = '<a href="'.route('users.edit', $user->uuid).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                $status = '<button type="button" class="btn btn-sm '.$statusClass.' toggleStatus" data-id="'.$user->uuid.'" title="Changer statut"><i class="fa '.$statusIcon.'"></i></button>';
                $delete = '<button type="button" class="btn btn-sm btn-outline-danger deleteUser" data-id="'.$user->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                
                return '<div class="d-flex align-items-center justify-content-center gap-1">' . $edit . $status . $delete . '</div>';
            })->rawColumns(['photo','contact','actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $services = ServiceMedical::orderBy('nom')->get();
        $roles = \Spatie\Permission\Models\Role::all();

        return view('users.create', compact('services', 'roles'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'role' => 'required|exists:roles,name',
            'service_medical_id' => 'nullable|exists:service_medicals,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Gestion de la photo
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $validated['prenom'] . ' ' . $validated['nom'],
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'service_medical_id' => $validated['service_medical_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'photo' => $validated['photo'] ?? null,
            'statut' => 'actif', // valeur par défaut
        ]);

        $user->assignRole($validated['role']);

        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = \Spatie\Permission\Models\Role::all();

        $statuts = [
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'suspendu' => 'Suspendu'
        ];
        
        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });
        
        $userRole = $user->roles->first()?->name;
        $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        
        return view('users.edit', compact('user', 'roles', 'statuts', 'permissions', 'userRole', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
            'statut' => 'required|in:actif,inactif,suspendu',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'permissions' => 'nullable|array'
        ]);

        // Mise à jour classique
        $data = $request->except(['photo', 'role', 'permissions']);
        
        $user->update($data);

        // Update role
        $user->syncRoles([$request->role]);
        
        // Update direct permissions
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        } else {
            $user->syncPermissions([]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $user->photo = $path;
            $user->save();
        }

        return redirect()->route('users.index')
            ->with('success', "Utilisateur {$user->prenom} {$user->nom} mis à jour avec succès !");
    }


    /**
     * Changer le statut d'un utilisateur
     */
    public function updateStatus(Request $request, User $user)
    {
        try {
            // (Optionnel) Vérifie l'autorisation
            // $this->authorize('update', $user);

            // ✅ Validation du statut reçu
            $request->validate([
                'statut' => 'required|in:actif,inactif,suspendu'
            ]);

            // ✅ Mise à jour dans la base
            $user->update(['statut' => $request->statut]);

            // ✅ Journalisation (utile pour déboguer)
            Log::info('✅ Statut utilisateur mis à jour', [
                'user_id' => $user->id,
                'nouveau_statut' => $request->statut,
            ]);

            // ✅ Réponse JSON vers ton AJAX
            return response()->json([
                'success' => true,
                'message' => "Statut mis à jour avec succès !".$user->statut,
                'statut' => $user->statut
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erreur updateStatus : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la mise à jour du statut.",
            ], 500);
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
            }
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Supprimer la photo si elle existe
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function profile()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'password' => 'nullable|confirmed|min:8',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->update([
            'name' => $validated['prenom'] . ' ' . $validated['nom'],
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'],
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->update([
                'photo' => $request->file('photo')->store('users', 'public'),
            ]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Afficher la liste des médecins
     */
    public function medecins(Request $request)
    {
        $query = User::role('medecin')->where('statut', 'actif');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('nom', 'like', "%{$search}%");
            });
        }

        $medecins = $query->latest()->paginate(20);

        return view('users.medecins', compact('medecins'));
    }


}
