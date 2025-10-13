@extends('layouts.app')

@section('titre','Liste des suivis')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">📋 Liste des suivis post-consultation</h2>

        <!-- Card Dashmix -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Suivis post-consultation</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped" id="suivis-table">
                    <thead class="table-dark">
                    <tr>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Consultation</th>
                        <th>Date & Heure</th>
                        <th>Motif</th>
                        <th>Résultat</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
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
                    { data: 'actions', name: 'actions', orderable:false, searchable:false }
                ]
            });

            // Supprimer
            $(document).on('click','.btn-delete',function(){
                let url = $(this).data('url');
                if(confirm('Voulez-vous vraiment supprimer ce suivi ?')){
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: {_token:"{{ csrf_token() }}"},
                        success:function(){
                            table.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection
