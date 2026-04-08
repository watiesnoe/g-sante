@extends('layouts.app')

@section('titre','Liste des Ordonnances')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('ordonnances.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-file-medical me-1"></i> Liste des Ordonnances
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter w-100" id="ordonnances-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Médicaments</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            let table = $('#ordonnances-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('ordonnances.index') }}",
                columns: [
                    { data: 'patient', name: 'consultation.patient.nom' },
                    { data: 'date', name: 'date' },
                    { data: 'medicaments', name: 'medicaments', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
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
                pagingType: 'simple_numbers'
            });

            $(document).on('click', '.btn-delete', function(){
                let url = $(this).data('url');
                if(confirm('Voulez-vous vraiment supprimer cette ordonnance ?')){
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(){
                            table.ajax.reload();
                        },
                        error: function(){
                            alert('Erreur lors de la suppression.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
