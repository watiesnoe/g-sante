@extends(auth()->user()->can('etablissements.gerer') ? 'layouts.admin' : 'layouts.app')

@section('title_page', 'Permissions')

@section('content')
    <div class="content">
        <div class="row">
        @include(auth()->user()->can('etablissements.gerer') ? 'application.plateforme._menu' : 'layouts.partials.configside')
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title"><i class="fa fa-key me-1"></i> Permissions</h3>
                    <div class="block-options small text-muted">Gestion des permissions d'accès aux différentes fonctionnalités</div>
                </div>
                <div class="block-content">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <select id="filtreModule" class="form-select form-select-sm no-select2" style="max-width:240px">
                            <option value="">Tous les modules ({{ $lignes->count() }})</option>
                            @foreach($modules as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                        </select>
                    </div>
                    <p class="small text-muted">
                        Les permissions sont définies par l'application : chacune protège un écran ou une action précise. Pour en donner à un ensemble de comptes,
                        modifiez un <a href="{{ route('admin.roles.index') }}">rôle</a>. Celles marquées <span class="badge bg-dark">plateforme</span> sont réservées aux administrateurs de la plateforme.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter table-hover fs-sm table-admin" id="tablePermissions" style="width:100%">
                            <thead><tr><th>#</th><th>Module</th><th>Entité</th><th>Action</th><th>Nom</th><th>Rôles</th></tr></thead>
                            <tbody>
                                @foreach($lignes as $i => $l)
                                    <tr data-module="{{ $l['module'] }}">
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td><span class="badge rounded-pill bg-primary-dark text-nowrap"><i class="fa fa-puzzle-piece me-1"></i>{{ $l['module'] }}</span></td>
                                        <td><i class="fa fa-cube text-muted me-1"></i>{{ $l['entite'] }}</td>
                                        <td><span class="badge bg-secondary">{{ $l['action'] }}</span></td>
                                        <td class="text-nowrap"><code>{{ $l['nom'] }}</code> @if($l['plateforme'])<span class="badge bg-dark">plateforme</span>@endif</td>
                                        <td title="{{ $l['roles']->pluck('libelle')->implode(', ') }}">
                                            @forelse($l['roles']->take(3) as $r)
                                                <span class="badge bg-body-dark text-dark fw-normal">{{ str_replace('_', ' ', $r['code']) }}</span>
                                            @empty
                                                <span class="text-muted small">—</span>
                                            @endforelse
                                            @if($l['roles']->count() > 3)<span class="badge bg-secondary">+{{ $l['roles']->count() - 3 }}</span>@endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // Filtre par module : sur la colonne « Module » du tableau (recherche, pagination et tri par DataTables)
            $('#filtreModule').on('change', function () {
                const valeur = this.value ? '^\\s*' + $.fn.dataTable.util.escapeRegex(this.value) + '\\s*$' : '';
                const tableau = $.fn.DataTable.isDataTable('#tablePermissions') ? $('#tablePermissions').DataTable() : null;
                if (tableau) {
                    tableau.column(1).search(valeur, true, false).draw();
                } else {
                    $('#tablePermissions tbody tr').each(function () { this.style.display = !valeur || this.dataset.module === $('#filtreModule').val() ? '' : 'none'; });
                }
            });
        });
    </script>
@endsection
