<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .page {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
        }
        .ticket {
            border: 1px dashed #000;
            padding-right: 20px;
            padding-left: 20px;
            padding-top: 10px;
            margin-top: 50px;
            padding-bottom: 20px;
            box-sizing: border-box;
        }
        h2, h3 {
            margin: 0;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .footer {
            text-align: right;
            margin-top: 10px;
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
    <div class="ticket">
        @include('application.ticket.ticket_content', ['ticket' => $ticket])
    </div>
</div>
</body>
</html>
