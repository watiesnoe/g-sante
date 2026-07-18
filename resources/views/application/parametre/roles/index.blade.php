@extends('layouts.app')

@section('titre', 'Rôles & Permissions')

@section('content')
<div class="container mt-4">
    <div class="row">
        @include('layouts.partials.configside')
        
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                    <h3 class="block-title">🛡️ Rôles & Permissions</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('export.model', 'roles') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                            <i class="fa fa-file-excel me-1"></i> Exporter
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="roles" data-label="Rôles">
                            <i class="fa fa-file-import me-1"></i> Importer
                        </button>
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus me-1"></i> Nouveau Rôle
                        </a>
                    </div>
                </div>
                <div class="block-content">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>Libellé</th>
                                    <th>Code (Nom)</th>
                                    <th class="text-center">Utilisateurs</th>
                                    <th class="text-center">Permissions</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="fw-semibold">{{ $role->libelle ?? $role->name }}</td>
                                        <td><span class="badge bg-secondary">{{ $role->name }}</span></td>
                                        <td class="text-center">{{ $role->users->count() }}</td>
                                        <td class="text-center">{{ $role->permissions->count() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.roles.edit', $role->uuid) }}" class="btn btn-sm btn-info" title="Modifier">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            @if(!in_array($role->name, ['super_admin', 'admin']))
                                                <button type="button" class="btn btn-sm btn-danger delete-role" data-id="{{ $role->uuid }}" title="Supprimer">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun rôle trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($roles->hasPages())
                        <div class="mt-3">
                            {{ $roles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @include('layouts.partials.import_modal')
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.delete-role').click(function() {
            let id = $(this).data('id');
            if (confirm('Voulez-vous vraiment supprimer ce rôle ?')) {
                $.ajax({
                    url: '/admin/roles/' + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if(response.success) {
                            location.reload();
                        } else {
                            alert(response.message || 'Une erreur est survenue.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Une erreur est survenue.');
                    }
                });
            }
        });
    });
</script>
@endsection