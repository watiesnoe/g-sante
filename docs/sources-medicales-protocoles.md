# Sources des protocoles de traitement (Infectiologie & Traitement)

Ce document liste les sources internationales vérifiables ayant servi à
valider ou corriger les données médicales du module Infectiologie/Traitement
(maladies, médicaments, protocoles). Il est tenu à jour au fil des vérifications
et destiné à permettre à un médecin réviseur de contrôler indépendamment
chaque donnée citée.

Toutes les corrections ci-dessous ont été appliquées à la fois dans la base de
données et dans les seeders correspondants (`WhoGuidelinesSeeder`,
`RemainingProtocolsSeeder`, `ProtocolesMedicamentsSeeder`).

## Corrections appliquées suite à vérification

| Pathologie | Donnée corrigée | Avant | Après | Source |
|---|---|---|---|---|
| VIH (nourrisson) | Régime de 1ère ligne | AZT + 3TC + NVP (ancien schéma) | Abacavir + Lamivudine + Dolutégravir (dès > 1 mois et > 3 kg) | Recommandation OMS 2023 ; extension FDA du dolutégravir pédiatrique au nouveau-né ≥ 2 kg (étude IMPAACT 2023) |
| Urticaire | Antihistaminique principal | Chlorphénamine (1ère génération) en principal | Cétirizine (2e génération) en principal, chlorphénamine reléguée en alternatif | Recommandations actuelles privilégiant les anti-H1 de 2e génération (moindre sédation) |
| Fausse couche / Avortement incomplet | Posologie Misoprostol | 600 µg sublingual (association route/dose incorrecte) | 600 µg par voie orale **ou** 400 µg par voie sublinguale | Directives OMS sur la prise en charge de l'avortement incomplet |
| Tuberculose multirésistante (MDR-TB) | Régime thérapeutique complet | Amikacine, Ofloxacine, Éthionamide, Cyclosérine (régime injectable pré-2022, abandonné) | Régime BPaLM : Bédaquiline + Prétomanide + Linézolide + Moxifloxacine, 100 % oral, 6 mois | [WHO consolidated guidelines on tuberculosis – module 4 (BPaLM)](https://tbksp.who.int/en/node/576) ; [BPaLM systematic review, PMC](https://www.ncbi.nlm.nih.gov/pmc/articles/PMC11665299/) |
| Hépatite B chronique | Posologie Ténofovir | « 1 mg/jour » (erreur de saisie) | 300 mg 1×/jour (dose standard TDF) | Dose TDF 300 mg/jour largement établie (littérature clinique, essais cliniques TDF chronic hepatitis B) |
| Diabète gestationnel | Description ambiguë | Mentionnait « HBPG 75g » sans contexte, pouvant être confondu avec un traitement | Clarifié : HGPO 75g = test diagnostique (dépistage 24-28 SA), pas un traitement | OMS — dépistage du diabète gestationnel |
| Troubles mentaux courants | Description | Mentionnait de façon incohérente un « diurétique thiazidique » sans rapport avec la pathologie | Fragment erroné retiré ; description recentrée sur dépression et troubles psychotiques | OMS mhGAP Intervention Guide |

## Protocoles nouvellement structurés (médicaments réels liés)

41 protocoles disposaient d'un texte descriptif OMS mais d'aucun médicament
structuré (table `protocole_medicament`). 40 ont reçu une liaison médicament
réelle avec posologie et durée vérifiables ; 1 (Bronchiolite du nourrisson)
reste volontairement sans médicament car l'OMS ne recommande aucun traitement
pharmacologique de 1ère intention pour cette pathologie (soins de support
uniquement).

Molécules/regimes concernés, par domaine :

- **Paludisme** : Artésunate IV / Artéméther IM / Quinine (forme grave) ;
  Quinine + Clindamycine puis ACT (grossesse) ; SP+AQ (chimioprévention
  saisonnière de l'enfant).
- **Tuberculose** : RHZE puis RH (1ère ligne) ; BPaLM (multirésistante, voir
  ci-dessus).
- **VIH/SIDA** : TLD (adulte 1ère ligne, PTME, PPE) ; DRV/r + 2 INTI (2ème
  ligne) ; ABC+3TC+DTG (pédiatrique) ; névirapine sirop (prophylaxie
  nourrisson).
- **Hépatites virales** : Ténofovir/Entécavir (B) ; Sofosbuvir/Velpatasvir
  (C).
- **Pédiatrie/IMCI** : Amoxicilline (pneumonie) ; SRO + Zinc (diarrhées) ;
  Ciprofloxacine (dysenterie) ; Amoxicilline 5 jours (malnutrition aiguë
  sévère) ; Vitamine A (carence).
- **Infections courantes** : Oseltamivir (grippe) ; Ceftriaxone + Dexaméthasone
  (méningite bactérienne) ; Benzathine pénicilline / Ceftriaxone + Azithromycine
  (IST) ; Nitrofurantoïne / Fosfomycine / Ciprofloxacine (infections
  urinaires).
- **Maladies tropicales négligées** : Amphotéricine B liposomale (leishmaniose
  viscérale, schéma OMS « SHORT ») ; Praziquantel (schistosomiase) ;
  Ivermectine (onchocercose, gale) ; Perméthrine (gale).
- **Maladies chroniques** : Metformine (diabète de type 2) ; Insuline (diabète
  gestationnel) ; Amlodipine/Hydrochlorothiazide (hypertension, programme OMS
  HEARTS) ; Budésonide + Salbutamol (asthme).
- **Santé maternelle** : Sulfate de magnésium (protocole Pritchard/Zuspan) +
  Labétalol/Méthyldopa (pré-éclampsie/éclampsie) ; Oxytocine + Misoprostol
  (hémorragie du post-partum) ; Ampicilline + Gentamicine + Métronidazole
  (infection du post-partum) ; Misoprostol (avortement incomplet).
- **Autres** : Prednisolone (syndrome néphrotique de l'enfant) ; Aciclovir
  (méningo-encéphalite herpétique) ; Morphine (soins palliatifs
  oncologiques) ; Amitriptyline/Fluoxétine/Halopéridol/Chlorpromazine
  (troubles mentaux courants, mhGAP) ; Naloxone (intoxication aux opiacés) ;
  Vaccin antirabique (schéma Essen) + immunoglobulines (rage) ; Antitoxine
  tétanique + Métronidazole (tétanos) ; Ciprofloxacine (charbon cutané).

## Références générales consultées

- [WHO Guidelines – TB Knowledge Sharing Platform](https://tbksp.who.int)
- World Health Organization, fiches d'information et directives cliniques
  (paludisme, VIH, hépatites virales, santé maternelle et infantile, mhGAP,
  HEARTS)
- PubMed / PMC (National Library of Medicine) pour la littérature clinique
  citée ci-dessus

## Limites connues

- Les posologies indiquées sont des posologies adultes standard sauf
  indication contraire ; les ajustements pédiatriques précis, rénaux ou
  hépatiques ne sont pas tous représentés et doivent être vérifiés au cas par
  cas par le prescripteur.
- Ce catalogue est un support d'aide à la prescription générique, à visée
  éducative et organisationnelle ; il ne remplace pas les référentiels
  nationaux ni le jugement clinique.
- La revue exhaustive de l'ensemble des ~245 protocoles n'est pas garantie ;
  seules les pathologies à risque élevé d'obsolescence ou signalées comme
  incomplètes ont fait l'objet d'une vérification ciblée à ce stade.
