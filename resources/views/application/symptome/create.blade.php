@extends('layouts.app')

@section('titre')
    🧪 {{ isset($famille) ? 'Modifier un examen' : 'Ajouter un examen' }}
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">
                            {{ isset($famille) ? '✏️ Modifier un famille' : '➕ Ajouter un nouvel famille' }}
                        </h5>
                    </div>
                    <div class="block-content">
                        <form id="examenForm"
                              action="{{ isset($famille) ? route('familles.update', $famille->id) : route('examens.store') }}"
                              method="POST" class="mb-4">
                            @csrf
                            @if(isset($famille))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Famille medicament</label>
                                <input type="text" name="nom" class="form-control"
                                       value="{{ old('nom', $famille->nom ?? '') }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill">
                                {{ isset($famille) ? '✏️ Mettre à jour' : '💾 Enregistrer' }}
                            </button>
                            <a href="{{ route('familles.index') }}" class="btn btn-secondary rounded-pill">↩️ Retour</a>
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
            $('#examenForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let method = form.find('input[name="_method"]').val() || 'POST';

                $.ajax({
                    url: form.attr('action'),
                    method: method,
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès ✅',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('examens.index') }}";
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
