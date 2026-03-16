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
                gei.allowed_guests,
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Wedding RSVP</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
        <a class="btn" href="index.php">Back</a>
    <?php else: ?>
        <h2>Hello, <?php echo e($guest['name']); ?></h2>
        <p class="small">Please RSVP for the events below.</p>

        <form method="post" action="submit_rsvp.php">
            <input type="hidden" name="phone" value="<?php echo e($guest['phone']); ?>">

            <?php foreach ($invites as $invite): ?>
                <div class="event-card">
                    <h3><?php echo e($invite['event_name']); ?></h3>
                    <p class="small"><?php echo e($invite['event_label']); ?> | <?php echo e($invite['event_date']); ?></p>

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
                        max="<?php echo (int)$invite['allowed_guests']; ?>"
                        value="<?php echo e($invite['guest_count'] ?? ''); ?>"
                        required
                    >

                    <label>Message (optional)</label>
                    <textarea name="message_<?php echo (int)$invite['invite_id']; ?>"><?php echo e($invite['message'] ?? ''); ?></textarea>
                </div>
            <?php endforeach; ?>

            <button type="submit">Submit RSVP</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
