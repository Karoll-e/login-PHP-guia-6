<?php
session_start();
// Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Login</title>
</head>

<body>
    <div class="container">
        <div class="box form-box">
            <?php
            include("php/config.php");
            if (isset($_POST['submit'])) {
                try {
                    // Use prepared statements to prevent SQL injection
                    $stmt = $con->prepare("SELECT id, username, email, password, birthdate FROM users WHERE email = ?");
                    $stmt->bind_param("s", $_POST['email']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if ($user && password_verify($_POST['password'], $user['password'])) {
                        // Store minimal necessary data in session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];

                        // Calculate age from birthdate
                        $birthdate = new DateTime($user['birthdate']);
                        $today = new DateTime();
                        $age = $birthdate->diff($today)->y;
                        $_SESSION['age'] = $age;

                        // Set session timeout
                        $_SESSION['last_activity'] = time();

                        // Redirect to home page
                        header("Location: home.php");
                        exit();
                    } else {
                        $error_message = "Correo o contraseña no válidos";
                    }
                } catch (Exception $e) {
                    $error_message = "Se ha producido un error. Vuelva a intentarlo más tarde.";
                    // Log the error securely
                    error_log("Login error: " . $e->getMessage());
                }

                if (isset($error_message)) {
                    echo "<div class='message'><p>" . htmlspecialchars($error_message) . "</p></div><br>";
                    echo "<a href='index.php'><button class='btn'>Atrás</button></a>";
                }
            } else {
            ?>
                <header>Bienvenido de nuevo</header>
                <form action="" method="post">
                    <div class="field input">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email"
                            autocomplete="email" required>
                    </div>

                    <div class="field input">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password"
                            autocomplete="current-password" required>
                    </div>

                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="Iniciar sesión">
                    </div>
                    <div class="links">
                        ¿Aún no tienes una cuenta? <a href="register.php">Regístrate</a>
                    </div>
                </form>
        </div>
    <?php } ?>
    </div>
</body>

</html>