<?php
// manage_holidays.php
include('db-connect.php');

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add':
        $title = $_POST['title'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? null;
        $all_day = isset($_POST['all_day']) ? 1 : 0;
        $color = $_POST['color'] ?? '#ff9f89';

        if ($title && $start_date) {
            $stmt = $conn->prepare("INSERT INTO `holidays` (`title`, `start_date`, `end_date`, `all_day`, `color`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $title, $start_date, $end_date, $all_day, $color);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Holiday added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add holiday.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        }
        break;

    case 'edit':
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? null;
        $all_day = isset($_POST['all_day']) ? 1 : 0;
        $color = $_POST['color'] ?? '#ff9f89';

        if ($id && $title && $start_date) {
            $stmt = $conn->prepare("UPDATE `holidays` SET `title` = ?, `start_date` = ?, `end_date` = ?, `all_day` = ?, `color` = ? WHERE `id` = ?");
            $stmt->bind_param("ssissi", $title, $start_date, $end_date, $all_day, $color, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Holiday updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update holiday.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM `holidays` WHERE `id` = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Holiday deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete holiday.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
