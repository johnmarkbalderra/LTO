<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'ltonew';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Form handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['telephone']);
    $password = trim($_POST['password']);
    $passwordRepeat = trim($_POST['password_repeat']);

    if ($password !== $passwordRepeat) {
        $register_status = "error";
        $error_message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $register_status = "error";
            $error_message = "Email is already registered.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert the new user into the database
            $sql = "INSERT INTO users (email, password, role, address, phone_number) VALUES (?, ?, 'user', ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $hashedPassword, $firstName . ' ' . $lastName, $phoneNumber]);

            $register_status = "success";
            $success_message = "Registration successful. You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LTO</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="icon" type="image/jpg" href="../assets/img/LTO-logo.jpg">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-12 col-xl-10">
                <div class="card shadow-lg o-hidden border-0 my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-flex">
                                <div class="flex-grow-1 bg-register-image" style="background-image: url('../assets/img/LTO-logo.jpg');"></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h4 class="text-dark mb-4">Create an Account!</h4>
                                    </div>
                                    <?php if (isset($register_status) && $register_status == "error"): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $error_message; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($register_status) && $register_status == "success"): ?>
                                    <div class="alert alert-success">
                                        <?php echo $success_message; ?>
                                    </div>
                                    <?php endif; ?>
                                    <form class="user" action="register.php" method="post">
                                        <div class="row mb-3">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <input class="form-control form-control-user" type="text" id="exampleFirstName" placeholder="First Name" name="first_name" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <input class="form-control form-control-user" type="text" id="exampleLastName" placeholder="Last Name" name="last_name" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <input class="form-control form-control-user" type="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Email Address" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <input class="form-control form-control-user" type="tel" id="exampleInputPhone" aria-describedby="phoneHelp" placeholder="Phone Number" name="telephone" required>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <input class="form-control form-control-user" type="password" id="examplePasswordInput" placeholder="Password" name="password" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <input class="form-control form-control-user" type="password" id="exampleRepeatPasswordInput" placeholder="Repeat Password" name="password_repeat" required>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary d-block btn-user w-100" type="submit">Register Account</button>
                                        <hr>
                                    </form>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="index.html">Already have an account? Login!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
