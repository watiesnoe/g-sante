@extends('layouts.app')

@section('titre')
    🛏️ {{ isset($lit) ? 'Modifier un lit' : 'Ajouter un lit' }}
@endsection

@section('content')
    <div class="content">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">
                            {{ isset($lit) ? '✏️ Modifier un lit' : '➕ Ajouter un nouveau lit' }}
                        </h5>
                    </div>
                    <div class="block-content">
                        <form id="litForm"
                              action="{{ isset($lit) ? route('lits.update', $lit->id) : route('lits.store') }}"
                              method="POST">
                            @csrf
                            @if(isset($lit))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Numéro du lit</label>
                                <input type="text" name="numero" class="form-control"
                                       value="{{ old('numero', $lit->numero ?? '') }}"
                                       placeholder="Ex : LIT-01" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Salle</label>
                                <select name="salle_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($salles as $salle)
                                        <option value="{{ $salle->id }}"
                                            {{ old('salle_id', $lit->salle_id ?? '') == $salle->id ? 'selected' : '' }}>
                                            {{ $salle->nom }} ({{ $salle->type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select name="statut" class="form-select" required>
                                    <option value="Libre" {{ old('statut', $lit->statut ?? '') == 'Libre' ? 'selected' : '' }}>Libre</option>
                                    <option value="Occupé" {{ old('statut', $lit->statut ?? '') == 'Occupé' ? 'selected' : '' }}>Occupé</option>
                                    <option value="Maintenance" {{ old('statut', $lit->statut ?? '') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill">
                                {{ isset($lit) ? '✏️ Mettre à jour' : '💾 Enregistrer' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#litForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = form.serialize();
                let method = form.find('input[name="_method"]').val() || 'POST';

                $.ajax({
                    url: form.attr('action'),
                    method: method,
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès ✅',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('lits.index') }}";
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endsection
