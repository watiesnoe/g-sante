<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Diagnostic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Signature des consultations et addenda (EF-04.05, RG-13), recherche dans la
 * nomenclature des diagnostics (EF-04.02).
 */
class ConsultationSignatureController extends Controller
{
    public function signer(Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.edit'), 403, 'Accès non autorisé.');
        abort_if($consultation->estSignee(), 422, 'Cette consultation est déjà signée.');
        // Seul le médecin de la consultation la signe (sauf administrateur)
        abort_unless($consultation->medecin_id === Auth::id() || Auth::user()->hasRole(['admin', 'super_admin']), 403, 'Seul le médecin de la consultation peut la signer.');

        $consultation->update(['signee_at' => now(), 'signee_par' => Auth::id()]);

        return back()->with('success', 'Consultation signée. Toute correction passera désormais par un addendum.');
    }

    public function addendum(Request $request, Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.edit'), 403, 'Accès non autorisé.');
        abort_unless($consultation->estSignee(), 422, 'La consultation n\'est pas signée : modifiez-la directement.');

        $validated = $request->validate(['contenu' => 'required|string|max:5000']);
        $consultation->addenda()->create(['contenu' => $validated['contenu'], 'user_id' => Auth::id()]);

        return back()->with('success', 'Addendum ajouté.');
    }

    public function diagnostics(Request $request)
    {
        $q = trim((string) $request->q);

        return response()->json(
            Diagnostic::where('actif', true)
                ->where(fn($w) => $w->where('code', 'like', $q . '%')->orWhere('libelle', 'like', '%' . $q . '%'))
                ->orderBy('code')->limit(30)->get(['id', 'code', 'libelle'])
        );
    }
}
