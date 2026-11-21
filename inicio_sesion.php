<?php
require_once 'conexion.php';

session_start();

// verifica que la peticion http sea post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}


$usuarioIngresado = trim($_POST['usuario'] ?? '');
$clavePlana = $_POST['clave'] ?? '';

$sql = 
    'SELECT id_usuario, nombre, correo, clave, estado FROM usuarios WHERE nombre = :nombreUsuario';


try {
    $sentencia = $pdo->prepare($sql);
    $sentencia->execute([
        ':nombreUsuario' => $usuarioIngresado
    ]);

    $usuario = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($usuario && (int)$usuario['estado'] === 1) {
        if (password_verify($clavePlana, $usuario['clave'])) {
            session_regenerate_id(true);
        // ¡Contraseña correcta! Asignamos variables de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            $_SESSION['correo_usuario'] = $usuario['correo'];

            // Redirigir al panel principal del sistema de inventario
            header("Location: listar_producto/registrar_producto.php");
            exit();
        } else {
            echo "<script>alert('Correo o contraseña incorrectos.'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('El usuario no existe o se encuentra inactivo.'); window.location.href='index.html';</script>";
    }
} catch (PDOException $e) {
    echo "Error en el sistema: " . $e->getMessage();
}