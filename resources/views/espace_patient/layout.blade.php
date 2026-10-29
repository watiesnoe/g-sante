<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a3366">
    <title>@yield('titre', 'Espace patient') — {{ config('gsante.nom_espace_patient') }}</title>
    <link rel="manifest" href="{{ route('patient.manifeste') }}">
    <link rel="icon" href="/patient-icone-192.png">
    <link rel="apple-touch-icon" href="/patient-icone-180.png">
    <style>
        :root { --bleu: #0a3366; --bleu-clair: #e8f1f7; --texte: #1d2733; --gris: #6b7684; --fond: #f5f7f9; --bord: #dde3e9;
                --vert: #1e7e34; --rouge: #b42318; --orange: #9a5b00; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; font-size: 16px; line-height: 1.45; color: var(--texte); background: var(--fond); }
        header { background: var(--bleu); color: #fff; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        header a, header button { color: #fff; text-decoration: none; background: none; border: 0; font: inherit; cursor: pointer; padding: 0; }
        header .marque { font-weight: 700; letter-spacing: .3px; }
        main { max-width: 560px; margin: 0 auto; padding: 16px; }
        h1 { font-size: 1.3rem; margin: 4px 0 12px; }
        h2 { font-size: 1.05rem; margin: 18px 0 8px; }
        .carte { background: #fff; border: 1px solid var(--bord); border-radius: 10px; padding: 14px; margin-bottom: 12px; }
        label { display: block; font-weight: 600; margin: 10px 0 4px; }
        input, select, textarea { width: 100%; font: inherit; padding: 11px 12px; border: 1px solid var(--bord); border-radius: 8px; background: #fff; }
        input:focus, select:focus, textarea:focus { outline: 2px solid var(--bleu); outline-offset: 1px; }
        .btn { display: inline-block; width: 100%; text-align: center; font: inherit; font-weight: 600; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--bleu); background: var(--bleu); color: #fff; text-decoration: none; cursor: pointer; margin-top: 12px; }
        .btn.sec { background: #fff; color: var(--bleu); }
        .btn.petit { width: auto; padding: 8px 12px; margin-top: 8px; font-size: .92rem; }
        .btn.danger { background: #fff; color: var(--rouge); border-color: var(--rouge); }
        .ligne { display: flex; gap: 8px; flex-wrap: wrap; }
        .aide { color: var(--gris); font-size: .9rem; }
        .alerte { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
        .alerte.info { background: var(--bleu-clair); }
        .alerte.erreur { background: #fdecea; color: var(--rouge); }
        .alerte.demo { background: #fff4e5; color: var(--orange); border: 1px dashed var(--orange); }
        .badge { display: inline-block; font-size: .8rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: var(--bleu-clair); color: var(--bleu); }
        .badge.confirmee { background: #e6f4ea; color: var(--vert); }
        .badge.refusee, .badge.conflit { background: #fdecea; color: var(--rouge); }
        .badge.annulee { background: #eceff2; color: var(--gris); }
        .badge.proposee { background: #fff4e5; color: var(--orange); }
        .creneaux { display: grid; grid-template-columns: repeat(auto-fill, minmax(78px, 1fr)); gap: 8px; }
        .creneaux input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .creneaux label { margin: 0; font-weight: 500; text-align: center; padding: 10px 4px; border: 1px solid var(--bord); border-radius: 8px; background: #fff; cursor: pointer; }
        .creneaux input:checked + span { color: #fff; }
        .creneaux label:has(input:checked) { background: var(--bleu); border-color: var(--bleu); color: #fff; }
        .creneaux input:focus-visible + span { outline: 2px solid var(--bleu); }
        a.carte { display: block; color: inherit; text-decoration: none; }
        .vide { text-align: center; color: var(--gris); padding: 24px 8px; }
        footer { text-align: center; color: var(--gris); font-size: .8rem; padding: 24px 16px; }
    </style>
</head>
<body>
    <header>
        <a class="marque" href="{{ route('patient.accueil') }}"><img src="/patient-icone-192.png" alt="" width="28" height="28" style="vertical-align:middle;border-radius:50%;background:#fff;margin-right:8px">{{ config('gsante.nom_espace_patient') }}</a>
        @if(session()->has(\App\Http\Middleware\PatientConnecte::SESSION))
            <form method="POST" action="{{ route('patient.deconnexion') }}">@csrf<button type="submit">Se déconnecter</button></form>
        @endif
    </header>
    <main>
        @if(session('info'))<div class="alerte info" role="status">{{ session('info') }}</div>@endif
        @if(session('erreur'))<div class="alerte erreur" role="alert">{{ session('erreur') }}</div>@endif
        @if($errors->any())<div class="alerte erreur" role="alert">{{ $errors->first() }}</div>@endif
        @yield('contenu')
    </main>
    <footer>Vos données ne servent qu'à la prise de rendez-vous. Le dossier médical reste dans l'établissement.</footer>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ route('patient.sw', [], false) }}', { scope: '/patient/' }).catch(function () {});
        }
    </script>
</body>
</html>
