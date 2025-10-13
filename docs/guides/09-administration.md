# 09 — Administration

Deux niveaux d'administration :

| | Qui | Ce qu'il fait |
|---|---|---|
| **Plateforme** | Le super-admin (vous) | Crée les espaces d'établissement et leurs propriétaires, accorde les modules, suspend ou réactive un espace, gère les rôles |
| **Établissement** | Le propriétaire (rôle « Propriétaire / administrateur ») | Configure son établissement et crée les comptes de son personnel ; il ne voit que son espace |

## Plateforme (super-admin)

Menu **Plateforme**, en haut à gauche.

1. **Nouvel espace** : renseignez l'établissement, cochez les modules accordés (pré-cochés selon le type) et indiquez le propriétaire (prénom, nom, e-mail).
2. L'écran affiche **une seule fois** l'identifiant et le **mot de passe provisoire** du propriétaire : remettez-les-lui par un moyen sûr.
3. À sa première connexion, le propriétaire doit choisir son propre mot de passe avant toute autre action.

Sur chaque ligne de la liste :
- **Entrer** : ouvrir l'espace pour l'assister ou le vérifier.
- **Configurer** : type, modules accordés, règles de fonctionnement et serveur en ligne de l'établissement. Le propriétaire ne gère que l'identité (nom, coordonnées, logo).
- **Propriétaire** : ajouter un compte propriétaire (par exemple pour un espace créé avant la plateforme).
- 🔑 : générer un nouveau mot de passe provisoire pour un propriétaire qui a perdu le sien.
- **Suspendre**, avec un motif : plus personne de l'espace ne peut travailler, et le motif s'affiche à la connexion. Les données sont conservées. **Réactiver** rend l'accès.

### Formules et abonnements

**Plateforme → Formules** : créez vos formules (par exemple Silver, Gold). Pour chacune :
- le **prix** et la **durée payée** (en mois) ;
- les **modules inclus** ;
- les **limites** : utilisateurs actifs, lits, SMS par mois (laissez vide pour illimité) ;
- les **jours de grâce** après l'échéance ;
- les **modules coupés en cas de retard**. Les urgences, l'hospitalisation et la maternité ne sont jamais coupées : des patients peuvent être en soins.

**Abonner un espace** : à sa création (choix de la formule, date de début, essai éventuel), ou ensuite par le bouton **Abonnement** de sa ligne. Les modules de l'espace deviennent ceux de la formule. **Changer de formule** garde les dates et met les modules à jour.

**Échéance** (vérifiée chaque matin à 6 h) :
1. **Rappels** par SMS au propriétaire, 15 jours puis 3 jours avant.
2. **Après l'échéance** : période de grâce ; le propriétaire voit un avertissement.
3. **Après la grâce** : les modules prévus sont coupés. Le personnel voit un bandeau d'explication. **Aucune donnée n'est supprimée.**

**Paiement reçu** : bouton **Abonnement → Enregistrer un paiement** (montant, mode, référence). L'abonnement est prolongé d'une période, sans jour perdu ni offert. Si les modules étaient coupés, la nouvelle période part du jour du paiement, et tout est rétabli immédiatement. Un **reçu numéroté** est imprimable.

Le tableau de bord de la plateforme indique les abonnements à jour, les échéances à moins de 30 jours, les retards et les recettes du mois.

Seul le super-admin gère les **rôles** (Paramètres → Rôles) : ils sont communs à tous les établissements.

## Établissement (propriétaire)

Menus : tous ceux des modules accordés, plus **Paramètres** et **Utilisateurs**.

Vous ne pouvez accorder à votre personnel que des droits que vous avez vous-même. Les droits de plateforme ne peuvent jamais être accordés.

## Avant le démarrage du pilote

1. **Paramètres → Établissement** : le propriétaire vérifie le nom, les coordonnées et le logo ; le super-admin règle type, modules et **règles de fonctionnement** (Administration → Établissements → Configurer) (validation des clôtures, séparation des tâches, tri, modes de paiement, format de numérotation, alerte de péremption, durée d'inactivité, etc.).
2. **Services, salles, lits** : créez la structure réelle (service → unité → chambre → lit).
3. **Prestations et tarifs** : saisissez les tarifs **officiels** de l'établissement, avec leur date d'entrée en vigueur. Un changement de tarif crée un nouveau tarif daté : les anciens tickets gardent leur prix.
4. **Assurances et conventions**, **règles de gratuité** : à saisir d'après les textes et conventions en vigueur.
5. **Utilisateurs** : un compte par personne, avec le bon rôle, **affecté à l'établissement**. Désactivez immédiatement le compte d'un agent qui part.
6. **Calcul des besoins** (Paramètres → Établissement) : durée de calcul de la CMM, délai de livraison, période couverte par une commande, stock de sécurité. Les valeurs par défaut (3, 1, 1 à 3 et 1 mois) sont à **confirmer avec le pharmacien** selon vos fournisseurs réels.
7. **Serveur en ligne** (quand il sera en service) : voir le guide 10, partie 9. Cochez « Publier les créneaux libres » seulement quand l'accueil est formé au traitement des demandes en ligne.
8. Pour un CSRéf : l'**identifiant DHIS2** de l'établissement (paramètre `dhis2.org_unit`), fourni par le district.

## Au quotidien

- **Caisse → Consolidation du jour** et **Supervision Caisses** : validez les clôtures et examinez les écarts.
- **Annulations** : vous seul annulez un encaissement, toujours avec un motif.
- **Dépenses** : validez celles des autres.
- **Propositions de commande** : approuvez ou rejetez celles de la pharmacie.
- **Hospitalisations → État des lits** : libérez les lits restés « Occupé » sans patient.
- **Journal d'audit** : qui a fait quoi et quand. Il ne peut pas être modifié.

## Fin de mois (CSRéf)

1. **Rapports mensuels** : générez le rapport du mois ; les chiffres viennent des saisies (consultations, accouchements, urgences, références…).
2. Vérifiez, puis **validez** et **transmettez**. Un rapport transmis est figé : toute correction crée une **nouvelle version**.
3. Exportez en CSV / DHIS2 pour le district.

## Sauvegardes

Une sauvegarde automatique est faite chaque nuit. Vérifiez **chaque semaine** qu'elle existe, et gardez une copie hors du serveur (clé USB ou disque externe rangé ailleurs). Une sauvegarde jamais testée n'est pas une sauvegarde : faites un essai de restauration avec le prestataire au début du pilote.

## Fusion de dossiers en double

**Dossiers Patients** : si deux dossiers concernent le même patient, fusionnez-les. La fusion est **réversible** et tracée.
