<?php 
require_once("php/config.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Editar perfil</title>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">Logo</a></p>
        </div>

        <div class="right-links">
            <a href="#">Editar perfil</a>
            <a href="index.php"> 
                <button class="btn">Volver al inicio</button> 
            </a>
        </div>
    </div>
    <div class="container">
        <div class="box form-box">
            <?php 
            try {
                $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
                
                if(isset($_POST['submit'])){
                    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
                    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
                    $birthdate = $_POST['birthdate'];

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Formato de correo inválido");
                    }

                    $birthdateObj = new DateTime($birthdate);
                    $today = new DateTime();
                    if ($birthdateObj > $today) {
                        throw new Exception("Fecha de nacimiento inválida");
                    }

                    $age = $birthdateObj->diff($today)->y;
                    if ($age < 13) {
                        throw new Exception("Debes tener al menos 13 años");
                    }

                    $check_stmt = $con->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $check_stmt->bind_param("si", $email, $user_id);
                    $check_stmt->execute();
                    if($check_stmt->get_result()->num_rows > 0) {
                        throw new Exception("Este correo ya está en uso");
                    }

                    $update_stmt = $con->prepare("UPDATE users SET username = ?, email = ?, birthdate = ? WHERE id = ?");
                    $update_stmt->bind_param("sssi", $username, $email, $birthdate, $user_id);
                    
                    if($update_stmt->execute()){
                        echo "<div class='message success'>
                                <p>¡Perfil actualizado con éxito!</p>
                              </div><br>";
                        echo "<a href='home.php'><button class='btn'>Ir al perfil</button></a>";
                    } else {
                        throw new Exception("Error al actualizar el perfil");
                    }
                } else {
                    $stmt = $con->prepare("SELECT username, email, birthdate FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if($user = $result->fetch_assoc()) {
                        $username = htmlspecialchars($user['username']);
                        $email = htmlspecialchars($user['email']);
                        $birthdate = $user['birthdate'];
            ?>
            <header>Editar perfil</header>
            <form action="" method="post" novalidate>
                <div class="field input">
                    <label for="username">Usuario</label>
                    <input type="text" name="username" id="username" 
                           value="<?php echo $username; ?>" 
                           autocomplete="username" required 
                           pattern="[A-Za-z0-9_]{3,50}"
                           title="El usuario debe tener entre 3 y 50 caracteres y solo puede contener letras, números y guiones bajos">
                </div>

                <div class="field input">
                    <label for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" 
                           value="<?php echo $email; ?>" 
                           autocomplete="email" required>
                </div>

                <div class="field input">
                    <label for="birthdate">Fecha de nacimiento</label>
                    <input type="date" name="birthdate" id="birthdate" 
                           value="<?php echo $birthdate; ?>" 
                           required max="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Actualizar">
                </div>
            </form>
            <?php 
                    } else {
                        throw new Exception("Usuario no encontrado");
                    }
                }
            } catch (Exception $e) {
                echo "<div class='message error'>
                        <p>" . htmlspecialchars($e->getMessage()) . "</p>
                      </div><br>";
                echo "<a href='javascript:history.back()'><button class='btn'>Volver</button></a>";
                error_log("Profile update error: " . $e->getMessage());
            }
            ?>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const birthdate = new Date(document.getElementById('birthdate').value);
        const today = new Date();
        const age = Math.floor((today - birthdate) / (365.25 * 24 * 60 * 60 * 1000));
        const email = document.getElementById('email').value;
        const username = document.getElementById('username').value;

        let errors = [];

        if (age < 13) {
            errors.push("Debes tener al menos 13 años");
        }
        if (!/^[A-Za-z0-9_]{3,50}$/.test(username)) {
            errors.push("El usuario debe tener entre 3 y 50 caracteres y solo puede contener letras, números y guiones bajos");
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push("Por favor, ingresa un correo electrónico válido");
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
    </script>
</body>
</html>