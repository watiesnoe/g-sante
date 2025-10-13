# 07 — Pharmacie

Menus : **Ordonnances**, **Stock**, **Caisse**, **Paiements fournisseurs** (règlement des commandes).

## Ouvrir sa caisse

La pharmacie encaisse elle-même : ouvrez votre caisse chaque matin (**Caisse → Ma Caisse**) et clôturez-la chaque soir. Voir guide 02.

## Délivrer une ordonnance

1. **Ordonnances → Ordonnances disponibles** : les ordonnances prescrites et pas encore délivrées.
2. Ouvrez l'ordonnance, vérifiez les produits et quantités disponibles.
3. Encaissez. Le stock est déduit automatiquement, **du lot qui périme le plus tôt** (premier périmé, premier sorti).
4. **Ordonnances payées** : l'historique.

Un produit **périmé** n'est jamais délivré : l'application ne le compte pas dans le stock disponible. Si elle indique « Stock insuffisant », c'est qu'il ne reste pas assez de produit non périmé.

## Recevoir une livraison

1. **Stock → Réceptions** : choisissez la commande.
2. Pour chaque produit, saisissez **le numéro de lot et la date de péremption** inscrits sur la boîte. Ne les inventez pas : ils servent au rappel de lots.
3. Validez : le stock augmente.

## Surveiller le stock

**Stock → Alertes de stock** : produits sous le seuil, et produits qui périment bientôt. Consultez-la chaque matin.

## Servir les services

**Stock → Réquisitions des services** : les demandes des services, à servir.

1. Cliquez **Servir** : la quantité disponible (hors périmés) s'affiche à côté de chaque ligne.
2. Ajustez les quantités servies si le stock ne suffit pas, choisissez le magasin, puis **Valider la sortie de stock**.
3. Les lots qui périment le plus tôt sortent en premier. Une réquisition que vous ne pouvez pas servir du tout se **rejette avec un motif**.

Avec la séparation des tâches, vous ne servez pas une réquisition que vous avez vous-même demandée.

## Commander chez les fournisseurs

1. **Stock → État des besoins** : pour chaque produit, la consommation moyenne mensuelle (**CMM**), le stock disponible, les mois de stock restants, ce qui est déjà commandé et la **quantité proposée**.
2. Vérifiez et corrigez les quantités, cochez les produits, puis **Créer la proposition de commande**.
3. Un **responsable approuve** la proposition (**Stock → Propositions de commande**).
4. Cliquez **Commander**, choisissez le fournisseur de chaque produit (le dernier fournisseur est proposé) : une commande est créée **par fournisseur**.

Dans une clinique, un CSRéf ou un hôpital, c'est le seul chemin vers une commande : le bouton « Commander » de la liste des médicaments envoie la sélection dans l'état des besoins. Dans un cabinet, la commande directe reste possible.

## Plusieurs magasins

Si l'établissement a un magasin central et une pharmacie de dispensation : **Stock → Magasins et transferts** pour approvisionner la pharmacie depuis le magasin.
