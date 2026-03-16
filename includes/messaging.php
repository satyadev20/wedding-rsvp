<?php

/*
 * Twilio configuration
 * Replace all placeholder values before use.
 */
define('TWILIO_ACCOUNT_SID', 'YOUR_TWILIO_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
define('TWILIO_SMS_FROM', '+10000000000');
define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');

function twilio_send_message(string $to, string $from, string $body): array {
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';

    $postFields = http_build_query([
        'To' => $to,
        'From' => $from,
        'Body' => $body,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return [
            'success' => false,
            'status' => 'curl_error',
            'message_id' => null,
            'raw' => $curlError,
        ];
    }

    $decoded = json_decode((string)$response, true);

    if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['sid'])) {
        return [
            'success' => true,
            'status' => $decoded['status'] ?? 'queued',
            'message_id' => $decoded['sid'],
            'raw' => (string)$response,
        ];
    }

    return [
        'success' => false,
        'status' => $decoded['status'] ?? 'failed',
        'message_id' => null,
        'raw' => (string)$response,
    ];
}

function build_invite_message(array $guest, array $events): string {
    $lines = [];
    foreach ($events as $event) {
        $lines[] = '- ' . $event['event_name'] . ' (' . $event['event_label'] . ' | ' . $event['event_date'] . ')';
    }

    $link = build_rsvp_link($guest['phone']);

    return "Hi {$guest['name']},\n\n"
        . "You are invited to:\n"
        . implode("\n", $lines)
        . "\n\nPlease RSVP here:\n{$link}";
}

function send_sms_invite(array $guest, array $events): array {
    $to = '+1' . $guest['phone'];
    return twilio_send_message($to, TWILIO_SMS_FROM, build_invite_message($guest, $events));
}

function send_whatsapp_invite(array $guest, array $events): array {
    $to = 'whatsapp:+1' . $guest['phone'];
    return twilio_send_message($to, TWILIO_WHATSAPP_FROM, build_invite_message($guest, $events));
}

function log_message_send(mysqli $conn, int $guestId, string $channel, array $result): void {
    $providerMessageId = $result['message_id'] ?? null;
    $status = $result['status'] ?? null;
    $payload = $result['raw'] ?? null;

    $stmt = $conn->prepare('INSERT INTO message_logs (guest_id, channel, provider_message_id, status, payload) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issss', $guestId, $channel, $providerMessageId, $status, $payload);
    $stmt->execute();
    $stmt->close();
}

function mark_invites_sent(mysqli $conn, int $guestId, string $channel): void {
    if ($channel === 'sms') {
        $sql = 'UPDATE guest_event_invites SET invite_sent_sms = 1, invite_sent_sms_at = NOW() WHERE guest_id = ?';
    } else {
        $sql = 'UPDATE guest_event_invites SET invite_sent_whatsapp = 1, invite_sent_whatsapp_at = NOW() WHERE guest_id = ?';
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $guestId);
    $stmt->execute();
    $stmt->close();
}
