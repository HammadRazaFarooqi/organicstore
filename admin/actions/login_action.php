<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Get PDO connection from the Database class
    $database = new Database();
    $pdo = $database->getConnection();

    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // fetch as assoc array

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            header('Location: ../dashboard.php');
            exit();
        } else {
            $_SESSION['login_error'] = 'Invalid email or password';
            header('Location: ../index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Database error occurred';
        header('Location: ../index.php');
        exit();
    }
}
?>
<?php