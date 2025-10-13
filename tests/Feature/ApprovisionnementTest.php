<?php

use App\Models\Commande;
use App\Models\DemandeAchat;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\Requisition;
use App\Models\ServiceMedical;
use App\Services\BesoinsService;
use App\Services\StockService;
use Tests\Support\Fabrique;

beforeEach(fn() => Fabrique::etablissement('csref'));

it('sert une réquisition de service par une dotation sortie du stock, sans achat', function () {
    $infirmier = Fabrique::utilisateur(['stock.requisition']);
    $pharmacien = Fabrique::utilisateur(['stock.servir']);
    $service = ServiceMedical::firstOrCreate(['nom' => 'Maternité']);
    $ocytocine = Fabrique::medicament('Ocytocine 10 UI');
    $gants = Fabrique::medicament('Gants stériles');
    app(StockService::class)->entree($ocytocine, 50, 'L1', today()->addYear(), null);
    app(StockService::class)->entree($gants, 10, 'L2', today()->addYear(), null);

    $this->actingAs($infirmier)->post(route('requisitions.store'), [
        'service_medical_id' => $service->id, 'motif' => 'Dotation hebdomadaire',
        'lignes' => [['medicament_id' => $ocytocine->id, 'quantite' => 20], ['medicament_id' => $gants->id, 'quantite' => 30]],
    ])->assertSessionHas('success');
    $requisition = Requisition::sole();
    expect($requisition->statut)->toBe('soumise')->and($requisition->numero)->toContain('RQ')
        ->and(DemandeAchat::count())->toBe(0);

    // Le demandeur ne sert pas sa propre réquisition (séparation des tâches)
    $infirmier->givePermissionTo('stock.servir');
    $lignes = $requisition->lignes->pluck('id', 'medicament_id');
    $this->post(route('requisitions.servir', $requisition), ['magasin_id' => $requisition->magasin_id, 'quantites' => [$lignes[$ocytocine->id] => 20]])
        ->assertSessionHas('error');

    // On ne sert pas plus que demandé
    $this->actingAs($pharmacien)->post(route('requisitions.servir', $requisition), ['magasin_id' => $requisition->magasin_id, 'quantites' => [$lignes[$ocytocine->id] => 25]])
        ->assertSessionHas('error');

    // Gants : 10 en stock pour 30 demandés → service partiel
    $this->post(route('requisitions.servir', $requisition), [
        'magasin_id' => $requisition->magasin_id,
        'quantites' => [$lignes[$ocytocine->id] => 20, $lignes[$gants->id] => 10],
    ])->assertSessionHas('success');

    expect($requisition->fresh()->statut)->toBe('partielle')
        ->and($ocytocine->fresh()->stock)->toBe(30)
        ->and($gants->fresh()->stock)->toBe(0)
        ->and((int) MouvementStock::where('type', 'dotation')->where('source_id', $requisition->id)->sum('quantite'))->toBe(-30);

    $this->post(route('requisitions.servir', $requisition), ['magasin_id' => $requisition->magasin_id, 'quantites' => [$lignes[$gants->id] => 1]])
        ->assertSessionHas('error');
    $this->get(route('requisitions.index', ['statut' => 'toutes']))->assertOk()->assertSee($requisition->numero)->assertSee('Servie en partie');
});

it('calcule la CMM hors jours de rupture et propose stock maximum − disponible − déjà commandé', function () {
    $this->actingAs(Fabrique::utilisateur(['stock.besoins']));
    $stock = app(StockService::class);
    $amox = Fabrique::medicament('Amoxicilline 500');
    $boite = $amox->unites()->create(['nom' => 'Boîte de 100', 'facteur' => 100, 'prix_achat' => 2000, 'prix_vente' => 3000, 'is_default' => false]);

    // Il y a 90 jours : 300 entrés ; consommation de 300 en 45 jours, puis 45 jours de rupture
    $this->travelTo(now()->subDays(90));
    $stock->entree($amox, 300, 'L-A', today()->addYears(2), null);
    $this->travelTo(now()->addDays(1));
    $stock->sortie($amox, 150, null, 'Dispensations');
    $this->travelTo(now()->addDays(44));
    $stock->sortie($amox, 150, null, 'Dispensations');
    $this->travelBack();

    // Une dispensation annulée ne compte pas dans la consommation
    $source = Fabrique::ordonnance($amox, 1);
    $stock->entree($amox, 40, 'L-B', today()->addYears(2), null);
    $stock->sortie($amox, 10, $source, 'Dispensation annulée ensuite');
    $stock->annulerSource($source, 'Erreur');

    // Commande en cours : 4 boîtes de 100, dont 3 déjà reçues → 100 unités attendues
    $commande = Commande::create(['reference' => 'CMD-T', 'date_commande' => today(), 'fournisseur_id' => Fournisseur::create(['nom' => 'PPM'])->id, 'statut' => 'en_cours', 'total' => 0]);
    $commande->lignes()->create(['medicament_id' => $amox->id, 'unite_id' => $boite->id, 'quantite' => 4, 'prix_unitaire' => 2000, 'total' => 8000]);
    \Illuminate\Support\Facades\DB::table('commande_medicaments')->where('commande_id', $commande->id)->update(['quantiterecue' => 3]);

    $ligne = app(BesoinsService::class)->etat([$amox->id])->first();

    // 300 consommés en ~45 jours de disponibilité → environ 200 par mois (et non 100)
    expect($ligne['consommation'])->toBe(300)
        ->and($ligne['jours_rupture'])->toBeGreaterThanOrEqual(44)
        ->and($ligne['cmm'])->toBeGreaterThan(190.0)->toBeLessThan(210.0)
        ->and($ligne['disponible'])->toBe(40)
        ->and($ligne['en_commande'])->toBe(100)
        ->and($ligne['statut'])->toBe('a_commander');

    // Paramètres csref par défaut : délai 1 + sécurité 1 + couverture 3 = 5 mois
    $attendu = (int) ceil($ligne['cmm'] * 5 - 40 - 100);
    expect(abs($ligne['propose_base'] - $attendu))->toBeLessThanOrEqual(1);

    $this->get(route('besoins.index'))->assertOk()->assertSee('Amoxicilline 500')->assertSee('À commander');
});

it('fait passer une commande par une proposition approuvée, puis une commande par fournisseur', function () {
    $pharmacien = Fabrique::utilisateur(['stock.besoins', 'achats.demander', 'stock.commandes']);
    $responsable = Fabrique::utilisateur(['achats.approuver']);
    $para = Fabrique::medicament('Paracétamol 500');
    $para->update(['stock_min' => 100]);
    $sro = Fabrique::medicament('SRO sachet');
    $ppm = Fournisseur::create(['nom' => 'PPM']);
    $grossiste = Fournisseur::create(['nom' => 'Grossiste Bamako']);

    // Séparation des tâches active : pas de commande directe, la sélection part dans l'état des besoins
    $this->actingAs($pharmacien)->get(route('commandes.create'))->assertRedirect(route('achats.index'));
    $this->post(route('commandes.panier.bulk-ajouter'), ['medicament_ids' => [$para->uuid, $sro->uuid]])
        ->assertRedirect(route('besoins.index', ['ids' => [$para->uuid, $sro->uuid]]));
    $this->get(route('besoins.index', ['ids' => [$para->uuid, $sro->uuid]]))->assertOk()->assertSee('Paracétamol 500')->assertSee('SRO sachet');

    $this->post(route('besoins.proposer'), ['choix' => [$para->id, $sro->id], 'quantites' => [$para->id => 200, $sro->id => 50]])
        ->assertRedirect(route('achats.index'));
    $proposition = DemandeAchat::sole();
    expect($proposition->statut)->toBe('soumise')->and($proposition->lignes)->toHaveCount(2);

    // Le pharmacien ne peut pas approuver sa propre proposition, le responsable si
    $pharmacien->givePermissionTo('achats.approuver');
    $this->post(route('achats.action', [$proposition, 'approuver']))->assertSessionHas('error');
    $this->actingAs($responsable)->post(route('achats.action', [$proposition, 'approuver']))->assertSessionHas('success');

    $lignes = $proposition->lignes->pluck('id', 'medicament_id');
    $this->actingAs($pharmacien)->post(route('achats.action', [$proposition, 'commander']), [
        'fournisseurs' => [$lignes[$para->id] => $ppm->id, $lignes[$sro->id] => $grossiste->id],
    ])->assertSessionHas('success');

    $commandes = Commande::where('demande_achat_id', $proposition->id)->with('lignes')->get();
    expect($proposition->fresh()->statut)->toBe('commandee')
        ->and($commandes)->toHaveCount(2)
        ->and($commandes->pluck('fournisseur_id')->sort()->values()->all())->toBe(collect([$ppm->id, $grossiste->id])->sort()->values()->all())
        ->and($commandes->firstWhere('fournisseur_id', $ppm->id)->lignes->first()->quantite)->toBe(200);

    $this->get(route('achats.index'))->assertOk()->assertSee($commandes[0]->reference)->assertSee($commandes[1]->reference);
});

it('garde la commande directe dans un cabinet sans séparation des tâches', function () {
    Fabrique::etablissement('cabinet');
    $this->actingAs(Fabrique::utilisateur(['stock.commandes']));
    $para = Fabrique::medicament();

    $this->post(route('commandes.panier.bulk-ajouter'), ['medicament_ids' => [$para->uuid]])->assertRedirect(route('commandes.create'));
    $this->get(route('commandes.create'))->assertOk();
});
