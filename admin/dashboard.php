<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$msg = $_GET['msg'] ?? '';
$msgType = $_GET['msg_type'] ?? '';

$events = [];
$res = $conn->query('SELECT id, event_name, event_label, event_date FROM events ORDER BY event_date ASC');
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}

$editGuest = null;
$editInvites = [];

if (isset($_GET['edit_guest_id'])) {
    $editGuestId = (int)$_GET['edit_guest_id'];

    $stmt = $conn->prepare('SELECT id, name, phone FROM guests WHERE id = ?');
    $stmt->bind_param('i', $editGuestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editGuest = $result->fetch_assoc();
    $stmt->close();

    if ($editGuest) {
        $stmt = $conn->prepare('SELECT event_id, allowed_guests FROM guest_event_invites WHERE guest_id = ?');
        $stmt->bind_param('i', $editGuestId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $editInvites[(int)$row['event_id']] = (int)$row['allowed_guests'];
        }
        $stmt->close();
    }
}

$guests = [];
$sql = "
    SELECT
        g.id,
        g.name,
        g.phone,
        GROUP_CONCAT(CONCAT(e.event_label, ' - ', e.event_name) ORDER BY e.event_date SEPARATOR '<br>') AS invited_events
    FROM guests g
    LEFT JOIN guest_event_invites gei ON gei.guest_id = g.id
    LEFT JOIN events e ON e.id = gei.event_id
    GROUP BY g.id, g.name, g.phone
    ORDER BY g.created_at DESC
";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $guests[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h1>Wedding Admin</h1>
        <div>
            <a class="btn" href="export_csv.php">Export CSV</a>
            <a class="btn btn-secondary" href="../logout.php">Logout</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert <?php echo $msgType === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo e($msg); ?>
        </div>
    <?php endif; ?>

    <h2><?php echo $editGuest ? 'Edit Guest' : 'Add Guest'; ?></h2>

    <form method="post" action="guest_save.php">
        <input type="hidden" name="guest_id" value="<?php echo e($editGuest['id'] ?? ''); ?>">

        <div class="grid-2">
            <div>
                <label>Guest / Family Name</label>
                <input type="text" name="name" value="<?php echo e($editGuest['name'] ?? ''); ?>" required>
            </div>
            <div>
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo e($editGuest['phone'] ?? ''); ?>" required>
            </div>
        </div>

        <h3>Event Invitations</h3>

        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <label>
                    <input
                        type="checkbox"
                        name="event_ids[]"
                        value="<?php echo (int)$event['id']; ?>"
                        <?php echo isset($editInvites[(int)$event['id']]) ? 'checked' : ''; ?>
                    >
                    <?php echo e($event['event_label']); ?> - <?php echo e($event['event_name']); ?> (<?php echo e($event['event_date']); ?>)
                </label>

                <label>Allowed Guests</label>
                <input
                    type="number"
                    min="1"
                    name="allowed_<?php echo (int)$event['id']; ?>"
                    value="<?php echo e($editInvites[(int)$event['id']] ?? '1'); ?>"
                >
            </div>
        <?php endforeach; ?>

        <button type="submit"><?php echo $editGuest ? 'Update Guest' : 'Save Guest'; ?></button>
        <?php if ($editGuest): ?>
            <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h2 style="margin-top:30px;">Guests</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Invited Events</th>
                <th>RSVP Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($guests as $guest): ?>
            <tr>
                <td><?php echo e($guest['name']); ?></td>
                <td><?php echo e($guest['phone']); ?></td>
                <td><?php echo $guest['invited_events'] ?: '-'; ?></td>
                <td><a href="<?php echo e(build_rsvp_link($guest['phone'])); ?>" target="_blank">Open RSVP</a></td>
                <td class="actions">
                    <a class="btn" href="dashboard.php?edit_guest_id=<?php echo (int)$guest['id']; ?>">Edit</a>

                    <form method="post" action="send_invite.php">
                        <input type="hidden" name="guest_id" value="<?php echo (int)$guest['id']; ?>">
                        <input type="hidden" name="channel" value="sms">
                        <button type="submit">Send SMS</button>
                    </form>

                    <form method="post" action="send_invite.php">
                        <input type="hidden" name="guest_id" value="<?php echo (int)$guest['id']; ?>">
                        <input type="hidden" name="channel" value="whatsapp">
                        <button type="submit">Send WhatsApp</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
