@extends('layouts.app')
@section('titre','Ajouter un suivi')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Ajouter un suivi pour la consultation #{{ $consultation->id }}</h2>

        <!-- Card Dashmix -->
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">Détails du suivi</h3>
            </div>
            <div class="card-body">
                <form id="suiviForm" action="{{ route('suivis.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Patient</label>
                        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                        <input type="text" class="form-control" value="{{ $patient->nom }} {{ $patient->prenom }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label>Médecin</label>
                        <input type="text" class="form-control" value="{{ $medecin->name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label>Date & Heure</label>
                        <input type="datetime-local" name="date_heure" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Motif</label>
                        <input type="text" name="motif" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Résultat <span class="text-danger">*</span></label>
                        <textarea name="resultat" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Statut</label>
                        <select name="statut" class="form-control" required>
                            <option value="prévu">Prévu</option>
                            <option value="réalisé">Réalisé</option>
                            <option value="annulé">Annulé</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">Enregistrer</button>
                        <a href="{{ route('consultations.show',$consultation->id) }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function(){
            $('#suiviForm').on('submit', function(e){
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function(response){
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('suivis.index') }}";
                        });
                    },
                    error: function(xhr){
                        if(xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value){
                                errorMsg += value[0] + "<br>";
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur de validation',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: "Une erreur est survenue !"
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
