<?php

use App\Models\Etablissement;
use App\Models\FicheMedicament;
use App\Services\Referentiel\ImportGuideMsf;
use Tests\Support\Fabrique;

/** Texte au format de pdftotext -layout du guide : sommaire, puis fiches commençant en haut de page. */
function guideFactice(int $nombre = 60, string $posologieAmox = 'Adulte : 1 g 2 fois par jour'): string
{
    $pages = ["Médicaments oraux\nMédicaments injectables\nMédicaments à usage externe et antiseptiques\nDeuxième partie\n\n                    Page 1 / 99"];
    $pages[] = "Médicaments oraux\nAMOXICILLINE oral\nDernière mise à jour : Avril 2024\n\n  Prescription sous contrôle médical\n\nAction thérapeutique\n   Antibactérien de la famille des pénicillines\n\nIndications\n   Angine streptococcique\n\nPosologie\n   {$posologieAmox}\n\n        Âge          Poids      Dose/jour\n < 3 mois           < 6 kg     125 mg x 2\n\nDurée\n   Angine : 6 jours\n\nContre-indications, effets indésirables, précautions\n   Ne pas administrer en cas d'allergie aux pénicillines.\n\nConservation\n   –       – Température inférieure à 25 °C\n\n                    Page 2 / 99";
    $pages[] = "ARTÉSUNATE (avec solvant à base\nd'arginine) injectable\n\nAction thérapeutique\n   Antipaludique\n\n                    Page 3 / 99";
    $pages[] = "ATROPINE injectable\n   Ne pas dépasser les doses recommandées, en particulier chez les enfants.\n\nAction thérapeutique\n   Anticholinergique\n\nIndications\n   Prémédication\n\n                    Page 3 / 99";
    for ($i = 1; $i <= $nombre; $i++) {
        $pages[] = "PRODUIT{$i} oral\n\nAction thérapeutique\n   Action {$i}\n\nIndications\n   Indication {$i}\n\n                    Page " . (3 + $i) . " / 99";
    }
    // Suite d'une fiche : une ligne indentée en capitales n'est pas un titre
    $pages[] = "  Allaitement : CONTRE-INDIQUÉ\n\nRemarques\n   Suite de la fiche précédente\n\n                    Page 88 / 99";
    // Solution de perfusion : commence par « Indications », sans « Action thérapeutique »
    $pages[] = "Solutions de perfusion\nRINGER LACTATE\nDernière mise à jour : Novembre 2023\n\nIndications\n   Déshydratation\n\nComposition\n   Sodium, potassium, lactate\n\n                    Page 89 / 99";
    $pages[] = "Médicaments à usage externe et\nantiseptiques\nPOVIDONE IODÉE 10%, solution aqueuse\n\nAction thérapeutique\n   Antiseptique\n\nIndications\n   Désinfection de la peau\n\n                    Page 90 / 99";
    $pages[] = "Deuxième partie\nOrganisation";

    return implode("\n", $pages);
}

it('reconnaît les fiches du guide : nom, voie, page, avertissement et rubriques, tableaux conservés', function () {
    $fiches = collect(app(ImportGuideMsf::class)->analyser(explode("\n", guideFactice(3))))->keyBy('nom');

    $amox = $fiches['AMOXICILLINE'];
    expect($amox['voie'])->toBe('oral')
        ->and($amox['page'])->toBe(2)
        ->and($amox['mention'])->toBe('Prescription sous contrôle médical')
        ->and($amox['mise_a_jour_source'])->toBe('Avril 2024')
        ->and($amox['posologie'])->toContain("< 3 mois           < 6 kg     125 mg x 2")
        ->and($amox['conservation'])->toBe('Température inférieure à 25 °C');

    expect($fiches['ATROPINE']['voie'])->toBe('injectable')
        ->and($fiches['ATROPINE']['avertissement'])->toContain('Ne pas dépasser les doses')
        ->and($fiches['POVIDONE IODÉE 10%, solution aqueuse']['voie'])->toBe('externe')
        ->and($fiches["ARTÉSUNATE (avec solvant à base d'arginine)"]['voie'])->toBe('injectable')
        ->and($fiches['RINGER LACTATE']['voie'])->toBe('perfusion')
        ->and($fiches['RINGER LACTATE']['indications'])->toBe('Déshydratation')
        ->and($fiches->keys()->filter(fn($n) => str_contains($n, 'Allaitement')))->toBeEmpty()
        ->and($fiches)->toHaveCount(8);
});

it('importe le guide, ne duplique rien à la relance et met à jour une fiche modifiée', function () {
    $fichier = tempnam(sys_get_temp_dir(), 'guide') . '.txt';
    file_put_contents($fichier, guideFactice());

    $premier = app(ImportGuideMsf::class)->importer($fichier, 'Guide test');
    expect($premier['creees'])->toBe(65);
    expect(app(ImportGuideMsf::class)->importer($fichier, 'Guide test')['inchangees'])->toBe(65);

    file_put_contents($fichier, guideFactice(60, 'Adulte : 1 g 3 fois par jour'));
    $troisieme = app(ImportGuideMsf::class)->importer($fichier, 'Guide test');
    expect($troisieme['modifiees'])->toBe(1)->and(FicheMedicament::count())->toBe(65);
    unlink($fichier);
});

it('fait valider chaque fiche par établissement ; une fiche modifiée redevient à valider', function () {
    $ici = Fabrique::etablissement('clinique');
    $fiche = FicheMedicament::create(['nom' => 'AMOXICILLINE', 'voie' => 'oral', 'source' => 'Guide test', 'posologie' => '1 g 2 fois par jour']);
    $medecin = Fabrique::utilisateur(['referentiel.view', 'referentiel.valider']);
    $infirmier = Fabrique::utilisateur(['referentiel.view']);

    $this->actingAs($infirmier)->get(route('referentiel.show', $fiche))->assertOk()->assertSee('À valider');
    $this->post(route('referentiel.valider', $fiche))->assertForbidden();

    $this->actingAs($medecin)->post(route('referentiel.valider', $fiche), ['commentaire' => 'Conforme'])->assertSessionHas('success');
    $this->get(route('referentiel.show', $fiche))->assertSee('Validée pour votre établissement');
    expect($fiche->validationCourante())->not->toBeNull();

    // Un autre établissement ne profite pas de cette validation
    $autre = Fabrique::autreEtablissement();
    Fabrique::dans($autre, fn() => expect($fiche->fresh()->validationCourante())->toBeNull());

    // Nouvelle édition de la source : la validation tombe d'elle-même
    $fiche->update(['posologie' => '1 g 3 fois par jour']);
    expect($fiche->fresh()->validationCourante())->toBeNull();
    expect(FicheMedicament::valideesIci()->count())->toBe(0);
});

it('relie un produit du stock de l\'établissement à sa fiche, jamais celui d\'un autre établissement', function () {
    Fabrique::etablissement('clinique');
    $fiche = FicheMedicament::create(['nom' => 'AMOXICILLINE', 'voie' => 'oral', 'source' => 'Guide test']);
    $amox = Fabrique::medicament('Amoxicilline 500 mg gélule');
    $autre = Fabrique::autreEtablissement();
    $ailleurs = Fabrique::dans($autre, fn() => Fabrique::medicament('Amoxicilline 250 mg'));
    $pharmacien = Fabrique::utilisateur(['referentiel.view', 'stock.medicaments']);

    $this->actingAs($pharmacien)->get(route('referentiel.show', $fiche))->assertOk()->assertSee('Amoxicilline 500 mg gélule')->assertDontSee('Amoxicilline 250 mg');
    $this->post(route('referentiel.lier', $fiche), ['medicament_id' => $amox->id, 'action' => 'lier'])->assertSessionHas('success');
    expect($amox->fresh()->fiche_medicament_id)->toBe($fiche->id);

    $this->post(route('referentiel.lier', $fiche), ['medicament_id' => $ailleurs->id, 'action' => 'lier'])->assertNotFound();
    expect(\App\Models\Medicament::sansCloisonnement()->find($ailleurs->id)->fiche_medicament_id)->toBeNull();
});

it('recherche dans le guide par nom ou indication', function () {
    Fabrique::etablissement('clinique');
    FicheMedicament::create(['nom' => 'ARTÉMÉTHER/LUMÉFANTRINE', 'voie' => 'oral', 'source' => 'Guide test', 'indications' => 'Paludisme non compliqué à P. falciparum']);
    FicheMedicament::create(['nom' => 'AMLODIPINE', 'voie' => 'oral', 'source' => 'Guide test', 'indications' => 'Hypertension artérielle']);

    $this->actingAs(Fabrique::utilisateur(['referentiel.view']));
    $this->get(route('referentiel.index', ['q' => 'paludisme']))->assertOk()->assertSee('ARTÉMÉTHER/LUMÉFANTRINE')->assertDontSee('AMLODIPINE');
    $this->actingAs(Fabrique::utilisateur())->get(route('referentiel.index'))->assertForbidden();
});
