{{-- Identifiants remis une seule fois (propriétaire ou administrateur) --}}
        @if($id = session('identifiants'))
            <div class="block block-rounded border border-success">
                <div class="block-header bg-success-light"><h3 class="block-title"><i class="fa fa-key me-1"></i> Identifiants — {{ $id['etablissement'] }}</h3></div>
                <div class="block-content pb-3">
                    <p class="mb-2">Remettez ces identifiants à la personne concernée par un moyen sûr (en main propre, SMS à son numéro). <strong>Le mot de passe ne sera plus affiché.</strong></p>
                    <table class="table table-sm mb-2" style="max-width:560px">
                        <tr><th style="width:170px">Adresse de connexion</th><td><code>{{ url('/login') }}</code></td></tr>
                        <tr><th>Identifiant (e-mail)</th><td><code>{{ $id['email'] }}</code></td></tr>
                        <tr><th>Mot de passe provisoire</th><td><code class="fs-5" id="mdpProvisoire">{{ $id['mot_de_passe'] }}</code>
                            <button type="button" class="btn btn-sm btn-alt-secondary ms-2" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('mdpProvisoire').textContent)"><i class="fa fa-copy"></i></button></td></tr>
                    </table>
                    <p class="small text-muted mb-0">À sa première connexion, la personne devra choisir son propre mot de passe.</p>
                </div>
            </div>
        @endif
