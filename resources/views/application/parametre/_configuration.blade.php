{{-- Configuration réservée à la plateforme : type, modules, règles, historique. Variables : $etablissement, $types, $modules, $parametres, $historique --}}
                    {{-- 2. Type --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">1. Type d'établissement</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">Choisir un type pré-coche les modules habituels ; vous pouvez ensuite les ajuster.@if($etablissement->abonnement) Cet établissement est abonné à « {{ $etablissement->abonnement->formule->nom }} » : un changement de formule remettra les modules de la formule.@endif</p>
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
                            <h3 class="block-title">2. Modules activés</h3>
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
                            <h3 class="block-title">3. Règles de fonctionnement</h3>
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

