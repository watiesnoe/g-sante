@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Système de Santé
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">

                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('services.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i> Ajouter
                    </a>
                </div>
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">📰 Liste des services</h5>
                    </div>
                    <div class="block-content ">
                        <table id="services-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#services-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('services.index') }}",
                columns: [

                    { data: 'nom', name: 'nom' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            }).ajax.reload();
        });
    </script>
@endsection
