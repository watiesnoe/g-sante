<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/** Modèle de document médical (certificat, lettre d'orientation) — EF-04.06. */
class ModeleDocument extends Model
{
    use AppartientEtablissement, Audite;

    protected $table = 'modeles_documents';

    protected $fillable = ['etablissement_id', 'type', 'titre', 'contenu', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public const TYPES = [
        'certificat'         => 'Certificat médical',
        'lettre_orientation' => 'Lettre d\'orientation',
    ];

    /** Champs utilisables dans le texte d'un modèle. */
    public const CHAMPS = [
        '{patient.nom}', '{patient.prenom}', '{patient.age}', '{patient.numero_dossier}', '{patient.genre}',
        '{medecin.nom}', '{etablissement.nom}', '{etablissement.adresse}', '{date}',
        '{consultation.date}', '{consultation.motif}', '{consultation.diagnostic}',
    ];

    /** Remplace les champs par les valeurs du patient, de la consultation et du médecin. */
    public function rendre(Patient $patient, ?Consultation $consultation, User $medecin): string
    {
        $etablissement = Etablissement::courant();

        return strtr($this->contenu, [
            '{patient.nom}'            => mb_strtoupper($patient->nom),
            '{patient.prenom}'         => $patient->prenom,
            '{patient.age}'            => (string) $patient->age,
            '{patient.numero_dossier}' => (string) $patient->numero_dossier,
            '{patient.genre}'          => $patient->genre === 'F' ? 'Mme' : 'M.',
            '{medecin.nom}'            => trim(($medecin->prenom ?? '') . ' ' . ($medecin->nom ?? $medecin->name)),
            '{etablissement.nom}'      => $etablissement->nom,
            '{etablissement.adresse}'  => (string) $etablissement->adresse,
            '{date}'                   => now()->format('d/m/Y'),
            '{consultation.date}'      => $consultation?->date_consultation ? \Illuminate\Support\Carbon::parse($consultation->date_consultation)->format('d/m/Y') : '',
            '{consultation.motif}'     => (string) ($consultation->motif ?? ''),
            '{consultation.diagnostic}' => (string) ($consultation?->diagnosticCode ? $consultation->diagnosticCode->code . ' — ' . $consultation->diagnosticCode->libelle : ($consultation->diagnostic ?? '')),
        ]);
    }

    /** Modèles de départ, à adapter par l'établissement. */
    public static function modelesParDefaut(): array
    {
        return [
            ['type' => 'certificat', 'titre' => 'Certificat médical (modèle à adapter)', 'contenu' => "Je soussigné(e), Dr {medecin.nom}, certifie avoir examiné ce jour {patient.genre} {patient.nom} {patient.prenom}, âgé(e) de {patient.age} ans (dossier {patient.numero_dossier}).\n\n[Constatations]\n\nCertificat établi à la demande de l'intéressé(e) et remis en main propre pour servir et valoir ce que de droit.\n\nFait à {etablissement.adresse}, le {date}."],
            ['type' => 'lettre_orientation', 'titre' => 'Lettre d\'orientation (modèle à adapter)', 'contenu' => "Cher(e) Confrère,\n\nJe vous adresse {patient.genre} {patient.nom} {patient.prenom}, {patient.age} ans (dossier {patient.numero_dossier}), vu(e) en consultation le {consultation.date} pour : {consultation.motif}.\n\nDiagnostic retenu : {consultation.diagnostic}\n\n[Examens et traitements réalisés]\n\nMerci de bien vouloir le/la prendre en charge pour : [motif de l'orientation].\n\nConfraternellement,\nDr {medecin.nom}\n{etablissement.nom}, le {date}"],
        ];
    }
}
