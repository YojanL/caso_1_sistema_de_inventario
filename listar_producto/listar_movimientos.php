<?php
// listar_productos.php

session_start();

// Verifica que el usuario haya iniciado sesión
if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.html');
    exit();
}

require_once __DIR__ . '/../conexion.php';

$sql = 
    'SELECT 
        m.id_movimiento, 
        m.fecha_creacion, 
        tm.nombre AS tipo_movimiento, 
        u.nombre AS nombre_usuario, 
        p.codigo AS codigo_producto, 
        m.cantidad, 
        m.observacion 
    FROM movimientos m 
    INNER JOIN tipo_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento 
    INNER JOIN usuarios u ON m.id_usuario = u.id_usuario 
    INNER JOIN productos p ON m.id_producto = p.id_producto
    ORDER BY m.fecha_creacion DESC;';

$movimientos = $pdo
    ->query($sql)
    ->fetchALL();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width,
        initial-scale=1">
    <title>Movimientos registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm mx-auto"
        style="max-width: 1000px
        margin=auto;">
            <div class="card-body p-4">
                <h1 class="h2 mb-3">Movimientos registrados</h1>

                <?php if (isset($_GET['ok'])): ?>
                    <div class="alert alert-success">Movimiento Registrado Correctamente.</div>
                <?php endif; ?>

                <table border="1" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo de Movimiento</th>
                            <th>Usuario</th>
                            <th>Codigo de Producto</th>
                            <th>Cantidad</th>
                            <th>Observacion</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $movimiento): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['id_movimiento'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['fecha_creacion'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['tipo_movimiento'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['nombre_usuario'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['codigo_producto'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    (string) (int) $movimiento['cantidad'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $movimiento['observacion'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="col-12 d-flex gap-2">
                    <p>
                        <a href="listar_productos.php" class="btn btn-outline-secondary">Volver a el Listado de Productos</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>