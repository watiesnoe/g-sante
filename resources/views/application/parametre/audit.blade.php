@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Journal d'audit
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Journal d'audit</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <p class="text-muted small">
                            Toutes les créations, modifications et annulations sur la caisse, le stock, les tickets,
                            les paiements, les patients, les consultations et les paramètres. Ce journal ne peut être
                            ni modifié ni supprimé.
                        </p>
                        <div class="table-responsive">
                            <table id="auditTable" class="table table-bordered table-striped table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Auteur</th>
                                        <th>Action</th>
                                        <th>Objet</th>
                                        <th>Détails (avant → après)</th>
                                    </tr>
                                </thead>
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
            $('#auditTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('audit.index') }}",
                order: [[0, 'desc']],
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'auteur', name: 'auteur', orderable: false },
                    { data: 'action', name: 'event' },
                    { data: 'objet', name: 'objet', orderable: false },
                    { data: 'details', name: 'details', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
