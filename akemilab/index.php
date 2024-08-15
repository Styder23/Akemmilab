<?php
// Seguridad de sesiones
session_start();
error_reporting(0);

// Obtén la sesión actual
$NomUsu = $_SESSION['Persona'];
$idcargo=$_SESSION['idtiposusaurio'];
$cargo=$_SESSION['tipousu'];

// Verifica si la sesión está vacía o no está establecida
if (!isset($_SESSION['idusuario'])) {
    // Redirige al usuario a la página de inicio de sesión
    header("Location: ./login/login.php");
  exit(); // Finaliza la ejecución del script
}
// // NO dejar poner atras y entrar
if(!isset($_SESSION['idusuario'])){
   header("Location: ./login/login.php");
}
?>

<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Laboratorio</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <!----css3---->
    <link rel="stylesheet" href="./css/custom.css">
    <!-- SLIDER REVOLUTION 4.x CSS SETTINGS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!--google material icon-->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap">
    <!--STYLO DEL INDEX PRINCIPAL-->
    <link rel="icon" href="./i.png" type="image/png">
    <link rel="stylesheet" href="style.css">
    <style>
    * {
        font-family: Poppins, sans-serif;
    }

    .sidebar-header {
        background-color: #62162F !important;
        border: none !important;
        text-align: center;
    }

    .ake {
        color: #f5f1f1;
    }

    hr {
        border: 1px solid white;
    }

    h3 {
        margin-right: 33px !important;
        padding-top: 15px;
    }

    .navbar {
        background-color: #62162F;
    }

    #sidebarCollapse:hover {
        background-color: #B24E76;
    }

    #sidebar ul li a:hover {
        background-color: #B24E76;
    }

    .list-unstyled.components .collapse.list-unstyled.menu li a {
        background-color: #ba1b50;
    }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="body-overlay"></div>
        <!-- Sidebar  -->
        <nav id="sidebar" style="background-color:#62162F;">
            <div class="sidebar-header">
                <h3><a href="./index.php"><img src="i.png" class="img-fluid" /><span class="ake">Akemilab</span></a>
                </h3>
            </div>

            <!-- usuario y eess  -->
            <ul class="list-unstyled components">
                <li class="active">
                    <a href="#" class="dashboard"><i class="material-icons">account_circle</i><span>Usuario:
                            <?php echo $NomUsu; ?></span></a>
                </li>
                <li class="active">
                    <a href="#" class="dashboard"><i class="material-icons">account_circle</i><span>Cargo:
                            <?php echo $cargo; ?></span></a>
                </li>
                <hr>
                <!-- ADMINISTRADOR-->
                <?php if($idcargo == 1){?>
                <li class="dropdown">
                    <a href="#homeSubmenuadmi" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"
                        id="toggleLink">
                        <i class="material-icons">admin_panel_settings</i><span>Administrador</span></a>
                    <ul class="collapse list-unstyled menu" id="homeSubmenuadmi">

                        <li>
                            <!-- INTENTÉ AGREGAR ÍCONOS PERO MEJOR A COLORES -->
                            <a href="#" onclick="cargarIframe('tablas/paciente/paciente.php')"><i
                                    class="material-icons">personal_injury</i>Pacientes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/medicos/medico.php')"><i
                                    class="material-icons">assignment_ind</i>Médicos</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/asignaexam/asigna.php')"><i
                                    class="material-icons">article</i>Exámenes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/areaclinica/area.php')"><i
                                    class="material-icons">folder_copy</i>Áreas</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/examen/examen.php')"><i
                                    class="material-icons">note_add</i>Tipos de Examen</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/perfiles2/perfiles.php')"><i
                                    class="material-icons">note_add</i>Perfiles</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/analisis/analisis.php')"><i
                                    class="material-icons">account_tree</i>Análisis</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/analisis/editar-analisis.php')"><i
                                    class="material-icons">account_tree</i>Configurar análisis</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/asignaexam/exam.php')"><i
                                    class="material-icons">rate_review</i>Exámenes pendientes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/editresul/res.php')"><i
                                    class="material-icons">upload_file</i>Resultados</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/muestras/index.php')"><i
                                    class="material-icons">summarize</i>Tipos De muestra</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/usuario/usuario.php')"><i
                                    class="material-icons">group_add</i>Usuarios</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/Comprobantes/comprobante.php')"><i
                                    class="material-icons">newspaper</i>Comprobantes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/unidades/unidades.php')"><i
                                    class="material-icons">playlist_add</i>Unidades</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/especialidad/especialidad.php')"><i
                                    class="material-icons">add</i>Especialidades</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/orden/orden.php')"><i
                                    class="material-icons">add</i>Orden Clínico</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/Historial/historial.php')"><i
                                    class="material-icons">work_history</i>Historial Pacientes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/Rep_atencion.php')"><i
                                    class="material-icons">work_history</i>Reporte Atenciones</a>
                        </li>
			<li>
                            <a href="#" onclick="cargarIframe('tablas/resultxoren/ordenimpri.php')"><i
                                    class="material-icons">work_history</i>Resultados Orden</a>
                        </li>
                    </ul>
                </li>
                <!-- FIN ADMINISTRADOR-->
                
                <!-- CAJA -->
                <li class="dropdown">
                    <a href="#pageSubmenu4" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="material-icons">paid</i><span>Gestión de Caja</span></a>
                    <ul class="collapse list-unstyled menu" id="pageSubmenu4">
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/caja.php')"><i
                                    class="material-icons">inventory_2</i>Abrir/Cerrar Caja</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/prueba.php')"><i
                                    class="material-icons">add_box</i>Registrar y ver movimientos</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/Rep_estadocaja.php')"><i
                                    class="material-icons">rate_review</i>Reporte Caja</a>
                        </li>        
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/frep_caja.php')"><i
                                    class="material-icons">inventory</i>Historial CAJAS</a>
                        </li> 
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/vaciocaja.php')"><i
                                    class="material-icons">inventory</i>Vaciado de cajas</a>
                        </li>                        
                    </ul>
                </li>
                <?php } ?>

                <?php if($idcargo == 2){?>
                <!-- CAJA -->
                <li class="dropdown">
                    <a href="#pageSubmenu4" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="material-icons">paid</i><span>Gestión de Caja</span></a>
                    <ul class="collapse list-unstyled menu" id="pageSubmenu4">
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/caja.php')"><i
                                    class="material-icons">inventory_2</i>Abrir/Cerrar Caja</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/prueba.php')"><i
                                    class="material-icons">add_box</i>Registrar y ver movimientos</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/caja/Rep_estadocaja.php')"><i
                                    class="material-icons">rate_review</i>Reporte Caja</a>
                        </li>                              
                    </ul>
                </li>
                
                <li class="dropdown">
                    <a href="#homeSubmenu1" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"
                        id="toggleLink">
                        <i class="material-icons">add_circle</i><span>Nuevos registros</span></a>
                    <ul class="collapse list-unstyled menu" id="homeSubmenu1">
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/paciente/paciente.php')"><i
                                    class="material-icons">personal_injury</i>Pacientes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/asignaexam/asigna.php')"><i
                                    class="material-icons">article</i>Examenes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/asignaexam/exam.php')"><i
                                    class="material-icons">rate_review</i>Exámenes pendientes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/Resultados/resultado.php')"><i
                                    class="material-icons">upload_file</i>Ver Resultados</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/Comprobantes/comprobante.php')"><i
                                    class="material-icons">newspaper</i>Ver Comprobantes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/Historial/historial.php')"><i
                                    class="material-icons">work_history</i>Historial Pacientes</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>
                <?php if($idcargo == 1){?>
                <li class="dropdown">
                    <a href="#pageSubmenu2" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="material-icons">analytics</i><span>Reportes</span></a>
                    <ul class="collapse list-unstyled menu" id="pageSubmenu2">
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/fecha.php')">Pacientes Atendidos</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/frep_exames.php')">Exámenes más frecuentes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/frep_pacientes.php')">Pacientes más
                                frecuentes</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/frep_ventasdia.php')">Ventas Por Día</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/frep_ventas.php')">Ventas según fechas</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/Rep_usuarios.php')">Reporte de Usuarios</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('reporte/Rep_pacientes.php')">Reporte de Pacientes</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>
                <?php if($idcargo == 3){?>
                <li class="dropdown">
                    <a href="#pageSubmenu4" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="material-icons">add_circle</i><span>Registro de Medicos</span></a>
                    <ul class="collapse list-unstyled menu" id="pageSubmenu4">
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/medicos/medico.php')"><i
                                    class="material-icons">assignment_ind</i>Médicos</a>
                        </li>
                        <li>
                            <a href="#" onclick="cargarIframe('tablas/especialidad/especialidad.php')"><i
                                    class="material-icons">add</i>Especialidades</a>
                        </li>                      
                    </ul>
                </li>
                <?php } ?>

                <!-- Salir -->
                <li class="dropdown">
                    <a href="./login/cerrar.php">
                        <i class="material-icons">logout</i><span>Salir</span></a>
                </li>











        </nav>
        <!-- Page Content  -->
        <div id="content">

            <div class="top-navbar">
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <button type="button" id="sidebarCollapse" class="d-xl-block d-lg-block d-md-none d-none">
                            <span class="material-icons">arrow_back_ios</span>
                        </button>
                        <label for="">REGISTROS Y CONSULTA DE ANÁLISIS MÉDICOS</label>
                        <button class="d-inline-block d-lg-none ml-auto more-button" type="button"
                            data-toggle="collapse" data-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="material-icons">menu_open</span>
                        </button>
                    </div>
                </nav>
            </div>
            <div class="main-content" id="m-content">
                <iframe id="mainContent" src="tablas/paciente/paciente.php"
                    style="width: 100%; height: 825px; border: none;">
                </iframe>
            </div>

        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="./js/jquery-3.3.1.slim.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/jquery-3.3.1.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
            $(this).find('.material-icons').text(function(_, text) {
                return text === 'arrow_back_ios' ? 'arrow_forward_ios' : 'arrow_back_ios';
            });
        });



        $('.more-button, .body-overlay').on('click', function() {
            $('#sidebar, .body-overlay').toggleClass('show-nav');
        });
    });
    </script>

    <!-- Para mostrar la url del php segun se mueva en otras paginas -->
    <script>
    function cargarIframe(url) {
        // Cambia el 'src' del <iframe> para cargar el contenido de la URL especificada
        const iframe = document.getElementById('mainContent');
        iframe.src = url;
    }
    </script>

</body>

</html>