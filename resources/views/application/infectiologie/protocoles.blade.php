@extends('layouts.app')

@section('titre', 'Protocoles de Traitement')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0">Protocoles de Traitement</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProtocole">
            <i class="fa fa-plus me-1"></i> Nouveau Protocole
        </button>
    </div>

    <div class="block block-rounded shadow-sm">
        <div class="block-content block-content-full">
            <table class="table table-bordered table-striped v-align-middle" id="protocoleTable">
                <thead>
                    <tr>
                        <th>Pathologie</th>
                        <th>Titre / Protocole</th>
                        <th>Traitement Principal</th>
                        <th>Signes & Diagnostics</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\ProtocoleTraitement::with('maladie')->get() as $p)
                    <tr>
                        <td><span class="badge bg-info">{{ $p->maladie->nom ?? '-' }}</span></td>
                        <td><strong>{{ $p->titre }}</strong></td>
                        <td>
                            <div class="fw-semibold text-success">{{ $p->traitement_principal }}</div>
                            <div class="small text-muted">{{ Str::limit($p->posologie_principale, 50) }}</div>
                        </td>
                        <td>
                            <div class="small"><strong>Signes:</strong> {{ Str::limit($p->signes, 40) }}</div>
                            <div class="small text-primary"><strong>Dx:</strong> {{ Str::limit($p->diagnostics, 40) }}</div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('infectiologie.protocoles.show', $p->id) }}" class="btn-sm" title="Voir les détails">
                                    <i class="fa fa-eye text-primary"></i>
                                </a>
                                <form action="{{ route('infectiologie.protocoles.destroy', $p->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Êtes-vous sûr ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm border-0 bg-transparent" title="Supprimer">
                                        <i class="fa fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Protocole -->
<div class="modal fade" id="modalProtocole" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-medical me-2"></i>Définir un protocole d'infectiologie expert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('infectiologie.protocoles.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pathologie concernée</label>
                            <select name="maladie_id" class="form-select border-primary" required>
                                <option value="">-- Choisir une maladie --</option>
                                @foreach(\App\Models\Maladie::all() as $m)
                                    <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Titre du protocole</label>
                            <input type="text" name="titre" class="form-control" placeholder="Ex: Protocole Méningites Bactériennes" required>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-stethoscope me-1"></i> Clinique & Diagnostic</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Signes cliniques</label>
                                    <textarea name="signes" class="form-control" rows="2" placeholder="Fièvre, raideur de nuque..."></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Examens paracliniques</label>
                                    <textarea name="diagnostics" class="form-control" rows="2" placeholder="LCR, PCR, Hémocultures..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3">
                                <h6 class="text-warning border-bottom pb-2 mb-3"><i class="fas fa-bacteria me-1"></i> Étiologies (Germes)</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Germes (Nourrisson/Enfant)</label>
                                    <textarea name="germes_nourrisson" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Germes (Adulte)</label>
                                    <textarea name="germes_adulte" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 border-start border-4 border-success">
                                <h6 class="text-success border-bottom pb-2 mb-3"><i class="fas fa-pills me-1"></i> Traitement Principal</h6>
                                <div class="mb-2">
                                    <label class="form-label small text-success fw-bold">Médicaments / Molécules</label>
                                    <input type="text" name="traitement_principal" class="form-control border-success">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Posologies & Durée</label>
                                    <textarea name="posologie_principale" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3">
                                <h6 class="text-secondary border-bottom pb-2 mb-3"><i class="fas fa-random me-1"></i> Traitement de secours / Alternatif</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Médicaments alternatifs</label>
                                    <input type="text" name="traitement_alternatif" class="form-control">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small">Posologies alternatives</label>
                                    <textarea name="posologie_alternative" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm p-3">
                                <h6 class="text-info border-bottom pb-2 mb-3"><i class="fas fa-capsules me-1"></i> Sélection des médicaments (Stock & Inventaire)</h6>
                                <label class="form-label small">Choisissez les médicaments enregistrés dans le système</label>
                                <select name="medicaments_ids[]" class="form-control js-select2" multiple="multiple" data-placeholder="Rechercher des médicaments...">
                                    @foreach(\App\Models\Medicament::orderBy('nom')->get() as $med)
                                        <option value="{{ $med->id }}">{{ $med->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Remarques & Observations particulières</label>
                            <textarea name="remarques" class="form-control" rows="2" placeholder="Contre-indications, durée si terrain particulier..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Enregistrer le protocole expert</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#protocoleTable').DataTable({
                language: {
                
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers',
                pageLength: 10,
                order: [[0, 'asc']]
            });

            if ($('.js-select2').length) {
                $('.js-select2').select2({
                    dropdownParent: $('#modalProtocole'),
                    width: '100%'
                });
            }
        });
    </script>
@endsection
@endsection
