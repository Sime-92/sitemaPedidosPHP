<?php
function leer_config($nombre, $esquema){
	$config = new DOMDocument();
	$config->load($nombre);
	$res = $config->schemaValidate($esquema);
	if ($res===FALSE){ 
	   throw new InvalidArgumentException("Revise fichero de configuración");
	} 		
	$datos = simplexml_load_file($nombre);	
	$ip = $datos->xpath("//ip");
	$nombre = $datos->xpath("//nombre");
	$usu = $datos->xpath("//usuario");
	$clave = $datos->xpath("//clave");	
	$cad = sprintf("mysql:dbname=%s;host=%s", $nombre[0], $ip[0]);
	$resul = [];
	$resul[] = $cad;
	$resul[] = $usu[0];
	$resul[] = $clave[0];
	return $resul;
}
function comprobar_usuario($nombre, $clave){
	$res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
	$bd = new PDO($res[0], $res[1], $res[2]);
	$ins = "select codRes, correo from restaurantes where correo = '$nombre' 
			and clave = '$clave'";
	$resul = $bd->query($ins);	
	if($resul->rowCount() === 1){		
		return $resul->fetch();		
	}else{
		return FALSE;
	}
}
function cargar_categorias(){
	$res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
	$bd = new PDO($res[0], $res[1], $res[2]);
	$ins = "select codCat, nombre from categorias";
	$resul = $bd->query($ins);	
	if (!$resul) {
		return FALSE;
	}
	if ($resul->rowCount() === 0) {    
		return FALSE;
    }
	//si hay 1 o +
	return $resul;	
}
function cargar_categoria($codCat){
    $res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
    $bd = new PDO($res[0], $res[1], $res[2]);
    $ins = "select nombre, descripcion from categorias where codcat = ?";
    $stmt = $bd->prepare($ins);    
    $stmt->execute([$codCat]);
    $resul = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resul === false) {
        return FALSE;
    }    
    return $resul;    
}

function cargar_productos_categoria($codCat){
	$res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
	$bd = new PDO($res[0], $res[1], $res[2]);	
	$sql = "select * from productos where categoria  = $codCat AND Stock > 0";	
	$resul = $bd->query($sql);	
	if (!$resul) {
		return FALSE;
	}
	if ($resul->rowCount() === 0) {    
		return FALSE;
    }	
	//si hay 1 o más
	return $resul;			
}
// recibe un array de códigos de productos
// devuelve  los datos de esos productos
function cargar_productos($codigosProductos){
    $res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
    $bd = new PDO($res[0], $res[1], $res[2]);

    if(empty($codigosProductos)) {
        // Si no hay códigos de producto, no se ejecuta la consulta
        return FALSE;
    }

    $texto_in = implode(",", array_map('intval', $codigosProductos));
    $ins = "select * from productos where codProd in($texto_in)";
    $resul = $bd->query($ins);    
    if (!$resul) {
        return FALSE;
    }
    return $resul;    
}
function insertar_pedido($carrito, $codRes){
    $res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
    $bd = new PDO($res[0], $res[1], $res[2]);
    $bd->beginTransaction();
    $hora = date("Y-m-d H:i:s", time());

    // Insertar el pedido
    $sql = "insert into pedidos(fecha, enviado, restaurante) values('$hora',0, $codRes)";
    $resul = $bd->query($sql);
    if (!$resul) {
        return FALSE;
    }

    // Coger el id del nuevo pedido para las filas detalle
    $pedido = $bd->lastInsertId();

    $pesoTotal = 0; // Inicializar el peso total del pedido

    // Insertar las filas en pedidoproductos y calcular el peso total
foreach($carrito as $codProd => $unidades){
    // Consulta preparada para obtener el peso del producto
    $stmt = $bd->prepare("SELECT Peso, Stock FROM productos WHERE codProd = ?");
    $stmt->execute([$codProd]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fila) {
        $pesoUnitario = $fila['Stock'] > 0 ? $fila['Peso'] / $fila['Stock'] : 0;
        $pesoTotal += $pesoUnitario * $unidades; // Acumula el peso total
    }

    // Insertar fila en pedidosproductos
    $sql = "INSERT INTO pedidosproductos (Pedido, Producto, Unidades) VALUES (?, ?, ?)";
    $stmt = $bd->prepare($sql);
    if (!$stmt->execute([$pedido, $codProd, $unidades])) {
        $bd->rollback();
        return FALSE;
    }
}


    $bd->commit();

    // Devolver el ID del pedido y el peso total
    return [$pedido, $pesoTotal];
}






/*
function insertar_pedido($carrito, $codRes){
	$res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
	$bd = new PDO($res[0], $res[1], $res[2]);
	$bd->beginTransaction();	
	$hora = date("Y-m-d H:i:s", time());
	// insertar el pedido
	$sql = "insert into pedidos(fecha, enviado, restaurante) 
			values('$hora',0, $codRes)";
	$resul = $bd->query($sql);	
	if (!$resul) {
		return FALSE;
	}
	// coger el id del nuevo pedido para las filas detalle
	$pedido = $bd->lastInsertId();
	// insertar las filas en pedidoproductos
	foreach($carrito as $codProd=>$unidades){
		$sql = "insert into pedidosproductos(Pedido, Producto, Unidades) 
		             values( $pedido, $codProd, $unidades)";			
		 $resul = $bd->query($sql);	
		if (!$resul) {
			$bd->rollback();
			return FALSE;
		}
	}
	$bd->commit();
	return $pedido;
}
*/
