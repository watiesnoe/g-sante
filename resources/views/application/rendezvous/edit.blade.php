@extends('layouts.app')

@section('titre', 'Modifier le Rendez-vous')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-calendar-edit me-2"></i> Modifier le Rendez-vous #{{ $rendezvous->id }}
            </h5>
            <div>
                <a href="{{ route('rendezvous.show', $rendezvous->uuid) }}" class="btn btn-light btn-sm">
                    <i class="fa fa-eye me-1"></i> Voir
                </a>
                <a href="{{ route('rendezvous.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rendezvous.update', $rendezvous->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- Patient --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fa fa-user me-1 text-primary"></i> Patient
                        </label>
                        <input type="text" class="form-control"
                               value="{{ $rendezvous->patient->nom ?? '' }} {{ $rendezvous->patient->prenom ?? '' }}"
                               disabled>
                        <input type="hidden" name="patient_id" value="{{ $rendezvous->patient_id }}">
                    </div>

                    {{-- Médecin --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fa fa-user-md me-1 text-primary"></i> Médecin
                        </label>
                        <select name="medecin_id" class="form-select" required>
                            <option value="">-- Sélectionner un médecin --</option>
                            @foreach($medecins as $medecin)
                                <option value="{{ $medecin->id }}"
                                    {{ $rendezvous->medecin_id == $medecin->id ? 'selected' : '' }}>
                                    {{ $medecin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date et Heure --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fa fa-calendar me-1 text-primary"></i> Date & Heure <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="date_heure" class="form-control"
                               value="{{ \Carbon\Carbon::parse($rendezvous->date_heure)->format('Y-m-d\TH:i') }}"
                               required>
                    </div>

                    {{-- Statut --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fa fa-info-circle me-1 text-primary"></i> Statut
                        </label>
                        <select name="statut" class="form-select" required>
                            <option value="prevu"    {{ $rendezvous->statut == 'prevu'    ? 'selected' : '' }}>Prévu</option>
                            <option value="en_attente" {{ $rendezvous->statut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="realise"  {{ $rendezvous->statut == 'realise'  ? 'selected' : '' }}>Réalisé</option>
                            <option value="annule"   {{ $rendezvous->statut == 'annule'   ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>

                    {{-- Motif --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="fa fa-comment me-1 text-primary"></i> Motif
                        </label>
                        <textarea name="motif" class="form-control" rows="3"
                                  placeholder="Motif du rendez-vous...">{{ old('motif', $rendezvous->motif) }}</textarea>
                    </div>

                    {{-- Consultation liée (lecture seule) --}}
                    @if($rendezvous->consultation)
                    <div class="col-12">
                        <div class="alert alert-info mb-0 d-flex align-items-center gap-2">
                            <i class="fa fa-stethoscope"></i>
                            <span>
                                Ce rendez-vous est lié à la consultation
                                <strong>#{{ $rendezvous->consultation->id }}</strong>.
                                @can('consultations.view')
                                    <a href="{{ route('consultations.show', $rendezvous->consultation->uuid) }}" class="ms-2">
                                        <i class="fa fa-external-link-alt"></i> Voir la consultation
                                    </a>
                                @endcan
                            </span>
                        </div>
                    </div>
                    @endif

                </div>

                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('rendezvous.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
