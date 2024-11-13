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
    <title>Inicio</title>
</head>

<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">Logo</a></p>
        </div>

        <div class="right-links">
            <?php
            try {
                $stmt = $con->prepare("SELECT username, email, birthdate FROM users WHERE id = ?");
                $default_user_id = 1;
                $stmt->bind_param("i", $default_user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($user = $result->fetch_assoc()) {
                    $birthdate = new DateTime($user['birthdate']);
                    $today = new DateTime();
                    $age = $birthdate->diff($today)->y;

                    $username = htmlspecialchars($user['username']);
                    $email = htmlspecialchars($user['email']);
                } else {
                    $username = "Invitado";
                    $email = "invitado@ejemplo.com";
                    $age = "N/A";
                }

                echo "<a href='edit.php?id=" . urlencode($default_user_id) . "'>Editar perfil</a>";
            } catch (Exception $e) {
                error_log("Error en página de inicio: " . $e->getMessage());
                echo "<div class='error'>Ocurrió un error. Por favor intenta más tarde.</div>";
            }
            ?>

            <a href="index.php">
                <button class="btn">
                    Volver al inicio
                </button>
            </a>
        </div>
    </div>
    <main>
        <div class="main-box top">
            <div class="top">
                <div class="box">
                    <p>Hola <b><?php echo $username ?></b>, Bienvenido</p>
                </div>
                <div class="box">
                    <p>Tu correo es <b><?php echo $email ?></b>.</p>
                </div>
            </div>
            <div class="bottom">
                <div class="box">
                    <p>Tienes <b><?php echo $age ?> años</b>.</p>
                </div>
            </div>
        </div>
    </main>
</body>

</html>