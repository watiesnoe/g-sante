@extends('layouts.app')

@section('titre', 'Ajouter un résultat')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Ajouter un résultat d'examen</h2>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="reponseForm" action="{{ route('reponses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="prescription_examen_id" class="form-label">Patient</label>
                        <input type="text" class="form-control"
                               value="{{ $prescription->consultation->patient->nom }} {{ $prescription->consultation->patient->prenom }}"
                               disabled>
                        <input type="hidden" name="prescription_examen_id" value="{{ $prescription->id }}">
                    </div>

                    @php $cat = $prescription->catalogue; @endphp
                    <div class="mb-2 small text-muted">Examen : <strong>{{ $prescription->examen }}</strong>
                        ({{ $prescription->type === 'imagerie' ? 'imagerie' : 'laboratoire' }})</div>
                    @if($prescription->type !== 'imagerie')
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Valeur</label>
                            <input type="text" name="valeur" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unité</label>
                            <input type="text" name="unite" class="form-control" value="{{ $cat->unite ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence min.</label>
                            <input type="number" step="any" name="valeur_min" class="form-control" value="{{ $cat->valeur_min ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence max.</label>
                            <input type="number" step="any" name="valeur_max" class="form-control" value="{{ $cat->valeur_max ?? '' }}">
                        </div>
                        <div class="col-12 form-text">Une valeur hors des références est signalée comme anormale.</div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="resultat" class="form-label">{{ $prescription->type === 'imagerie' ? 'Compte rendu' : 'Résultat / commentaire' }}</label>
                        <textarea name="resultat" id="resultat" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="fichier" class="form-label">Fichier (optionnel)</label>
                        <input type="file" name="fichier" id="fichier" class="form-control" accept=".pdf,.jpg,.png">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <a href="{{ route('laboratoire.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section("scripts")
    <script>
        $('#reponseForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    Swal.fire({
                        title: 'Enregistrement...',
                        text: 'Veuillez patienter',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Réponse enregistrée avec succès !',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#reponseForm')[0].reset();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    let message = "";
                    if(errors){
                        $.each(errors, function(key, val){
                            message += val[0] + "<br>";
                        });
                    } else {
                        message = "Une erreur est survenue.";
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        html: message
                    });
                }
            });
        });
    </script>
@endsection
