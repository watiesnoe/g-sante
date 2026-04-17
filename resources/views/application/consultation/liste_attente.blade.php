@extends('layouts.app')

@section('titre', 'Liste d\'Attente')

@section('content')
<div class="container mt-4">
    <div class="block block-rounded shadow-sm">
        <div class="block-header block-header-default bg-primary-dark">
            <h3 class="block-title text-white">
                <i class="fa fa-users me-2"></i> File d'attente des patients
            </h3>
        </div>
        <div class="block-content block-content-full">
            <div class="table-responsive">
                <table id="waitingTable" class="table table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Âge</th>
                            <th>Motif de visite (Ticket)</th>
                            <th>Heure d'arrivée</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
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
    $('#waitingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('liste.attente') }}",
        columns: [
            { data: 'patient', name: 'patient' },
            { data: 'age', name: 'age' },
            { data: 'motif', name: 'motif' },
            { data: 'created_at', name: 'created_at', render: function(data) {
                return new Date(data).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }},
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[3, 'asc']], // Order by arrival time
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        }
    });

    // Auto refresh every 30 seconds
    setInterval(function() {
        $('#waitingTable').DataTable().ajax.reload(null, false);
    }, 30000);
});
</script>
@endsection
