import re

data = """
1	Anesthésie générale	Halothane, Isoflurane, Kétamine, Propofol (ou Thiopenthal)	Selon protocole	Perte de conscience, analgésie, amnésie
2	Oxygénothérapie	Oxygène	Selon besoin clinique	Détresse respiratoire sévère
3	Anesthésie locale / rachianesthésie	Bupivacaïne, Lidocaïne	Selon poids (max 2-3 mg/kg)	Douleur localisée, perte de sensibilité
4	Anesthésie dentaire	Lidocaïne + Épinéphrine	Selon protocole dentaire	Douleur localisée
5	Prémédication anesthésique	Atropine, Midazolam, Morphine	0,01-0,02 mg/kg	Sédation, bradycardie
6	Sédation (actes courts)	Midazolam	Oral: 0,3-0,5 mg/kg (max 20 mg)	Anxiété, agitation
7	Douleurs légères à modérées, fièvre	Paracétamol, Ibuprofène (>3 mois)	10-15 mg/kg toutes les 6 heures	Fièvre, myalgies
8	Douleurs sévères	Morphine	Injection: 0,1-0,2 mg/kg/4h; Oral: 0,2-0,5 mg/kg/4h	Douleur intense
9	Douleurs neuropathiques	Amitriptyline	0,5-2 mg/kg/jour (dose unique le soir)	Brûlures, décharges électriques
10	Soins palliatifs (douleur)	Morphine (LI ou LP)	Selon protocole	Douleur chronique
11	Soins palliatifs (sédation)	Midazolam, Diazépam	Selon protocole	Agitation terminale
12	Soins palliatifs (nausées)	Cyclizine, Ondansétron	Selon protocole	Nausées, vomissements
13	Soins palliatifs (constipation)	Docusate sodique, Lactulose, Senna	Selon protocole	Constipation
14	Soins palliatifs (sécrétions)	Bromhydrate d'hyoscine	Selon protocole	Râles terminaux
15	Rhumatisme articulaire aigu	Acide acétylsalicylique	80-100 mg/kg/jour (max 4 g/jour)	Arthrite, cardite
16	Polyarthrite juvénile	Acide acétylsalicylique, Hydroxychloroquine, Méthotrexate	Selon protocole	Douleurs articulaires chirurgicales
17	Maladie de Kawasaki	Acide acétylsalicylique	Selon protocole	Fièvre prolongée, conjonctivite
18	Réactions allergiques (mineures)	Chlorphénamine (>1 an)	0,1-0,2 mg/kg toutes les 6-8 h	Urticaire, prurit
19	Réactions allergiques sévères / Anaphylaxie	Épinéphrine (Adrénaline), Dexaméthasone, Hydrocortisone, Prednisolone	0,01 mg/kg (max 0,5 mg)	Hypotension, dyspnée
20	Intoxication médicamenteuse (non spécifique)	Charbon activé	1 g/kg	Ingestion toxique
21	Intoxication au paracétamol	Acétylcystéine	Dose de charge : 150 mg/kg	Vomissements, douleurs abdominales
22	Intoxication aux organophosphorés	Atropine	0,02-0,05 mg/kg	Salivation, myosis
23	Intoxication au sulfate de magnésium	Gluconate de calcium	0,5 ml/kg	Abolition réflexes
24	Intoxication aux opioïdes (morphine)	Naloxone	5-10 microgrammes/kg	Dépression respiratoire
25	Intoxication aux métaux lourds (Pb, As, Hg)	Calcium édétate de sodium, Dimercaprol, Succimer	Selon protocole	Neuropathie, douleurs abdominales
26	Surcharge en fer (thalassémie)	Déféroxamine (ou Déférasirox)	20-40 mg/kg/jour	Hémosidérose
27	Épilepsie (crises généralisées, partielles)	Carbamazépine, Phénytoïne, Phénobarbital	Selon protocole	Convulsions tonico-cloniques
28	Épilepsie (crises généralisées, absence)	Acide valproïque (Valproate de sodium)	10-15 mg/kg/jour	Rupture de contact
29	Épilepsie (crises d'absence)	Éthosuximide	15-20 mg/kg/jour	Absences brèves
30	État de mal convulsif	Diazépam (rectal), Lorazépam (IV), Phénobarbital (IV), Phénytoïne (IV)	0,5 mg/kg (max 10 mg)	Convulsions prolongées
31	Ascaridiase (Ascaris)	Albendazole, Mébendazole, Pyrantel, Lévamisole	400 mg dose unique	Douleurs abdominales, toux
32	Oxyurose (Enterobius)	Albendazole, Mébendazole, Pyrantel	100 mg dose unique	Prurit anal nocturne
33	Trichocéphalose (Trichuris)	Albendazole, Mébendazole	400 mg dose unique	Diarrhée sanglante
34	Ankylostomiase	Albendazole, Mébendazole	400 mg dose unique	Anémie, douleurs abdominales
35	Téniasis (Taenia)	Praziquantel, Niclosamide (si échec)	5-10 mg/kg dose unique	Segments dans les selles
36	Filariose lymphatique	Diéthylcarbamazine (DEC), Albendazole, Ivermectine	6 mg/kg/jour	Éléphantiasis
37	Onchocercose	Ivermectine	150 microgrammes/kg	Prurit, cécité
38	Schistosomiase (S. haematobium, mansoni, etc.)	Praziquantel	40 mg/kg dose unique	Hématurie
39	Schistosomiase à S. mansoni (alternative)	Oxamniquine	Selon protocole	Diarrhée, hépato-splénomégalie
40	Fasciolose (douves du foie)	Triclabendazole	10 mg/kg dose unique	Douleurs hypocondre droit
41	Infections ORL (angine, otite, sinusite)	Amoxicilline, Phénoxyméthylpénicilline, Céfalexine	Selon protocole	Mal de gorge, otalgie
42	Angine streptococcique / Prévention RAA	Benzathine benzylpénicilline, Phénoxyméthylpénicilline	Selon protocole	Fièvre, maux de gorge
43	Infections respiratoires basses (pneumonie)	Amoxicilline, Ampicilline, Ceftriaxone	Selon protocole	Toux, tachypnée
44	Infections urinaires	Co-trimoxazole, Nitrofurantoïne, Triméthoprime, Ciprofloxacine	Selon protocole	Brûlures mictionnelles
45	Cystite aiguë	Nitrofurantoïne, Fosfomycine trométamol	3 g dose unique	Pollakiurie, dysurie
46	Pyélonéphrite	Ciprofloxacine, Ceftriaxone	Selon protocole	Douleur lombaire, fièvre
47	Infections cutanées (impétigo, cellulite)	Cloxacilline, Céfalexine, Mupirocine (topique)	Selon protocole	Lésions croûteuses, érythème
48	Infections à staphylocoques	Cloxacilline, Clindamycine	Selon protocole	Furoncles, abcès
49	Infections à germes anaérobies	Métronidazole, Clindamycine	Selon protocole	Odeur fétide, nécrose
50	Méningite bactérienne	Ceftriaxone, Céfotaxime, Ampicilline, Gentamicine, Chloramphénicol	100 mg/kg/jour	Raideur nuque, photophobie
51	Septicémie	Ampicilline, Gentamicine, Ceftriaxone, Céfotaxime	Selon protocole	Fièvre élevée, choc
52	Méningite épidémique (>2 ans)	Chloramphénicol huileux (IM)	100 mg/kg dose unique	Syndrome méningé
53	Infections néonatales	Ampicilline + Gentamicine, Céfotaxime	Selon protocole	Léthargie, refus téter
54	Prophylaxie chirurgicale	Céfazoline	Dose unique pré-op	Asymptomatique (prévention)
55	Infections nosocomiales multirésistantes	Imipénem + cilastatine, Vancomycine	Selon protocole	Fièvre persistante sous ATB
56	Infections à Pseudomonas	Ceftazidime	Selon protocole	Infections sévères résistantes
57	Infections à germes producteurs de bêta-lactamases	Amoxicilline + acide clavulanique	Selon protocole	Infections résistantes
58	Choléra	Doxycycline, Azithromycine	Selon protocole	Diarrhée "eau de riz"
59	Peste	Doxycycline, Gentamicine	Selon protocole	Bubon, détresse respiratoire
60	Brucellose	Doxycycline + Streptomycine (ou Gentamicine) + Rifampicine	Selon protocole	Fièvre ondulante, sueurs
61	Leptospirose	Benzylpénicilline, Doxycycline	Selon protocole	Ictère, douleurs musculaires
62	Typhoïde (fièvre typhoïde)	Ciprofloxacine, Chloramphénicol, Azithromycine, Ceftriaxone	Selon protocole	Fièvre, dissociation pouls-temp
63	Shigellose	Ciprofloxacine, Co-trimoxazole, Azithromycine	Selon protocole	Dysenterie sanglante
64	Charbon cutané	Ciprofloxacine, Doxycycline, Amoxicilline	Selon protocole	Pustule maligne, œdème
65	Trachome	Azithromycine, Tétracycline (pommade ophtalmique)	20 mg/kg dose unique	Conjonctivite folliculaire
66	Coqueluche	Érythromycine, Azithromycine	Selon protocole	Quinte de toux, chant du coq
67	Diphtérie	Érythromycine, Pénicilline, Antitoxine diphtérique	Selon protocole	Fausse membrane pharyngée
68	Infections à Chlamydia	Érythromycine, Azithromycine, Doxycycline	Selon protocole	Écoulement urétral, cervicite
69	Syphilis	Benzathine benzylpénicilline, Benzylpénicilline, Doxycycline	Selon protocole	Chancre, roséole
70	Infections gonococciques	Ceftriaxone	Dose unique	Écoulement purulent
71	Lèpre paucibacillaire	Dapsone + Rifampicine (en blister)	Selon blister	Taches cutanées anesthésiées
72	Lèpre multibacillaire	Dapsone + Rifampicine + Clofazimine (en blister)	Selon blister	Nodules cutanés
73	Tuberculose (1ère ligne)	Isoniazide (H) + Rifampicine (R) + Pyrazinamide (Z) + Éthambutol (E) (associations fixes)	Selon protocole	Toux, amaigrissement
74	Tuberculose latente	Isoniazide, Rifampicine, Rifapentine	Selon protocole	Asymptomatique
75	Tuberculose multirésistante (2ème ligne)	Amikacine, Kanamycine, Capréomycine, Ofloxacine (Lévofloxacine), Éthionamide, Cyclosérine, Acide p-aminosalicylique	Selon protocole	Échec traitement 1ère ligne
76	Candidose oropharyngée	Nystatine, Fluconazole, Miconazole (gel)	100 000 UI 4x/jour	Muguet buccale
77	Candidose œsophagienne	Fluconazole	3-6 mg/kg/jour	Dysphagie, odynophagie
78	Candidose cutanée	Nystatine, Miconazole (crème)	Selon protocole	Intertrigo, érythème
79	Candidose vulvovaginale	Nystatine (ovules), Clotrimazole	Selon protocole	Leucorrhées, prurit vulvaire
80	Cryptococcose	Amphotéricine B + Flucytosine, puis Fluconazole	Selon protocole	Céphalées, syndrome méningé
81	Teignes (dermatophytoses du cuir chevelu)	Griséofulvine, Terbinafine	10-20 mg/kg/jour	Alopécie, squames
82	Mycoses cutanées	Miconazole, Terbinafine	Selon protocole	Lésion circinée
83	Histoplasmose	Amphotéricine B, Itraconazole	Selon protocole	Toux, syndrome grippal
84	Leishmaniose	Amphotéricine B, Stibogluconate de sodium, Miltéfosine, Paromomycine	Selon protocole	Fièvre, splénomégalie
85	Sporotrichose	Iodure de potassium	Selon protocole	Nodules lymphangitiques
86	Herpès, zona, varicelle sévère	Aciclovir	80 mg/kg/jour	Vésicules en bouquet, douleur
87	Kératite herpétique	Aciclovir (pommade ophtalmique)	5 fois/jour	Douleur oculaire, rougeur
88	VIH / SIDA (traitement)	Associations d'antirétroviraux : 2 NRTI + 1 NNRTI ou 1 IP	Selon protocole	Immunodéficience
89	VIH (PTME - prévention transmission mère-enfant)	Zidovudine, Névirapine	Selon protocole	Nouveau-né de mère VIH+
90	Grippe pandémique A(H1N1)	Oseltamivir	Selon protocole	Syndrome grippal sévère
91	Fièvres hémorragiques virales	Ribavirine	Selon protocole	Syndrome hémorragique, fièvre
92	Amibiase	Métronidazole + Diloxanide	Selon protocole	Diarrhée sanglante, ténesme
93	Giardiase	Métronidazole, Tinidazole	Selon protocole	Diarrhée graisseuse, ballonnements
94	Trypanosomiase africaine (1er stade) – T.b. gambiense	Pentamidine	Selon protocole	Chancres, adénopathies
95	Trypanosomiase africaine (1er stade) – T.b. rhodesiense	Suramine sodique	Selon protocole	Fièvre, céphalées
96	Trypanosomiase africaine (2e stade) – T.b. gambiense	Éflornithine (ou association Nifurtimox+Éflornithine)	Selon protocole	Troubles du sommeil
97	Trypanosomiase africaine (2e stade) – T.b. rhodesiense	Mélarsoprol	Selon protocole	Troubles neurologiques sévères
98	Maladie de Chagas (Trypanosomiase américaine)	Benznidazole, Nifurtimox	Selon protocole	Signe de Romaña
99	Pneumocystose (PJP)	Co-trimoxazole (SMX+TMP), Pentamidine	8-10 mg/kg/jour	Dyspnée, toux sèche (VIH)
100	Toxoplasmose	Pyriméthamine + Sulfadiazine, Co-trimoxazole	Selon protocole	Adénopathies, céphalées
101	Paludisme à P. falciparum (non compliqué)	ACT : Artéméther+Luméfantrine, Artésunate+Amodiaquine	Selon poids	Fièvre, courbatures, anémie
102	Paludisme grave	Artésunate IV, Quinine IV, Artéméther IM	2,4 mg/kg IV	Coma, convulsions, choc
103	Paludisme à P. vivax (traitement)	Chloroquine	10 mg/kg J1, J2	Fièvre tierce
104	Paludisme à P. vivax/ovale (guérison radicale)	Primaquine (14 jours)	0,5 mg/kg/jour	Prévention rechute
105	Paludisme (prophylaxie)	Méfloquine, Doxycycline (>8 ans), Proguanile + Chloroquine	Selon poids	Asymptomatique (prévention)
106	Crise migraineuse	Ibuprofène, Paracétamol	Selon protocole	Céphalée pulsatile
107	Prophylaxie de la migraine	Propranolol	Selon protocole	Fréquence crises élevée
108	Leucémie lymphoblastique aiguë (LLA)	Vincristine, Asparaginase, Méthotrexate, Mercaptopurine, Cyclophosphamide	Selon protocole	Asthénie, pâleur, purpura
109	Lymphome de Burkitt	Cyclophosphamide, Cytarabine, Doxorubicine, Vincristine, Prednisolone	Selon protocole	Masse maxillaire ou abdominale
110	Tumeur de Wilms (néphroblastome)	Dactinomycine, Vincristine, Daunorubicine	Selon protocole	Masse abdominale, hématurie
111	Hyperuricémie (post-chimiothérapie)	Allopurinol	10 mg/kg/jour	Goutte, calculs
112	Cystite hémorragique (post-cyclophosphamide)	Mesna	Selon protocole	Hématurie
113	Transplantation d'organes	Ciclosporine, Azathioprine	Selon protocole	Prévention rejet
114	Anémie ferriprive	Sels ferreux (Fer)	3-6 mg/kg/jour	Pâleur, fatigue
115	Anémie mégaloblastique (carence B9)	Acide folique	250-500 microgrammes/jour	Pâleur, langue rouge
116	Anémie pernicieuse (carence B12)	Hydroxocobalamine (Vitamine B12)	100 microgrammes/mois	Pâleur, troubles neurologiques
117	Hémorragie du nouveau-né (prévention/traitement)	Phytoménadione (Vitamine K1)	1 mg dose unique	Saignements cordon, tube digestif
118	Anticoagulation (curative/préventive)	Héparine, Warfarine	Selon protocole	Thrombose, embolie
119	Neutralisation de l'héparine	Sulfate de protamine	Selon protocole	Hémorragie sous héparine
120	Thalassémie (surcharge en fer)	Déféroxamine	Selon protocole	Pigmentation, troubles cardiaques
121	Drépanocytose	Hydroxycarbamide	10-20 mg/kg/jour	Crises de douleur osseuse
122	Hypertension artérielle	Énalapril	0,1-0,5 mg/kg/jour	Céphalées, vertiges
123	Insuffisance cardiaque	Digoxine, Furosémide, Dopamine	Selon protocole	Dyspnée, œdèmes
124	Œdèmes (cardiaque, rénal, hépatique)	Furosémide, Hydrochlorothiazide, Spironolactone	Selon protocole	Gonflement membres, ascite
125	Choc	Dopamine	5-10 microgrammes/kg/min	Hypotension, tachycardie
126	Crise d'asthme (bronchodilatation)	Salbutamol (aérosol, nébulisation, SC/IV)	2-10 bouffées	Sibilants, oppression thoracique
127	Crise d'asthme sévère	Épinéphrine (Adrénaline)	0,01 mg/kg	Silence auscultatoire, cyanose
128	Asthme chronique (traitement de fond)	Budésonide	100-400 microgrammes 2x/jour	Symptômes nocturnes
129	Diagnostic lésions cornéennes	Fluorescéine (collyre)	1-2 gouttes	Douleur, photophobie
130	Mydriase (examen)	Tropicamide, Atropine (>3 mois)	1-2 gouttes	Fond d'œil
131	Kératite herpétique (topique)	Aciclovir pommade	5 fois/jour	Douleur, larmoiement
132	Conjonctivite bactérienne	Gentamicine collyre, Tétracycline pommade	2 f/jour	Sécrétions purulentes
133	Trachome (topique)	Tétracycline pommade, Azithromycine (systémique)	2 f/jour	Rugosité palpébrale
134	Prévention conjonctivite du nouveau-né	Tétracycline pommade 1%	Dose unique	Prévention
135	Glaucome	Pilocarpine, Épinéphrine collyre	Selon protocole	Vision floue, douleur
136	Anesthésie oculaire	Tétracaïne (collyre)	1-2 gouttes	Extraction corps étranger
137	Gale	Perméthrine 5% crème, Benzoate de benzyle (>2 ans)	App unique	Prurit nocturne, sillons
138	Poux (pédiculose)	Perméthrine 1% lotion, Diméticone	App unique	Prurit cuir chevelu
139	Eczéma	Hydrocortisone crème, Bétaméthasone, Calamine lotion	Selon protocole	Lésions érythémato-squameuses
140	Brûlures	Sulfadiazine argentique crème (>2 mois)	Quotidien	Douleur, phlyctènes
141	Impétigo (topique)	Mupirocine pommade	3 f/jour	Croûtes mélicériques
142	Acné	Peroxyde de benzoyle	2 f/jour	Comédons, pustules
143	Psoriasis	Goudron de houille, Acide salicylique	Selon protocole	Squames argentées
144	Verrues	Acide salicylique	Selon protocole	Excroissance kératosique
145	Condylomes (HPV)	Résine de podophylle, Podophyllotoxine	Selon protocole	Crêtes de coq
146	Mycoses cutanées (topique)	Miconazole crème, Terbinafine	Selon protocole	Lésion annulaire
147	Peau sèche	Urée crème	Selon protocole	Xérose
148	Ulcère gastroduodénal	Oméprazole, Ranitidine	0,7-1,4 mg/kg/jour	Douleur épigastrique
149	Reflux gastro-œsophagien (RGO)	Oméprazole	Selon protocole	Pyrosis
150	Nausées / vomissements	Métoclopramide (sauf nouveau-né), Ondansétron (>1 mois)	0,1-0,15 mg/kg	Nausées, vomissements
151	Constipation	Lactulose, Docusate, Senna	0,5-1 ml/kg/jour	Selles rares et dures
152	Déshydratation par diarrhée	Sels de réhydratation orale (SRO)	50-100 ml/kg	Soif, pli cutané
153	Diarrhée aiguë (adjuvant)	Sulfate de zinc	10-20 mg/jour	Diarrhée
154	Diabète sucré type 1	Insuline (soluble + intermédiaire)	0,5-1 UI/kg/jour	Polyurie, polydipsie
155	Diabète sucré type 2	Metformine	500-2000 mg/jour	Hyperglycémie
156	Hypoglycémie sévère	Glucagon	0,5-1 mg	Sueur, coma
157	Insuffisance surrénale	Hydrocortisone, Fludrocortisone	10-20 mg/m²/jour	Hypotension, fatigue
158	Hypothyroïdie	Lévothyroxine	2-4 microgrammes/kg/jour	Constipation, bradycardie
159	Hyperthyroïdie	Propylthiouracil, Iodure de potassium, Solution de Lugol	Selon protocole	Tachycardie, goitre
160	Goitre / Carence en iode	Huile iodée, Capsules d'iode	Selon protocole	Grosseur cou
161	Apnée du prématuré	Citrate de caféine	20 mg/kg dose charge	Pauses respiratoires
162	Persistance du canal artériel	Ibuprofène IV, Prostaglandine E	Selon protocole	Souffle cardiaque
163	Détresse respiratoire du prématuré	Surfactant	100-200 mg/kg	Cyanose, tachypnée
164	Rachitisme (carence Vitamine D)	Cholécalciférol (Vitamine D3)	1000-2000 UI/jour	Genu varum, retard marche
165	Scorbut (carence Vitamine C)	Acide ascorbique (Vitamine C)	100-300 mg/jour	Gingivorragies
166	Béribéri (carence Vitamine B1)	Thiamine (Vitamine B1)	10-50 mg/jour	Neuropathie, œdèmes
167	Carence Vitamine B6 (neuropathie à isoniazide)	Pyridoxine (Vitamine B6)	5-10 mg/jour	Paresthésies
168	Carence Vitamine B2	Riboflavine	Selon protocole	Chéilite, glossite
169	Carence en iode (substitut)	Iode (capsules, huile iodée)	Selon protocole	Crétinisme
170	Carence en vitamine A (traitement)	Rétinol (Vitamine A)	50000-200000 UI	Xérophtalmie, cécité crépusculaire
171	Carence en folate	Acide folique	5 mg/jour	Anémie
172	Carence en calcium	Gluconate de calcium	Selon protocole	Tétanie, rachitisme
173	Prévention des caries	Fluorure de sodium	Selon protocole	Caries
174	Otite externe	Acide acétique topique, Ciprofloxacine gouttes	3 f/jour	Douleur pavillon oreille
175	Otite moyenne chronique suppurée	Ciprofloxacine gouttes	Selon protocole	Écoulement purulent
176	Rhinite allergique	Budésonide spray nasal	1-2 f/jour	Éternuements, rhinorrhée
177	Congestion nasale	Xylométazoline (>3 mois)	2 f/jour	Nez bouché
178	Antisepsie cutanée	Chlorhexidine, Éthanol 70%, Polyvidone iodée	Selon besoin	Plaie
179	Soins du cordon ombilical	Chlorhexidine	Selon protocole	Nouveau-né
180	Désinfection des surfaces	Composé chloré (NaDCC), Chloroxylénol	Selon besoin	Besoins hygiène
181	Désinfection haut niveau (matériel médical)	Glutaral	Selon besoin	Nettoyage instruments
182	Remplissage vasculaire / Déshydratation	Chlorure de sodium 0,9%, Glucose 5%, Ringer lactate	20 ml/kg bolus	Choc, déshydratation sévère
183	Hypokaliémie	Chlorure de potassium (KCl)	1-3 mmol/kg/jour	Fatigue, troubles rythme
184	Hypoglycémie (IV)	Glucose 10% ou 50%	1-2 ml/kg	Coma, sueurs
185	Acidose métabolique	Bicarbonate de sodium 1,4% ou 8,4%	1-2 mmol/kg	Respiration Kussmaul
186	Reconstitution de médicaments injectables	Eau pour préparations injectables (ppi)	Selon besoin	Reconstitution
"""

protocoles = []
medicaments_all = set()
maladies = []
symptomes_all = set()
liens_maladie_symptome = {}

lines = data.strip().split("\n")
for line in lines:
    parts = line.strip().split("\t")
    if len(parts) >= 3:
        num = parts[0]
        maladie_nom = parts[1]
        traitement_meds_raw = parts[2]
        posologie = parts[3] if len(parts) >= 4 else "Selon protocole"
        symptomes_raw = parts[4] if len(parts) >= 5 else ""
        
        # Cleanup meds
        meds = [m.strip() for m in re.split(r'[,/+]|ou\s+|et\s+', traitement_meds_raw.replace('(', '').replace(')', '')) if m.strip()]
        for m in meds:
            medicaments_all.add(m)
            
        # Cleanup symptoms
        symps = [s.strip() for s in re.split(r'[,/+]|ou\s+|et\s+', symptomes_raw) if s.strip()]
        for s in symps:
            symptomes_all.add(s)
            
        maladies.append({'nom': maladie_nom, 'description': f"Catégorie {maladie_nom}"})
        liens_maladie_symptome[maladie_nom] = symps
        
        # Protocol
        protocoles.append({
            'maladie_nom': maladie_nom,
            'titre': f"Protocole pour {maladie_nom}",
            'signes': symptomes_raw,
            'posologie_principale': posologie,
            'traitement_principal': traitement_meds_raw,
            'meds_list': meds
        })

def esc(s):
    return s.replace("'", "\\'")

# Generation MedicamentSeeder
print("// --- MEDICAMENTS ---")
for m in sorted(list(medicaments_all)):
    print(f"            ['nom' => '{esc(m)}', 'description' => 'Médicament essentiel'],")

# Generation MaladieSymptomeSeeder
print("\n// --- SYMPTOMES ---")
for s in sorted(list(symptomes_all)):
    print(f"            ['nom' => '{esc(s)}', 'description' => 'Symptôme clinique'],")

print("\n// --- MALADIES ---")
for m in maladies:
    print(f"            ['nom' => '{esc(m['nom'])}', 'description' => '{esc(m['description'])}'],")

print("\n// --- LIENS MALADIE-SYMPTOME ---")
for m, s_list in liens_maladie_symptome.items():
    if s_list:
        s_list_esc = [f"'{esc(s)}'" for s in s_list]
        print(f"            '{esc(m)}' => [{', '.join(s_list_esc)}],")

# Generation ProtocoleTraitementSeeder
print("\n// --- PROTOCOLES ---")
for p in protocoles:
    print(f"            [")
    print(f"                'maladie_nom' => '{esc(p['maladie_nom'])}',")
    print(f"                'titre' => '{esc(p['titre'])}',")
    print(f"                'signes' => '{esc(p['signes'])}',")
    print(f"                'traitement_principal' => '{esc(p['traitement_principal'])}',")
    print(f"                'posologie_principale' => '{esc(p['posologie_principale'])}',")
    print(f"            ],")

# Generation ProtocoleMedicamentSeeder
print("\n// --- PROTOCOLES ET MEDICAMENTS ---")
for p in protocoles:
    for m in p['meds_list']:
        print(f"            ['protocole_titre' => '{esc(p['titre'])}', 'medicament_nom' => '{esc(m)}', 'type' => 'principal', 'posologie' => '{esc(p['posologie_principale'])}', 'duree' => 'Selon évolution'],")
