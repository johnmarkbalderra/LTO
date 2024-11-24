<?php
require_once 'session.php';
require_once 'dbconnect.php';

// Fetch user ID from session
$userId = $_SESSION['user_id'];

// Fetch all notifications for the user, both read and unread
$notif_query = $conn->prepare("SELECT * FROM `notifications` WHERE `user_id` = ? ORDER BY `created_at` DESC");
$notif_query->bind_param("i", $userId);
$notif_query->execute();
$notif_result = $notif_query->get_result();
$notifications = $notif_result->fetch_all(MYSQLI_ASSOC);
$notif_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h1>Your Notifications</h1>
    <div class="list-group">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <a href="javascript:void(0);" 
                   class="list-group-item list-group-item-action <?= $notification['is_read'] ? 'list-group-item-secondary' : 'list-group-item-warning'; ?>" 
                   data-id="<?= $notification['id'] ?>" 
                   onclick="openNotificationModal(<?= $notification['id'] ?>)">
                    <div class="d-flex w-100 justify-content-between">
                        <!-- Render message with allowed HTML tags -->
                        <div>
                            <?= strip_tags($notification['message'], '<br><strong>'); ?>
                        </div>
                        <small class="text-muted"><?= date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></small>
                    </div>
                    <div>
                        <?php if ($notification['is_read']): ?>
                            <span class="badge bg-secondary">Read</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Unread</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">You have no notifications.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for Notification Details -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Notification details will be populated here via JavaScript -->
        <p id="notificationMessage"></p>
        <p><small id="notificationDate" class="text-muted"></small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap and JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Function to open the notification modal and fetch details
    function openNotificationModal(notificationId) {
        // Fetch notification details
        fetch('get_notification_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.text()) // Get the response as text first for debugging
        .then(text => {
            console.log('Raw Response:', text); // Log the raw response for inspection
            
            // Now try parsing the response as JSON
            let data;
            try {
                data = JSON.parse(text); 
            } catch (err) {
                console.error('Failed to parse JSON:', err);
                throw new Error('Invalid JSON format');
            }
            
            console.log(data); // Debugging line
            if (data.success) {
                // Set innerHTML to render HTML tags correctly
                document.getElementById('notificationMessage').innerHTML = data.message;
                document.getElementById('notificationDate').innerText = new Date(data.created_at).toLocaleString();

                // Show the modal
                var notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
                notificationModal.show();

                // Optionally, mark the notification as read via AJAX
                markNotificationAsRead(notificationId);
            } else {
                alert('Failed to load notification details.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Optional: Function to mark notification as read
    function markNotificationAsRead(notificationId) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optionally, update the notification badge or styling
                location.reload(); // Reload the page to reflect changes
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>
