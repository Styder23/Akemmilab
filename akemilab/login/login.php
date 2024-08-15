<?php
session_start();

if (isset($_SESSION["idusuario"])) {
    header("Location: ../index.php");
    exit();
}

include_once '../conexion/conexprueba.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ["status" => "error", "errors" => []];

    $usuario = isset($_POST["username"]) ? trim($_POST["username"]) : null;
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;

    if (empty($usuario)) {
        $response["errors"]["username"] = "El usuario es obligatorio";
    }
    if (empty($password)) {
        $response["errors"]["password"] = "La contraseña es obligatoria";
    }

    if (empty($response["errors"])) {
        $sql = "SELECT idusuario, nomusu, psw, concat_ws(' ', Nombre, Apellido) as Persona, idtiposusaurio,tipousu
                FROM usuario u
                INNER JOIN personas p on p.idpersonas = u.fk_idpersonas 
                INNER JOIN roles r on u.idusuario = r.fk_idusuario
                INNER JOIN tiposusaurio t on t.idtiposusaurio = r.fk_idtiposusaurio
                WHERE nomusu = ?";
        $stmt = $msqly->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['psw'] === $password) {
                    $_SESSION['idusuario'] = $row['idusuario'];
                    $_SESSION['Persona'] = $row['Persona'];
                    $_SESSION['idtiposusaurio'] = $row['idtiposusaurio'];
                    $_SESSION['tipousu'] = $row['tipousu'];

                    $response["status"] = "success";
                    $response["message"] = "Inicio de sesión correcto";
                } else {
                    $response["errors"]["password"] = "Contraseña incorrecta";
                }
            } else {
                $response["errors"]["username"] = "Usuario incorrecto";
            }
            $stmt->close();
        } else {
            $response["errors"]["database"] = "Error en la consulta a la base de datos";
        }
    }
    $msqly->close();

    echo json_encode($response);
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="img/i.png" type="image/png">
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="style.css" />
    <title>Akemilab</title>
  </head>
  <body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form class="sign-in-form" id="loginForm">
            <h2 class="title">Iniciar sesión</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="Usuario" id="username" name="username" required>
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="Contraseña" id="password" name="password" required>
              <span class="input-group-text">
                <input type="checkbox" id="showPassword" title="Mostrar/Ocultar contraseña">
                <i class="fas fa-eye" id="togglePasswordIcon"></i>
              </span>
            </div>
            <button type="submit" class="btn solid btn-block">Ingresar</button>
          </form>
        </div>
      </div>


      <div class="panels-container">
        <div class="panel left-panel">
          <img src="img/logo.png" class="image" alt="" />
          <div class="content">
            <h3>¡BIENVENIDO!</h3>
          </div> 
          <img src="img/login.svg" class="image" alt="" />      
        </div>
      </div>


    </div>


    <script>
      document.getElementById('showPassword').addEventListener('change', function() {
          const passwordInput = document.getElementById('password');
          const toggleIcon = document.getElementById('togglePasswordIcon');
          if (this.checked) {
              passwordInput.type = 'text';
              toggleIcon.classList.remove('fa-eye');
              toggleIcon.classList.add('fa-eye-slash');
          } else {
              passwordInput.type = 'password';
              toggleIcon.classList.remove('fa-eye-slash');
              toggleIcon.classList.add('fa-eye');
          }
      });

      // También agregar un evento de clic al ícono
      document.getElementById('togglePasswordIcon').addEventListener('click', function() {
          const passwordInput = document.getElementById('password');
          const showPasswordCheckbox = document.getElementById('showPassword');
          if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              this.classList.remove('fa-eye');
              this.classList.add('fa-eye-slash');
              showPasswordCheckbox.checked = true;
          } else {
              passwordInput.type = 'password';
              this.classList.remove('fa-eye-slash');
              this.classList.add('fa-eye');
              showPasswordCheckbox.checked = false;
          }
      });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#showPassword').on('change', function() {
                var passwordInput = $('#password');
                if ($(this).is(':checked')) {
                    passwordInput.attr('type', 'text');
                } else {
                    passwordInput.attr('type', 'password');
                }
            });

            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                var username = $('#username').val();
                var password = $('#password').val();
                
                $.ajax({
                    url: 'login.php',
                    type: 'POST',
                    data: {
                        username: username,
                        password: password
                    },
                    success: function(response) {
                      console.log(response);
                        var data = JSON.parse(response);
                        
                        $('.error-message').text(''); // Clear previous error messages
                        if (data.status === "success") {
                            window.location.href = '../index.php';
                        } else {
                            if (data.errors.username) {
                                $('#error-username').text(data.errors.username);
                            }
                            if (data.errors.password) {
                                $('#error-password').text(data.errors.password);
                            }
                        }
                    }
                });
            });
        });
    </script>
  </body>
</html>
