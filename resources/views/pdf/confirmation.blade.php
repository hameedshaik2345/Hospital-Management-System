<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Confirmation</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; }
        .container { padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0066CC; padding-bottom: 10px; }
        .header h1 { color: #0066CC; margin: 0; }
        .details { margin-top: 30px; }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .details-table .label { font-weight: bold; color: #555; width: 150px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #888; }
    </style>

    <link rel="icon" type="image/svg+xml" href="{{ asset('medflow-favicon.svg') }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MedFlow</h1>
            <p>Appointment Confirmation</p>
        </div>

        <div class="details">
            <p>Dear {{ $appointment->patient->name }},</p>
            <p>Your appointment has been successfully scheduled. Please find the details below:</p>
            
            <table class="details-table">
                <tr>
                    <td class="label">Appointment ID:</td>
                    <td>#{{ $appointment->id }}</td>
                </tr>
                <tr>
                    <td class="label">Doctor:</td>
                    <td>{{ $appointment->doctor->name }} ({{ $appointment->doctor->specialty }})</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Time:</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Reason for Visit:</td>
                    <td>{{ $appointment->reason ?? 'Not given' }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing MedFlow. Please arrive 15 minutes early for your appointment.</p>
            <p>If you need to reschedule, please visit your patient portal.</p>
        </div>
    </div>
</body>
</html>