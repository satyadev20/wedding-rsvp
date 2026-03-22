<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$phone = normalize_phone($_POST['phone'] ?? '');
$inviteIds = $_POST['invite_ids'] ?? [];
$error = '';

if (!is_valid_us_phone($phone)) {
    $error = 'Invalid phone number.';
}

$stmt = $conn->prepare('SELECT id, name FROM guests WHERE phone = ?');
$stmt->bind_param('s', $phone);
$stmt->execute();
$result = $stmt->get_result();
$guest = $result->fetch_assoc();
$stmt->close();

if (!$guest) {
    $error = 'Guest not found.';
}

if (!$error && empty($inviteIds)) {
    $error = 'No event invitations were submitted.';
}

if (!$error) {
    foreach ($inviteIds as $inviteId) {
        $inviteId = (int)$inviteId;

        $stmt = $conn->prepare('SELECT id FROM guest_event_invites WHERE id = ? AND guest_id = ?');
        $stmt->bind_param('ii', $inviteId, $guest['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $invite = $result->fetch_assoc();
        $stmt->close();

        if (!$invite) {
            continue;
        }

        $status = isset($_POST["status_$inviteId"]) ? 'Yes' : 'No';
        $guestCount = isset($_POST["guest_count_$inviteId"]) ? (int)$_POST["guest_count_$inviteId"] : 0;
        $message = trim($_POST["message_$inviteId"] ?? '');

        if ($status === 'No') {
            $guestCount = 0;
        }

        if ($guestCount < 0) {
            $guestCount = 0;
        }

        if ($status === 'Yes' && $guestCount < 1) {
            $guestCount = 1;
        }

        $stmt = $conn->prepare('UPDATE guest_event_invites SET rsvp_status = ?, guest_count = ?, message = ?, responded_at = NOW() WHERE id = ? AND guest_id = ?');
        $stmt->bind_param('sisii', $status, $guestCount, $message, $inviteId, $guest['id']);
        $stmt->execute();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP Saved</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
        <a class="btn" href="index.php">Back</a>
    <?php else: ?>
        <div class="alert alert-success">Thank you! Your RSVP has been recorded.</div>
        <a class="btn" href="index.php">Done</a>
    <?php endif; ?>
</div>
</body>
</html>
