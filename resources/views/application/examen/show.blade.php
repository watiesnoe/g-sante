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
                    <div class="block-content">
                        <form action="" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nom de la structure</label>
                                <input type="text" class="form-control" placeholder="Ex: Centre de Santé Municipal">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" placeholder="Adresse complète">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" placeholder="+223 70 00 00 00">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Sauvegarder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
