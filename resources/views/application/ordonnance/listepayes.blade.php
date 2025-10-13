@extends('layouts.app')

@section('title_page', 'Ordonnances Payées / Partiellement Payées')

@section('content')
    <div class="container-fluid mt-3">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Liste des ordonnances payées ou partiellement payées</h4>
            </div>
            <div class="card-body">
                <table id="ordonnancesTables" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Date Ordonnance</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{-- DataTables remplira ici --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            let table = $('#ordonnancesTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("ordonnances.lespayer") }}', // doit pointer sur ton contrôleur
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'patient', name: 'patient' },
                    { data: 'date', name: 'date' },
                    {
                        data: 'statutordo',
                        name: 'statutordo',
                        render: function(data){
                            if(data === 'paye'){
                                return '<span class="badge bg-success">Payé</span>';
                            } else if(data === 'partiellement'){
                                return '<span class="badge bg-warning text-dark">Partiellement</span>';
                            }
                            return '<span class="badge bg-secondary">'+data+'</span>';
                        }
                    },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']]
            });

            // Suppression AJAX
            $(document).on('click', '.btn-delete', function(){
                let url = $(this).data('url');
                if(confirm("Voulez-vous vraiment supprimer cette ordonnance ?")){
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(){
                            table.ajax.reload();
                        },
                        error: function(){
                            alert("Erreur lors de la suppression !");
                        }
                    });
                }
            });
        });
    </script>
@endsection
