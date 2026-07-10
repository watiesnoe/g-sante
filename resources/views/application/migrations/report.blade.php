<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Technique des Migrations — G-Santé</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            line-height: 1.5;
            font-size: 10pt;
        }
        
        /* Cover Page */
        .cover-page {
            page-break-after: always;
            padding-top: 5cm;
            text-align: center;
        }
        .cover-logo {
            font-size: 40pt;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .cover-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cover-subtitle {
            font-size: 16pt;
            color: #64748b;
            margin-bottom: 5cm;
        }
        .cover-meta {
            margin-top: 3cm;
            font-size: 11pt;
            color: #475569;
            border-top: 1px solid #cbd5e1;
            padding-top: 20px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Typography */
        h1 {
            font-size: 20pt;
            color: #1e293b;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14pt;
            color: #0f172a;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* General Layout */
        .page {
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        
        /* Stats Dashboard Table */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
            margin-bottom: 30px;
            margin-top: 10px;
        }
        .stats-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 20%;
        }
        .stats-value {
            font-size: 20pt;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        /* Table styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            text-align: left;
            padding: 10px;
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 2px solid #cbd5e1;
        }
        .data-table td {
            padding: 10px;
            font-size: 9pt;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 12px;
            text-align: center;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-warning {
            background-color: #fef9c3;
            color: #a16207;
        }
        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        
        /* Migration details card */
        .migration-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .migration-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .migration-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            float: left;
        }
        .migration-status {
            float: right;
        }
        .clear {
            clear: both;
        }
        .migration-meta {
            font-size: 8pt;
            color: #64748b;
            margin-top: 5px;
        }
        
        .column-list {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }
        .column-list th {
            font-size: 8pt;
            background-color: #f8fafc;
            color: #475569;
            padding: 5px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .column-list td {
            font-size: 8pt;
            padding: 5px;
            border-bottom: 1px solid #f1f5f9;
        }
        .column-name {
            font-family: monospace;
            font-weight: bold;
            color: #0f172a;
        }
        
        .text-muted {
            color: #64748b;
        }
        
        .footer-note {
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-logo">🏥</div>
        <div class="cover-title">G-Santé</div>
        <div class="cover-subtitle">Rapport Technique des Migrations</div>
        
        <div style="margin-top: 2cm; font-size: 12pt; color: #334155;">
            Structure complète de la base de données de l'application
        </div>
        
        <div class="cover-meta">
            <strong>Généré le :</strong> {{ date('d/m/Y H:i:s') }}<br>
            <strong>Version de l'application :</strong> Laravel v{{ app()->version() }} (PHP v{{ PHP_VERSION }})<br>
            <strong>Nombre total de fichiers :</strong> {{ $stats['total'] }} migrations<br>
            <strong>Statut global :</strong> 
            @if($stats['pending'] == 0)
                <span class="badge badge-success" style="padding: 5px 10px; font-size: 9pt;">Base de données à jour</span>
            @else
                <span class="badge badge-warning" style="padding: 5px 10px; font-size: 9pt;">{{ $stats['pending'] }} en attente</span>
            @endif
        </div>
    </div>

    <!-- Page 1: Overview & Dashboard -->
    <div class="page">
        <h1>1. Tableau de Bord des Migrations</h1>
        
        <p>Ce rapport contient l'analyse automatique et détaillée de toutes les migrations SQL de l'application G-Santé. Il présente la structure logique des tables, les colonnes et les clés de relation définies dans les fichiers de migration du projet.</p>
        
        <table class="stats-table">
            <tr>
                <td class="stats-card">
                    <div class="stats-value">{{ $stats['total'] }}</div>
                    <div class="stats-label">Migrations</div>
                </td>
                <td class="stats-card" style="border-left: 4px solid #10b981;">
                    <div class="stats-value" style="color: #10b981;">{{ $stats['run'] }}</div>
                    <div class="stats-label">Exécutées</div>
                </td>
                <td class="stats-card" style="border-left: 4px solid #f59e0b;">
                    <div class="stats-value" style="color: #f59e0b;">{{ $stats['pending'] }}</div>
                    <div class="stats-label">En attente</div>
                </td>
                <td class="stats-card" style="border-left: 4px solid #3b82f6;">
                    <div class="stats-value" style="color: #3b82f6;">{{ $stats['created'] }}</div>
                    <div class="stats-label">Tables créées</div>
                </td>
                <td class="stats-card" style="border-left: 4px solid #8b5cf6;">
                    <div class="stats-value" style="color: #8b5cf6;">{{ $stats['altered'] }}</div>
                    <div class="stats-label">Altérations</div>
                </td>
            </tr>
        </table>

        <h2>Statut Global de Migration</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date / Index</th>
                    <th style="width: 55%;">Nom de la Migration</th>
                    <th style="width: 15%;">Batch</th>
                    <th style="width: 15%;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($migrations, 0, 15) as $m)
                <tr>
                    <td>{{ substr($m['file'], 0, 10) }}</td>
                    <td><strong>{{ $m['name'] }}</strong><br><span style="font-size: 7.5pt; color:#64748b;">{{ $m['file'] }}</span></td>
                    <td>{{ $m['batch'] ?? '-' }}</td>
                    <td>
                        @if($m['status'] == 'Exécutée')
                            <span class="badge badge-success">Exécutée</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if(count($migrations) > 15)
            <p class="text-muted" style="font-size: 8.5pt; text-align: center; font-style: italic;">
                Affichage des 15 premières migrations. Voir les pages suivantes pour le catalogue technique complet des {{ count($migrations) }} migrations.
            </p>
        @endif
    </div>

    <!-- Page 2+: Detailed catalogue of all migrations -->
    <div class="page">
        <h1>2. Catalogue Technique des Migrations</h1>
        
        @foreach($migrations as $m)
            <div class="migration-card">
                <div class="migration-header">
                    <div class="migration-title">{{ $m['name'] }}</div>
                    <div class="migration-status">
                        @if($m['status'] == 'Exécutée')
                            <span class="badge badge-success">Exécutée (Batch {{ $m['batch'] }})</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </div>
                    <div class="clear"></div>
                    <div class="migration-meta">
                        Fichier : <code>{{ $m['file'] }}</code>
                    </div>
                </div>

                <div style="font-size: 9pt; margin-bottom: 8px;">
                    @if(!empty($m['created_tables']))
                        <strong>Tables créées :</strong> 
                        @foreach($m['created_tables'] as $t)
                            <span class="badge badge-info">{{ $t }}</span>
                        @endforeach
                    @endif
                    
                    @if(!empty($m['altered_tables']))
                        <strong>Tables modifiées :</strong> 
                        @foreach($m['altered_tables'] as $t)
                            <span class="badge badge-info" style="background-color: #f3e8ff; color: #6b21a8;">{{ $t }}</span>
                        @endforeach
                    @endif
                    
                    @if(!empty($m['dropped_tables']))
                        <strong>Tables supprimées :</strong> 
                        @foreach($m['dropped_tables'] as $t)
                            <span class="badge badge-warning" style="background-color: #fee2e2; color: #991b1b;">{{ $t }}</span>
                        @endforeach
                    @endif
                </div>

                @if(!empty($m['columns']))
                    <table class="column-list">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Colonne / Champ</th>
                                <th style="width: 25%;">Type Migration</th>
                                <th style="width: 40%;">Description Technique</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($m['columns'] as $col)
                            <tr>
                                <td class="column-name">{{ $col['name'] }}</td>
                                <td><code style="color: #be185d;">{{ $col['type'] }}</code></td>
                                <td class="text-muted">{{ $col['desc'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted" style="font-size: 8pt; font-style: italic; margin-top: 5px;">
                        Aucune déclaration de colonne directe (migration d'index, suppression de table ou d'autres opérations structurelles).
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Final Page: Conclusion and info -->
    <div class="page" style="page-break-after: avoid;">
        <h1>3. Intégrité de la Base de Données</h1>
        <p>Le schéma actuel intègre toutes les fonctionnalités de G-Santé. Les dernières migrations notables incluent la refonte du conditionnement des médicaments avec l'introduction des unités multiples (boîtes, ampoules, flacons, etc.), ainsi que le déplacement des codes-barres vers la table des médicaments pour assurer un suivi optimal des stocks.</p>
        
        <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; margin-top: 30px;">
            <h3 style="margin-top: 0; color: #1e293b;">Note de synchronisation</h3>
            <p style="font-size: 9pt; margin-bottom: 0;">Ce document fait foi de l'état structurel de la base de données au {{ date('d/m/Y') }}. Toutes les tables mentionnées sont indispensables au bon fonctionnement des modules de Consultation, Pharmacie, Caisse, Hospitalisation et Infectiologie.</p>
        </div>
        
        <div class="footer-note">
            <p>Fin du rapport technique · G-Santé SIH · Rapport automatisé généré via Laravel & DomPDF</p>
        </div>
    </div>

</body>
</html>
