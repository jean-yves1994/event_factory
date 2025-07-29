<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Returns Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h2>Item Returns for Event: {{ $event->event_name }}</h2>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Good</th>
                <th>Damaged</th>
                <th>Lost</th>
                <th>Returned On</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemReturns as $return)
    <tr>
        <td>{{ $return->stockMovement->item->name ?? '-' }}</td>
        <td>{{ $return->good_condition }}</td>
        <td>{{ $return->damaged_quantity }}</td>
        <td>{{ $return->lost_quantity }}</td>
        <td>{{ $return->created_at->format('Y-m-d H:i') }}</td>
    </tr>
@endforeach
        </tbody>
    </table>
</body>
</html>
