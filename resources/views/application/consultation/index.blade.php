@extends('layouts.app')

@section('titre', 'Liste des Consultations')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('consultations.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-stethoscope me-1"></i> Liste des Consultations
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter w-100" id="consultations-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Ticket</th>
                                <th>Date</th>
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
        $(document).ready(function() {
            $('#consultations-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('consultations.index') }}",
                columns: [
                    { data: 'patient', name: 'patient' },
                    { data: 'medecin', name: 'medecin' },
                    { data: 'ticket', name: 'ticket' },
                    { data: 'date_consultation', name: 'date_consultation' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
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
        });
    </script>
@endsection
