<?php
session_start();

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

require_once __DIR__ . '/../conexion.php';



$idProducto = trim($_POST['id_producto'] ?? '');

$nombre = trim($_POST['edit_nombre'] ?? '');

$categoriaId = trim($_POST['categoria_id'] ?? '');

$precio = trim($_POST['precio'] ?? '');

$stockActual = trim($_POST['stock_actual'] ?? '');

$stockMinimo = trim($_POST['stock_minimo'] ?? '');

$descripcion = trim($_POST['descripcion'] ?? '');

$estado = trim($_POST['edit_estado'] ?? '');


$errores = [];

if (mb_strlen($nombre) < 3|| mb_strlen($nombre) > 100)
{
    $errores[] = 'Nombre entre 3 y 100 caracteres.';
}

if ($precio === false|| $precio === null|| $precio < 0)
{
    $errores[] = 'Precio inválido.';
}

if ($categoriaId === false|| $categoriaId === null|| $categoriaId < 1)
{
    $errores[] = 'Categoría inválida.';
}

if ($stockActual === false|| $stockActual === null|| $stockActual < 0)
{
    $errores[] = 'Stock actual inválido.';
}

if ($stockMinimo === false|| $stockMinimo === null|| $stockMinimo < 0)
{
    $errores[] = 'Stock mínimo inválido.';
}


if ($errores)
{
    http_response_code(422);
    echo '<h1>Revise los datos</h1><ul>';

    foreach ($errores as $error) {
        echo '<li>'
            . htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            )
        . '</li>';
    }

    echo '</ul>';
    echo '<a href="listar_productos.php">Volver</a>';
    exit;
}


$sql = 'UPDATE productos
        SET id_categoria = :id_categoria,
            nombre = :nombre,
            precio = :precio,
            stock_actual = :stock_actual,
            stock_minimo = :stock_minimo,
            descripcion = :descripcion,
            estado = :estado
        WHERE id_producto = :id_producto';

try {
    $sentencia = $pdo->prepare($sql);
    $sentencia->execute([
        ':id_categoria' => $categoriaId,
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':stock_actual' => $stockActual,
        ':stock_minimo' => $stockMinimo,
        ':descripcion' => ($descripcion === '') ? null : $descripcion,
        ':estado' => $estado,
        ':id_producto' => $idProducto,
    ]);

    header('Location: listar_productos.php?editado=1');
    exit();
} catch (PDOException $e) {
    exit('Error al actualizar el producto: ' . $e->getMessage());
}

?>