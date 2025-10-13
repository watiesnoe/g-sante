@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Nomenclature des diagnostics
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Nomenclature des diagnostics ({{ $total }})</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('export.model.xlsx', ['module' => 'diagnostics', 'template' => 1]) }}" class="btn btn-sm btn-outline-secondary">Modèle de fichier</a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="diagnostics" data-label="Nomenclature des diagnostics">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        @if($total === 0)
                            <div class="alert alert-warning">
                                La nomenclature est vide. Importez la liste officielle validée (CIM-10 ou nomenclature nationale) :
                                colonnes <code>code</code>, <code>libelle</code>, <code>chapitre</code>.
                            </div>
                        @endif
                        <table id="diagnosticsTable" class="table table-bordered table-striped table-sm w-100">
                            <thead><tr><th>Code</th><th>Libellé</th><th>Chapitre</th></tr></thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partials.import_modal')
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#diagnosticsTable').DataTable({
                processing: true, serverSide: true, ajax: "{{ route('diagnostics.index') }}",
                columns: [{ data: 'code', name: 'code' }, { data: 'libelle', name: 'libelle' }, { data: 'chapitre', name: 'chapitre' }]
            });
        });
    </script>
@endsection
