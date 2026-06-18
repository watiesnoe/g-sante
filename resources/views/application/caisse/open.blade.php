@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="block block-rounded">
                    <div class="block-header block-header-default bg-primary">
                        <h3 class="block-title text-white">Ouverture de Caisse</h3>
                    </div>
                    <div class="block-content">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-1"></i> Vous devez déclarer votre fonds de roulement avant de commencer les encaissements.
                        </div>

                        <form action="{{ route('caisse.storeOpen') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="solde_initial">Fonds de caisse (Solde Initial) en XOF</label>
                                <input type="text" class="form-control form-control-lg price-input @error('solde_initial') is-invalid @enderror" id="solde_initial" name="solde_initial" value="{{ old('solde_initial', 0) }}" required>
                                @error('solde_initial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fa fa-lock-open me-1"></i> Ouvrir ma Caisse
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
