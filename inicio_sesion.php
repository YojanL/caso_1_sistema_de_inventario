<?php
require_once 'conexion.php';



// verifica que la peticion http sea post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}


$usuario = trim($_POST['usuario'] ?? '');

$clavePlana = $_POST['clave'];



$claveSegura = password_hash($clavePlana, PASSWORD_DEFAULT);