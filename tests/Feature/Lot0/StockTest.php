<?php

use App\Exceptions\StockInsuffisantException;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\MouvementStock;
use App\Services\StockService;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement();
    $this->stock = app(StockService::class);
    $this->med = Fabrique::medicament();
});

it('sort le lot qui périme le premier (RG-10, FEFO)', function () {
    $this->stock->entree($this->med, 10, 'L-TARD', now()->addYear(), null);
    $this->stock->entree($this->med, 10, 'L-TOT', now()->addMonth(), null);

    $mouvements = $this->stock->sortie($this->med, 4, null);

    expect($mouvements)->toHaveCount(1)
        ->and($mouvements->first()->lot->numero)->toBe('L-TOT')
        ->and($this->med->fresh()->stock)->toBe(16);
});

it('ne dispense jamais un lot périmé (RG-10)', function () {
    $this->stock->entree($this->med, 5, 'L-PERIME', now()->subDay(), null);

    expect(fn () => $this->stock->sortie($this->med, 1, null))->toThrow(StockInsuffisantException::class);
    expect($this->stock->disponible($this->med))->toBe(0);
});

it('refuse la vente du dernier flacon une seconde fois : le stock ne devient jamais négatif (REC-13, RG-09)', function () {
    $this->stock->entree($this->med, 1, 'L1', now()->addYear(), null);

    $this->stock->sortie($this->med, 1, null);

    expect(fn () => $this->stock->sortie($this->med, 1, null))->toThrow(StockInsuffisantException::class);
    expect($this->med->fresh()->stock)->toBe(0)
        ->and($this->med->lots()->where('quantite', '<', 0)->count())->toBe(0);
});

it('valide un inventaire par des mouvements d\'ajustement ; stock = somme des mouvements (REC-15, RG-08, RG-11)', function () {
    $this->stock->entree($this->med, 10, 'L1', now()->addYear(), null);
    $responsable = Fabrique::utilisateur(['stock.inventaire']);

    $inventaire = Inventaire::create(['reference' => 'INV-T-1', 'date_inventaire' => today(), 'statut' => 'brouillon', 'user_id' => $responsable->id]);
    InventaireLigne::create(['inventaire_id' => $inventaire->id, 'medicament_id' => $this->med->id, 'stock_theorique' => 10, 'stock_reel' => 7]);

    $this->actingAs($responsable)->postJson(route('inventaires.valider', $inventaire))->assertOk();

    $med = $this->med->fresh();
    expect($med->stock)->toBe(7)
        ->and((int) MouvementStock::where('medicament_id', $med->id)->sum('quantite'))->toBe(7)
        ->and((int) MouvementStock::where('medicament_id', $med->id)->where('type', 'ajustement')->sum('quantite'))->toBe(-3);
});

it('interdit de modifier ou supprimer un mouvement de stock (RG-01)', function () {
    $mouvement = $this->stock->entree($this->med, 3, 'L1', null, null);

    expect(fn () => $mouvement->update(['quantite' => 300]))->toThrow(LogicException::class)
        ->and(fn () => $mouvement->delete())->toThrow(LogicException::class);
});

it('vend une ordonnance puis annule la vente : stock remis dans le même lot, caisse neutralisée', function () {
    $this->stock->entree($this->med, 10, 'L1', now()->addYear(), null);
    $pharmacien = Fabrique::utilisateur(['ordonnances.payer']);
    $responsable = Fabrique::utilisateur(['ordonnances.payer', 'caisse.annuler']);
    Fabrique::ouvrirCaisse($pharmacien);
    Fabrique::ouvrirCaisse($responsable);
    $ordonnance = Fabrique::ordonnance($this->med, 4);

    $this->actingAs($pharmacien)
        ->postJson(route('ordonnances.payer', $ordonnance), ['medicaments' => [$this->med->id => 4]])
        ->assertOk()->assertJson(['montant' => 400]);
    expect($this->med->fresh()->stock)->toBe(6);

    $this->actingAs($responsable)
        ->postJson(route('ordonnances.annulerPaiement', $ordonnance), ['motif' => 'Erreur de produit'])
        ->assertOk();

    expect($this->med->fresh()->stock)->toBe(10)
        ->and($this->med->lots()->first()->quantite)->toBe(10)
        ->and($ordonnance->fresh()->statutordo)->toBe('impaye');
});
