<?php
include '../../conexion/config.php';
$idexamen = $_GET['id'];

// Consulta para obtener médicos
$sentenciaMedicos = $conectar->prepare("SELECT idusuario, concat_ws(' ',Nombre,Apellido) as analísta
from usuario u inner join personas p on p.idpersonas=u.fk_idpersonas;");
$sentenciaMedicos->execute();
$resultadoMedicos = $sentenciaMedicos->get_result();

// Inicializar $medicos
$medicos = $resultadoMedicos->fetch_all(MYSQLI_ASSOC);

// Consulta para obtener datos del médico
$sentenciaMedico = $conectar->prepare("SELECT idmedicos, colegiatura, concat_ws(' ', Nombre, Apellido) AS Medico
from medicos m
inner join personas p on p.idpersonas=m.fk_personas;");
$sentenciaMedico->execute();
$resultadoMedico = $sentenciaMedico->get_result();

// Inicializar $medico
$medico = $resultadoMedico->fetch_all(MYSQLI_ASSOC);

// Preparar y ejecutar la consulta SQL
$sentencia = $conectar->prepare("SELECT nm.nommedi, a.nomtit, nm.idnommedible, nm.rangomin,nm.rangomax,u.unidades
FROM examen e
JOIN tipoexamen te ON te.idtipoexamen = e.fk_idtipoexamen
JOIN nommedible nm ON te.idtipoexamen = nm.fk_idtipoexamen
LEFT JOIN nombretit a ON a.idnombretit = nm.fk_idnombretit
left join unidades u on u.idunidades=nm.fk_idunidades
WHERE e.idexamen = ?
order by nm.idnommedible");
$sentencia->bind_param('i', $idexamen);
$sentencia->execute();
$resultado = $sentencia->get_result();

// Inicializar $lista
$lista = $resultado->fetch_all(MYSQLI_ASSOC);

// Verificar si $lista está vacía y manejar el caso
if (empty($lista)) {
    echo "No hay datos disponibles para el examen con ID: " . $idexamen;
    exit;
}

// Agrupar datos por nomanalisis
$analisisAgrupados = [];
foreach ($lista as $item) {
    $nomanalisis = $item['nomtit'];
    $nommedi = $item['nommedi'];
    $idnommedible = $item['idnommedible'];
    $rangomin = $item['rangomin'];
    $rangomax = $item['rangomax'];
    $unidades = $item['unidades'];
    if (!isset($analisisAgrupados[$nomanalisis])) {
        $analisisAgrupados[$nomanalisis] = [];
    }
    $analisisAgrupados[$nomanalisis][] = ['nommedi' => $nommedi, 'idnommedible' => $idnommedible, 'rangomin' => $rangomin, 'rangomax' => $rangomax, 'unidades' => $unidades ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5 mb-4">Resultado dex examen <?php echo "tipoexam"?></h1>

        <form id="hemogramaForm">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Examen</th>
                        <th>Resultado</th>
                        <th>Unidad</th>
                        <th>Valores MIN</th>
                        <th>Valores MAX</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analisisAgrupados as $nomanalisis => $medidas) { ?>
                    <tr>
                        <td colspan="5"><b><?php echo $nomanalisis; ?></b></td>
                    </tr>
                    <?php foreach ($medidas as $medida) { ?>
                    <tr>
                        <td><?php echo $medida['nommedi']; ?></td>
                        <td>
                            <textarea name="datos[<?php echo $medida['idnommedible']; ?>][valor_medida]" required
                                style="width: 100%; height: 40px;"></textarea>
                        </td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][unidades]"
                                value="<?php echo $medida['unidades']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][rangomin]"
                                value="<?php echo $medida['rangomin']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][rangomax]"
                                value="<?php echo $medida['rangomax']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][metodo]"></td>
                        <input type="hidden" name="datos[<?php echo $medida['idnommedible']; ?>][idnommedible]"
                            value="<?php echo $medida['idnommedible']; ?>">
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
            <input type="hidden" name="fk_examen" value="<?php echo $idexamen; ?>">
            <button type="button" id="submitBtn">Enviar</button>
        </form>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#submitBtn').click(function() {
            var formData = $('#hemogramaForm').serialize();
            console.log(formData); // Para verificar los datos enviados
            $.ajax({
                type: 'POST',
                url: './inseresul.php',
                data: formData,
                success: function(response) {
                    alert(response);
                    window.location.href = 'resultado.php';
                },
                error: function() {
                    alert('Hubo un error al procesar la solicitud.');
                }
            });
        });
    });
    </script>
</body>

</html>