@extends('layouts.app')

@section('titre', 'Liste des Tickets Prestation')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-list me-1"></i> Liste des Tickets Prestation
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table id="ticketsTable" class="table table-bordered table-striped table-vcenter w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Nombre de prestations</th>
                                <th>Total (XOF)</th>
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
            $('#ticketsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tickets.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'patient',
                        name: 'patient'
                    },
                    {
                        data: 'medecin',
                        name: 'medecin'
                    },
                    {
                        data: 'nombre_prestations',
                        name: 'nombre_prestations'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'date',
                        name: 'date'
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
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },

            });

            // Annulation d'un ticket : motif obligatoire (RG-01)
            $(document).on('click', '.btn-annuler-ticket', function () {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Annuler ce ticket ?',
                    html: '<small>L\'encaissement sera neutralisé en caisse. Le ticket reste visible, marqué annulé.</small>',
                    icon: 'warning',
                    input: 'text',
                    inputPlaceholder: 'Motif de l\'annulation (obligatoire)',
                    inputValidator: (value) => !value && 'Le motif est obligatoire.',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Annuler le ticket',
                    cancelButtonText: 'Fermer'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}', motif: result.value },
                        success: (res) => {
                            Swal.fire('Ticket annulé', res.message, 'success');
                            $('#ticketsTable').DataTable().ajax.reload(null, false);
                        },
                        error: (xhr) => Swal.fire('Annulation refusée', xhr.responseJSON?.message || 'Une erreur est survenue.', 'error')
                    });
                });
            });
        });
    </script>

@endsection
