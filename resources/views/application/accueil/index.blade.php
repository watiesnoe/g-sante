@extends('layouts.app')

@section('title_page', 'Accueil des patients')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-door-open me-2"></i>Accueil — {{ now()->translatedFormat('l d F Y') }}</h2>
            @if(auth()->user()->hasModuleAccess('urgences') && auth()->user()->can('urgences.gerer'))
                <a href="{{ route('urgences.index') }}" class="btn btn-danger ms-auto me-2"><i class="fa fa-ambulance me-1"></i> Urgence</a>
            @endif
            @can('accueil.create')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArrivee">
                    <i class="fa fa-user-plus me-1"></i> Nouvelle arrivée
                </button>
            @endcan
        </div>

        <div class="row g-2 mb-3">
            @foreach(\App\Models\Passage::STATUTS as $code => $libelle)
                @if($code !== 'trie' || $triage)
                    <div class="col-6 col-md-3 col-xl">
                        <div class="block block-rounded mb-0 text-center py-2">
                            <div class="fs-3 fw-bold text-{{ \App\Models\Passage::COULEURS[$code] === 'light' ? 'muted' : \App\Models\Passage::COULEURS[$code] }}">{{ $statsJour[$code] ?? 0 }}</div>
                            <div class="small text-muted">{{ $libelle }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">File d'attente {{ $triage ? '(par priorité de triage, puis par arrivée)' : '(par ordre d\'arrivée)' }}</h3>
                <div class="d-flex gap-2">
                    <select id="filtreService" class="form-select form-select-sm no-select2" style="width:auto">
                        <option value="">Tous les services</option>
                        @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                    </select>
                    <select id="filtreStatut" class="form-select form-select-sm no-select2" style="width:auto">
                        <option value="ouverts">En cours</option>
                        <option value="">Tous les passages du jour</option>
                    </select>
                </div>
            </div>
            <div class="block-content block-content-full">
                <table id="passagesTable" class="table table-bordered table-striped table-vcenter w-100">
                    <thead>
                        <tr>
                            <th>Arrivée</th>
                            <th>N° passage</th>
                            <th>Patient</th>
                            <th>Service</th>
                            @if($triage)<th>Priorité</th>@endif
                            <th>Attente</th>
                            <th>Statut</th>
                            <th class="text-center">Orienter vers</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Nouvelle arrivée --}}
    <div class="modal fade" id="modalArrivee" tabindex="-1">
        <div class="modal-dialog">
            <form id="formArrivee" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle arrivée</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                        <select id="patientArrivee" name="patient_id" class="form-select" style="width:100%" required></select>
                        <div class="form-text">Recherche par nom, téléphone ou n° de dossier. <a href="{{ route('patients.create') }}">Nouveau patient</a></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <select name="service_medical_id" class="form-select no-select2">
                            <option value="">—</option>
                            @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motif de la venue</label>
                        <input type="text" name="motif" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer l'arrivée</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            const triage = @json($triage);
            const colonnes = [
                { data: 'arrive_at', name: 'arrive_at' },
                { data: 'numero', name: 'numero' },
                { data: 'patient', name: 'patient', orderable: false, searchable: false },
                { data: 'service', name: 'service', orderable: false, searchable: false },
            ];
            if (triage) colonnes.push({ data: 'priorite', name: 'priorite', searchable: false });
            colonnes.push(
                { data: 'attente', name: 'attente', orderable: false, searchable: false },
                { data: 'statut', name: 'statut', searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
            );

            const table = $('#passagesTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('passages.index') }}",
                    data: (d) => { d.statut = $('#filtreStatut').val(); d.service_id = $('#filtreService').val(); }
                },
                columns: colonnes,
            });
            $('#filtreStatut, #filtreService').on('change', () => table.ajax.reload());
            setInterval(() => table.ajax.reload(null, false), 60000);

            $('#patientArrivee').select2({
                dropdownParent: $('#modalArrivee'),
                placeholder: 'Rechercher un patient…',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('patients.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({ q: params.term }),
                    processResults: (data) => ({ results: data.map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''} — ${p.telephone ?? ''}` })) })
                }
            });

            $('#formArrivee').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('passages.store') }}", $(this).serialize())
                    .done((res) => { $('#modalArrivee').modal('hide'); this.reset(); $('#patientArrivee').val(null).trigger('change'); Swal.fire('Arrivée enregistrée', res.message, 'success'); table.ajax.reload(); })
                    .fail((xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Une erreur est survenue.', 'error'));
            });

            $(document).on('click', '.btn-statut', async function () {
                const url = $(this).data('url');
                const vers = $(this).data('vers');
                let priorite = null;
                if (vers === 'trie') {
                    const r = await Swal.fire({
                        title: 'Niveau de priorité',
                        input: 'select',
                        inputOptions: { 1: 'P1 — Urgence vitale', 2: 'P2 — Urgent', 3: 'P3 — Peut attendre', 4: 'P4 — Non urgent' },
                        showCancelButton: true, confirmButtonText: 'Valider le triage', cancelButtonText: 'Annuler'
                    });
                    if (!r.isConfirmed) return;
                    priorite = r.value;
                }
                $.post(url, { _token: '{{ csrf_token() }}', vers: vers, priorite: priorite })
                    .done(() => table.ajax.reload(null, false))
                    .fail((xhr) => Swal.fire('Refusé', xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {}).flat()[0] || 'Une erreur est survenue.', 'error'));
            });
        });
    </script>
@endsection
