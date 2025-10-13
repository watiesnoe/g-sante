<?php

use App\Models\AbreviationClinique;
use App\Models\DciReference;
use App\Models\FicheMedicament;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;
use App\Models\Symptome;
use App\Services\Referentiel\ImportGuideClinique;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\Fabrique;

/** Classeur réduit au format du fichier « Maladies, symptômes et traitements ». */
function classeurGuideClinique(bool $berrebi = false): string
{
    $onglets = [
        'Maladies' => [
            ['Chapitre', 'Maladie / syndrome', 'Symptômes', 'Signes de gravité / quand référer', 'Diagnostic', 'Traitement (MSF)', 'Prévention', 'Médicaments cités (DCI)', 'Fiche MSF'],
            ['6. Parasitaires', 'Paludisme', "paludisme simple : fièvre, frissons, sueurs; chez l'E. dlr abdominales, diarrhée, vomissements", 'convulsions; coma; DR', 'TDR du paludisme', 'ACT PO 3 j', 'moustiquaire', 'artéméther, luméfantrine', 'https://exemple.org/paludisme'],
            ['2. Respiratoire / ORL', 'Pneumonie', 'toux; fièvre souvent > 39 °C; fièvre absente ou modérée chez le SA', 'cyanose; tirage', 'clinique', 'amoxicilline PO', 'non abordée ds la fiche', 'amoxicilline', 'https://exemple.org/pneumonie'],
        ],
        'Maladie → médicaments' => [
            ['Maladie', 'Chapitre', 'Médicament (DCI)', 'Liste OMS 2025', 'Guide MSF', 'Noms', 'Arrêtés', 'Dorosz 2022', 'Posologie dans cette maladie', 'Posologie de référence', 'Vidal', 'Fiche'],
            ['Paludisme', '6. Parasitaires', 'artéméther, luméfantrine', 'Oui', 'Oui', '', '', '', 'ACT 3 j selon le protocole national', '', '', ''],
            ['Pneumonie', '2. Respiratoire / ORL', 'amoxicilline', 'Oui', 'Oui', 'CLAMOXYL®', '', 'Oui', 'E. 30 mg/kg 3x/j; A. 1 g 3x/j', '', '', ''],
        ],
        'Posologies de référence' => [
            ['Médicament (DCI)', 'Forme', 'Posologie', 'Durée', 'Contre-indications / précautions', 'Fiche MSF'],
            ['amoxicilline', 'oral', 'A. 1 g 3x/j', '5 j', 'allergie aux pénicillines', ''],
        ],
        'Index des médicaments' => [
            ['Médicament (DCI)', 'Nb', 'Maladies', 'Liste OMS 2025', 'Guide MSF', 'Noms commerciaux', 'Arrêtés', 'Dorosz 2022', 'Posologie de référence', 'Posologie Vidal', 'Fiche'],
            ['amoxicilline', 1, 'Pneumonie', 'Oui', 'Oui', 'CLAMOXYL®', '', 'Oui', '[oral] A. 1 g 3x/j', 'CLAMOXYL® : 1 g 3 fois par jour', 'https://exemple.org/amox'],
            ['artéméther, luméfantrine', 1, 'Paludisme', 'Oui', 'Oui', 'RIAMET®', '', '', '', '', ''],
        ],
        'Abréviations' => [
            ['Abréviation', 'Signification'],
            ['E.', 'enfant'], ['A.', 'adulte'], ['DR', 'détresse respiratoire'], ['dlr', 'douleur'],
        ],
    ];

    if ($berrebi) {
        // Format « Base médicaments et maladies » : colonnes Berrebi et alertes en plus
        $onglets['Maladies'][1] = array_merge($onglets['Maladies'][1], ['Fièvre au cours de la grossesse', 'OMS 2025 : molécule X retirée']);
        $onglets['Maladie → médicaments'][1] = array_merge($onglets['Maladie → médicaments'][1], ['Retiré de la liste OMS 2025']);
        $onglets['Berrebi – fiches'] = [
            ['Spécialité', 'Fiche', 'Pages', 'Lisibles', 'Démarche', 'Médicaments cités', 'Traitement', 'Maladies MSF liées'],
            ['Obstétrique', 'Fièvre au cours de la grossesse', 'p. 10–12', '3/3', 'Rechercher une pyélonéphrite ; Penser au paludisme', 'quinine (QUINIMAX)', 'Traitement OCR', ''],
        ];
        $onglets['Berrebi – ordonnances'] = [
            ['Spécialité', 'Fiche', 'DCI', 'Nom 2005', 'Posologie OCR', 'Nom Vidal', 'Statut 2022', 'OMS', 'MSF', 'Posologie réf.', 'Statut 2025'],
            ['Obstétrique', 'Fièvre au cours de la grossesse', 'quinine', 'QUINIMAX', '1 cp x 3/jour', 'QUINIMAX®', 'Arrêt de commercialisation 2017-2021', 'Oui', 'Oui', '', ''],
            ['Obstétrique', 'Fièvre au cours de la grossesse', 'paracétamol', 'DOLIPRANE', '1 g', '', 'Non retrouvé dans Vidal 2018 ni Dorosz 2022 (à vérifier : peut-être arrêté)', 'Oui', 'Oui', '', ''],
        ];
    }

    $classeur = new Spreadsheet();
    $classeur->removeSheetByIndex(0);
    foreach ($onglets as $titre => $lignes) {
        $classeur->createSheet()->setTitle($titre)->fromArray($lignes);
    }
    $fichier = tempnam(sys_get_temp_dir(), 'guide') . '.xlsx';
    (new Xlsx($classeur))->save($fichier);

    return $fichier;
}

beforeEach(function () {
    Fabrique::etablissement('csref');
    FicheMedicament::create(['nom' => 'AMOXICILLINE', 'voie' => 'oral', 'source' => 'MSF']);
    $this->ancienne = Maladie::create(['nom' => 'Konate D et all, Infection néonatale']);
    $this->ancienSymptome = Symptome::create(['nom' => 'hépatotoxicité']);
    $this->fichier = classeurGuideClinique();
});

it('importe les maladies, découpe les symptômes et supprime l\'ancien référentiel inutilisé', function () {
    $resultat = app(ImportGuideClinique::class)->importer($this->fichier, true);

    $paludisme = Maladie::where('nom', 'Paludisme')->first();
    expect($resultat['maladies'])->toBe(2)
        ->and($paludisme->chapitre)->toBe('6. Parasitaires')
        ->and($paludisme->signesGraviteListe())->toBe(['convulsions', 'coma', 'DR'])
        ->and($paludisme->symptomes->pluck('nom')->sort()->values()->all())
            ->toBe(['Diarrhée', 'Douleurs abdominales', 'Fièvre', 'Frissons', 'Sueurs', 'Vomissements']);

    // « fièvre souvent > 39 °C » relie aussi la pneumonie à « Fièvre » ; « fièvre absente… » non
    $pneumonie = Maladie::where('nom', 'Pneumonie')->first();
    expect($pneumonie->symptomes->pluck('nom'))->toContain('Fièvre', 'Fièvre souvent > 39 °C', 'Toux')
        ->and($pneumonie->prevention)->toBeNull();

    // Ancien référentiel : supprimé, car aucune consultation ne s'y réfère
    expect($this->ancienne->fresh())->toBeNull()
        ->and($this->ancienSymptome->fresh())->toBeNull()
        ->and(Maladie::count())->toBe(2);

    // Molécules : association écrite avec « + », jamais ajoutée d'office (type « option »), reliée au guide des médicaments
    $lignes = ProtocoleTraitement::where('maladie_id', $paludisme->id)->first()->lignes;
    expect($lignes->first()->nom_medicament)->toBe('artéméther + luméfantrine')
        ->and($lignes->first()->type)->toBe('option');
    $amox = DciReference::where('dci', 'amoxicilline')->first();
    expect($amox->contre_indications)->toBe('[oral] allergie aux pénicillines')
        ->and($amox->posologie_vidal)->toContain('CLAMOXYL')
        ->and($amox->fiche->nom)->toBe('AMOXICILLINE')
        ->and(AbreviationClinique::count())->toBe(4);

    // Un nouvel import ne crée pas de doublon
    app(ImportGuideClinique::class)->importer($this->fichier, true);
    expect(Maladie::count())->toBe(2)->and($paludisme->protocole->lignes()->count())->toBe(1);
});

it('reprend les alertes OMS 2025 et les fiches Berrebi, dont les posologies restent à vérifier', function () {
    $fichier = classeurGuideClinique(berrebi: true);
    $resultat = app(ImportGuideClinique::class)->importer($fichier, true);

    $paludisme = Maladie::where('nom', 'Paludisme')->first();
    $fiche = \App\Models\FicheBerrebi::where('titre', 'Fièvre au cours de la grossesse')->first();
    expect($resultat['berrebi'])->toBe(1)
        ->and($paludisme->alertesOms())->toBe(['OMS 2025 : molécule X retirée'])
        ->and($paludisme->protocole->lignes->first()->alerte)->toBe('Retiré de la liste OMS 2025')
        ->and($fiche->maladies->pluck('nom')->all())->toBe(['Paludisme'])
        ->and($fiche->ordonnances)->toHaveCount(2)
        ->and($fiche->ordonnances[0]->estObsolete())->toBeTrue()
        ->and($fiche->ordonnances[1]->estIncertain())->toBeTrue()->and($fiche->ordonnances[1]->estObsolete())->toBeFalse();

    $this->actingAs(Fabrique::utilisateur(['referentiel.view']));
    $this->get(route('guide-clinique.show', $paludisme))->assertOk()->assertSee('OMS 2025 : molécule X retirée')->assertSee('Fièvre au cours de la grossesse');
    $this->get(route('guide-clinique.berrebi', $fiche))->assertOk()->assertSee('à vérifier')->assertSee('1 cp x 3/jour')->assertSee('table-danger', false);
    $this->get(route('guide-clinique.index', ['vue' => 'berrebi', 'q' => 'grossesse']))->assertOk()->assertSee('Fièvre au cours de la grossesse');

    // Un médicament Berrebi n'est jamais proposé à la prescription
    $this->actingAs(Fabrique::utilisateur(['consultations.create']));
    $noms = collect($this->getJson(route('infectiologie.get_protocole', $paludisme->id))->json('protocoles.0.medicaments'))->pluck('dci');
    expect($noms->all())->toBe(['artéméther + luméfantrine']);
});

it('propose en consultation les signes de gravité et les médicaments du guide, sans les prescrire d\'office', function () {
    app(ImportGuideClinique::class)->importer($this->fichier, true);
    $pneumonie = Maladie::where('nom', 'Pneumonie')->first();
    $this->actingAs(Fabrique::utilisateur(['consultations.create']));

    $reponse = $this->getJson(route('infectiologie.get_protocole', $pneumonie->id))->assertOk();
    $amox = $reponse->json('protocoles.0.medicaments.0');

    expect($reponse->json('maladie.gravite'))->toBe(['cyanose', 'tirage'])
        ->and($amox['pivot']['type'])->toBe('option')
        ->and($amox['reference']['liste_oms'])->toBe('Oui')
        ->and($amox['reference']['contre_indications'])->toBe('[oral] allergie aux pénicillines');

    // Questions pour affiner : les autres symptômes des maladies compatibles
    $fievre = Symptome::where('nom', 'Fièvre')->value('id');
    $questions = collect($this->getJson(route('clinical.suggest_symptoms', ['symptomes' => [$fievre]]))->assertOk()->json())->pluck('nom');
    expect($questions)->toContain('Frissons', 'Toux')->not->toContain('Fièvre');
});

it('affiche le guide clinique et classe les maladies par symptômes retrouvés', function () {
    app(ImportGuideClinique::class)->importer($this->fichier, true);
    $this->actingAs(Fabrique::utilisateur(['referentiel.view']));
    $ids = Symptome::whereIn('nom', ['Fièvre', 'Frissons'])->pluck('id')->all();

    $this->get(route('guide-clinique.index', ['symptomes' => $ids]))->assertOk()
        ->assertSeeInOrder(['Paludisme', '2 / 6', 'Pneumonie', '1 / ']);
    $this->get(route('guide-clinique.show', Maladie::where('nom', 'Pneumonie')->first()))->assertOk()
        ->assertSee('Signes de gravité')->assertSee('allergie aux pénicillines')->assertSee('<abbr title="enfant">E.</abbr>', false);
    $this->get(route('guide-clinique.index'))->assertDontSee('Konate D');
});

it('réserve le guide clinique aux utilisateurs autorisés', function () {
    $this->actingAs(Fabrique::utilisateur([]));

    $this->get(route('guide-clinique.index'))->assertForbidden();
});
