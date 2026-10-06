@extends('layouts.app')

@section('titre', 'Rendez-vous réalisés')

@section('content')
    <div class="container-fluid mt-3">
        <div class="card shadow-lg">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h4 class="m-0"><i class="fa fa-calendar-check me-2"></i>Rendez-vous réalisés</h4>
            </div>
            <div class="card-body">

                {{-- Rendez-vous déjà réalisés : historique figé, non modifiable ni supprimable
                     (voir RendezvousController::edit/update/destroy). --}}
                <table class="table table-bordered" id="rendezvous-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Patient</th>
                            <th>Médecin</th>
                            <th>Consultation</th>
                            <th>Date & Heure</th>
                            <th>Motif</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('#rendezvous-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('rendezvous.realiser') }}",
                columns: [
                    { data: 'patient', name: 'patient.nom' },
                    { data: 'medecin', name: 'medecin.name' },
                    { data: 'consultation', name: 'consultation.id' },
                    { data: 'date_heure', name: 'date_heure' },
                    { data: 'motif', name: 'motif' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
