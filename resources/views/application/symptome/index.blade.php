
@extends('layouts.app')

@section('titre','Gestion des Symptômes')

@section('content')
    <div class="content">
        <div class="row">
            @include('layouts.partials.configside')
            <div class="col-xl-9">
                <div class="block block-rounded">
                    <div class="block-header d-flex justify-content-between align-items-center">
                        <h3>Symptômes</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#symptomeModal">Ajouter Symptôme</button>
                    </div>
                    <div class="block-content">
                        <table id="symptomesTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter/Modifier -->
    <div class="modal fade" id="symptomeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter / Modifier Symptôme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="symptomeForm">
                        @csrf
                        <input type="hidden" id="symptome_id" name="symptome_id">
                        <div class="mb-3">
                            <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom du symptôme" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="description" id="description" class="form-control" placeholder="Description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            // Datatable
            var table = $('#symptomesTable').DataTable({
                processing:true,
                serverSide:true,
                ajax: "{{ route('symptomes.index') }}",
                columns:[
                    {data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false},
                    {data:'nom', name:'nom'},
                    {data:'description', name:'description'},
                    {data:'actions', name:'actions', orderable:false, searchable:false}
                ]
            });

            // Store / Update
            $('#symptomeForm').submit(function(e){
                e.preventDefault();
                let id = $('#symptome_id').val();
                let url = id ? '/symptomes/'+id : "{{ route('symptomes.store') }}";
                let type = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success:function(res){
                        Swal.fire('Succès', res.message, 'success');
                        $('#symptomeForm')[0].reset();
                        $('#symptome_id').val('');
                        $('#symptomeModal').modal('hide');
                        table.ajax.reload();
                    },
                    error:function(xhr){
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        for(let key in errors){ errorMsg += errors[key] + '\n'; }
                        Swal.fire('Erreur', errorMsg, 'error');
                    }
                });
            });

            // Edit
            $('#symptomesTable').on('click','.edit', function(){
                let id = $(this).data('id');
                $.get('/symptomes/'+id+'/edit', function(data){
                    $('#symptome_id').val(data.id);
                    $('#nom').val(data.nom);
                    $('#description').val(data.description);
                    $('#symptomeModal').modal('show');
                });
            });

            // Delete
            $('#symptomesTable').on('click','.delete', function(){
                let id = $(this).data('id');
                Swal.fire({
                    title:'Êtes-vous sûr ?',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonText:'Oui, supprimer !'
                }).then((result)=>{
                    if(result.isConfirmed){
                        $.ajax({
                            url:'/symptomes/'+id,
                            type:'DELETE',
                            data:{_token:"{{ csrf_token() }}"},
                            success:function(res){
                                Swal.fire('Supprimé', res.message, 'success');
                                table.ajax.reload();
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection
