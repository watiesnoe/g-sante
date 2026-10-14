# G-SANTÉ — Plateforme intégrée de gestion sanitaire

Application de gestion pour cabinets médicaux, cliniques, CSRéf et hôpitaux : accueil, consultations,
ordonnances, laboratoire, hospitalisation, maternité, pharmacie et stock, caisse.
Un noyau commun et des modules activables par établissement (Paramètres → Établissement).

Le cahier des charges de référence est `Cahier_des_charges_plateforme_gestion_sanitaire_v2.1.pdf`.

## Prérequis

- PHP 8.3 ou plus (exigé par spatie/laravel-permission 7 et laravel-backup 10), avec les extensions `pdo_mysql`, `mbstring`, `zip`, `gd`, `intl`
- MySQL 8 ou MariaDB 10.6 et plus (XAMPP convient en développement)
- Composer 2, Node.js 18 et plus

## Installation (environ 20 minutes)

```bash
git clone https://github.com/watiesnoe/g-sante.git && cd g-sante
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Dans `.env`, renseigner `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, et `DB_DUMP_BINARY_PATH`
(sous XAMPP : `/opt/lampp/bin`). Créer ensuite les deux bases, puis les tables et les droits :

```sql
CREATE DATABASE gsante      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE gsante_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate
php artisan db:seed --class=PermissionRoleSeeder
php artisan serve
```

À la première connexion d'un administrateur, un bandeau invite à configurer l'établissement :
nom, type, modules activés et règles de fonctionnement.

## Tests

```bash
php artisan test
```

Les tests utilisent **uniquement** la base `gsante_test` (forcée dans `phpunit.xml`) : ils la vident à
chaque exécution. Ne jamais les pointer sur la base de travail.

Les tests du dossier `tests/Feature/Lot0` vérifient les critères de sortie du lot 0
(REC-07, REC-08, REC-13, REC-14, REC-15). L'intégration continue (`.github/workflows/tests.yml`)
les lance à chaque envoi.

## Sauvegarde et restauration (ENF-05)

Sauvegarde manuelle de la base :

```bash
php artisan backup:run --only-db
```

Les archives sont dans `storage/app/private/g-sante/`. Pour une sauvegarde nocturne automatique,
ajouter cette ligne à la crontab du serveur (`crontab -e`) :

```
* * * * * cd /chemin/vers/g-sante && php artisan schedule:run >> /dev/null 2>&1
```

Elle déclenche la purge des anciennes archives à 1 h, la sauvegarde complète à 1 h 30 et un contrôle
de santé à 7 h. Pour une copie sur un second support, monter un disque externe ou un NAS, puis
renseigner `BACKUP_EXTERNAL_PATH` et `BACKUP_DISKS=local,sauvegarde_externe`. Renseigner aussi
`BACKUP_ARCHIVE_PASSWORD` pour chiffrer les archives.

Restauration sur un serveur vierge :

```bash
unzip storage/app/private/g-sante/AAAA-MM-JJ-HH-MM-SS.zip -d /tmp/restauration
mysql -u root -p -e "CREATE DATABASE gsante CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p gsante < /tmp/restauration/db-dumps/mysql-gsante.sql
```

Tester cette restauration chaque trimestre sur une base temporaire (scénario REC-10).

## Règles à connaître avant de modifier le code

- **Argent** : un encaissement, un paiement ou un mouvement de caisse ne se supprime ni ne se modifie.
  Il s'annule avec un motif, ce qui crée une écriture inverse (`App\Services\CaisseService`).
  Le modèle lève une exception en cas de tentative (`App\Traits\Inalterable`).
- **Stock** : la quantité d'un médicament est la somme de ses mouvements. Toute entrée ou sortie passe
  par `App\Services\StockService` (lots, péremptions, sortie FEFO, verrou contre les ventes simultanées).
  `medicaments.stock` n'est qu'un total tenu à jour par ce service.
- **Caisse** : une session passe de ouverte à clôturée (écart justifié) puis validée, sans retour
  en arrière. Selon les règles de l'établissement, un responsable valide la clôture et on ne valide
  ni n'annule ses propres opérations (séparation des tâches).
- **Audit** : les modèles métier utilisent `App\Traits\Audite`. Le journal (Paramètres → Journal
  d'audit) est protégé par des déclencheurs en base contre toute modification.
- **Établissement** : les données métier portent `etablissement_id` (`App\Traits\AppartientEtablissement`).
  Le code teste un module ou un paramètre (`Etablissement::courant()->parametre(...)`), jamais le type
  d'établissement.
