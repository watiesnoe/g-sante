/**
 * CRUD Helper for Ges-Santé
 * Centralizes DataTable initialization and AJAX CRUD operations.
 */
window.CrudHelper = {
    init: function(config) {
        // Default configuration
        const defaults = {
            tableId: '#crudTable',
            formId: '#crudForm',
            modalId: '#crudModal',
            modalLabel: '#modalTitle',
            btnAddId: '#btnAdd',
            btnSaveId: '#btnSave',
            hiddenId: '#id',
            editClass: '.edit',
            deleteClass: '.delete',
            viewClass: '.view',
            addTitle: 'Ajouter',
            editTitle: 'Modifier',
            viewTitle: 'Détails',
            csrfToken: $('meta[name="csrf-token"]').attr('content'),
            languageUrl: '/admin/js/plugins/datatables/i18n/fr-FR.json'
        };

        const settings = $.extend({}, defaults, config);

        // 1. Initialize DataTable
        const table = $(settings.tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: settings.ajaxData
                ? { url: settings.ajaxUrl, data: settings.ajaxData }
                : settings.ajaxUrl,
            columns: settings.columns,
            language: { 
                url: settings.languageUrl,
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>'
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12'i><'col-sm-12'p>>",
            pagingType: 'simple_numbers'
        });

        // 2. Add Button Click
        $(settings.btnAddId).click(function() {
            $(settings.formId)[0].reset();
            $(settings.hiddenId).val('');
            $(settings.formId).find('input, textarea, select').prop('disabled', false);
            
            // Reset Select2 if present
            if ($.fn.select2) {
                $(settings.formId).find('select').val(null).trigger('change');
            }

            if (settings.onAdd) settings.onAdd();
            
            $(settings.modalLabel).text(settings.addTitle);
            // 🔓 Réinitialiser le bouton (au cas où il était désactivé)
            $(settings.btnSaveId).prop('disabled', false).text('Ajouter').show();
            $(settings.modalId).modal('show');
        });

        // 3. Edit/View Button Click
        const handleShow = function(id, isReadOnly) {
            const editUrl = settings.showUrl ? settings.showUrl.replace(':id', id) : (settings.baseUrl + '/' + id);

            $.get(editUrl, function(data) {
                $(settings.formId)[0].reset();
                $(settings.hiddenId).val(data.uuid || data.id);
                
                // Map data to form fields
                if (settings.mapData) {
                    settings.mapData(data);
                } else {
                    for (let key in data) {
                        let field = $(settings.formId).find(`[name="${key}"]`);
                        if (field.length) field.val(data[key]);
                    }
                }

                if (isReadOnly) {
                    $(settings.modalLabel).text(settings.viewTitle);
                    $(settings.btnSaveId).hide();
                    $(settings.formId).find('input, textarea, select').prop('disabled', true);
                } else {
                    $(settings.modalLabel).text(settings.editTitle);
                    // 🔓 Réinitialiser le bouton (au cas où il était désactivé)
                    $(settings.btnSaveId).prop('disabled', false).text('Enregistrer').show();
                    $(settings.formId).find('input, textarea, select').prop('disabled', false);
                }
                
                $(settings.modalId).modal('show');
            });
        };

        $(settings.tableId).on('click', settings.editClass, function() {
            handleShow($(this).data('id'), false);
        });

        $(settings.tableId).on('click', settings.viewClass, function() {
            handleShow($(this).data('id'), true);
        });

        // 4. Form Submit (Store & Update)
        $(settings.formId).submit(function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn  = $(settings.btnSaveId);
            const id    = $(settings.hiddenId).val();

            // Fallback storeUrl → baseUrl (évite url = undefined)
            const storeUrl = settings.storeUrl || settings.baseUrl;
            const url  = id ? (settings.updateUrl ? settings.updateUrl.replace(':id', id) : settings.baseUrl + '/' + id)
                           : storeUrl;
            const type = id ? 'PUT' : 'POST';

            // 🔒 Anti-double-soumission : désactiver le bouton
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...');

            $.ajax({
                url: url,
                type: type,
                data: $form.serialize(),
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: res.message || res.success,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $(settings.modalId).modal('hide');
                    table.ajax.reload();
                    // Le bouton sera réinitialisé à la prochaine ouverture du modal
                },
                error: function(xhr) {
                    // 🔓 Réactiver le bouton en cas d'erreur
                    $btn.prop('disabled', false).text('Enregistrer');
                    let errData = xhr.responseJSON || {};
                    let errors = errData.errors || { error: [errData.message || 'Une erreur est survenue'] };
                    let msg = '';
                    for (let k in errors) msg += errors[k] + '\n';
                    Swal.fire('Erreur', msg, 'error');
                }
            });
        });

        // 5. Delete Action
        $(settings.tableId).on('click', settings.deleteClass, function() {
            const id = $(this).data('id');
            const deleteUrl = settings.deleteUrl ? settings.deleteUrl.replace(':id', id) : (settings.baseUrl + '/' + id);

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: { _token: settings.csrfToken },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Supprimé',
                                text: res.message || res.success,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        },
                        error: function() {
                            Swal.fire('Erreur', "Impossible de supprimer l'élément.", 'error');
                        }
                    });
                }
            });
        });

        return table;
    }
};
