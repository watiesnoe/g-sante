@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Système de Santé
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">📰 Formulaire d'ajout des services</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <a href="{{ route('services.index') }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                                Voir liste
                            </a>
                        </div>
                    </div>
                    <div class="block-content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('services.store') }}" id="serviceForm"  class="mb-2" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom du service</label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                       id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description (optionnelle)</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description">{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Enregistrer</button>
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
            $('#serviceForm').on('submit', function(e) {
                e.preventDefault(); // Bloque le submit classique

                let formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès !',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });

                            // Reset du formulaire
                            $('#serviceForm')[0].reset();

                            // Optionnel : rafraîchir la table AJAX si tu utilises DataTables
                            // if($('#services-table').length) {
                            //     $('#services-table').DataTable().ajax.reload();
                            // }
                        }
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
