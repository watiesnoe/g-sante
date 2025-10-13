@extends('layouts.app')

@section('titre','Liste des Ordonnances')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">📜 Liste des Ordonnances</h2>

        <a href="{{ route('ordonnances.create') }}" class="btn btn-primary mb-3">➕ Nouvelle Ordonnance</a>

        <table class="table table-bordered table-striped" id="ordonnances-table">
            <thead class="table-dark">
            <tr>
                <th>Patient</th>
                <th>Date</th>
                <th>Médicaments</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
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
                    { data: 'medicaments', name: 'medicaments', orderable:false, searchable:false },
                    { data: 'actions', name: 'actions', orderable:false, searchable:false }
                ]
            });

            // Supprimer
            $(document).on('click','.btn-delete',function(){
                let url = $(this).data('url');
                if(confirm('Voulez-vous vraiment supprimer cette ordonnance ?')){
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: {_token:"{{ csrf_token() }}"},
                        success:function(){
                            table.ajax.reload();
                            alert('Ordonnance supprimée avec succès.');
                        },
                        error:function(){
                            alert('Erreur lors de la suppression.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
