{{-- Liaison de l'établissement avec le serveur en ligne (plateforme). Variables : $etablissement, $synchros --}}
                {{-- Lot 7 : liaison avec le serveur en ligne (formulaire distinct) --}}
                <form action="{{ route('plateforme.en_ligne', $etablissement) }}" method="POST" class="block block-rounded" id="serveur-en-ligne">
                    @csrf
                    @method('PUT')
                    <div class="block-header block-header-default">
                        <h3 class="block-title">4. Serveur en ligne (espace patient)</h3>
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
                        <p class="small text-muted mb-3">Pour déconnecter l'établissement, videz l'adresse et enregistrez. La publication des créneaux se règle dans les règles de fonctionnement (« Publier les créneaux libres… »).</p>
                    </div>
                </form>
                <form id="form-synchro" action="{{ route('plateforme.synchroniser', $etablissement) }}" method="POST" class="d-none">@csrf</form>
