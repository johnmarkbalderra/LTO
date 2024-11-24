<?php
require_once "navbar.php";
require_once "db-connect.php"; // Include database connection file

// Check if the admin is logged in (uncomment and adjust as needed)
// if (!isset($_SESSION['admin_id'])) {
//    header("Location: admin_login.php"); 
//    exit;
// }

// Handle account activation/deactivation
if (isset($_POST['toggle_activation'])) {
    $user_id = $_POST['user_id'];
    $isActive = $_POST['isActive'] ? 0 : 1;
    
    $updateQuery = "UPDATE users SET IsActive = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $isActive, $user_id);
    $updateStmt->execute();
}

// Pagination settings
$perPage = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($page - 1) * $perPage;

// Search term
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Query to get the total number of users (for pagination)
$countQuery = "SELECT COUNT(*) AS total FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Query to fetch users with pagination and search
$fetchQuery = "SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchQuery);
$fetchStmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $perPage, $offset);
$fetchStmt->execute();
$fetchResult = $fetchStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/fullcalendar/lib/main.min.css">
    <style>
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }
        table, tbody, td, tfoot, th, thead, tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>User List</h2>
            <br>

        <form class="mb-3" method="get">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>IsActive</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $fetchResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['user_id']); ?>">
                            <input type="hidden" name="isActive" value="<?php echo htmlspecialchars($row['IsActive']); ?>">
                            <button type="submit" name="toggle_activation" class="btn btn-sm <?php echo $row['IsActive'] ? 'btn-success' : 'btn-secondary'; ?>">
                                <?php echo $row['IsActive'] ? 'Activated' : 'Inactivated'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <!-- <a href="edit_user.php?id=<?php echo htmlspecialchars($row['user_id']); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a> -->
                        <a href="delete_user.php?id=<?php echo htmlspecialchars($row['user_id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <script src="./assets/js/script.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
