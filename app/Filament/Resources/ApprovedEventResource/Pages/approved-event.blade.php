<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approved Event Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Event Name: {{ $event->event_name }}</h1>
    <p>Event Date: {{ $event->event_date }}</p>
    <p>Event Location: {{ $event->event_location }}</p>
    <p>Responsible Person Name: {{ $event->responsible_person_name }}</p>
    <p>Responsible Person Phone: {{ $event->responsible_person_phone }}</p>


    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Action Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockMovements as $movement)
                <tr>
                    <td>{{ $movement->item->name }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->action_date ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
