# 09 — Administration

Menus : tous, plus **Paramètres** et **Utilisateurs**.

## Avant le démarrage du pilote

1. **Paramètres → Établissement** : vérifiez le type (cabinet, clinique, CSRéf, hôpital), les **modules actifs** et les **règles** (validation des clôtures, séparation des tâches, tri, modes de paiement, format de numérotation, alerte de péremption, durée d'inactivité, etc.).
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
