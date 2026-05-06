<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Réinitialisation de mot de passe - Mali Kènèya Hub</title>
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
        
        <h4 class="fw-bold text-center mb-4">Nouveau mot de passe</h4>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-muted">Adresse Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $request->email) }}" required readonly>
                @if ($errors->has('email'))
                    <div class="text-danger small mt-1">{{ $errors->first('email') }}</div>
                @endif
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-bold text-muted">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                @if ($errors->has('password'))
                    <div class="text-danger small mt-1">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label small fw-bold text-muted">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                @if ($errors->has('password_confirmation'))
                    <div class="text-danger small mt-1">{{ $errors->first('password_confirmation') }}</div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary mb-3">
                <i class="fas fa-key me-2"></i> Réinitialiser le mot de passe
            </button>
        </form>
    </div>
</div>

</body>
</html>
