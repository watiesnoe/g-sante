```blade
@extends('layouts.app')

@section('title_page', 'Résultats des examens réalisés')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📋 Résultats des examens réalisés</h4>
                    </div>
                    <div class="card-body">
                        <table id="resultatsTable" class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Examen</th>
                                    <th>Résultat</th>
                                    <th>Fichier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery + DataTables -->
    <script>
        $(function () {
            let table = $('#resultatsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('reponses.index') }}",
                columns: [
                    { data: 'patient', name: 'patient' },
                    { data: 'examen', name: 'examen' },
                    { data: 'resultat', name: 'resultat' },
                    { data: 'fichier', name: 'fichier', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    // url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                }
            });

            // Suppression avec SweetAlert2
            $(document).on('click', '.btn-delete', function () {
                let url = $(this).data('url');
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {_token: '{{ csrf_token() }}'},
                            success: function (response) {
                                Swal.fire('Supprimé !', response.message, 'success');
                                table.ajax.reload();
                            },
                            error: function () {
                                Swal.fire('Erreur', 'Impossible de supprimer', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

