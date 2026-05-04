@extends('layouts.app')

@section('titre', 'Modifier un Rôle')

@section('content')
<div class="container mt-4">
    <div class="row">
        @include('layouts.partials.configside')
        
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                    <h3 class="block-title">✏️ Modifier le Rôle: {{ $role->libelle ?? $role->name }}</h3>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
                <div class="block-content">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="libelle">Libellé (Nom lisible) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="libelle" name="libelle" value="{{ old('libelle', $role->libelle) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="name">Code (Nom technique) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required @if(in_array($role->name, ['super_admin', 'admin'])) readonly @endif>
                                @if(in_array($role->name, ['super_admin', 'admin']))
                                    <small class="text-warning"><i class="fa fa-exclamation-triangle"></i> Le code de ce rôle système ne peut pas être modifié.</small>
                                @endif
                            </div>
                        </div>

                        <h4 class="border-bottom pb-2 mb-3">Permissions par Module</h4>
                        
                        <div class="row">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="col-md-6 col-xl-4 mb-4">
                                    <div class="card h-100 shadow-sm border-0 bg-light">
                                        <div class="card-header bg-white fw-bold text-capitalize border-bottom-0 pt-3 pb-0">
                                            <div class="form-check">
                                                <input class="form-check-input module-checkbox" type="checkbox" id="module_{{ $module }}">
                                                <label class="form-check-label fs-5" for="module_{{ $module }}">
                                                    {{ $module }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            @foreach($modulePermissions as $permission)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input permission-checkbox module-{{ $module }}" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}" 
                                                           id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ explode('.', $permission->name)[1] ?? $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-4 mt-3">
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialiser l'état des modules au chargement
        $('.module-checkbox').each(function() {
            let moduleId = $(this).attr('id').replace('module_', '');
            let totalChecks = $('.module-' + moduleId).length;
            let checkedChecks = $('.module-' + moduleId + ':checked').length;
            
            if(totalChecks > 0 && totalChecks === checkedChecks) {
                $(this).prop('checked', true);
            }
        });

        // Sélectionner toutes les permissions d'un module
        $('.module-checkbox').change(function() {
            let moduleId = $(this).attr('id').replace('module_', '');
            $('.module-' + moduleId).prop('checked', $(this).prop('checked'));
        });
        
        // Mettre à jour la case du module si toutes ses permissions sont cochées/décochées
        $('.permission-checkbox').change(function() {
            let classes = $(this).attr('class').split(' ');
            let moduleClass = classes.find(c => c.startsWith('module-'));
            
            if (moduleClass) {
                let moduleId = moduleClass.replace('module-', '');
                let allChecked = $('.' + moduleClass + ':not(:checked)').length === 0;
                $('#module_' + moduleId).prop('checked', allChecked);
            }
        });
    });
</script>
@endsection
