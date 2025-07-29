<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Item Returns Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
        .header-image {
            width: 100%;
            margin: 5px auto 10px auto; /* Small margin top and bottom */
            display: block;
        }
    </style>
</head>
<body>
    <h2>Item Returns Report</h2>
    <img src="{{ public_path('images/header.jpeg') }}" class="header-image" alt="Header Image">

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Event</th>
                <th>Event Date</th>
                <th>Good</th>
                <th>Damaged</th>
                <th>Lost</th>
                <th>Returned On</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returns as $r)
                <tr>
                    <td>{{ $r->item->name ?? '-' }}</td>
                    <td>{{ $r->stockMovement->requisition->event->event_name ?? '-' }}</td>
                    <td>{{ $r->stockMovement->requisition->event->event_date ?? '-' }}</td>
                    <td>{{ $r->good_condition }}</td>
                    <td>{{ $r->damaged_quantity }}</td>
                    <td>{{ $r->lost_quantity }}</td>
                    <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
