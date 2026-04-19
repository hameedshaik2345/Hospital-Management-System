<!DOCTYPE html>
<html>
<head>
    <title>Token Alert</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f9fc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #0066cc; text-align: center;">MedFlow Token Alert</h2>
        
        <p style="font-size: 16px; color: #333333;">Your turn is approaching!</p>
        
        <div style="background-color: #f0f7ff; padding: 15px; border-left: 4px solid #0066cc; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Doctor:</strong> Dr. {{ $doctorName }}</p>
            <p style="margin: 5px 0;"><strong>Your Token:</strong> #{{ $patientToken }}</p>
            <p style="margin: 5px 0;"><strong>Current Serving:</strong> #{{ $currentToken }}</p>
        </div>
        
        <p style="font-size: 16px; color: #333333; font-weight: bold;">
            There are only {{ $tokensLeft }} patients before you.
        </p>
        
        <p style="font-size: 14px; color: #555555; text-align: center; margin-top: 30px;">
            Please head to the clinic to ensure you do not miss your appointment.<br>
            Thank you for choosing MedFlow Healthcare.
        </p>
    </div>
</body>
</html>
