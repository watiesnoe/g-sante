<section>
    <header class="mb-4">
        <p class="text-muted small">
            {{ __("Mettre à jour les informations de profil et l'adresse email de votre compte.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-bold small text-muted">{{ __('Nom d\'utilisateur') }}</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @if($errors->has('name'))
                <div class="text-danger small mt-1">{{ $errors->first('name') }}</div>
            @endif
        </div>

        <div class="mb-4">
            <label for="email" class="form-label fw-bold small text-muted">{{ __('Adresse Email') }}</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @if($errors->has('email'))
                <div class="text-danger small mt-1">{{ $errors->first('email') }}</div>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-warning">
                        {{ __('Votre adresse email n\'est pas vérifiée.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline small text-muted">
                            {{ __('Cliquez ici pour renvoyer l\'email de vérification.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-success small fw-bold">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse email.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa fa-save me-2"></i>{{ __('Enregistrer') }}
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small animate__animated animate__fadeOut animate__delay-2s">
                    <i class="fa fa-check-circle me-1"></i>{{ __('Enregistré.') }}
                </span>
            @endif
        </div>
    </form>
</section>
