@extends('layouts.app')

@section('titre','Liste des Examens prescrits')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4 text-primary">📋 Examens prescrits</h2>

        <a href="{{ route('examens.create') }}" class="btn btn-success mb-3">
            <i class="bi bi-plus-circle"></i> Nouvelle prescription
        </a>

        <div class="table-responsive shadow-lg rounded p-2">
            <table class="table table-bordered table-striped table-hover align-middle" id="examens-table">
                <thead class="table-primary text-center text-white">
                <tr>
                    <th>Patient</th>
                    <th>Examen</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody class="table-light">
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('#examens-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('prescriptions.index') }}",
                columns: [
                    { data: 'patient', name: 'patient', orderable: false, searchable: false },
                    { data: 'examen', name: 'examen' },
                    { data: 'notes', name: 'notes' },
                    { data: 'actions', name: 'actions', orderable:false, searchable:false }
                ],
                language: {
                    emptyTable: "Aucune prescription trouvée",
                    search: "🔍 Rechercher :",
                    lengthMenu: "Afficher _MENU_ entrées",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ prescriptions",
                    infoEmpty: "Affichage de 0 à 0 sur 0 prescriptions",
                    paginate: {
                        first: "Première",
                        last: "Dernière",
                        next: "Suivant",
                        previous: "Précédent"
                    }
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[1, 'asc']]
            });

            // Supprimer prescription
            $(document).on('click','.btn-delete', function(){
                let url = $(this).data('url');
                if(confirm('Voulez-vous supprimer cette prescription ?')){
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {_token: "{{ csrf_token() }}"},
                        success: function(){
                            $('#examens-table').DataTable().ajax.reload();
                        },
                        error: function(){
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Impossible de supprimer la prescription.'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
