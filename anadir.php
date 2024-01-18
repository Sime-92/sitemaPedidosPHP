<?php 
/*comprobar  que el usuario haya abierto sesión sino redifirig*/
require_once 'sesiones.php';
comprobar_sesion();
$cod = $_POST['cod'];
$unidades = (int)$_POST['unidades'];
$categoria = $_POST['categoria']; // Capturar la categoría
/*si existe el código sumar las unidades*/
if(isset($_SESSION['carrito'][$cod])){
	$_SESSION['carrito'][$cod] += $unidades;
}else{
	$_SESSION['carrito'][$cod] = $unidades;		
}
header("Location: productos.php?categoria=" . urlencode($categoria)); // Redirigir a productos.php en la misma categoria