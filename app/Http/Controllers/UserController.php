<?php

namespace App\Http\Controllers;

use App\Models\ServiceMedical;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 👈 ajoute ceci
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

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
            $users = User::select(['id', 'uuid', 'name', 'prenom', 'nom', 'email', 'telephone', 'photo', 'statut', 'created_at'])
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))
                ->with('roles:id,name,libelle')
                ->latest()
                ->get();

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
            'total'    => \App\Models\Etablissement::tableUtilisateurs()->count(),
            'actifs'   => \App\Models\Etablissement::tableUtilisateurs()->where('statut', 'actif')->count(),
            'inactifs' => \App\Models\Etablissement::tableUtilisateurs()->where('statut', 'inactif')->count(),
        ];

        $roles = \Spatie\Permission\Models\Role::all();
        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });

        return view('auth.index', compact('stats', 'roles', 'permissions'));
    }

    public function getData(Request $request)
    {
        // Comptes affectés à l'établissement courant (EF-01.03)
        $users = User::deLEtablissement()->whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))->latest()->get();

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
        $etablissements = Auth::user()->etablissementsAccessibles();
        $etablissementsUtilisateur = [\App\Models\Etablissement::courantId()];

        return view('users.create', compact('services', 'roles', 'etablissements', 'etablissementsUtilisateur'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        // Validation des données
        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'role' => 'required|exists:roles,name',
            'service_medical_id' => 'nullable|required_if:role,medecin|exists:service_medicals,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'etablissements' => 'nullable|array',
            'etablissements.*' => 'integer|exists:etablissements,id',
        ]);

        // 🔒 Seul un super_admin peut créer un compte super_admin
        if ($validated['role'] === 'super_admin' && !Auth::user()->hasRole('super_admin')) {
            abort(403, 'Seul un super administrateur peut attribuer ce rôle.');
        }

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
        $user->etablissements()->sync($this->etablissementsAutorises($request));

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
        $this->exigerMemeEtablissement($user);

        $services = ServiceMedical::orderBy('nom')->get();
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
        $etablissements = Auth::user()->etablissementsAccessibles();
        $etablissementsUtilisateur = $user->etablissements()->pluck('etablissements.id')->all();

        return view('users.edit', compact('user', 'services', 'roles', 'statuts', 'permissions', 'userRole', 'userPermissions', 'etablissements', 'etablissementsUtilisateur'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->exigerMemeEtablissement($user);

        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
            'statut' => 'required|in:actif,inactif,suspendu',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'permissions' => 'nullable|array',
            'service_medical_id' => 'nullable|required_if:role,medecin|exists:service_medicals,id',
        ]);

        // 🔒 Seul un super_admin peut promouvoir un compte au rôle super_admin
        if ($validated['role'] === 'super_admin' && !Auth::user()->hasRole('super_admin')) {
            abort(403, 'Seul un super administrateur peut attribuer ce rôle.');
        }

        // 🔒 Mise à jour explicite (jamais $request->except(), qui laisserait passer
        // n'importe quel champ fillable du modèle, comme "password", sans validation)
        $user->update([
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'name' => $validated['prenom'] . ' ' . $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'service_medical_id' => $validated['service_medical_id'] ?? null,
            'statut' => $validated['statut'],
        ]);

        // Update role
        $user->syncRoles([$validated['role']]);

        // Affectations : on ne touche qu'aux établissements que l'administrateur gère lui-même
        $geres = Auth::user()->etablissementsAccessibles()->pluck('id');
        $horsPerimetre = $user->etablissements()->whereNotIn('etablissements.id', $geres)->pluck('etablissements.id');
        $user->etablissements()->sync($horsPerimetre->merge($this->etablissementsAutorises($request))->unique()->all());
        
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
        // 🔒 Vérifiée avant le try/catch pour ne pas être avalée en erreur 500 générique
        $this->authorize('update', $user);

        try {
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
        if ($user->id === Auth::id()) {
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
        $user = Auth::user();
        return view('application.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

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
        $query = User::deLEtablissement()->role('medecin')->where('statut', 'actif');

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



    /** Établissements cochés, limités à ceux de l'administrateur ; au moins l'établissement courant. */
    private function etablissementsAutorises(Request $request): array
    {
        $geres = Auth::user()->etablissementsAccessibles()->pluck('id');
        $choisis = collect($request->input('etablissements', []))->map(fn($id) => (int) $id)->intersect($geres);

        return ($choisis->isEmpty() ? collect([\App\Models\Etablissement::courantId()]) : $choisis)->values()->all();
    }

    /** Un administrateur ne gère que les comptes de son établissement (SEC-04). */
    private function exigerMemeEtablissement(User $user): void
    {
        if (Auth::user()->hasRole(['super_admin', 'superadmin'])) {
            return;
        }
        abort_unless($user->etablissements()->where('etablissements.id', \App\Models\Etablissement::courantId())->exists(), 404);
    }
}
