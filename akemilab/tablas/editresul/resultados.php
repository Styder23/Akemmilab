<?php
include '../../conexion/config.php';
$idexamen = $_GET['id'];

// Preparar y ejecutar la consulta SQL para obtener los resultados actuales
$sentencia = $conectar->prepare("SELECT er.idresultados, nm.nommedi, a.nomtit, nm.idnommedible, nm.rangomin, nm.rangomax, u.unidades, er.valores, er.medoto,te.tipoexam
FROM examen e
JOIN tipoexamen te ON te.idtipoexamen = e.fk_idtipoexamen
JOIN nommedible nm ON te.idtipoexamen = nm.fk_idtipoexamen
LEFT JOIN nombretit a ON a.idnombretit = nm.fk_idnombretit
LEFT JOIN unidades u ON u.idunidades = nm.fk_idunidades
LEFT JOIN resultados er ON er.fk_idnommedible = nm.idnommedible AND er.fk_idexamen = e.idexamen
WHERE e.idexamen = ?
ORDER BY nm.idnommedible");
$sentencia->bind_param('i', $idexamen);
$sentencia->execute();
$resultado = $sentencia->get_result();
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
    $valor_medida = $item['valores'];
    $metodo = $item['medoto'];
    $idresultados = $item['idresultados'];

    if (!isset($analisisAgrupados[$nomanalisis])) {
        $analisisAgrupados[$nomanalisis] = [];
    }
    $analisisAgrupados[$nomanalisis][] = [
        'nommedi' => $nommedi,
        'idnommedible' => $idnommedible,
        'rangomin' => $rangomin,
        'rangomax' => $rangomax,
        'unidades' => $unidades,
        'valor_medida' => $valor_medida,
        'metodo' => $metodo,
        'idresultados' => $idresultados
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Resultados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5 mb-4">Editar Resultados del Examen</h1>

        <form id="editHemogramaForm">
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
                        <td colspan="6"><b><?php echo $nomanalisis; ?></b></td>
                    </tr>
                    <?php foreach ($medidas as $medida) { ?>
                    <tr>
                        <td><?php echo $medida['nommedi']; ?></td>
                        <td>
                            <textarea name="datos[<?php echo $medida['idnommedible']; ?>][valor_medida]" required
                                style="width: 100%; height: 40px;"><?php echo $medida['valor_medida']; ?></textarea>
                        </td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][unidades]"
                                value="<?php echo $medida['unidades']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][rangomin]"
                                value="<?php echo $medida['rangomin']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][rangomax]"
                                value="<?php echo $medida['rangomax']; ?>" readonly></td>
                        <td><input type="text" name="datos[<?php echo $medida['idnommedible']; ?>][metodo]"
                                value="<?php echo $medida['metodo']; ?>"></td>
                        <td>
                            <input type="hidden" name="datos[<?php echo $medida['idnommedible']; ?>][idresultados]"
                                value="<?php echo $medida['idresultados']; ?>">
                            <input type="hidden" name="datos[<?php echo $medida['idnommedible']; ?>][idnommedible]"
                                value="<?php echo $medida['idnommedible']; ?>">
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
            <input type="hidden" name="fk_examen" value="<?php echo $idexamen; ?>">
            <button type="button" id="submitBtn">Guardar Cambios</button>
            <button type="button" id="submitBtn1">Atras</button>

        </form>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#submitBtn').click(function() {
            var formData = $('#editHemogramaForm').serialize();
            console.log(formData); // Para verificar los datos enviados
            $.ajax({
                type: 'POST',
                url: './updateresul.php',
                data: formData,
                success: function(response) {
                    alert(response);

                },
                error: function() {
                    alert('Hubo un error al procesar la solicitud.');
                }
            });
        });
    });

    $(document).ready(function() {
        $('#submitBtn1').click(function() {
            window.location.href = './res.php';
        });

    });
    </script>
</body>

</html>