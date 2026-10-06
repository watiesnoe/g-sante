@extends('layouts.app')

@section('titre','Liste des Examens prescrits')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4 text-primary">📋 Examens prescrits</h2>

        <a href="{{ route('examens.create') }}" class="btn btn-success mb-3">
            <i class="bi bi-plus-circle"></i> Nouvelle prescription
        </a>

        {{-- Les examens prescrits lors d'une même consultation sont regroupés sur
             une seule ligne, avec un bouton pour imprimer le bon d'examens complet. --}}
        <div class="table-responsive shadow-lg rounded p-2">
            <table class="table table-bordered table-striped table-hover align-middle" id="examens-table">
                <thead class="table-primary text-center text-white">
                <tr>
                    <th>Patient</th>
                    <th>Date consultation</th>
                    <th>Examens prescrits</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody class="table-light">
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modale de modification rapide d'un examen prescrit (nom + notes uniquement ;
         désactivée côté serveur dès qu'un résultat a été saisi). --}}
    <div class="modal fade" id="editExamenModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editExamenForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier l'examen prescrit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Examen</label>
                            <input type="text" name="examen" id="edit_examen_nom" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="edit_examen_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('#examens-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('prescriptions.index') }}",
                columns: [
                    { data: 'patient', name: 'patient', orderable: false, searchable: false },
                    { data: 'date', name: 'date_consultation' },
                    { data: 'examens', name: 'examens', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable:false, searchable:false }
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
                pagingType: 'simple_numbers',
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[1, 'desc']]
            });

            // Modifier un examen individuel (nom + notes), tant qu'aucun résultat n'a été saisi
            let editUrl = null;
            $(document).on('click', '.btn-edit-examen', function(){
                editUrl = $(this).data('url');
                $('#edit_examen_nom').val($(this).data('examen'));
                $('#edit_examen_notes').val($(this).data('notes'));
                $('#editExamenModal').modal('show');
            });

            $('#editExamenForm').submit(function(e){
                e.preventDefault();
                if (!editUrl) return;
                $.ajax({
                    url: editUrl,
                    type: 'PUT',
                    data: {
                        _token: "{{ csrf_token() }}",
                        examen: $('#edit_examen_nom').val(),
                        notes: $('#edit_examen_notes').val()
                    },
                    success: function(){
                        $('#editExamenModal').modal('hide');
                        $('#examens-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr){
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: xhr.responseJSON?.message || 'Impossible de modifier cet examen (un résultat a peut-être déjà été saisi).'
                        });
                    }
                });
            });

            // Supprimer un examen individuel (bouton imbriqué dans la colonne "Examens prescrits")
            $(document).on('click','.btn-delete-examen', function(){
                let url = $(this).data('url');
                if(confirm('Voulez-vous supprimer cet examen prescrit ?')){
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {_token: "{{ csrf_token() }}"},
                        success: function(){
                            $('#examens-table').DataTable().ajax.reload();
                        },
                        error: function(){
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Impossible de supprimer la prescription.'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
