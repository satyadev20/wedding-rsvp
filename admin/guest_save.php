<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$guestId = (int)($_POST['guest_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$phone = normalize_phone($_POST['phone'] ?? '');
$eventIds = $_POST['event_ids'] ?? [];

if ($name === '' || !is_valid_us_phone($phone)) {
    redirect_with_message('dashboard.php', 'error', 'Please enter a valid name and 10-digit phone number.');
}

$conn->begin_transaction();

try {
    if ($guestId > 0) {
        $stmt = $conn->prepare('UPDATE guests SET name = ?, phone = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $phone, $guestId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM guest_event_invites WHERE guest_id = ?');
        $stmt->bind_param('i', $guestId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare('INSERT INTO guests (name, phone) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $phone);
        $stmt->execute();
        $guestId = $stmt->insert_id;
        $stmt->close();
    }

    foreach ($eventIds as $eventId) {
        $eventId = (int)$eventId;
        $allowedGuests = max(1, (int)($_POST["allowed_$eventId"] ?? 1));

        $stmt = $conn->prepare('INSERT INTO guest_event_invites (guest_id, event_id, allowed_guests) VALUES (?, ?, ?)');
        $stmt->bind_param('iii', $guestId, $eventId, $allowedGuests);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    redirect_with_message('dashboard.php', 'success', 'Guest saved successfully.');
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with_message('dashboard.php', 'error', 'Failed to save guest: ' . $e->getMessage());
}
