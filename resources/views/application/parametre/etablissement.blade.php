@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Établissement
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('etablissement.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- 1. Identité --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">1. Votre établissement</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">Ces informations sont imprimées sur les tickets, ordonnances et reçus.</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $etablissement->nom) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slogan / sous-titre</label>
                                    <input type="text" name="slogan" class="form-control" value="{{ old('slogan', $etablissement->slogan) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $etablissement->adresse) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Région</label>
                                    <input type="text" name="region" class="form-control" value="{{ old('region', $etablissement->region) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">District sanitaire</label>
                                    <input type="text" name="district" class="form-control" value="{{ old('district', $etablissement->district) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $etablissement->telephone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $etablissement->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site web</label>
                                    <input type="text" name="site_web" class="form-control" value="{{ old('site_web', $etablissement->site_web) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Logo (PNG ou JPG, 2 Mo max)</label>
                                    <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    @if(file_exists($etablissement->logoPath()))
                                        <img src="{{ asset($etablissement->logo ?: 'image/logo/logo.png') }}?v={{ filemtime($etablissement->logoPath()) }}" alt="Logo" style="max-height: 70px;">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Type --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">2. Type d'établissement</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">Choisir un type pré-coche les modules habituels ; vous pouvez ensuite les ajuster.</p>
                            <div class="row g-3 mb-4">
                                @foreach($types as $cle => $libelle)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input type-etablissement" type="radio" name="type" id="type_{{ $cle }}" value="{{ $cle }}"
                                                {{ old('type', $etablissement->type) === $cle ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-semibold" for="type_{{ $cle }}">{{ $libelle }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 3. Modules --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">3. Modules activés</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">
                                Un module désactivé disparaît des menus et ses pages sont bloquées pour tous les utilisateurs.
                                Ses données sont conservées et réapparaissent si vous le réactivez.
                                Patients, tickets, consultations et caisse sont toujours actifs.
                            </p>
                            @php $actifs = old('modules', $etablissement->modules ?? []); @endphp
                            <div class="row g-3 mb-4">
                                @foreach($modules as $cle => $module)
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input module-etablissement" type="checkbox" name="modules[]" id="module_{{ $cle }}" value="{{ $cle }}"
                                                {{ in_array($cle, $actifs) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="module_{{ $cle }}">
                                                <span class="fw-semibold">{{ $module['label'] }}</span><br>
                                                <small class="text-muted">{{ $module['description'] }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 4. Règles de fonctionnement --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">4. Règles de fonctionnement</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">Les règles marquées « fixe » protègent la sécurité des patients et de l'argent : elles ne se modifient pas.</p>
                            <div class="mb-4">
                                @foreach($parametres as $cle => $parametre)
                                    @php $nomChamp = 'parametres[' . str_replace('.', '_', $cle) . ']'; $valeur = $etablissement->parametre($cle); @endphp
                                    @switch($parametre['type'] ?? 'booleen')
                                        @case('nombre')
                                        @case('texte')
                                            <div class="mb-3">
                                                <label class="form-label" for="param_{{ $loop->index }}">{{ $parametre['label'] }}</label>
                                                <input type="{{ ($parametre['type'] ?? '') === 'nombre' ? 'number' : 'text' }}" class="form-control" id="param_{{ $loop->index }}" name="{{ $nomChamp }}" value="{{ $valeur }}" min="0">
                                            </div>
                                            @break
                                        @case('choix')
                                            <div class="mb-3">
                                                <label class="form-label" for="param_{{ $loop->index }}">{{ $parametre['label'] }}</label>
                                                <select class="form-select" id="param_{{ $loop->index }}" name="{{ $nomChamp }}">
                                                    @foreach($parametre['choix'] as $code => $libelle)
                                                        <option value="{{ $code }}" {{ $valeur === $code ? 'selected' : '' }}>{{ $libelle }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @break
                                        @case('liste')
                                            <div class="mb-3">
                                                <label class="form-label d-block">{{ $parametre['label'] }}</label>
                                                @foreach($parametre['choix'] as $code => $libelle)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" name="{{ $nomChamp }}[]" id="param_{{ $loop->parent->index }}_{{ $code }}" value="{{ $code }}" {{ in_array($code, (array) $valeur) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="param_{{ $loop->parent->index }}_{{ $code }}">{{ $libelle }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @break
                                        @default
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="{{ $nomChamp }}" id="param_{{ $loop->index }}" value="1"
                                                    {{ $valeur ? 'checked' : '' }} {{ !empty($parametre['fixe']) ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="param_{{ $loop->index }}">
                                                    {{ $parametre['label'] }}
                                                    @if(!empty($parametre['fixe']))<span class="badge bg-secondary ms-1">fixe</span>@endif
                                                </label>
                                            </div>
                                    @endswitch
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($historique->isNotEmpty())
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Historique des changements <small class="text-muted">({{ $historique->count() }})</small></h3>
                            <div class="block-options">
                                {{-- Sans attribut name : la case n'est pas envoyée avec le formulaire --}}
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="afficherHistorique">
                                    <label class="form-check-label small" for="afficherHistorique">Afficher</label>
                                </div>
                            </div>
                        </div>
                        <div id="historiqueContenu" class="block-content pb-3 table-responsive d-none" style="max-height:420px; overflow-y:auto;">
                            <table class="table table-sm table-striped fs-sm" style="table-layout:fixed; word-break:break-word;">
                                <colgroup><col style="width:120px"><col style="width:120px"><col style="width:28%"><col><col></colgroup>
                                <thead><tr><th>Date</th><th>Auteur</th><th>Réglage</th><th>Avant</th><th>Après</th></tr></thead>
                                <tbody>
                                    @foreach($historique as $ligne)
                                        <tr>
                                            <td class="text-nowrap">{{ $ligne['date'] }}</td>
                                            <td>{{ $ligne['auteur'] }}</td>
                                            <td>{{ $ligne['champ'] }}</td>
                                            <td class="text-muted">{{ $ligne['avant'] }}</td>
                                            <td>{{ $ligne['apres'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="text-end mb-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>

                {{-- Lot 7 : liaison avec le serveur en ligne (formulaire distinct) --}}
                <form action="{{ route('etablissement.en_ligne') }}" method="POST" class="block block-rounded" id="serveur-en-ligne">
                    @csrf
                    @method('PUT')
                    <div class="block-header block-header-default">
                        <h3 class="block-title">5. Serveur en ligne (espace patient)</h3>
                    </div>
                    <div class="block-content">
                        <p class="small text-muted">
                            Le serveur local de l'établissement reste maître : il envoie ses créneaux libres et récupère les demandes des patients toutes les 5 minutes.
                            Sans Internet, l'activité interne continue ; les échanges reprennent au retour de la connexion.
                            Identifiant de l'établissement à communiquer à l'administrateur du serveur en ligne :
                            <code>{{ $etablissement->uuid }}</code>
                        </p>
                        @error('en_ligne_url')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                        @error('en_ligne_cle')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                        @error('en_ligne_secret')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label" for="en_ligne_url">Adresse du serveur en ligne</label>
                                <input type="url" class="form-control" id="en_ligne_url" name="en_ligne_url" value="{{ old('en_ligne_url', $etablissement->en_ligne_url) }}" placeholder="https://…">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="en_ligne_cle">Clé</label>
                                <input type="text" class="form-control" id="en_ligne_cle" name="en_ligne_cle" value="{{ old('en_ligne_cle', $etablissement->en_ligne_cle) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="en_ligne_secret">Secret</label>
                                <input type="password" class="form-control" id="en_ligne_secret" name="en_ligne_secret" autocomplete="new-password"
                                       placeholder="{{ $etablissement->en_ligne_secret ? 'Enregistré — laisser vide pour le garder' : 'Fourni par le serveur en ligne' }}">
                            </div>
                        </div>
                        @if($etablissement->connecteEnLigne())
                            <p class="small mb-2">
                                Dernière synchronisation réussie : {{ $etablissement->en_ligne_synchro_at?->format('d/m/Y H:i') ?? 'jamais' }}
                                @if($etablissement->en_ligne_erreur)<span class="text-danger"> — dernière erreur : {{ $etablissement->en_ligne_erreur }}</span>@endif
                            </p>
                            @if($synchros->isNotEmpty())
                                <table class="table table-sm fs-sm mb-3">
                                    <thead><tr><th>Début</th><th>Statut</th><th>Reçus</th><th>Envoyés</th><th>Message</th></tr></thead>
                                    <tbody>
                                        @foreach($synchros as $s)
                                            <tr><td>{{ $s->debut->format('d/m H:i:s') }}</td><td class="{{ $s->statut === 'ok' ? 'text-success' : 'text-danger' }}">{{ $s->statut }}</td><td>{{ $s->recus }}</td><td>{{ $s->envoyes }}</td><td class="small">{{ $s->message }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @endif
                        <div class="d-flex gap-2 mb-3">
                            <button type="submit" class="btn btn-outline-primary"><i class="fa fa-save me-1"></i> Enregistrer la connexion</button>
                            @if($etablissement->connecteEnLigne())
                                <button type="submit" form="form-synchro" class="btn btn-outline-secondary"><i class="fa fa-sync me-1"></i> Synchroniser maintenant</button>
                            @endif
                        </div>
                        <p class="small text-muted mb-3">Pour déconnecter l'établissement, videz l'adresse et enregistrez. La publication des créneaux se règle dans les règles de fonctionnement ci-dessus (« Publier les créneaux libres… »).</p>
                    </div>
                </form>
                <form id="form-synchro" action="{{ route('en_ligne.synchroniser') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // Historique masqué par défaut ; le choix de l'utilisateur est retenu sur ce poste
            const cleHistorique = 'gsante.etablissement.historique';
            let historiqueVisible = false;
            try { historiqueVisible = localStorage.getItem(cleHistorique) === '1'; } catch (e) {}
            $('#afficherHistorique').prop('checked', historiqueVisible);
            $('#historiqueContenu').toggleClass('d-none', !historiqueVisible);
            $('#afficherHistorique').on('change', function () {
                $('#historiqueContenu').toggleClass('d-none', !this.checked);
                try { localStorage.setItem(cleHistorique, this.checked ? '1' : '0'); } catch (e) {}
            });

            const profils = @json($profils);

            // Changer de type pré-coche les modules du profil
            $('.type-etablissement').on('change', function () {
                const modules = profils[$(this).val()] || [];
                $('.module-etablissement').each(function () {
                    $(this).prop('checked', modules.includes($(this).val()));
                });
            });
        });
    </script>
@endsection
