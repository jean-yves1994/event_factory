<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Requisition Notification</title>
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
            background-color: #0046FF;
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
            color: #0046FF;
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
                📋 New Event Requisition Created
            </div>
            <div class="content">
                <p>Hello Admin,</p>
                <p>A new event has been created and its requisition is waiting for your approval. Here are the details:</p>
                <ul class="details">
                    <li><strong>Event Name:</strong> {{ $event->event_name }}</li>
                    <li><strong>Event Date:</strong> {{ $event->event_date }}</li>
                    <li><strong>Location:</strong> {{ $event->event_location }}</li>
                    <li><strong>Customer:</strong> {{ $event->customer }}</li>
                    <li><strong>Urgency:</strong> {{ ucfirst($event->urgency) }}</li>
                </ul>
                <p>Please log in to the system to review and approve the requisition.</p>
            </div>
            <div class="footer">
                This is an automated message from <strong>Event Factory</strong>. Please do not reply directly.
            </div>
        </div>
    </div>
</body>
</html>
