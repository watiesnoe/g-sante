<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Connexion - Ges-Santé</title>
    <!-- On charge Bootstrap 5 et FontAwesome pour être en harmonie avec le reste de l'application -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f6fa;
            overflow-x: hidden;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
        }
        .login-left {
            flex: 1.2;
            /* Magnifique overlay bleu sur une image hospitalière HD d'Unsplash */
            background: linear-gradient(135deg, rgba(28, 97, 231, 0.85) 0%, rgba(13, 46, 110, 0.95) 100%), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 60px;
            text-align: center;
            position: relative;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PHBhdGggZmlsbD0id2hpdGUiIGZpbGwtb3BhY2l0eT0iMC4wNSIgZD0iTTAgMGgyMHYyMEgwem0xMCAxMGgxMHYxMEgxMHoiLz48L3N2Zz4=');
        }
        .illustration-text {
            position: relative;
            z-index: 10;
        }
        .login-right {
            flex: 0.8;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background: #ffffff;
            box-shadow: -10px 0 30px rgba(0,0,0,0.05);
            z-index: 5;
        }
        @media (max-width: 991px) {
            .login-left { display: none; }
            .login-right { flex: 1; }
        }
        .form-container {
            width: 100%;
            max-width: 420px;
        }
        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1c61e7;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: -1px;
        }
        .brand-subtitle {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 40px;
            text-align: center;
        }
        .form-floating > label {
            padding-left: 1.25rem;
            color: #6c757d;
        }
        .form-control {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            height: calc(3.8rem + 2px);
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #1c61e7;
            box-shadow: 0 0 0 4px rgba(28, 97, 231, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #1c61e7, #154cbd);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(28, 97, 231, 0.3);
            color: white;
        }
        .custom-checkbox .form-check-input {
            width: 1.2em;
            height: 1.2em;
            border-radius: 4px;
            margin-top: 0.15em;
        }
        .custom-checkbox .form-check-input:checked {
            background-color: #1c61e7;
            border-color: #1c61e7;
        }
        .illustration-text h1 {
            font-weight: 800;
            font-size: 3.5rem;
            margin-bottom: 20px;
            letter-spacing: -1px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .illustration-text p {
            font-size: 1.25rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Côté Gauche: Visuel & Marque -->
    <div class="login-left">
        <div class="illustration-text">
            <div class="mb-4">
                <i class="fas fa-heartbeat bg-white text-primary p-4 rounded-circle shadow-lg" style="font-size: 3.5rem;"></i>
            </div>
            <h1>Ges-Santé</h1>
            <p>Le système de gestion hospitalière intelligent, ultra-sécurisé et innovant conçu pour simplifier votre quotidien clinique.</p>
        </div>
    </div>

    <!-- Côté Droit : Formulaire de Connexion -->
    <div class="login-right">
        <div class="form-container">
            <div class="text-center mb-5">
                <div class="brand-logo">
                    <i class="fas fa-clinic-medical"></i> HealthCare
                </div>
                <div class="brand-subtitle">Bienvenue ! Veuillez vous authentifier pour continuer.</div>
            </div>

            <!-- Affichage des erreurs de validation (ex: Identifiants incorrects) -->
            @if ($errors->any())
                <div class="alert alert-danger rounded-4 shadow-sm border-0 mb-4" style="font-size: 0.95rem; background-color: #fee2e2; color: #991b1b;">
                    <div class="d-flex align-items-center mb-2 fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> Accès refusé
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Adresse Email -->
                <div class="form-floating mb-4">
                    <input type="email" class="form-control fw-medium" id="email" name="email" value="{{ old('email') }}" placeholder="nom@hopital.com" required autofocus autocomplete="username">
                    <label for="email"><i class="fas fa-envelope text-muted me-2"></i>Adresse Email</label>
                </div>

                <!-- Mot de passe -->
                <div class="form-floating mb-3">
                    <input type="password" class="form-control fw-medium" id="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
                    <label for="password"><i class="fas fa-lock text-muted me-2"></i>Mot de passe</label>
                </div>

                <!-- Mémoriser & Mdp oublié -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div class="form-check custom-checkbox">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label text-muted fw-medium user-select-none" for="remember_me" style="font-size: 0.95rem; cursor: pointer;">
                            Se souvenir de moi
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: #1c61e7; font-size: 0.95rem; font-weight: 600;">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <!-- Bouton de Soumission -->
                <button type="submit" class="btn btn-login shadow-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Accéder au portail
                </button>
            </form>
            
            <div class="mt-5 text-center text-muted fw-medium" style="font-size: 0.85rem;">
                &copy; {{ date('Y') }} Ges-Santé &middot; Développé par <span class="fw-bold text-primary">DigitAfrika</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
