<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=wedding_rsvp_export.csv');

$out = fopen('php://output', 'w');

fputcsv($out, [
    'Guest Name',
    'Phone',
    'Event',
    'Event Date',
    'Allowed Guests',
    'RSVP Status',
    'Guest Count',
    'Message',
    'Responded At',
    'Invite Sent SMS',
    'Invite Sent WhatsApp',
]);

$sql = '
    SELECT
        g.name,
        g.phone,
        e.event_name,
        e.event_date,
        gei.allowed_guests,
        gei.rsvp_status,
        gei.guest_count,
        gei.message,
        gei.responded_at,
        gei.invite_sent_sms,
        gei.invite_sent_whatsapp
    FROM guest_event_invites gei
    INNER JOIN guests g ON g.id = gei.guest_id
    INNER JOIN events e ON e.id = gei.event_id
    ORDER BY g.name ASC, e.event_date ASC
';

$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    fputcsv($out, $row);
}

fclose($out);
exit;
