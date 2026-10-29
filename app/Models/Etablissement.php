<?php

namespace App\Models;

use App\Traits\Audite;
use Illuminate\Database\Eloquent\Model;

/**
 * Réglages de l'établissement (une seule ligne en base).
 *
 * Le code ne doit jamais tester le type d'établissement : il teste un module
 * (moduleActif). Le type ne sert qu'à pré-cocher les modules à la configuration.
 */
class Etablissement extends Model
{
    use Audite;
    protected $fillable = [
        'type', 'code', 'nom', 'slogan', 'adresse', 'region', 'district', 'telephone', 'email', 'site_web', 'logo',
        'modules', 'parametres', 'configure', 'actif', 'motif_suspension', 'modules_suspendus',
        'en_ligne_url', 'en_ligne_cle', 'en_ligne_secret', 'en_ligne_curseur', 'en_ligne_synchro_at', 'en_ligne_erreur',
    ];

    protected $hidden = ['en_ligne_secret'];

    protected $casts = [
        'modules'    => 'array',
        'parametres' => 'array',
        'modules_suspendus' => 'array',
        'configure'  => 'boolean',
        'actif'      => 'boolean',
        'en_ligne_secret'     => 'encrypted',
        'en_ligne_synchro_at' => 'datetime',
    ];

    /** Relié à un espace patient : serveur en ligne séparé (lot 7) ou espace intégré à cette installation. */
    public function connecteEnLigne(): bool
    {
        return $this->connecteServeurDistant() || $this->espacePatientIntegre();
    }

    /** Serveur en ligne séparé : adresse et identifiants renseignés. */
    public function connecteServeurDistant(): bool
    {
        return filled($this->en_ligne_url) && filled($this->en_ligne_cle) && filled($this->en_ligne_secret);
    }

    /** L'installation sert elle-même l'espace patient (GSANTE_ESPACE_PATIENT=true, mode local). */
    public static function espacePatientDisponible(): bool
    {
        return config('gsante.mode') === 'local' && config('gsante.espace_patient');
    }

    /**
     * Établissement visible dans l'espace patient intégré : module activé, établissement
     * actif, et pas de serveur en ligne séparé (qui reste prioritaire s'il est configuré).
     */
    public function espacePatientIntegre(): bool
    {
        return static::espacePatientDisponible() && $this->exists && $this->actif !== false
            && $this->moduleActif('espace_patient') && blank($this->en_ligne_url);
    }

    /**
     * Paramètres de comportement (cahier des charges, section 4.1) : valeur par
     * défaut selon le type d'établissement. Un paramètre « fixe » protège une
     * règle de sécurité et n'est jamais modifiable par l'établissement.
     */
    public const PARAMETRES = [
        'caisse.validation_cloture' => [
            'label'   => 'Faire valider chaque clôture de caisse par un responsable',
            'defauts' => ['cabinet' => false, 'clinique' => true, 'csref' => true, 'hopital' => true],
        ],
        'securite.separation_taches' => [
            'label'   => 'Séparation des tâches : on ne valide ni n\'annule ses propres opérations',
            'defauts' => ['cabinet' => false, 'clinique' => true, 'csref' => true, 'hopital' => true],
        ],
        'paiement.bloquant_urgence' => [
            'label'   => 'Le paiement peut bloquer la prise en charge d\'une urgence',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => false, 'hopital' => false],
            'fixe'    => true,
        ],
        'caisse.multi_guichets' => [
            'label'   => 'Plusieurs guichets de caisse (chaque session est ouverte sur un guichet)',
            'defauts' => ['cabinet' => false, 'clinique' => true, 'csref' => true, 'hopital' => true],
        ],
        'hospitalisation.niveaux' => [
            'label'   => 'Organisation des lits',
            'type'    => 'choix',
            'choix'   => ['chambre_lit' => 'Chambre → lit', 'salle_lit' => 'Salle → lit', 'service_unite_chambre_lit' => 'Service → unité → chambre → lit'],
            'defauts' => ['cabinet' => 'chambre_lit', 'clinique' => 'chambre_lit', 'csref' => 'salle_lit', 'hopital' => 'service_unite_chambre_lit'],
        ],
        'stock.multi_magasins' => [
            'label'   => 'Plusieurs magasins (pharmacie centrale et dépôts)',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => true, 'hopital' => true],
        ],
        'finance.blocage_depassement' => [
            'label'   => 'Bloquer la validation d\'une dépense qui dépasse sa ligne budgétaire (sinon simple alerte)',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => true, 'hopital' => true],
        ],
        'dhis2.org_unit' => [
            'label'   => 'Identifiant DHIS2 de l\'établissement (orgUnit, fourni par le district)',
            'type'    => 'texte',
            'defauts' => ['cabinet' => '', 'clinique' => '', 'csref' => '', 'hopital' => ''],
        ],
        'circuit.triage_actif' => [
            'label'   => 'Triage des patients à l\'arrivée (niveau de priorité)',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => true, 'hopital' => true],
        ],
        'paiement.moment' => [
            'label'   => 'Moment du paiement de la consultation',
            'type'    => 'choix',
            'choix'   => ['avant' => 'Avant la consultation (ticket)', 'apres' => 'Après la consultation'],
            'defauts' => ['cabinet' => 'apres', 'clinique' => 'avant', 'csref' => 'avant', 'hopital' => 'avant'],
        ],
        'patient.provenance' => [
            'label'   => 'Demander la provenance du patient et le CSCom d\'origine',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => true, 'hopital' => true],
        ],
        'caisse.modes' => [
            'label'   => 'Modes de paiement acceptés',
            'type'    => 'liste',
            'choix'   => CaisseMouvement::MODES,
            'defauts' => [
                'cabinet'  => ['especes', 'mobile'],
                'clinique' => ['especes', 'mobile', 'cheque', 'virement', 'carte', 'prise_en_charge'],
                'csref'    => ['especes', 'mobile', 'prise_en_charge'],
                'hopital'  => ['especes', 'mobile', 'cheque', 'virement', 'carte', 'prise_en_charge'],
            ],
        ],
        'numerotation.format' => [
            'label'   => 'Format des numéros ({CODE} établissement, {TYPE} document, {AAAA} année, {NUM} compteur)',
            'type'    => 'texte',
            'defauts' => ['cabinet' => '{CODE}-{TYPE}{AAAA}-{NUM}', 'clinique' => '{CODE}-{TYPE}{AAAA}-{NUM}', 'csref' => '{CODE}-{TYPE}{AAAA}-{NUM}', 'hopital' => '{CODE}-{TYPE}{AAAA}-{NUM}'],
        ],
        'stock.alerte_peremption_jours' => [
            'label'   => 'Alerte de péremption : nombre de jours avant la date',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 90, 'clinique' => 90, 'csref' => 90, 'hopital' => 90],
        ],
        'stock.besoins_periode_mois' => [
            'label'   => 'Besoins : nombre de mois de consommation pris pour la CMM',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 3, 'clinique' => 3, 'csref' => 3, 'hopital' => 3],
        ],
        'stock.besoins_delai_mois' => [
            'label'   => 'Besoins : délai de livraison du fournisseur (mois)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 1, 'clinique' => 1, 'csref' => 1, 'hopital' => 1],
        ],
        'stock.besoins_couverture_mois' => [
            'label'   => 'Besoins : période couverte par une commande (mois)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 1, 'clinique' => 2, 'csref' => 3, 'hopital' => 2],
        ],
        'stock.besoins_securite_mois' => [
            'label'   => 'Besoins : stock de sécurité (mois de consommation)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 1, 'clinique' => 1, 'csref' => 1, 'hopital' => 1],
        ],
        'en_ligne.rendez_vous' => [
            'label'   => 'Publier les créneaux libres sur le serveur en ligne (demandes de rendez-vous des patients)',
            'defauts' => ['cabinet' => false, 'clinique' => false, 'csref' => false, 'hopital' => false],
        ],
        'en_ligne.duree_creneau' => [
            'label'   => 'Rendez-vous en ligne : durée d\'un créneau (minutes)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 30, 'clinique' => 30, 'csref' => 30, 'hopital' => 30],
        ],
        'en_ligne.jours_publies' => [
            'label'   => 'Rendez-vous en ligne : nombre de jours à l\'avance proposés',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 14, 'clinique' => 14, 'csref' => 14, 'hopital' => 14],
        ],
        'en_ligne.delai_annulation_heures' => [
            'label'   => 'Rendez-vous en ligne : le patient peut annuler ou déplacer jusqu\'à ce délai avant (heures)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 24, 'clinique' => 24, 'csref' => 24, 'hopital' => 24],
        ],
        'securite.inactivite_minutes' => [
            'label'   => 'Déconnexion automatique après inactivité (minutes)',
            'type'    => 'nombre',
            'defauts' => ['cabinet' => 15, 'clinique' => 15, 'csref' => 15, 'hopital' => 15],
        ],
        'stock.gestion_lots' => [
            'label'   => 'Suivi du stock par lot et date de péremption',
            'defauts' => ['cabinet' => true, 'clinique' => true, 'csref' => true, 'hopital' => true],
            'fixe'    => true,
        ],
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
        'stock'           => ['label' => 'Pharmacie / Stock', 'description' => 'Médicaments, fournisseurs, commandes, inventaire',   'prefixes' => ['stock', 'fournisseurs', 'achats']],
        'paiements'       => ['label' => 'Paiements',       'description' => 'Suivi des paiements',                                  'prefixes' => ['paiements']],
        'maternity'       => ['label' => 'Maternité',       'description' => 'Suivi de grossesse et consultations prénatales',       'prefixes' => ['maternity']],
        'infectiologie'   => ['label' => 'Infectiologie',   'description' => 'Pathologies, protocoles, suivi des traitements',       'prefixes' => ['infectiologie']],
        'bloc'            => ['label' => 'Bloc opératoire', 'description' => 'Programme opératoire, comptes rendus, consommables',   'prefixes' => ['bloc']],
        'maintenance'     => ['label' => 'Maintenance',     'description' => 'Équipements, pannes, maintenance préventive',          'prefixes' => ['maintenance']],
        'urgences'        => ['label' => 'Urgences et triage', 'description' => 'Registre des urgences, priorités, décisions',        'prefixes' => ['urgences']],
        'references'      => ['label' => 'Références',      'description' => 'Références et évacuations, contre-références',         'prefixes' => ['references']],
        'gratuites'       => ['label' => 'Gratuités',       'description' => 'Règles de gratuité appliquées automatiquement',        'prefixes' => ['gratuites']],
        'budget'          => ['label' => 'Budget',          'description' => 'Lignes budgétaires, engagements, dépassements',        'prefixes' => ['budget']],
        'statistiques'    => ['label' => 'Rapports mensuels', 'description' => 'Rapport mensuel d\'activité, export vers le système national', 'prefixes' => ['rapports.mensuel', 'rapports.valider']],
        'rh'              => ['label' => 'Personnel et gardes', 'description' => 'Plannings, gardes, astreintes',                     'prefixes' => ['rh']],
        'tiers_payants'   => ['label' => 'Tiers payants',   'description' => 'Conventions, parts organismes, bordereaux, créances',  'prefixes' => ['tiers_payants']],
        'finances'        => ['label' => 'Dépenses',        'description' => 'Saisie, validation et paiement des dépenses',          'prefixes' => ['depenses']],
        'transfert'       => ['label' => 'Transferts',      'description' => 'Transferts entre médecins, services et hôpitaux',      'prefixes' => ['transferts']],
        // Sans préfixe : l'écran « Demandes en ligne » sert aussi aux établissements reliés à un serveur en ligne séparé
        'espace_patient'  => ['label' => 'Espace patient en ligne', 'description' => 'Les patients se connectent par SMS sur /patient et demandent un rendez-vous', 'prefixes' => []],
    ];

    /** Modules pré-cochés selon le type d'établissement. */
    public const PROFILS = [
        'cabinet'  => ['rendezvous', 'ordonnance', 'finances'],
        'clinique' => ['rendezvous', 'ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'transfert', 'finances', 'tiers_payants', 'rh'],
        'csref'    => ['ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'maternity', 'infectiologie', 'transfert', 'finances', 'tiers_payants', 'rh', 'urgences', 'references', 'gratuites', 'budget', 'statistiques', 'maintenance'],
        'hopital'  => ['rendezvous', 'ordonnance', 'examens', 'hospitalisation', 'stock', 'paiements', 'maternity', 'infectiologie', 'transfert', 'finances', 'tiers_payants', 'rh', 'urgences', 'references', 'gratuites', 'budget', 'statistiques', 'bloc', 'maintenance'],
    ];

    /** Clé de session de l'établissement choisi par l'utilisateur connecté. */
    public const SESSION = 'etablissement_id';

    protected static function booted(): void
    {
        static::creating(function (Etablissement $e) {
            $e->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
        static::created(function (Etablissement $e) {
            if (!$e->code) {
                $e->updateQuietly(['code' => 'ETAB' . $e->id]);
            }
        });
        static::saved(function (Etablissement $e) {
            static::oublierCourant();
            // Espace patient intégré : module retiré ou établissement suspendu → retiré aussitôt de l'espace patient
            if (static::espacePatientDisponible() && !$e->espacePatientIntegre()) {
                \App\Models\EnLigne\Site::where('uuid', $e->uuid)->update(['actif' => false]);
            }
        });
        static::deleted(fn () => static::oublierCourant());
    }

    public static function oublierCourant(): void
    {
        app()->forgetInstance('etablissement.courant');
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Requête brute (DB::table) limitée à l'établissement courant (SEC-04).
     * À utiliser à la place de DB::table() pour toute table métier.
     */
    public static function table(string $table): \Illuminate\Database\Query\Builder
    {
        $nom = trim(preg_split('/\s+as\s+/i', $table)[1] ?? $table);

        return \Illuminate\Support\Facades\DB::table($table)->where($nom . '.etablissement_id', static::courantId());
    }

    /** Comptes affectés à l'établissement courant, en requête brute. */
    public static function tableUtilisateurs(): \Illuminate\Database\Query\Builder
    {
        return \Illuminate\Support\Facades\DB::table('users')->whereIn('users.id', function ($q) {
            $q->select('user_id')->from('etablissement_user')->where('etablissement_id', static::courantId());
        });
    }

    public static function courantId(): ?int
    {
        return static::courant()->id;
    }

    /**
     * Établissement courant. Tant que rien n'est configuré, renvoie les valeurs
     * historiques de l'application avec tous les modules actifs.
     */
    public static function courant(): self
    {
        if (app()->bound('etablissement.courant')) {
            return app('etablissement.courant');
        }

        $choisi = session()->isStarted() || app()->runningUnitTests() ? session(self::SESSION) : null;
        $etablissement = ($choisi ? static::query()->find($choisi) : null) ?? static::query()->orderBy('id')->first();

        if ($etablissement) {
            app()->instance('etablissement.courant', $etablissement);

            return $etablissement;
        }

        return new static([
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
        return $this->exists && $this->configure;
    }

    /** Un module du tronc commun (hors MODULES) est toujours actif. */
    public function moduleActif(string $module): bool
    {
        return !isset(self::MODULES[$module])
            || (in_array($module, $this->modules ?? []) && !in_array($module, $this->modules_suspendus ?? []));
    }

    /** Abonnement en cours (le plus récent). */
    public function abonnement()
    {
        return $this->hasOne(Abonnement::class)->latestOfMany('id');
    }

    /** Vrai si la permission (ex. "maternity.cpn") appartient à un module désactivé. */
    public function permissionDesactivee(string $permission): bool
    {
        $prefixe = explode('.', $permission)[0];

        foreach (self::MODULES as $module => $definition) {
            if (in_array($prefixe, $definition['prefixes']) || in_array($permission, $definition['prefixes'])) {
                return !$this->moduleActif($module);
            }
        }

        return false;
    }

    /** Valeur d'un paramètre : réglage enregistré, sinon défaut du type d'établissement. */
    public function parametre(string $cle): mixed
    {
        $definition = self::PARAMETRES[$cle] ?? null;
        if (!$definition) {
            throw new \InvalidArgumentException("Paramètre inconnu : {$cle}");
        }

        $defaut = $definition['defauts'][$this->type] ?? null;
        if (!empty($definition['fixe'])) {
            return $defaut;
        }

        return array_key_exists($cle, $this->parametres ?? []) ? $this->parametres[$cle] : $defaut;
    }

    /** Chemin absolu du logo, pour les PDF. */
    public function logoPath(): string
    {
        return public_path($this->logo ?: 'image/logo/logo.png');
    }
}
