<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnLigne\Evenement;
use App\Models\EnLigne\Site;
use App\Services\EnLigne\PortailService;
use Illuminate\Http\Request;

/** Serveur en ligne : point d'échange avec les serveurs locaux (requêtes signées, voir VerifierSignatureSynchro). */
class SynchroController extends Controller
{
    public function catalogue(Request $request, PortailService $portail)
    {
        $request->validate([
            'etablissement.nom'      => 'required|string|max:255',
            'medecins'               => 'present|array',
            'medecins.*.uuid'        => 'required|uuid',
            'medecins.*.nom'         => 'required|string|max:255',
            'creneaux'               => 'present|array|max:20000',
            'creneaux.*.medecin_uuid' => 'required|uuid',
            'creneaux.*.debut'       => 'required|date',
        ]);
        $portail->publierCatalogue($this->site($request), $request->all());

        return response()->json(['ok' => true]);
    }

    /** Événements du serveur local (décisions sur les demandes) ; renvoie ceux qui sont acquis. */
    public function recevoir(Request $request, PortailService $portail)
    {
        $request->validate([
            'evenements'          => 'present|array|max:' . config('gsante.synchro.lot'),
            'evenements.*.uuid'   => 'required|uuid',
            'evenements.*.type'   => 'required|string|max:40',
            'evenements.*.donnees' => 'present|array',
        ]);
        $site = $this->site($request);
        foreach ($request->evenements as $evenement) {
            $portail->appliquer($site, $evenement);
        }
        $site->update(['derniere_synchro_at' => now()]);

        return response()->json(['acquis' => array_column($request->evenements, 'uuid')]);
    }

    /** Événements à récupérer par le serveur local, après le dernier qu'il a reçu. */
    public function envoyer(Request $request)
    {
        $apres = max(0, (int) $request->query('apres', 0));
        $evenements = Evenement::where('site_id', $this->site($request)->id)->where('id', '>', $apres)
            ->orderBy('id')->limit(config('gsante.synchro.lot'))->get(['id', 'type', 'donnees']);

        return response()->json(['evenements' => $evenements]);
    }

    private function site(Request $request): Site
    {
        return $request->attributes->get('site');
    }
}
