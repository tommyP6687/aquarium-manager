<?php
require_once __DIR__ . '/../includes/auth.php';

redirectIfLoggedIn();

$errorMessage = "";
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } else {
        $result = registerUser($username, $email, $password);

        if ($result['success']) {
            redirect('login.php');
        } else {
            $errorMessage = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Sign Up</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

         <main class="login-wrapper">
            <section class="login-card">
                <h2>Sign Up</h2>

                <?php if ($errorMessage !== ""): ?>
                    <p class="error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="POST" nonvalidate>
                    <div class="input-component">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" >
                    </div>

                    <div class="input-component">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" >
                    </div>

                    <div class="input-component">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                    </div>

                    <div class="input-component">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>

                    <div class="action-component">
                        <button type="submit" class="btn-primary">Sign Up</button>
                    </div>
                </form>

                <p>Already have an account? <a href="login.php">Log in</a></p>
            </section>
         </main>
    </body>
</html>
