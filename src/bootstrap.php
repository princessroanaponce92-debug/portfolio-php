/*
|--------------------------------------------------------------------------
| MAILJET EMAIL HELPER
|--------------------------------------------------------------------------
*/

function send_resend_email(
    string $to,
    string $subject,
    string $html,
    string $text = ''
): bool {
    $apiKey = getenv('MAILJET_API_KEY');
    $secretKey = getenv('MAILJET_SECRET_KEY');

    $fromEmail = getenv('MAILJET_FROM_EMAIL')
        ?: 'princessroanaponce92@gmail.com';

    $fromName = getenv('MAILJET_FROM_NAME') ?: 'PortfolioGen';

    if (!$apiKey || !$secretKey) {
        throw new RuntimeException(
            'Mailjet is not configured. Check the Render environment variables.'
        );
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL is not enabled.');
    }

    $message = [
        'From' => [
            'Email' => $fromEmail,
            'Name' => $fromName,
        ],
        'To' => [
            ['Email' => $to],
        ],
        'Subject' => $subject,
        'HTMLPart' => $html,
    ];

    if ($text !== '') {
        $message['TextPart'] = $text;
    }

    $payload = [
        'Messages' => [$message],
    ];

    $ch = curl_init('https://api.mailjet.com/v3.1/send');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => $apiKey . ':' . $secretKey,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        error_log('Mailjet connection failed: ' . $curlError);

        throw new RuntimeException(
            'Could not connect to the email service.'
        );
    }

    $result = json_decode($response, true);
    $messageStatus = $result['Messages'][0]['Status'] ?? '';

    if (
        $status < 200 ||
        $status >= 300 ||
        $messageStatus !== 'success'
    ) {
        error_log(
            'Mailjet returned HTTP ' . $status . ': ' . $response
        );

        throw new RuntimeException(
            'Mailjet could not send the email. Check the Mailjet dashboard logs.'
        );
    }

    return true;
}