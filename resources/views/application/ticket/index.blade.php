@extends('layouts.app')

@section('titre', 'Liste des Tickets Prestation')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Tickets Prestation</h5>
                <a href="{{ route('tickets.create') }}" class="btn btn-light btn-sm">➕ Nouveau Ticket</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ticketsTable" class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Nombre de prestations</th>
                            <th>Total (XOF)</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#ticketsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tickets.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'patient', name: 'patient' },
                    { data: 'nombre_prestations', name: 'nombre_prestations' },
                    { data: 'total', name: 'total' },
                    { data: 'date', name: 'date' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                dom: 'Bfrtip', // Position des boutons
                buttons: [
                    'copyHtml5',
                    'csvHtml5',
                    'excelHtml5',
                    'pdfHtml5',
                    'print'
                ],
                // language: {
                //     url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                // }
            });
        });
    </script>

@endsection
