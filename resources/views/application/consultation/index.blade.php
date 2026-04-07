@extends('layouts.app')

@section('titre', 'Liste des Consultations')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('consultations.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Ajouter</a>
        </div>
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Consultations</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="consultations-table">
                    <thead class="table-light">
                    <tr>

                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Ticket</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
