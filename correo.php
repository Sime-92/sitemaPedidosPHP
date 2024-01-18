<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'C:\xampp\htdocs\dwes\ultimaphp\vendor\autoload.php';

function enviar_correos($carrito, $pedido, $correo){
	$cuerpo = crear_correo($carrito, $pedido, $correo);
	return enviar_correo_multiples("$correo, pedidos@empresafalsa.com", 
                        	$cuerpo, "Pedido $pedido confirmado");
}
function crear_correo($carrito, $pedido, $correo){
    $texto = "<h1>Pedido nº $pedido </h1><h2>Restaurante: $correo </h2>";
    $texto .= "Detalle del pedido:";
    $productos = cargar_productos(array_keys($carrito));  
    $texto .= "<table>"; // abrir la tabla
    $texto .= "<tr><th>Nombre</th><th>Descripción</th><th>Peso</th><th>Unidades</th></tr>";

    $pesoTotal = 0; // Inicializar el peso total

    foreach($productos as $producto){
        $cod = $producto['CodProd'];
        $nom = $producto['Nombre'];
        $des = $producto['Descripcion'];
        $peso = $producto['Peso'];
        $stock = $producto['Stock'];
        $unidades = $_SESSION['carrito'][$cod];

        // Calcular el peso unitario y acumular el peso total
        $pesoUnitario = $stock > 0 ? $peso / $stock : 0;
        $pesoTotal += $pesoUnitario * $unidades;

        $texto .= "<tr><td>$nom</td><td>$des</td><td>$pesoUnitario</td><td>$unidades</td></tr>";
    }

    $texto .= "</table>";
    $texto .= "<p>Peso total del pedido: " . $pesoTotal . " kg</p>"; // Mostrar el peso total
    return $texto;
}


function enviar_correo_multiples($lista_correos,  $cuerpo,  $asunto = ""){
		$mail = new PHPMailer();		
		$mail->IsSMTP(); 					
		$mail->SMTPDebug  = 2; 
		$mail->SMTPAuth   = true;                  
		$mail->SMTPSecure = "tls";                 
		$mail->Host       = "smtp.gmail.com";      
		$mail->Port       = 587;                   
		$mail->Username   = "";  //usuario de gmail
		$mail->Password   = ""; //contraseña de gmail          
		$mail->SetFrom('noreply@empresafalsa.com', 'Sistema de pedidos');
		$mail->Subject    = $asunto;
		$mail->MsgHTML($cuerpo);
		
		$correos = explode(",", $lista_correos);
		foreach($correos as $correo){
			$mail->AddAddress($correo, $correo);
		}
		if(!$mail->Send()) {
		  return $mail->ErrorInfo;
		} else {
		  return TRUE;
		}
	}	
