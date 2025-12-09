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
            $users = User::latest()->get();

            return datatables()->of($users)
                ->addIndexColumn()
                ->addColumn('photo', function ($user) {
                    $photo = $user->photo ? asset('storage/' . $user->photo) : asset('images/default-user.png');
                    return '<img src="' . $photo . '" class="rounded-circle" width="40" height="40">';
                })
                ->addColumn('utilisateur', function ($user) {
                    return "<strong>{$user->prenom} {$user->nom}</strong><br><small>{$user->email}</small>";
                })
                ->addColumn('contact', fn($user) => $user->telephone ?? '-')
                ->addColumn('role', fn($user) => ucfirst($user->role))
                ->addColumn('statut', function ($user) {
                    $badge = $user->statut === 'actif' ? 'success' : 'danger';
                    return "<span class='badge bg-{$badge} text-uppercase'>{$user->statut}</span>";
                })
                ->addColumn('date_creation', fn($user) => $user->created_at->format('d/m/Y'))
                ->addColumn('actions', function ($user) {
                    $statusIcon = $user->statut === 'actif'
                        ? '<i class="fa fa-toggle-on text-success"></i>'
                        : '<i class="fa fa-toggle-off text-danger"></i>';

                    return '
                    <div class="dropdown text-center">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $user->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $user->id . '">
                            <li>
                                <a class="dropdown-item edit-user" href="#" data-id="' . $user->id . '">
                                    <i class="fa fa-pencil-alt me-1"></i> Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item toggle-status" href="#" data-id="' . $user->id . '">
                                    ' . $statusIcon . ' Changer le statut
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item delete-user" href="#" data-id="' . $user->id . '">
                                    <i class="fa fa-trash-alt me-1"></i> Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                    ';
                })

                ->rawColumns(['photo', 'utilisateur', 'statut', 'actions'])
                ->make(true);
        }

        $stats = [
            'total' => User::count(),
            'medecins' => User::where('role', 'medecin')->count(),
            'secretaires' => User::where('role', 'secretaire')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'clients' => User::where('role', 'client')->count(),
            'actifs' => User::where('statut', 'actif')->count(),
            'inactifs' => User::where('statut', 'inactif')->count(),
        ];

        return view('auth.index', compact('stats'));
    }

    public function getData(Request $request)
    {
        $users = User::latest()->get();

        return datatables()->of($users)
            ->addColumn('photo', function($user){
                return $user->photo ? '<img src="'.asset('storage/'.$user->photo).'" width="40" class="rounded-circle">' : '';
            })
            ->addColumn('contact', function($user){
                return $user->email.'<br>'.$user->telephone;
            })
            ->addColumn('actions', function ($user) {
                // Choix de l'icône de statut
                $statusIcon = $user->statut === 'actif'
                    ? '<i class="fa fa-toggle-on text-success"></i>'
                    : '<i class="fa fa-toggle-off text-danger"></i>';

                return '
                    <div class="btn-group" role="group">
                        <!-- Modifier -->
                        <button type="button" class="btn btn-sm btn-primary editUser" data-id="' . $user->id . '" title="Modifier">
                            <i class="fa fa-pencil-alt"></i>
                        </button>
                        <!-- Changer le statut -->
                        <button type="button" class="btn btn-sm btn-warning toggleStatus" data-id="' . $user->id . '" title="Changer le statut">
                            ' . $statusIcon . '
                        </button>
                        <!-- Supprimer -->
                        <button type="button" class="btn btn-sm btn-danger deleteUser" data-id="' . $user->id . '" title="Supprimer">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>
                    ';
            })->rawColumns(['photo','contact','actions'])
            ->make(true);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $services = ServiceMedical::where('statut', 'actif')->get();
        $roles = [
            'admin' => 'Administrateur',
            'medecin' => 'Médecin',
            'secretaire' => 'Secrétaire',
            'client' => 'Client'
        ];

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
            'role' => 'required|in:admin,medecin,secretaire,client',
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
            'role' => $validated['role'],
            'service_medical_id' => $validated['service_medical_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'photo' => $validated['photo'] ?? null,
            'statut' => 'actif', // valeur par défaut
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ]);
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

        $roles = [
            'admin' => 'Administrateur',
            'medecin' => 'Médecin',
            'secretaire' => 'Secrétaire',
            'client' => 'Client'
        ];

        $statuts = [
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'suspendu' => 'Suspendu'
        ];
        if (request()->ajax()) {
            return view('auth.users_edit', compact('user', 'roles', 'statuts'))->render();
        }
        return view('users.index');
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
            'role' => 'required|in:admin,medecin,secretaire,client',
            'statut' => 'required|in:actif,inactif,suspendu',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $user->update($request->except('photo'));

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $user->photo = $path;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Utilisateur {$user->prenom} {$user->nom} mis à jour avec succès !"
        ]);
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
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Supprimer la photo si elle existe
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function profile()
    {
        $user = auth()->user();
        return view('users.profile', compact('user'));
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

        return redirect()->route('profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Afficher la liste des médecins
     */
    public function medecins(Request $request)
    {
        $query = User::where('role', 'medecin')->where('statut', 'actif');

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
