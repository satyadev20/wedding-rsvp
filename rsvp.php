<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$phone = normalize_phone($_POST['phone'] ?? $_GET['phone'] ?? '');
$error = '';
$guest = null;
$invites = [];

if (!$phone || !is_valid_us_phone($phone)) {
    $error = 'Please enter a valid 10-digit phone number.';
} else {
    $stmt = $conn->prepare('SELECT id, name, phone FROM guests WHERE phone = ?');
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $guest = $result->fetch_assoc();
    $stmt->close();

    if (!$guest) {
        $error = 'Invitation not found for that phone number.';
    } else {
        $stmt = $conn->prepare('
            SELECT
                gei.id AS invite_id,
                gei.rsvp_status,
                gei.guest_count,
                gei.message,
                e.event_name,
                e.event_date,
                e.event_label
            FROM guest_event_invites gei
            INNER JOIN events e ON e.id = gei.event_id
            WHERE gei.guest_id = ?
            ORDER BY e.event_date ASC
        ');
        $stmt->bind_param('i', $guest['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $invites[] = $row;
        }
        $stmt->close();

        if (!$invites) {
            $error = 'No event invitations were found.';
        }
    }
}

$invitedEventCount = count($invites);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="wedding-body">
<div class="container invitation-shell">
    <h1>Wedding RSVP</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
        <a class="btn btn-luxury" href="index.php">Back</a>
    <?php else: ?>
        <div class="invitation-summary">
            <p class="eyebrow">Your Invitation</p>
            <h2>Hello, <?php echo e($guest['name']); ?></h2>
            <p class="section-copy invitation-copy">
                We found <?php echo $invitedEventCount; ?> event<?php echo $invitedEventCount === 1 ? '' : 's'; ?> on your invitation.
                Please RSVP only for the celebrations listed below.
            </p>
            <div class="invitation-stats">
                <div class="invitation-stat">
                    <span class="invitation-stat-label">Invited Events</span>
                    <strong><?php echo $invitedEventCount; ?></strong>
                </div>
            </div>

            <div class="invitation-events-overview">
                <p class="invitation-events-title">Events on your invitation</p>
                <div class="invitation-events-list">
                    <?php foreach ($invites as $invite): ?>
                        <div class="invitation-events-item">
                            <strong><?php echo e($invite['event_name']); ?></strong>
                            <span><?php echo e($invite['event_label']); ?> | <?php echo e($invite['event_date']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <form method="post" action="submit_rsvp.php">
            <input type="hidden" name="phone" value="<?php echo e($guest['phone']); ?>">

            <?php foreach ($invites as $invite): ?>
                <div class="event-card invitation-event-card">
                    <div class="invitation-event-header">
                        <div>
                            <p class="event-invite-badge">Included in your invitation</p>
                            <h3><?php echo e($invite['event_name']); ?></h3>
                            <p class="small"><?php echo e($invite['event_label']); ?> | <?php echo e($invite['event_date']); ?></p>
                        </div>
                    </div>

                    <input type="hidden" name="invite_ids[]" value="<?php echo (int)$invite['invite_id']; ?>">

                    <label>Will you attend?</label>
                    <select name="status_<?php echo (int)$invite['invite_id']; ?>" required>
                        <option value="">Select</option>
                        <option value="Yes" <?php echo (($invite['rsvp_status'] ?? '') === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="No" <?php echo (($invite['rsvp_status'] ?? '') === 'No') ? 'selected' : ''; ?>>No</option>
                    </select>

                    <label>Number of Guests Attending</label>
                    <input
                        type="number"
                        name="guest_count_<?php echo (int)$invite['invite_id']; ?>"
                        min="0"
                        value="<?php echo e($invite['guest_count'] ?? ''); ?>"
                        required
                    >

                    <label>Message (optional)</label>
                    <textarea name="message_<?php echo (int)$invite['invite_id']; ?>"><?php echo e($invite['message'] ?? ''); ?></textarea>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-luxury">Submit RSVP</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
