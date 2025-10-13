@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Système de Santé
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar gauche -->
            <div class="col-xl-3 col-lg-4">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">⚙️ Menu</h3>
                    </div>
                    <div class="block-content">
                        <ul class="nav nav-pills flex-column push">
                            <li class="nav-item mb-1">
                                <a class="nav-link active" href="#">
                                    <i class="fa fa-hospital me-1"></i> Structure
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-stethoscope me-1"></i> Services médicaux
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-users me-1"></i> Utilisateurs
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-door-open me-1"></i> Salles
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-bed me-1"></i> Lits
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-vials me-1"></i> Examens
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-layer-group me-1"></i> Unités
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-users-cog me-1"></i> Famille
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-user-md me-1"></i> Spécialités
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a class="nav-link" href="#">
                                    <i class="fa fa-id-card me-1"></i> Sécurité sociale
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">⚙️ Configuration - Structure</h3>
                    </div>
                    <div class="block-content ">
                        <table id="services-table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>#</th>
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
                ajax: "{{ route('service.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'nom', name: 'nom' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
