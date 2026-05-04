<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .page {
            width: 100%;
            padding: 20px;
        }
        .ticket {
            border: 1px solid #dee2e6;
            padding: 25px;
            margin-bottom: 40px;
            border-radius: 10px;
            position: relative;
            background: #fff;
        }
        .ticket:after {
            content: "Coupure - Garder cette partie";
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            color: #ccc;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="page">
    {{-- 🔹 Premier ticket A5 --}}
    <div class="ticket">
        @include('application.ticket.ticket_content', ['ticket' => $ticket])
    </div>

    {{-- 🔹 Deuxième ticket A5 (identique) --}}
    <div class="ticket" style="border-style: dashed;">
        @include('application.ticket.ticket_content', ['ticket' => $ticket])
    </div>
</div>
</body>
</html>
