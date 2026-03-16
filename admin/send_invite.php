<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/messaging.php';

$guestId = (int)($_POST['guest_id'] ?? 0);
$channel = $_POST['channel'] ?? '';

if ($guestId <= 0 || !in_array($channel, ['sms', 'whatsapp'], true)) {
    redirect_with_message('dashboard.php', 'error', 'Invalid invite request.');
}

$stmt = $conn->prepare('SELECT id, name, phone FROM guests WHERE id = ?');
$stmt->bind_param('i', $guestId);
$stmt->execute();
$result = $stmt->get_result();
$guest = $result->fetch_assoc();
$stmt->close();

if (!$guest) {
    redirect_with_message('dashboard.php', 'error', 'Guest not found.');
}

$stmt = $conn->prepare('
    SELECT e.id, e.event_name, e.event_date, e.event_label
    FROM guest_event_invites gei
    INNER JOIN events e ON e.id = gei.event_id
    WHERE gei.guest_id = ?
    ORDER BY e.event_date ASC
');
$stmt->bind_param('i', $guestId);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

if (!$events) {
    redirect_with_message('dashboard.php', 'error', 'No events are assigned to this guest.');
}

$result = $channel === 'sms'
    ? send_sms_invite($guest, $events)
    : send_whatsapp_invite($guest, $events);

log_message_send($conn, $guestId, $channel, $result);

if ($result['success']) {
    mark_invites_sent($conn, $guestId, $channel);
    redirect_with_message('dashboard.php', 'success', strtoupper($channel) . ' invite sent to ' . $guest['name'] . '.');
}

redirect_with_message('dashboard.php', 'error', 'Failed to send ' . $channel . ' invite.');
