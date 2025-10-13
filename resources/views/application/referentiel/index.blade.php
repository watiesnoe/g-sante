@extends('layouts.app')

@section('title_page', 'Guide des médicaments')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <h2 class="h3 fw-bold mb-0"><i class="fa fa-book-medical me-2"></i>Guide des médicaments</h2>
                <div class="text-muted small">{{ $total }} fiches · <strong>{{ $nbValidees }}</strong> validée(s) par votre établissement</div>
            </div>
        </div>
        <div class="alert alert-light border small mb-3">
            <i class="fa fa-info-circle me-1 text-primary"></i>
            Source : {{ $sources->implode(' ; ') }}. Chaque fiche indique la page d'origine.
            Une fiche <strong>« à valider »</strong> n'a pas encore été relue par un médecin ou un pharmacien de votre établissement : vérifiez-la avant de vous y fier.
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un médicament ou une indication (ex. amoxicilline, paludisme)…" autofocus></div>
            <div class="col-md-3">
                <select name="voie" class="form-select no-select2" onchange="this.form.submit()">
                    <option value="">Toutes les voies</option>
                    @foreach(\App\Models\FicheMedicament::VOIES as $code => $libelle)<option value="{{ $code }}" @selected(request('voie') === $code)>{{ $libelle }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="etat" class="form-select no-select2" onchange="this.form.submit()">
                    <option value="">Toutes les fiches</option>
                    <option value="validees" @selected(request('etat') === 'validees')>Validées par mon établissement</option>
                    <option value="a_valider" @selected(request('etat') === 'a_valider')>À valider</option>
                    <option value="deconseilles" @selected(request('etat') === 'deconseilles')>Déconseillés (dangereux, obsolètes)</option>
                </select>
            </div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary"><i class="fa fa-search"></i></button></div>
        </form>

        <div class="block block-rounded"><div class="block-content block-content-full table-responsive">
            <table class="table table-hover table-vcenter mb-2">
                <thead><tr><th>Médicament</th><th>Voie</th><th>Action thérapeutique</th><th>État</th><th class="text-end">Page</th></tr></thead>
                <tbody>
                    @forelse($fiches as $f)
                        <tr style="cursor:pointer" onclick="window.location='{{ route('referentiel.show', $f) }}'">
                            <td><a href="{{ route('referentiel.show', $f) }}" class="fw-semibold">{{ $f->nom }}</a>
                                @if($f->avertissement)<i class="fa fa-exclamation-triangle text-warning ms-1" title="Avertissement dans la fiche"></i>@endif</td>
                            <td class="text-nowrap">{{ \App\Models\FicheMedicament::VOIES[$f->voie] ?? $f->voie }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($f->action_therapeutique, 80) }}</td>
                            <td class="text-nowrap">
                                @if($f->deconseille)<span class="badge bg-danger">Déconseillé</span>
                                @elseif($validees->contains($f->id))<span class="badge bg-success">Validée</span>
                                @else<span class="badge bg-warning text-dark">À valider</span>@endif
                            </td>
                            <td class="text-end text-muted small">p. {{ $f->page }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">Aucune fiche ne correspond à la recherche.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $fiches->links() }}
        </div></div>
    </div>
@endsection
