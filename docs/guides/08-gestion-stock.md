# 08 — Gestion du stock

Menu : **Stock**.

## Référentiel

- **Familles** et **Unités** : à créer avant les médicaments. Le prix de vente et d'achat peut être fixé par unité.
- **Médicaments** : nom, famille, unité, seuil d'alerte.
- **Fournisseurs**.

## Approvisionnement

1. **Demandes d'achat** : les services expriment leurs besoins ; le responsable approuve.
2. **Commandes** : passez la commande au fournisseur.
3. **Réceptions** : saisissez **numéro de lot et date de péremption** pour chaque produit reçu.

## Magasins et transferts

**Magasins et transferts** : le stock est suivi par magasin. Un transfert sort les produits du magasin d'origine (lots les plus proches de la péremption d'abord) et les entre, avec les mêmes lots, dans le magasin de destination.

## Inventaire

1. **📋 Inventaire** : comptez physiquement chaque produit.
2. Saisissez la quantité comptée ; l'écart avec le stock théorique s'affiche.
3. Tant qu'il est en **brouillon**, l'inventaire peut être corrigé. Une fois **validé**, il ne se modifie plus et les ajustements sont appliqués.

## Règles

- Le stock n'est jamais modifié à la main : toute variation est un **mouvement** (entrée, sortie, transfert, ajustement, retour) daté et signé.
- Produits périmés : retirez-les du lot concerné avec le motif « périmé ».
