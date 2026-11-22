<?php
// listar_productos.php

session_start();

if (empty($_SESSION['id_usuario'])) {
    header('Location: ../index.html');
    exit();
}

require_once __DIR__ . '/../conexion.php';

$buscar      = trim($_GET['buscar'] ?? '');
$categoriaId = trim($_GET['categoria'] ?? '');
$estadoStock = trim($_GET['estado'] ?? '');
$disponible  = trim($_GET['disponible'] ?? '');

$categorias = $pdo
    ->query('SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC')
    ->fetchAll();

$sql =
    'SELECT
        p.id_producto,
        p.codigo,
        p.nombre,
        p.descripcion,
        p.id_categoria,
        c.nombre AS categoria,
        p.precio,
        p.stock_actual,
        p.stock_minimo,
        p.estado
    FROM productos p
    INNER JOIN categorias c
        ON c.id_categoria = p.id_categoria';

$where  = [];
$params = [];

if ($buscar !== '') {
    $where[]                = '(p.codigo LIKE :buscar_codigo OR p.nombre LIKE :buscar_nombre)';
    $params[':buscar_codigo'] = '%' . $buscar . '%';
    $params[':buscar_nombre'] = '%' . $buscar . '%';
}

if ($categoriaId !== '' && ctype_digit($categoriaId)) {
    $where[]               = 'p.id_categoria = :categoria_id';
    $params[':categoria_id'] = (int) $categoriaId;
}

if ($estadoStock === 'bajo') {
    $where[] = 'p.stock_actual < p.stock_minimo';
} elseif ($estadoStock === 'disponible') {
    $where[] = 'p.stock_actual >= p.stock_minimo';
}

if ($disponible === 'disponible') {
    $where[] = 'p.estado = 1';
} elseif ($disponible === 'retirado') {
    $where[] = 'p.estado = 0';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY p.id_producto DESC';

$sentencia = $pdo->prepare($sql);
$sentencia->execute($params);
$productos = $sentencia->fetchAll();

$hayStockBajo = false;
foreach ($productos as $p) {
    if ((int) $p['stock_actual'] < (int) $p['stock_minimo']) {
        $hayStockBajo = true;
        break;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width,
        initial-scale=1">
    <title>Productos registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<!-- Modal Modificar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="actualizar_producto.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar producto <span id="modalCodigoBadge" class="badge bg-secondary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="edit_id_producto">

                    <div class="row g-2 mb-3">
                        <div class="col-sm-7 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="edit_nombre" id="edit_nombre">
                        </div>
                        <div class="col-sm-5 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="edit_estado" id="edit_estado" required>
                                <option value="1">En Venta</option>
                                <option value="0">Retirado</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_categoria_id" class="form-label">Categoría</label>
                        <select class="form-select" name="categoria_id" id="edit_categoria_id" required>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>">
                                    <?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_precio" class="form-label">Precio</label>
                        <input type="number" class="form-control" name="precio" id="edit_precio" min="0" step="0.01" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="edit_stock_actual" class="form-label">Stock actual</label>
                            <input type="number" class="form-control" name="stock_actual" id="edit_stock_actual" min="0" step="1" required>
                        </div>
                        <div class="col-6">
                            <label for="edit_stock_minimo" class="form-label">Stock mínimo</label>
                            <input type="number" class="form-control" name="stock_minimo" id="edit_stock_minimo" min="0" step="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalEditar = document.getElementById('modalEditarProducto');

modalEditar.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;

    document.getElementById('edit_id_producto').value = btn.getAttribute('data-id');
    document.getElementById('modalCodigoBadge').textContent = btn.getAttribute('data-codigo');
    document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('edit_categoria_id').value = btn.getAttribute('data-categoria');
    document.getElementById('edit_precio').value = btn.getAttribute('data-precio');
    document.getElementById('edit_stock_actual').value = btn.getAttribute('data-stock-actual');
    document.getElementById('edit_stock_minimo').value = btn.getAttribute('data-stock-minimo');
    document.getElementById('edit_descripcion').value = btn.getAttribute('data-descripcion');
    document.getElementById('edit_estado').value = btn.getAttribute('data-estado');
});
</script>

<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm mx-auto"
        style="max-width: 1000px
        margin=auto;">
            <div class="card-body p-4">
                <h1 class="h2 mb-3">Productos registrados</h1>

                <?php if (isset($_GET['creado'])): ?>
                    <div class="alert alert-success">Producto guardado correctamente.</div>
                <?php endif; ?>

                <?php if (isset($_GET['editado'])): ?>
                    <div class="alert alert-success">Producto editado correctamente.</div>
                <?php endif; ?>

                <?php if ($hayStockBajo): ?>
                    <div class="alert alert-danger">Hay productos con bajo nivel de stock.</div>
                <?php endif; ?>

                <!-- Formulario de filtros -->
                <form method="get" class="row g-2 mb-4">
                    <div class="col-md-5">
                        <input type="text" name="buscar" class="form-control" autocomplete="off" placeholder="Buscar por código o nombre..." value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="categoria" class="form-select text-muted">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>"
                                    <?= ((string) $cat['id_categoria'] === $categoriaId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select text-muted" title="Filtrar si quedan mas existencias que las minimas de los productos">
                            <option value="" disabled selected>Nivel de Stock</option>
                            <option value="bajo" <?= ($estadoStock === 'bajo') ? 'selected' : '' ?>>
                                Stock bajo
                            </option>
                            <option value="disponible" <?= ($estadoStock === 'disponible') ? 'selected' : '' ?>>
                                Disponible
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="disponible" class="form-select text-muted" title="Filtrar si el producto sigue a la venta o no">
                            <option value="" disabled selected>Estado Venta</option>
                            <option value="retirado" <?= ($disponible === 'retirado') ? 'selected' : '' ?> >Retirado</option>
                            <option value="disponible" <?= ($disponible === 'disponible') ? 'selected' : '' ?> >En Venta</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="listar_productos.php" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <table border="1" class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th></th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr class="<?= ((int) $producto['stock_actual'] < (int) $producto['stock_minimo']) ? 'table-danger' : '' ?>">
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-editar bi bi-pencil-square"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarProducto"
                                    data-id="<?= (int) $producto['id_producto'] ?>"
                                    data-codigo="<?= htmlspecialchars($producto['codigo'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-nombre="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-categoria="<?= (int) $producto['id_categoria'] ?>"
                                    data-precio="<?= htmlspecialchars((string) $producto['precio'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-stock-actual="<?= (int) $producto['stock_actual'] ?>"
                                    data-stock-minimo="<?= (int) $producto['stock_minimo'] ?>"
                                    data-descripcion="<?= htmlspecialchars((string) ($producto['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    data-estado="<?= ((int) $producto['estado'] === 1) ? '1' : '0' ?>">
                                </button>
                            </td>
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
                            <td>
                                <?php if (
                                    $producto['estado']
                                    == 1
                                ): ?>
                                    En Venta
                                <?php else: ?>
                                    Retirado
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="col-12 d-flex gap-2">
                    <p>
                        <a href="registrar_producto.php" class="btn btn-outline-primary">
                            Registrar un producto nuevo
                        </a>
                    </p>
                    <p>
                        <a href="registro_movimientos.php" class="btn btn-outline-secondary">Registrar Movimiento</a>
                    </p>
                    <p>
                        <a href="listar_movimientos.php" class="btn btn-outline-dark">Ver Movimientos</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>