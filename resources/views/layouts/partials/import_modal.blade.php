{{--
    Partial réutilisable : Modal d'importation Excel/CSV + Script AJAX
    Utilisation : @include('layouts.partials.import_modal')
    Déclenchement : <button ... data-module="monModule" data-label="Mon Libellé" class="btn-open-import-modal">
--}}

<!-- Modal Importation Excel/CSV -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a73e8, #0d47a1);">
                <h5 class="modal-title text-white fw-bold" id="importExcelModalLabel">
                    <i class="fa fa-file-import me-2"></i> Importer des données
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module" id="importModule">
                <div class="modal-body p-4">

                    {{-- Download Template Link --}}
                    <div class="text-center mb-4">
                        <div class="mb-2 text-muted small">Téléchargez d'abord le modèle de fichier pour connaître le format attendu :</div>
                        <a href="#" id="downloadTemplateLink" target="_blank"
                           class="btn btn-sm btn-outline-success rounded-pill">
                            <i class="fa fa-download me-1"></i> Télécharger le modèle (.csv)
                        </a>
                    </div>

                    <hr class="my-3">

                    {{-- File Input --}}
                    <div class="mb-3">
                        <label for="excelFileInput" class="form-label fw-semibold">
                            <i class="fa fa-file-csv text-success me-1"></i> Sélectionner le fichier CSV
                        </label>
                        <input class="form-control" type="file" id="excelFileInput" name="file" accept=".csv,.txt" required>
                        <div class="form-text">Format accepté : <strong>.csv</strong> — Encodage recommandé : UTF-8</div>
                    </div>

                    {{-- Info Alert --}}
                    <div class="alert alert-info border-0 py-2 px-3 mb-0 d-flex align-items-start gap-2" style="background:#e8f4fd;">
                        <i class="fa fa-info-circle text-info mt-1 flex-shrink-0"></i>
                        <div class="small">
                            L'importation effectue une <strong>mise à jour intelligente</strong> si l'élément existe déjà,
                            sinon une <strong>création</strong>. Les relations manquantes sont créées automatiquement.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light rounded-pill" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill" id="btnSubmitImport">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="importSpinner" role="status"></span>
                        <i class="fa fa-upload me-1" id="importIcon"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        function initImportModal() {
            if (typeof window.jQuery === 'undefined') {
                setTimeout(initImportModal, 50);
                return;
            }
            var $ = window.jQuery;

            // Open modal and configure it for the clicked module
            $(document).on('click', '.btn-open-import-modal', function (e) {
                e.preventDefault();
                var module = $(this).data('module');
                var label  = $(this).data('label') || 'les données';

                $('#importModule').val(module);
                $('#importExcelModalLabel').html('<i class="fa fa-file-import me-2"></i> Importer ' + label);
                
                // Dynamically generate the template URL using Laravel route helper to support subdirectory hosting (e.g. XAMPP)
                var templateUrl = "{{ route('export.model', ':module') }}?template=1".replace(':module', module);
                $('#downloadTemplateLink').attr('href', templateUrl);
                $('#importExcelForm')[0].reset();

                $('#importExcelModal').modal('show');
            });

            // AJAX form submit (using event delegation on document for maximum reliability)
            $(document).on('submit', '#importExcelForm', function (e) {
                e.preventDefault();

                var $btn    = $('#btnSubmitImport');
                var $spinner = $('#importSpinner');
                var $icon   = $('#importIcon');
                var module  = $('#importModule').val();

                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $icon.addClass('d-none');

                // Dynamically generate the import URL using Laravel route helper
                var importUrl = "{{ route('import.model', ':module') }}".replace(':module', module);

                $.ajax({
                    url: importUrl,
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                        $icon.removeClass('d-none');

                        if (data.success) {
                            $('#importExcelModal').modal('hide');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Importation réussie !',
                                    text: data.message,
                                    timer: 2500,
                                    showConfirmButton: false
                                }).then(function () { window.location.reload(); });
                            } else {
                                alert(data.message);
                                window.location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Erreur', text: data.message || 'Une erreur est survenue.' });
                            } else {
                                alert(data.message || 'Erreur.');
                            }
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                        $icon.removeClass('d-none');

                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Erreur de communication avec le serveur.';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });
        }
        initImportModal();
    });
</script>
