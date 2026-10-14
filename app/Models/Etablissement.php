<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Réglages de l'établissement (une seule ligne en base).
 *
 * Le code ne doit jamais tester le type d'établissement : il teste un module
 * (moduleActif). Le type ne sert qu'à pré-cocher les modules à la configuration.
 */
class Etablissement extends Model
{
    protected $fillable = [
        'type', 'nom', 'slogan', 'adresse', 'telephone', 'email', 'site_web', 'logo', 'modules',
    ];

    protected $casts = [
        'modules' => 'array',
    ];

    public const TYPES = [
        'cabinet'  => 'Cabinet médical',
        'clinique' => 'Clinique',
        'csref'    => 'CSRéf (Centre de Santé de Référence)',
        'hopital'  => 'Hôpital',
    ];

    /**
     * Modules optionnels : clé du module (celle du menu) → libellé, description
     * et préfixes de permissions qu'il couvre. Les modules absents de cette liste
     * (patients, tickets, consultations, caisse, paramètres, utilisateurs) forment
     * le tronc commun et sont toujours actifs.
     */
    public const MODULES = [
        'rendezvous'      => ['label' => 'Rendez-vous',     'description' => 'Agenda, rendez-vous de suivi, emploi du temps',        'prefixes' => ['rendezvous']],
        'ordonnance'      => ['label' => 'Ordonnances',     'description' => 'Liste des ordonnances et encaissement à la pharmacie', 'prefixes' => ['ordonnances']],
        'examens'         => ['label' => 'Laboratoire',     'description' => 'Examens prescrits, analyses et résultats',             'prefixes' => ['examens']],
        'hospitalisation' => ['label' => 'Hospitalisation', 'description' => 'Salles, lits, séjours et paiements',                   'prefixes' => ['hospitalisations']],
        'stock'           => ['label' => 'Pharmacie / Stock', 'description' => 'Médicaments, fournisseurs, commandes, inventaire',   'prefixes' => ['stock', 'fournisseurs']],
        'paiements'       => ['label' => 'Paiements',       'description' => 'Suivi des paiements',                                  'prefixes' => ['paiements']],
        'maternity'       => ['label' => 'Maternité',       'description' => 'Suivi de grossesse et consultations prénatales',       'prefixes' => ['maternity']],
        'infectiologie'   => ['label' => 'Infectiologie',   'description' => 'Pathologies, protocoles, suivi des traitements',       'prefixes' => ['infectiologie']],
        'transfert'       => ['label' => 'Transferts',      'description' => 'Transferts entre médecins, services et hôpitaux',      'prefixes' => ['transferts']],
    ];

    /** Modules pré-cochés selon le type d'établissement. */
    public const PROFILS = [
        'cabinet'  => ['rendezvous', 'ordonnance'],
        'clinique' => ['rendezvous', 'ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'transfert'],
        'csref'    => ['rendezvous', 'ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'maternity', 'infectiologie', 'transfert'],
        'hopital'  => ['rendezvous', 'ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'maternity', 'infectiologie', 'transfert'],
    ];

    protected static ?Etablissement $courant = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::$courant = null);
        static::deleted(fn () => static::$courant = null);
    }

    /**
     * Établissement courant. Tant que rien n'est configuré, renvoie les valeurs
     * historiques de l'application avec tous les modules actifs.
     */
    public static function courant(): self
    {
        return static::$courant ??= static::query()->first() ?? new static([
            'type'      => 'clinique',
            'nom'       => 'G-SANTÉ',
            'slogan'    => 'Soins de qualité, notre priorité',
            'adresse'   => 'Ségou, Mali',
            'telephone' => '(+225) 07 00 00 00 00 / 05 00 00 00 00',
            'email'     => 'contact@g-sante.com',
            'site_web'  => 'www.g-sante.com',
            'modules'   => array_keys(self::MODULES),
        ]);
    }

    public function estConfigure(): bool
    {
        return $this->exists;
    }

    /** Un module du tronc commun (hors MODULES) est toujours actif. */
    public function moduleActif(string $module): bool
    {
        return !isset(self::MODULES[$module]) || in_array($module, $this->modules ?? []);
    }

    /** Vrai si la permission (ex. "maternity.cpn") appartient à un module désactivé. */
    public function permissionDesactivee(string $permission): bool
    {
        $prefixe = explode('.', $permission)[0];

        foreach (self::MODULES as $module => $definition) {
            if (in_array($prefixe, $definition['prefixes'])) {
                return !$this->moduleActif($module);
            }
        }

        return false;
    }

    /** Chemin absolu du logo, pour les PDF. */
    public function logoPath(): string
    {
        return public_path($this->logo ?: 'image/logo/logo.png');
    }
}
