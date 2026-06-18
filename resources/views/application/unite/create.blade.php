@extends('layouts.app')

@section('titre')
    🧪 {{ isset($examen) ? 'Modifier un examen' : 'Ajouter un examen' }}
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">
                            {{ isset($examen) ? '✏️ Modifier un examen' : '➕ Ajouter un nouvel examen' }}
                        </h5>
                    </div>
                    <div class="block-content">
                        <form id="examenForm"
                              action="{{ isset($examen) ? route('examens.update', $examen->id) : route('examens.store') }}"
                              method="POST" class="mb-4">
                            @csrf
                            @if(isset($examen))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Nom de l'examen</label>
                                <input type="text" name="nom" class="form-control"
                                       value="{{ old('nom', $examen->nom ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Service médical</label>
                                <select name="service_medical_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}"
                                            {{ old('service_medical_id', $examen->service_medical_id ?? '') == $service->id ? 'selected' : '' }}>
                                            {{ $service->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $examen->description ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Prix (optionnel)</label>
                                <input type="text" name="prix" class="form-control price-input"
                                       value="{{ old('prix', $examen->prix ?? '') }}">
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill">
                                {{ isset($examen) ? '✏️ Mettre à jour' : '💾 Enregistrer' }}
                            </button>
                            <a href="{{ route('examens.index') }}" class="btn btn-secondary rounded-pill">↩️ Retour</a>
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
