<?php 
    require 'sesiones.php';
    require_once 'bd.php';
    comprobar_sesion();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset = "UTF-8">
        <title>Tabla de productos por categoría</title>        
    </head>
    <body>
        <?php 
        require 'cabecera.php';
		$categoria = $_GET['categoria'] ?? null; // Usar el operador de fusión de null
		if ($categoria === null) {
			echo "<p>Categoría no especificada.</p>";
			exit;
		}
		
        $cat = cargar_categoria($categoria);
        $productos = cargar_productos_categoria($categoria);      
        if($cat === FALSE or $productos === FALSE){
            echo "<p class='error'>Error al conectar con la base datos</p>";
            exit;
        }
        echo "<h1>". $cat['nombre']. "</h1>";
        echo "<p>". $cat['descripcion']."</p>";      
        echo "<table>"; // abrir la tabla
        echo "<tr><th>Nombre</th><th>Descripción</th><th>Peso</th><th>Stock</th><th>Comprar</th></tr>";
        foreach($productos as $producto){
            $cod = $producto['CodProd'];
            $nom = $producto['Nombre'];
            $des = $producto['Descripcion'];
            $peso = $producto['Peso'];
            $stock = $producto['Stock'];                               
            echo "<tr><td>$nom</td><td>$des</td><td>$peso</td><td>$stock</td>
            <td><form action = 'anadir.php' method = 'POST'>
            <input name = 'unidades' type='number' min = '1' value = '1'>
            <input type = 'submit' value='Comprar'>
            <input name = 'cod' type='hidden' value = '$cod'>
            <input name = 'categoria' type='hidden' value='$categoria'> <!-- Campo oculto para la categoría -->
            </form></td></tr>";
        }
        echo "</table>";            
        ?>              
    </body>
</html>
