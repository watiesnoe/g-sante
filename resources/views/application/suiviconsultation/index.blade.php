@extends('layouts.app')

@section('titre','Liste des suivis')

@section('content')
    <div class="container mt-4">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-notes-medical me-1"></i> Suivis post-consultation
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter w-100" id="suivis-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Consultation</th>
                                <th>Date &amp; Heure</th>
                                <th>Motif</th>
                                <th>Résultat</th>
                                <th>Statut</th>
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
        $(function(){
            let table = $('#suivis-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('suivis.index') }}",
                columns: [
                    { data: 'patient', name: 'patient.nom' },
                    { data: 'medecin', name: 'medecin.name' },
                    { data: 'consultation', name: 'consultation.id' },
                    { data: 'date_heure', name: 'date_heure' },
                    { data: 'motif', name: 'motif' },
                    { data: 'resultat', name: 'resultat' },
                    { data: 'statut', name: 'statut' },
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
                if(confirm('Voulez-vous vraiment supprimer ce suivi ?')){
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(){
                            table.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection
