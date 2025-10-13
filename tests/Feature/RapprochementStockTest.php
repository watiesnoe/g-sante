<?php

use App\Models\FicheMedicament;
use App\Services\Referentiel\RapprochementStock;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('csref');
    $this->paraOral = FicheMedicament::create(['nom' => 'PARACÉTAMOL = ACÉTAMINOPHÈNE', 'voie' => 'oral', 'source' => 'MSF']);
    $this->paraInj = FicheMedicament::create(['nom' => 'PARACÉTAMOL = ACÉTAMINOPHÈNE', 'voie' => 'injectable', 'source' => 'MSF']);
    $this->ibu = FicheMedicament::create(['nom' => 'IBUPROFÈNE', 'voie' => 'oral', 'source' => 'MSF']);
    $this->aciOph = FicheMedicament::create(['nom' => 'ACICLOVIR, pommade ophtalmique', 'voie' => 'externe', 'source' => 'MSF']);
    $this->cetirizine = FicheMedicament::create(['nom' => 'CÉTIRIZINE', 'voie' => 'oral', 'source' => 'MSF']);

    $classeur = new Spreadsheet();
    $classeur->getActiveSheet()->setTitle(RapprochementStock::ONGLET_VIDAL)->fromArray([
        ['Nom (Vidal 2018)', 'DCI'],
        ['DAFALGAN® 500 mg', 'paracétamol'], ['PERFALGAN® 1000 mg', 'paracétamol'],
        ['ANTARÈNE® gel', 'ibuprofène'], ['ACTIVIR®', 'aciclovir'],
        ['DOUBLON® A', 'paracétamol'], ['DOUBLON® B', 'ibuprofène'],
    ]);
    $this->fichier = tempnam(sys_get_temp_dir(), 'vidal') . '.xlsx';
    (new Xlsx($classeur))->save($this->fichier);
});

it('relie les produits du stock à la fiche de la bonne voie, et laisse les cas douteux à relier à la main', function () {
    $produits = collect([
        'DAFALGAN 500 mg', 'DAFALGAN Injectable', 'PERFALGAN 1000 mg', 'ANTARÈNE Gel', 'ACTIVIR',
        'DOUBLON C', 'Cétirizine', 'DOLIPRANE Suppositoires', 'Produit inconnu',
    ])->mapWithKeys(fn($nom) => [$nom => Fabrique::medicament($nom)]);
    $dejaRelie = Fabrique::medicament('Ibuprofène maison');
    $dejaRelie->forceFill(['fiche_medicament_id' => $this->cetirizine->id])->save();

    $r = app(RapprochementStock::class)->relier($this->fichier);
    $fiche = fn($nom) => $produits[$nom]->fresh()->fiche_medicament_id;

    expect($fiche('DAFALGAN 500 mg'))->toBe($this->paraOral->id)          // nom Vidal
        ->and($fiche('DAFALGAN Injectable'))->toBe($this->paraInj->id)    // marque, forme injectable
        ->and($fiche('Cétirizine'))->toBe($this->cetirizine->id)          // nom = DCI
        ->and($fiche('ANTARÈNE Gel'))->toBeNull()                         // gel : pas de fiche externe d'ibuprofène
        ->and($fiche('ACTIVIR'))->toBeNull()                              // fiche ophtalmique, produit qui ne l'est pas
        ->and($fiche('DOUBLON C'))->toBeNull()                            // marque à deux DCI
        ->and($fiche('DOLIPRANE Suppositoires'))->toBeNull()              // rectal : aucune fiche rectale
        ->and($fiche('Produit inconnu'))->toBeNull()
        ->and($dejaRelie->fresh()->fiche_medicament_id)->toBe($this->cetirizine->id) // lien existant jamais modifié
        ->and($r['deja'])->toBe(1)
        ->and(array_keys($r['non_relies']))->toContain('ACTIVIR', 'Produit inconnu');
});

it('ne modifie rien en mode essai', function () {
    $produit = Fabrique::medicament('DAFALGAN 500 mg');

    $r = app(RapprochementStock::class)->relier($this->fichier, true);

    expect($r['relies'])->toBe(1)->and($produit->fresh()->fiche_medicament_id)->toBeNull();
});
