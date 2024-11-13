<?php
session_start();
require_once("php/config.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register</title>
</head>

<body>
    <div class="container">
        <div class="box form-box">
            <?php
            if (isset($_POST['submit'])) {
                try {
                    // Sanitize and validate inputs
                    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
                    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
                    $birthdate = $_POST['birthdate'];
                    $password = $_POST['password'];

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Formato de e-mail no válido");
                    }

                    // Validate password strength
                    if (strlen($password) < 8) {
                        throw new Exception("La contraseña debe tener al menos 8 caracteres");
                    }

                    // Validate birthdate
                    $birthdateObj = new DateTime($birthdate);
                    $today = new DateTime();
                    if ($birthdateObj > $today) {
                        throw new Exception("Invalid birthdate");
                    }

                    // Calculate age
                    $age = $birthdateObj->diff($today)->y;
                    if ($age < 13) {
                        throw new Exception("Debes tener al menos 13 años para registrarte");
                    }

                    // Check for existing email and username using prepared statements
                    $stmt = $con->prepare("SELECT email FROM users WHERE email = ? OR username = ?");
                    $stmt->bind_param("ss", $email, $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        throw new Exception("El email o nombre de usuario ya existe");
                    }

                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user
                    $insert_stmt = $con->prepare("INSERT INTO users (username, email, birthdate, password) VALUES (?, ?, ?, ?)");
                    $insert_stmt->bind_param("ssss", $username, $email, $birthdate, $hashed_password);

                    if ($insert_stmt->execute()) {
                        echo "<div class='message success'>
                            <p>¡Registro realizado con éxito!</p>
                          </div><br>";
                        echo "<a href='index.php'><button class='btn'>Iniciar sesión</button></a>";
                    } else {
                        throw new Exception("Registro fallido");
                    }
                } catch (Exception $e) {
                    echo "<div class='message error'>
                        <p>" . htmlspecialchars($e->getMessage()) . "</p>
                      </div><br>";
                    echo "<a href='javascript:self.history.back()'><button class='btn'>Atrás</button></a>";
                }
            } else {
            ?>
                <header>Comienza ahora</header>
                <form action="" method="post" novalidate>
                    <div class="field input">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" name="username" id="username"
                            autocomplete="username" required
                            pattern="[A-Za-z0-9_]{3,50}"
                            title="El nombre de usuario debe tener entre 3 y 50 caracteres y puede contener letras, números y guiones bajos.">
                    </div>

                    <div class="field input">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email"
                            autocomplete="email" required>
                    </div>

                    <div class="field input">
                        <label for="birthdate">Fecha de nacimiento</label>
                        <input type="date" name="birthdate" id="birthdate"
                            required max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="field input">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password"
                            autocomplete="new-password" required
                            minlength="8"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                            title="Debe contener al menos un número y una letra mayúscula y minúscula, y al menos 8 o más caracteres">
                    </div>

                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="Registrarse">
                    </div>
                    <div class="links">
                        ¿Ya tienes una cuenta? <a href="index.php">Inicia sesión</a>
                    </div>
                </form>
        </div>
    <?php } ?>
    </div>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const birthdate = new Date(document.getElementById('birthdate').value);
            const today = new Date();
            const age = Math.floor((today - birthdate) / (365.25 * 24 * 60 * 60 * 1000));

            let errors = [];

            if (password.length < 8) {
                errors.push("La contraseña debe tener al menos 8 caracteres");
            }
            if (!/\d/.test(password)) {
                errors.push("La contraseña debe contener al menos un número");
            }
            if (!/[A-Z]/.test(password)) {
                errors.push("La contraseña debe contener al menos una letra mayúscula");
            }
            if (!/[a-z]/.test(password)) {
                errors.push("La contraseña debe contener al menos una letra minúscula");
            }
            if (age < 13) {
                errors.push("Debes tener al menos 13 años para inscribirte");
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join("\n"));
            }
        });
    </script>
</body>

</html>