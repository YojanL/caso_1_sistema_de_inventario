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

$idTipoMovimiento = trim($_POST['tipo_movimiento'] ?? '');
$idProducto = trim($_POST['id_producto'] ?? '');
$cantidad = trim($_POST['cantidad'] ?? '');
$nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
$observacion = trim($_POST['observacion'] ?? '');

$errores = [];

if ($idTipoMovimiento === '' || !ctype_digit($idTipoMovimiento) || (int) $idTipoMovimiento < 1) {
    $errores[] = 'Tipo de movimiento inválido.';
}

if ($idProducto === '' || !ctype_digit($idProducto) || (int) $idProducto < 1) {
    $errores[] = 'Producto inválido.';
}

if ($cantidad === '' || !is_numeric($cantidad) || (int) $cantidad == 0) {
    $errores[] = 'Cantidad inválida.';
}

if (mb_strlen($nombreUsuario) < 2 || mb_strlen($nombreUsuario) > 255) {
    $errores[] = 'Nombre de usuario entre 2 y 255 caracteres.';
}

$tipoMov = null;
$producto = null;
$esSalida = false;
$magnitud = abs((int) $cantidad);
$cantidadGuardar = (int) $cantidad;

if ($errores) {
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
    echo '<a href="registro_movimientos.php">Volver</a>';
    exit;
}


$sentenciaTipo = $pdo->prepare('SELECT id_tipo_movimiento, nombre FROM tipo_movimiento WHERE id_tipo_movimiento = :id');
$sentenciaTipo->execute([':id' => (int) $idTipoMovimiento]);
$tipoMovimiento = $sentenciaTipo->fetch();

if (!$tipoMovimiento) {
    $errores[] = 'El tipo de movimiento no existe.';
} else {
    $esSalida = (strcasecmp(trim($tipoMovimiento['nombre']), 'Salida') === 0);
    $cantidadGuardar = $esSalida ? -$magnitud : $magnitud;
}


$sentenciaProd = $pdo->prepare('SELECT id_producto, stock_actual, estado FROM productos WHERE id_producto = :id');
$sentenciaProd->execute([':id' => (int) $idProducto]);
$producto = $sentenciaProd->fetch();

if (!$producto) {
    $errores[] = 'El producto no existe.';
} elseif ((int) $producto['estado'] !== 1) {
    $errores[] = 'El producto está inactivo.';
} elseif ($esSalida && (int) $producto['stock_actual'] < $magnitud) {
    $errores[] = 'Stock insuficiente para realizar la salida.';
}




try {
    $pdo->beginTransaction();

    // Buscar usuario por nombre; si no existe, crearlo
    $sentenciaUsuario = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE LOWER(nombre) = LOWER(:nombre) LIMIT 1');
    $sentenciaUsuario->execute([':nombre' => $nombreUsuario]);
    $usuario = $sentenciaUsuario->fetch();

    if ($usuario) {
        $idUsuarioDestino = (int) $usuario['id_usuario'];
    } else {
        $sentenciaInsertarUsuario = $pdo->prepare('INSERT INTO usuarios (nombre, correo, estado) VALUES (:nombre, NULL, 1)');
        $sentenciaInsertarUsuario->execute([':nombre' => $nombreUsuario]);
        $idUsuarioDestino = (int) $pdo->lastInsertId();
    }

    // Registrar movimiento
    $sqlMovimiento = 'INSERT INTO movimientos (id_tipo_movimiento, cantidad, observacion, id_producto, id_usuario)
               VALUES (:id_tipo_movimiento, :cantidad, :observacion, :id_producto, :id_usuario)';
    $sentenciaMovimiento = $pdo->prepare($sqlMovimiento);
    $sentenciaMovimiento->execute([
        ':id_tipo_movimiento' => (int) $idTipoMovimiento,
        ':cantidad' => $cantidadGuardar,
        ':observacion' => ($observacion === '') ? null : $observacion,
        ':id_producto' => (int) $idProducto,
        ':id_usuario' => $idUsuarioDestino,
    ]);

    // Actualizar stock del producto
    $nuevoStock = $esSalida ? ((int) $producto['stock_actual'] - $magnitud) : ((int) $producto['stock_actual'] + $magnitud);

    $sqlStock = 'UPDATE productos SET stock_actual = :stock_actual WHERE id_producto = :id_producto';
    $sentenciaStock = $pdo->prepare($sqlStock);
    $sentenciaStock->execute([
        ':stock_actual' => $nuevoStock,
        ':id_producto' => (int) $idProducto,
    ]);

    $pdo->commit();

    header('Location: listar_movimientos.php?ok=1');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit('Error al registrar el movimiento: ' . $e->getMessage());
}