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
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers',
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
