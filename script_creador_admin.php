<?php
// Este script crea el usuario del admin y lo inserta en la bd, esto para que la contraseña del admin este creada con la funcion password_hash()
// Solo ejecutar una vez

require_once 'conexion.php';

$nombreUsuario = 'admin';
$correo = 'admin@gmail.com';
$clavePlana = 'Admin123';
$claveSegura = password_hash($clavePlana, PASSWORD_DEFAULT);

$sql = 
    'INSERT INTO 
        usuarios (nombre, correo, clave) 
    VALUES 
        (:nombreUsuario, :correo, :clave)';


try {
    $sentencia = $pdo->prepare($sql);
    $sentencia->execute([
        ':nombreUsuario' => $nombreUsuario,
        ':correo' => $correo,
        ':clave' => $claveSegura
    ]);

    echo "Usuario Registrado Correctamente";
} catch (PDOException $e) {
    echo "Error al insertar el usuario: " . $e->getMessage();
}