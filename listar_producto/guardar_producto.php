<?php
// guardar_producto.php
require_once 'conexion.php';



// verifica que la peticion http sea post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}



// valida datos del post
$codigo = strtoupper(
    trim($_POST['codigo'] ?? '')
);

$nombre = trim($_POST['nombre'] ?? '');

$descripcion = trim(
    $_POST['descripcion'] ?? ''
);

$categoriaId = filter_input(
    INPUT_POST,
    'categoria_id',
    FILTER_VALIDATE_INT
);

$precio = filter_input(
    INPUT_POST,
    'precio',
    FILTER_VALIDATE_FLOAT
);

$stockActual = filter_input(
    INPUT_POST,
    'stock_actual',
    FILTER_VALIDATE_INT
);

$stockMinimo = filter_input(
    INPUT_POST,
    'stock_minimo',
    FILTER_VALIDATE_INT
);



// inicializa un arreglo de errores vacio
$errores = [];

// añade un string al arreglo de errores por cada error que encuentre (se prueban todos los datos del post)
if ($codigo === ''|| !preg_match('/^[A-Z0-9-]+$/',$codigo))
{
    $errores[] = 'Código obligatorio o inválido.';
}

if (mb_strlen($nombre) < 3|| mb_strlen($nombre) > 100)
{
    $errores[] = 'Nombre entre 3 y 100 caracteres.';
}

if ($categoriaId === false|| $categoriaId === null|| $categoriaId < 1)
{
    $errores[] = 'Categoría inválida.';
}

if ($precio === false|| $precio === null|| $precio < 0)
{
    $errores[] = 'Precio inválido.';
}

if ($stockActual === false|| $stockActual === null|| $stockActual < 0)
{
    $errores[] = 'Stock actual inválido.';
}

if ($stockMinimo === false|| $stockMinimo === null|| $stockMinimo < 0)
{
    $errores[] = 'Stock mínimo inválido.';
}

// si encuentra un error, responde con 422 y lista los errores encontrados
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
    echo '<a href="index.html">Volver</a>';
    exit;
}

// una vez validados los datos los inserta en la bd
$sql = 'INSERT INTO productos
    (codigo, nombre, descripcion,
    precio, stock_actual,
    stock_minimo, id_categoria)
    VALUES
    (:codigo, :nombre, :descripcion,
    :precio, :stock_actual,
    :stock_minimo, :id_categoria)';


try {
    $sentencia = $pdo->prepare($sql);
    $sentencia->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':descripcion' =>
            $descripcion !== ''
                ? $descripcion
                : null,
        ':precio' => $precio,
        ':stock_actual' => $stockActual,
        ':stock_minimo' => $stockMinimo,
        ':id_categoria' => $categoriaId,
    ]);

    header(
        'Location: listar_productos.php'
        . '?creado=1'
    );
    exit;
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        exit(
            'El código ya existe. '
            . '<a href="index.html">'
            . 'Volver</a>'
        );
    }
    
    exit('No fue posible guardar.');
}