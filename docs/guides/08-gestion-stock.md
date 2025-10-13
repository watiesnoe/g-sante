# 08 — Gestion du stock

Menu : **Stock**.

## Référentiel

- **Familles** et **Unités** : à créer avant les médicaments. Le prix de vente et d'achat peut être fixé par unité.
- **Médicaments** : nom, famille, unité, seuil d'alerte.
- **Fournisseurs**.

## Approvisionnement

1. **Réquisitions des services** : les services demandent à la pharmacie, qui les sert depuis son stock (sortie « dotation »). Ce n'est pas un achat.
2. **État des besoins** : la quantité à commander est calculée sur la consommation moyenne mensuelle (CMM) des derniers mois, jours de rupture exclus :
   - point de commande = CMM × (délai de livraison + stock de sécurité) ;
   - stock maximum = point de commande + CMM × période couverte ;
   - quantité proposée = stock maximum − disponible − déjà commandé.

   Un produit sans consommation enregistrée utilise son seuil d'alerte saisi à la main.
3. **Propositions de commande** : créées depuis l'état des besoins, approuvées par un responsable, puis transformées en une commande par fournisseur.
4. **Commandes** : vérifiez les prix, envoyez le bon de commande au fournisseur.
5. **Réceptions** : saisissez **numéro de lot et date de péremption** pour chaque produit reçu.

## Magasins et transferts

**Magasins et transferts** : le stock est suivi par magasin. Un transfert sort les produits du magasin d'origine (lots les plus proches de la péremption d'abord) et les entre, avec les mêmes lots, dans le magasin de destination.

## Inventaire

1. **📋 Inventaire** : comptez physiquement chaque produit.
2. Saisissez la quantité comptée ; l'écart avec le stock théorique s'affiche.
3. Tant qu'il est en **brouillon**, l'inventaire peut être corrigé. Une fois **validé**, il ne se modifie plus et les ajustements sont appliqués.

## Règles

- Le stock n'est jamais modifié à la main : toute variation est un **mouvement** (entrée, sortie, transfert, ajustement, retour) daté et signé.
- Produits périmés : retirez-les du lot concerné avec le motif « périmé ».
