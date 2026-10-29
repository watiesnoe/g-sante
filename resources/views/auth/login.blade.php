<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Mali Kènèya Hub</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo/logo.png') }}">
    {{-- Ressources locales (gabarit Dashmix : Bootstrap, icônes, police Inter) : la page
         s'affiche correctement même sur un serveur d'établissement sans Internet --}}
    <link rel="stylesheet" href="{{ asset('admin/css/dashmix.min.css') }}">
    <style>
        :root { --bleu: #1c61e7; --bleu-fonce: #0d2e6e; --texte: #1d2733; --gris: #6b7684; --bord: #e2e8f0; --fond: #f8fafc; }
        html, body { height: 100%; margin: 0; }
        body { font-family: 'Inter', -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #f4f6fa; color: var(--texte); }

        .connexion { min-height: 100vh; display: flex; }

        /* Côté gauche : la plateforme */
        .connexion-visuel {
            flex: 1.15; position: relative; display: flex; align-items: center; justify-content: center; padding: 56px; color: #fff; text-align: center;
            /* Dégradé seul si l'image n'est pas disponible (serveur sans Internet) */
            background: linear-gradient(135deg, rgba(28, 97, 231, .88) 0%, rgba(13, 46, 110, .96) 100%),
                        url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1600&q=70') center/cover, var(--bleu-fonce);
        }
        .connexion-visuel .contenu { max-width: 520px; }
        .connexion-visuel .logo-rond { width: 140px; height: 140px; border-radius: 50%; background: #fff; padding: 10px; box-shadow: 0 12px 30px rgba(0, 0, 0, .25); object-fit: contain; }
        .connexion-visuel h1 { font-size: 3rem; font-weight: 800; letter-spacing: -1px; margin: 24px 0 12px; color: #fff; }
        .connexion-visuel .accroche { font-size: 1.15rem; opacity: .92; line-height: 1.55; margin-bottom: 22px; }
        .types { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-bottom: 28px; }
        .types span { background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); border-radius: 999px; padding: 5px 14px; font-size: .9rem; font-weight: 600; }
        .atouts { list-style: none; padding: 0; margin: 0 auto; text-align: left; display: inline-block; }
        .atouts li { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 1rem; opacity: .95; }
        .atouts i { width: 34px; height: 34px; border-radius: 10px; background: rgba(255, 255, 255, .15); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

        /* Côté droit : le formulaire */
        .connexion-formulaire { flex: .85; display: flex; align-items: center; justify-content: center; padding: 40px 24px; background: #fff; box-shadow: -10px 0 30px rgba(0, 0, 0, .05); }
        .formulaire { width: 100%; max-width: 410px; }
        .entete { text-align: center; margin-bottom: 28px; }
        .entete img { max-height: 76px; }
        .entete .marque-mobile { display: none; font-weight: 800; font-size: 1.35rem; color: var(--bleu-fonce); margin-top: 8px; }
        .entete h2 { font-size: 1.6rem; font-weight: 800; margin: 14px 0 4px; color: var(--texte); }
        .entete p { color: var(--gris); margin: 0; }

        .champ { margin-bottom: 18px; }
        .champ label { font-size: .92rem; font-weight: 600; color: #475569; margin-bottom: 6px; display: block; }
        .saisie { display: flex; align-items: stretch; border: 1px solid var(--bord); border-radius: 12px; background: var(--fond); transition: border-color .15s, box-shadow .15s, background .15s; }
        .saisie:focus-within { border-color: var(--bleu); background: #fff; box-shadow: 0 0 0 4px rgba(28, 97, 231, .12); }
        .saisie.invalide { border-color: #dc2626; }
        .saisie .icone { display: flex; align-items: center; padding: 0 14px; color: var(--gris); }
        .saisie input { flex: 1; min-width: 0; border: 0; background: transparent; padding: 13px 4px; font-size: 1rem; color: var(--texte); outline: none; }
        .saisie .voir { border: 0; background: transparent; color: var(--gris); padding: 0 14px; cursor: pointer; }
        .saisie .voir:focus-visible { outline: 2px solid var(--bleu); border-radius: 8px; }

        .options { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px 16px; margin: 4px 0 22px; font-size: .93rem; }
        .options .form-check-input:checked { background-color: var(--bleu); border-color: var(--bleu); }
        .options a { color: var(--bleu); font-weight: 600; text-decoration: none; }
        .options a:hover { text-decoration: underline; }

        .bouton { width: 100%; border: 0; border-radius: 12px; padding: 14px; font-weight: 700; font-size: 1.05rem; color: #fff;
                  background: linear-gradient(135deg, var(--bleu), #154cbd); transition: transform .15s, box-shadow .15s, opacity .15s; }
        .bouton:hover { transform: translateY(-1px); box-shadow: 0 10px 25px rgba(28, 97, 231, .3); }
        .bouton:disabled { opacity: .75; transform: none; box-shadow: none; cursor: wait; }

        .message { display: flex; gap: 12px; border-radius: 12px; padding: 12px 14px; margin-bottom: 20px; font-size: .94rem; line-height: 1.45; }
        .message i { margin-top: 3px; }
        .message.erreur { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .message.attention { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
        .message.info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .message strong { display: block; margin-bottom: 2px; }

        .pied { margin-top: 32px; text-align: center; color: var(--gris); font-size: .84rem; }
        .pied strong { color: var(--bleu); }

        @media (max-width: 991.98px) {
            .connexion-visuel { display: none; }
            .entete .marque-mobile { display: block; }
            .entete h2 { font-size: 1.4rem; }
        }
    </style>
</head>

<body>
    <div class="connexion">
        <!-- Côté gauche : la plateforme -->
        <div class="connexion-visuel">
            <div class="contenu">
                <img src="{{ asset('image/logo/logo3.png') }}" class="logo-rond" alt="">
                <h1>Mali Kènèya Hub</h1>
                <p class="accroche">La plateforme de gestion des établissements de santé : de l'accueil du patient jusqu'au rapport mensuel.</p>
                <div class="types"><span>Cabinets</span><span>Cliniques</span><span>CSRéf</span><span>Hôpitaux</span></div>
                <ul class="atouts">
                    <li><i class="fa fa-file-medical"></i> Dossier patient, consultations et examens</li>
                    <li><i class="fa fa-cash-register"></i> Caisse, pharmacie et stock</li>
                    <li><i class="fa fa-shield-alt"></i> Chaque établissement ne voit que ses données</li>
                </ul>
            </div>
        </div>

        <!-- Côté droit : le formulaire -->
        <main class="connexion-formulaire">
            <div class="formulaire">
                <div class="entete">
                    <img src="{{ asset('image/logo/logo.png') }}" alt="Mali Kènèya Hub">
                    <div class="marque-mobile">Mali Kènèya Hub</div>
                    <h2>Connexion</h2>
                    <p>Accédez à l'espace de votre établissement.</p>
                </div>

                @php
                    $erreurConnexion = $errors->first('email') ?: $errors->first('password');
                    $typeErreur = match (true) {
                        str_contains((string) $erreurConnexion, 'suspendu')                   => ['attention', 'fa-pause-circle', 'Espace suspendu'],
                        str_contains((string) $erreurConnexion, 'verrouillé')                 => ['attention', 'fa-lock', 'Compte temporairement verrouillé'],
                        str_contains((string) $erreurConnexion, 'Trop de tentatives')         => ['attention', 'fa-hourglass-half', 'Trop de tentatives'],
                        str_contains((string) $erreurConnexion, 'désactivé')
                            || str_contains((string) $erreurConnexion, 'inactif')             => ['attention', 'fa-user-slash', 'Compte désactivé'],
                        default                                                                => ['erreur', 'fa-exclamation-circle', 'Connexion impossible'],
                    };
                @endphp

                @if (session('status'))
                    <div class="message info" role="status"><i class="fa fa-info-circle"></i><span>{{ session('status') }}</span></div>
                @endif
                @if ($erreurConnexion)
                    <div class="message {{ $typeErreur[0] }}" role="alert">
                        <i class="fa {{ $typeErreur[1] }}"></i>
                        <span><strong>{{ $typeErreur[2] }}</strong>{{ $erreurConnexion }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="formConnexion" novalidate>
                    @csrf

                    <div class="champ">
                        <label for="email">Adresse e-mail</label>
                        <div class="saisie {{ $errors->has('email') ? 'invalide' : '' }}">
                            <span class="icone" aria-hidden="true"><i class="fa fa-envelope"></i></span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nom@etablissement.ml"
                                   required autocomplete="username" inputmode="email" {{ old('email') ? '' : 'autofocus' }}>
                        </div>
                    </div>

                    <div class="champ">
                        <label for="password">Mot de passe</label>
                        <div class="saisie {{ $errors->has('password') ? 'invalide' : '' }}">
                            <span class="icone" aria-hidden="true"><i class="fa fa-lock"></i></span>
                            <input type="password" id="password" name="password" placeholder="Votre mot de passe" required
                                   autocomplete="current-password" {{ old('email') ? 'autofocus' : '' }}>
                            <button type="button" class="voir" id="voirMotDePasse" aria-label="Afficher le mot de passe" aria-pressed="false">
                                <i class="fa fa-eye" id="iconeVoir"></i>
                            </button>
                        </div>
                    </div>

                    <div class="options">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label text-muted" for="remember_me">Se souvenir de moi</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <button type="submit" class="bouton" id="boutonConnexion">
                        <i class="fa fa-sign-in-alt me-2"></i><span>Se connecter</span>
                    </button>
                </form>

                @if(\App\Models\Etablissement::espacePatientDisponible())
                    <p class="text-center mt-3 mb-0">
                        Vous êtes patient ? <a href="{{ route('patient.connexion') }}"><i class="fa fa-mobile-alt me-1"></i>Espace patient</a>
                    </p>
                @endif

                <div class="pied">&copy; {{ date('Y') }} Mali Kènèya Hub · Développé par <strong>DigitAfrika</strong></div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const champ = document.getElementById('password');
            const bouton = document.getElementById('voirMotDePasse');
            const icone = document.getElementById('iconeVoir');
            bouton.addEventListener('click', function () {
                const visible = champ.type === 'password';
                champ.type = visible ? 'text' : 'password';
                icone.classList.toggle('fa-eye', !visible);
                icone.classList.toggle('fa-eye-slash', visible);
                bouton.setAttribute('aria-pressed', String(visible));
                bouton.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            });

            // Un seul envoi : pas de double clic sur une connexion lente
            document.getElementById('formConnexion').addEventListener('submit', function (e) {
                if (!this.checkValidity()) { e.preventDefault(); this.reportValidity(); return; }
                const envoi = document.getElementById('boutonConnexion');
                envoi.disabled = true;
                envoi.innerHTML = '<i class="fa fa-circle-notch fa-spin me-2"></i>Connexion…';
            });
        });
    </script>
</body>

</html>
