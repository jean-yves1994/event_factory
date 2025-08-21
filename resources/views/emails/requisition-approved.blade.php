<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Requisition Approved</title>
    <style>
        body {
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            background-color: #FAF8F5;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            padding: 20px 0;
            background-color: #FAF8F5;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #28a745;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .details {
            background-color: #f4f6f8;
            border-radius: 8px;
            padding: 20px;
        }
        .details li {
            list-style: none;
            margin-bottom: 10px;
            font-size: 15px;
            line-height: 1.5;
        }
        .details li strong {
            color: #28a745;
        }
        .footer {
            padding: 20px;
            font-size: 13px;
            color: #7f8c8d;
            text-align: center;
        }
        @media (max-width: 480px) {
            .container { width: 90% !important; }
            .header { font-size: 20px !important; padding: 20px 10px; }
            .content { padding: 20px 10px; }
            .details { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                ✅ Requisition Approved
            </div>
            <div class="content">
                <p>Hello,</p>
                <p>The requisition for the event <strong>{{ $requisition->event->event_name }}</strong> has been approved. Please prepare the items listed below for pickup:</p>
                <ul class="details">
                    <li><strong>Event Name:</strong> {{ $requisition->event->event_name }}</li>
                    <li><strong>Event Date:</strong> {{ $requisition->event->event_date }}</li>
                    <li><strong>Pickup Date:</strong> {{ $requisition->expected_pickup_date }}</li>
                    <li><strong>Return Date:</strong> {{ $requisition->expected_return_date }}</li>
                </ul>
                <p><strong>Items Requested:</strong></p>
                <ul class="details">
                    @foreach ($requisition->items as $item)
                        <li>{{ $item->name }} ({{ $item->pivot->quantity }})</li>
                    @endforeach
                </ul>
                <p>Please log in to the system to manage the stock movement.</p>
            </div>
            <div class="footer">
                This is an automated message from <strong>Event Factory</strong>. Please do not reply directly.
            </div>
        </div>
    </div>
</body>
</html>