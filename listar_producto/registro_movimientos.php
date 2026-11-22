<?php
session_start();

// Verifica que el usuario haya iniciado sesión
if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.html');
    exit();
}

require_once __DIR__ . '/../conexion.php';


$tipoMovimientos = $pdo
    ->query('SELECT id_tipo_movimiento, nombre, descripcion FROM tipo_movimiento ORDER BY nombre ASC')
    ->fetchAll();

$productos = $pdo
    ->query('SELECT id_producto, codigo, nombre, descripcion, precio, stock_actual, stock_minimo, fecha_registro, id_categoria, estado FROM productos WHERE estado = 1 ORDER BY id_producto ASC')
    ->fetchAll();

$usuarios = $pdo
    ->query('SELECT id_usuario, nombre FROM usuarios WHERE estado = 1 ORDER BY nombre ASC')
    ->fetchAll();

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1">
    <title>
        Registro de Movimientos
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 760px;">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Registrar Movimiento</h1>
                <p class="text-secondary">Complete con los Datos del Movimiento</p>

                <form action="registrar_movimiento.php" method="post">
                    <div class="row g-3">

                        <div class="col-md">
                            <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                            <select name="tipo_movimiento" id="tipo_movimiento" class="form-select">
                                <option value="" disabled selected class="text-muted">Seleccione...</option>
                                <?php foreach ($tipoMovimientos as $tipoMov): ?>
                                    <option value="<?= (int) $tipoMov['id_tipo_movimiento'] ?>" data-tipo="<?= htmlspecialchars(trim($tipoMov['nombre']), ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($tipoMov['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <fieldset id="camposMovimiento" disabled class="card shadow-sm mx-auto">
                                <div class="row g-3 card-body">

                                    <div class="col-md-12">
                                        <label for="id_producto" class="form-label">Codigo de Producto</label>
                                        <select name="id_producto" id="id_producto" class="form-select">
                                            <option value="" disabled selected class="text-muted">Seleccione...</option>
                                            <?php foreach ($productos as $producto): ?>
                                                <option value="<?= (int) $producto['id_producto'] ?>">
                                                    <?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>nombre</th>
                                                    <th>precio</th>
                                                    <th>stock</th>
                                                    <th>stockMin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr id="filaProductoDetalle">
                                                    <td id="det_id" class="text-muted">-</td>
                                                    <td id="det_nombre" class="text-muted">Seleccione un producto</td>
                                                    <td id="det_precio" class="text-muted">-</td>
                                                    <td id="det_stock" class="text-muted">-</td>
                                                    <td id="det_stock_min" class="text-muted">-</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cantidad" class="form-label">Cantidad</label>
                                        <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" step="1" required placeholder="Escribe la cantidad de salida/entrada...">
                                        <div id="ayudaCantidad" class="form-text"></div>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label for="nombre_usuario" class="form-label">Usuario</label>
                                        <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-control" list="listaUsuarios" autocomplete="off" placeholder="Escribe o selecciona un usuario..." required>
                                        <datalist id="listaUsuarios">
                                            <?php foreach ($usuarios as $usr): ?>
                                                <option value="<?= htmlspecialchars($usr['nombre'], ENT_QUOTES, 'UTF-8') ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                        <div id="ayudaUsuario" class="form-text">Si el nombre no existe, se creará automáticamente.</div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="observacion" class="form-label">Observación (opcional)</label>
                                        <input type="text" name="observacion" id="observacion" class="form-control" autocomplete="off" maxlength="255">
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

<script>
// Aqui comienza el script para rellenar la tabla con los datos importantes del producto seleccionad
const selectTipo = document.getElementById('tipo_movimiento');
const fieldsetCampos = document.getElementById('camposMovimiento');
const selectProducto = document.getElementById('id_producto');

// Convertimos el arreglo de PHP a un mapa por id_producto para acceso directo
const productosLista = <?= json_encode($productos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const productosPorId = {};

for (const prod of productosLista) {
    productosPorId[String(prod.id_producto)] = prod;
}

const celId = document.getElementById('det_id');
const celNombre = document.getElementById('det_nombre');
const celPrecio = document.getElementById('det_precio');
const celStock = document.getElementById('det_stock');
const celStockMin = document.getElementById('det_stock_min');

const formatoPrecio = new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    maximumFractionDigits: 0
});

selectTipo.addEventListener('change', function () {
    fieldsetCampos.disabled = (this.value === '');
});

selectProducto.addEventListener('change', function () {
    const prod = productosPorId[this.value];

    if (!prod) {
        celId.textContent = '-';
        celNombre.textContent = 'Seleccione un producto';
        celPrecio.textContent = '-';
        celStock.textContent = '-';
        celStockMin.textContent = '-';
        return;
    }

    celId.textContent = prod.id_producto;
    celNombre.textContent = prod.nombre;
    celPrecio.textContent = formatoPrecio.format(Number(prod.precio));
    celStock.textContent = prod.stock_actual;
    celStockMin.textContent = prod.stock_minimo;
});

// Aqui termina el script para la tabla




// Este script cambia el signo de lo enviado por el formulario hacia el php a negativo si es de salida y evita que la salida sea mayor que el stock
const formulario = document.querySelector('form');
const inputCantidad = document.getElementById('cantidad');
const ayudaCantidad = document.getElementById('ayudaCantidad');

function esSalida() {
    const opcion = selectTipo.options[selectTipo.selectedIndex];
    return opcion && opcion.dataset.tipo === 'Salida';
}

function actualizarLimiteCantidad() {
    const prod = productosPorId[selectProducto.value];

    if (esSalida() && prod) {
        const stockDisponible = Number(prod.stock_actual);
        inputCantidad.max = stockDisponible;
        ayudaCantidad.textContent = `Máximo permitido para salida: ${stockDisponible} unidades.`;

        if (stockDisponible <= 0) {
            inputCantidad.value = '';
            ayudaCantidad.textContent = 'No hay stock disponible para realizar una salida.';
        } else if (Number(inputCantidad.value) > stockDisponible) {
            inputCantidad.value = stockDisponible;
        }
    } else {
        inputCantidad.removeAttribute('max');
        ayudaCantidad.textContent = '';
    }
}

selectTipo.addEventListener('change', function () {
    fieldsetCampos.disabled = (this.value === '');
    if (!fieldsetCampos.disabled) {
        actualizarLimiteCantidad();
    }
});

selectProducto.addEventListener('change', function () {
    const prod = productosPorId[this.value];

    if (!prod) {
        celId.textContent = '-';
        celNombre.textContent = 'Seleccione un producto';
        celPrecio.textContent = '-';
        celStock.textContent = '-';
        celStockMin.textContent = '-';
        actualizarLimiteCantidad();
        return;
    }

    celId.textContent = prod.id_producto;
    celNombre.textContent = prod.nombre;
    celPrecio.textContent = formatoPrecio.format(Number(prod.precio));
    celStock.textContent = prod.stock_actual;
    celStockMin.textContent = prod.stock_minimo;

    actualizarLimiteCantidad();
});

formulario.addEventListener('submit', function (e) {
    const magnitud = Math.abs(parseInt(inputCantidad.value, 10) || 0);
    const prod = productosPorId[selectProducto.value];

    if (magnitud <= 0) {
        e.preventDefault();
        alert('Ingrese una cantidad mayor a 0.');
        return;
    }

    if (esSalida()) {
        const stockDisponible = prod ? Number(prod.stock_actual) : 0;
        if (magnitud > stockDisponible) {
            e.preventDefault();
            alert(`La cantidad no puede superar el stock actual (${stockDisponible}).`);
            return;
        }
        inputCantidad.value = -magnitud;
    } else {
        inputCantidad.value = magnitud;
    }
});
// Aqui termina el script del signo de salida de cantidad y maximo de salida


</script>

</html>