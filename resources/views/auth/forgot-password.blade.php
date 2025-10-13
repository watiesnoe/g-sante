<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Récupération de mot de passe - Mali Kènèya Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html { height: 100%; margin: 0; font-family: 'Inter', sans-serif; background-color: #f4f6fa; }
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
            background: linear-gradient(135deg, rgba(28, 97, 231, 0.05) 0%, rgba(13, 46, 110, 0.1) 100%); }
        .auth-card { width: 100%; max-width: 450px; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .brand-logo { text-align: center; margin-bottom: 30px; }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid #e2e8f0; background-color: #f8fafc; }
        .form-control:focus { border-color: #1c61e7; box-shadow: 0 0 0 4px rgba(28, 97, 231, 0.1); }
        .btn-primary { background: linear-gradient(135deg, #1c61e7, #154cbd); border: none; border-radius: 12px; padding: 12px; font-weight: 600; width: 100%; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(28, 97, 231, 0.3); }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="brand-logo">
            <img src="{{ asset('image/logo/logo.png') }}" style="max-height: 70px;" alt="Logo">
        </div>
        
        <h4 class="fw-bold text-center mb-2">Mot de passe oublié ?</h4>
        <p class="text-muted text-center small mb-4">Indiquez votre adresse email et nous vous enverrons un lien de réinitialisation.</p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success rounded-3 border-0 small mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="form-label small fw-bold text-muted">Adresse Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required autofocus style="border-radius: 0 12px 12px 0;">
                </div>
                @if ($errors->has('email'))
                    <div class="text-danger small mt-1">{{ $errors->first('email') }}</div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary mb-3">
                <i class="fas fa-paper-plane me-2"></i> Envoyer le lien
            </button>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none small fw-bold" style="color: #1c61e7;">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la connexion
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
