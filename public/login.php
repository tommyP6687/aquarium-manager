<?php 
//establish session management safely 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//load environment settings and dependencies using relative pathing 
require_once __DIR__ . '../config/database.php';
require_once __DIR__ .'../includes/auth.php';
require_once __DIR__ .'../includes/functions.php';

//bounce already authorized managers to dashboard immediately 
if (isset($_SESSION['user_id'])) {
    header("Location: /index.php");
}   

$errorMessage = "";

//evaluate form transmission block
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST['password'] ??'');

    if ($username !== '' && $password !== '') {
        try {
            //query user account using the PDO instance ($pdo) initialized in database.php
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            //cryptographic validation checkpoint
            if ($user && password_verify($password, $user['password_hash'])) {
                //prevent session hijacking via fixation vectors
                session_regenerate_id(true);

                //establish persistence state parameters
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: /index.php");
                exit;
            } else {
                $errorMessage = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            //mitigate systemic information exposure leaks to clients 
            error_log("Login processing anomaly: " . $e->getMessage());
            $errorMessage = "An infrastructure resource issue occured. Please retry.";
        }
    } else {
        $errorMessage = "Please complete all mandatory credential inputs.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Login</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <!-- Modular navigation architecture link hook -->
         <?php include __DIR__ . '/../includes/nav.php'; ?>

         <main class="login-wrapper">
            <section class="login-card">
                <h2>Login</h2>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="POST" nonvalidate>
                    <div class="input-component">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" >
                    </div>

                    <div class="input-component">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>

                    <div class="action-component">
                        <button type="submit" class="btn-primary">Login</button>
                    </div>
                </form>
            </section>
         </main>
    </body>
</html>