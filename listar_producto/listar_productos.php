<?php
// listar_productos.php
require_once 'conexion.php';


$sql = 'SELECT
            p.codigo,
            p.nombre,
            c.nombre AS categoria,
            p.precio,
            p.stock_actual,
            p.stock_minimo
        FROM productos p
        INNER JOIN categorias c
            ON c.id_categoria = p.id_categoria
        ORDER BY p.id_producto DESC';


$productos = $pdo
    ->query($sql)
    ->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width,
        initial-scale=1">
    <title>Productos registrados</title>
</head>
<body>
    <h1>Productos registrados</h1>

    <?php if (isset($_GET['creado'])): ?>
        <p>Producto guardado correctamente.</p>
    <?php endif; ?>

    <table border="1">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
            <tr>
                <td>
                    <?= htmlspecialchars(
                        $producto['codigo'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $producto['nombre'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </td>
                <td>
                    <?= htmlspecialchars(
                        $producto['categoria'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </td>
                <td>
                    $<?= number_format(
                        (float) $producto['precio'],
                        0,
                        ',',
                        '.'
                    ) ?>
                </td>
                <td>
                    <?= (int)
                        $producto['stock_actual'] ?>
                </td>
                <td>
                    <?php if (
                        $producto['stock_actual']
                        < $producto['stock_minimo']
                    ): ?>
                        Stock bajo
                    <?php else: ?>
                        Disponible
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p>
        <a href="index.html">
            Registrar otro producto
        </a>
    </p>
</body>
</html>