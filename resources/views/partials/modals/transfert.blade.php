<!-- Modal de Transfert -->
<div class="modal fade" id="modal-transfert" tabindex="-1" role="dialog" aria-labelledby="modal-transfert" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Transférer le Patient</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content">
                    <form id="form-transfert">
                        @csrf
                        <input type="hidden" name="patient_id" id="transfert-patient-id">
                        <input type="hidden" name="consultation_id" id="transfert-consultation-id">
                        <input type="hidden" name="hospitalisation_id" id="transfert-hospitalisation-id">

                        <div class="mb-4">
                            <label class="form-label" for="transfert-type">Type de Transfert</label>
                            <select class="form-select" id="transfert-type" name="type" required>
                                <option value="">Choisir un type...</option>
                                <option value="medecin">Vers un autre Médecin</option>
                                <option value="service">Vers un autre Service (Interne)</option>
                                <option value="hopital_externe">Vers un autre Hôpital (Externe)</option>
                            </select>
                        </div>

                        <!-- Champ pour Médecin -->
                        <div class="mb-4 d-none" id="div-dest-medecin">
                            <label class="form-label" for="dest_medecin_id">Sélectionner le Médecin</label>
                            <select class="form-select" id="dest_medecin_id" name="dest_medecin_id" style="width: 100%;">
                                <option value="">Choisir...</option>
                                @foreach(\App\Models\User::where('role', 'medecin')->where('statut', 'actif')->get() as $medecin)
                                    <option value="{{ $medecin->id }}">
                                        {{ $medecin->prenom ? $medecin->prenom . ' ' . $medecin->nom : $medecin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Champ pour Service -->
                        <div class="mb-4 d-none" id="div-dest-service">
                            <label class="form-label" for="dest_service_id">Sélectionner le Service</label>
                            <select class="form-select" id="dest_service_id" name="dest_service_id" style="width: 100%;">
                                <option value="">Choisir...</option>
                                @foreach(\App\Models\ServiceMedical::all() as $service)
                                    <option value="{{ $service->id }}">{{ $service->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Champ pour Hôpital Externe -->
                        <div class="mb-4 d-none" id="div-hopital-externe">
                            <label class="form-label" for="hopital_destination">Hôpital de Destination</label>
                            <input type="text" class="form-control" id="hopital_destination" name="hopital_destination" placeholder="Nom de l'hôpital">
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="transfert-motif">Motif du Transfert</label>
                            <textarea class="form-control" id="transfert-motif" name="motif" rows="3" required placeholder="Expliquez la raison du transfert..."></textarea>
                        </div>

                        <div class="block-content block-content-full text-end bg-body-light">
                            <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary">Confirmer le Transfert</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('transfert-type');
    const divMedecin = document.getElementById('div-dest-medecin');
    const divService = document.getElementById('div-dest-service');
    const divHopital = document.getElementById('div-hopital-externe');

    typeSelect.addEventListener('change', function() {
        divMedecin.classList.add('d-none');
        divService.classList.add('d-none');
        divHopital.classList.add('d-none');

        if (this.value === 'medecin') {
            divMedecin.classList.remove('d-none');
        } else if (this.value === 'service') {
            divService.classList.remove('d-none');
        } else if (this.value === 'hopital_externe') {
            divHopital.classList.remove('d-none');
        }
    });

    const form = document.getElementById('form-transfert');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const submitBtnHtml = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Envoi...';
        
        fetch("{{ route('transferts.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                Dashmix.helpers('jq-notify', {type: 'success', icon: 'fa fa-check me-1', message: data.message});
                setTimeout(() => window.location.reload(), 1500);
            } else {
                let msg = data.message || 'Une erreur est survenue.';
                if (data.errors) {
                    msg = Object.values(data.errors).flat().join('<br>');
                }
                Dashmix.helpers('jq-notify', {type: 'danger', icon: 'fa fa-times me-1', message: msg});
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtnHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Dashmix.helpers('jq-notify', {type: 'danger', icon: 'fa fa-times me-1', message: 'Erreur réseau ou serveur.'});
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtnHtml;
        });
    });
});

// Fonction globale pour ouvrir le modal
function openTransfertModal(patientId, consultationId = null, hospitalisationId = null) {
    document.getElementById('transfert-patient-id').value = patientId;
    document.getElementById('transfert-consultation-id').value = consultationId;
    document.getElementById('transfert-hospitalisation-id').value = hospitalisationId;
    
    // Reset form
    document.getElementById('form-transfert').reset();
    document.getElementById('transfert-type').dispatchEvent(new Event('change'));
    
    const modal = new bootstrap.Modal(document.getElementById('modal-transfert'));
    modal.show();
}
</script>
