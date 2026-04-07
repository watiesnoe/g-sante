<x-config-layout titre="Sécurité Sociale (Assurances)" icon="fa fa-id-card">
    <x-slot name="actions">
        <a href="{{ route('assurances.create') }}" class="btn btn-primary shadow-sm rounded-pill">
            <i class="fa fa-plus me-1"></i> Ajouter une assurance
        </a>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <i class="fa fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table id="assuranceTable" class="table table-bordered table-striped table-vcenter">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Contact</th>
                    <th>Adresse</th>
                    <th>Taux (Prise en charge)</th>
                    <th width="150" class="text-center">Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @section('scripts')
        <script>
            $(document).ready(function() {
                const table = $('#assuranceTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('assurances.index') }}",
                    columns: [
                        { data: 'nom', name: 'nom' },
                        { data: 'telephone', name: 'telephone' },
                        { data: 'adresse', name: 'adresse' },
                        { 
                            data: 'taux', 
                            name: 'taux',
                            render: function(data) {
                                return '<span class="badge bg-success">' + data + '%</span>';
                            }
                        },
                        { 
                            data: 'actions', 
                            name: 'actions', 
                            orderable: false, 
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                    }
                });

                // Handle Delete via Ajax for better UX
                $('#assuranceTable').on('click', '.delete', function() {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Êtes-vous sûr ?',
                        text: "Cette action est irréversible !",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Oui, supprimer !',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/assurances/' + id,
                                type: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function(res) {
                                    Swal.fire('Supprimé !', res.message, 'success');
                                    table.ajax.reload();
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endsection
</x-config-layout>
