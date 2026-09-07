<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurante_delicias";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("No se pudo conectar al servidor MySQL: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS restaurante_delicias");
$conn->select_db($dbname);

$conn->query("CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT NOT NULL,
    producto VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL,
    pago VARCHAR(50) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->set_charset("utf8");

$pedido_realizado = false;
$mensaje_error = "";
$pedido_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = htmlspecialchars(trim($_POST["nombre"]));
    $telefono = htmlspecialchars(trim($_POST["telefono"]));
    $direccion = htmlspecialchars(trim($_POST["direccion"]));
    $producto = htmlspecialchars(trim($_POST["producto"]));
    $cantidad = max(1, intval($_POST["cantidad"]));
    $pago = htmlspecialchars(trim($_POST["pago"]));

    $precios = array(
        "Hamburguesa" => 80,
        "Pizza" => 150,
        "Pollo Frito" => 120,
        "Tacos" => 70,
        "Refresco" => 25,
        "Hot Dog" => 60,
        "Nachos" => 75,
        "Burrito" => 90,
        "Alitas" => 110,
        "Papas Fritas" => 50,
        "Sandwich" => 65,
        "Ensalada" => 70,
        "Quesadilla" => 80,
        "Gringas" => 80,
        "Spaghetti" => 100,
        "Pastel" => 55
    );

    if (!isset($precios[$producto])) {
        $mensaje_error = "El producto seleccionado no existe en el menú.";
    } else {
        $precio = $precios[$producto];
        $total = $precio * $cantidad;

        $sql = "INSERT INTO pedidos (nombre, telefono, direccion, producto, cantidad, pago, total) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $mensaje_error = "No se pudo preparar la consulta: " . $conn->error;
        } else {
            $stmt->bind_param("sssssis", $nombre, $telefono, $direccion, $producto, $cantidad, $pago, $total);

            if ($stmt->execute()) {
                $pedido_realizado = true;
                $pedido_id = $conn->insert_id;
            } else {
                $mensaje_error = "No se pudo guardar el pedido: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Restaurante Delicias - Delivery</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #333;
}

/* ENCABEZADO */

header {
    background: #d62828;
    color: white;
    text-align: center;
    padding: 30px 15px;
}

header h1 {
    margin: 0;
    font-size: 38px;
}

header p {
    font-size: 18px;
}

/* CONTENEDOR */

.contenedor {
    width: 92%;
    max-width: 1200px;
    margin: 30px auto;
}

.titulo {
    text-align: center;
    color: #d62828;
    font-size: 30px;
}

/* MENU */

.menu {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 22px;
}

/* PRODUCTOS */

.producto {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0px 4px 12px #ccc;
    transition: 0.3s;
}

.producto:hover {
    transform: translateY(-5px);
}

.producto img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.producto-contenido {
    padding: 18px;
    text-align: center;
}

.producto h2 {
    margin: 5px 0;
    color: #d62828;
}

.producto p {
    min-height: 45px;
}

.precio {
    font-size: 23px;
    font-weight: bold;
    color: #222;
}

/* FORMULARIO */

.formulario {
    background: white;
    padding: 30px;
    margin-top: 40px;
    border-radius: 12px;
    box-shadow: 0px 4px 12px #ccc;
}

.formulario h2 {
    text-align: center;
    color: #d62828;
    font-size: 28px;
}

label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}

input,
select,
textarea {
    width: 100%;
    padding: 13px;
    margin-top: 7px;
    border: 1px solid #bbb;
    border-radius: 6px;
    font-size: 16px;
}

textarea {
    height: 100px;
    resize: none;
}

/* BOTON */

button {
    width: 100%;
    padding: 16px;
    margin-top: 25px;
    background: #d62828;
    color: white;
    border: none;
    border-radius: 7px;
    font-size: 19px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #a51d1d;
}

/* CONFIRMACION */

.confirmacion {
    background: #d8f3dc;
    border: 2px solid #52b788;
    padding: 25px;
    margin-top: 30px;
    border-radius: 10px;
}

.confirmacion h2 {
    color: #2d6a4f;
}

/* QR */

.qr {
    background: white;
    text-align: center;
    padding: 30px;
    margin-top: 40px;
    border-radius: 12px;
    box-shadow: 0px 4px 12px #ccc;
}

.qr h2 {
    color: #d62828;
}

.qr img {
    width: 220px;
    height: 220px;
}

/* PIE DE PAGINA */

footer {
    margin-top: 50px;
    background: #222;
    color: white;
    text-align: center;
    padding: 25px;
}

</style>

</head>

<body>


<header>

<h1>🍔 RESTAURANTE DELICIAS</h1>

<p>🚚 Comida deliciosa directamente hasta tu casa</p>

</header>


<div class="contenedor">

<h2 class="titulo">🍴 NUESTRO MENÚ</h2>


<div class="menu">


<!-- PRODUCTO 1 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍔 Hamburguesa</h2>

<p>Hamburguesa con carne, queso, vegetales y papas.</p>

<div class="precio">L. 80</div>

</div>

</div>


<!-- PRODUCTO 2 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍕 Pizza</h2>

<p>Pizza familiar con queso, tomate y pepperoni.</p>

<div class="precio">L. 150</div>

</div>

</div>


<!-- PRODUCTO 3 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍗 Pollo Frito</h2>

<p>Pollo crujiente acompañado de papas fritas.</p>

<div class="precio">L. 120</div>

</div>

</div>


<!-- PRODUCTO 4 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1552332386-f8dd00dc2f85?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🌮 Tacos</h2>

<p>Tacos de carne con vegetales y salsa especial.</p>

<div class="precio">L. 70</div>

</div>

</div>


<!-- PRODUCTO 5 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍟 Papas Fritas</h2>

<p>Papas fritas crujientes con salsa especial.</p>

<div class="precio">L. 50</div>

</div>

</div>


<!-- PRODUCTO 6 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1612392062631-94dd858cba88?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🌭 Hot Dog</h2>

<p>Hot dog con salchicha, vegetales y aderezos.</p>

<div class="precio">L. 60</div>

</div>

</div>


<!-- PRODUCTO 7 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🧀 Nachos</h2>

<p>Nachos con queso, carne, jalapeños y salsa.</p>

<div class="precio">L. 75</div>

</div>

</div>


<!-- PRODUCTO 8 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1626700051175-6818013e1d4f?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🌯 Burrito</h2>

<p>Burrito relleno de carne, queso y vegetales.</p>

<div class="precio">L. 90</div>

</div>

</div>


<!-- PRODUCTO 9 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1527477396000-e27163b481c2?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍗 Alitas</h2>

<p>Alitas de pollo bañadas en salsa especial.</p>

<div class="precio">L. 110</div>

</div>

</div>


<!-- PRODUCTO 10 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🥪 Sandwich</h2>

<p>Sandwich de pollo, queso, tomate y lechuga.</p>

<div class="precio">L. 65</div>

</div>

</div>


<!-- PRODUCTO 11 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🥗 Ensalada</h2>

<p>Ensalada fresca con vegetales y aderezo.</p>

<div class="precio">L. 70</div>

</div>

</div>


<!-- PRODUCTO 12 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1618040996337-56904b7850b9?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🧀 Quesadilla</h2>

<p>Quesadilla con queso, pollo y salsa.</p>

<div class="precio">L. 80</div>

</div>

</div>


<!-- PRODUCTO 13 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1551892374-ecf8754cf8b0?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍝 Spaghetti</h2>

<p>Spaghetti con salsa de tomate y carne.</p>

<div class="precio">L. 100</div>

</div>

</div>


<!-- PRODUCTO 14 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1551024506-0bccd828d307?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🍰 Pastel</h2>

<p>Delicioso pastel de chocolate.</p>

<div class="precio">L. 55</div>

</div>

</div>


<!-- PRODUCTO 15 -->

<div class="producto">

<img src="https://images.unsplash.com/photo-1629203849820-fdd70d49c38e?auto=format&fit=crop&w=600&q=80">

<div class="producto-contenido">

<h2>🥤 Refresco</h2>

<p>Refresco frío para acompañar tu comida.</p>

<div class="precio">L. 25</div>

</div>

</div>


</div>


<!-- FORMULARIO -->

<div class="formulario">

<h2>🚚 REALIZAR PEDIDO</h2>

<form method="POST">


<label>👤 Nombre completo:</label>

<input
type="text"
name="nombre"
placeholder="Escriba su nombre"
required
>


<label>📱 Número de teléfono:</label>

<input
type="tel"
name="telefono"
placeholder="Escriba su teléfono"
required
>


<label>🏠 Dirección de entrega:</label>

<textarea
name="direccion"
placeholder="Escriba su dirección completa"
required
></textarea>


<label>🍴 Seleccione su producto:</label>

<select name="producto" required>

<option value="">-- Seleccione un producto --</option>

<option value="Hamburguesa">Hamburguesa - L. 80</option>

<option value="Pizza">Pizza - L. 150</option>

<option value="Pollo Frito">Pollo Frito - L. 120</option>

<option value="Tacos">Tacos - L. 70</option>

<option value="Refresco">Refresco - L. 25</option>

<option value="Hot Dog">Hot Dog - L. 60</option>

<option value="Nachos">Nachos - L. 75</option>

<option value="Burrito">Burrito - L. 90</option>

<option value="Alitas">Alitas - L. 110</option>

<option value="Papas Fritas">Papas Fritas - L. 50</option>

<option value="Sandwich">Sandwich - L. 65</option>

<option value="Ensalada">Ensalada - L. 70</option>

<option value="Quesadilla">Quesadilla - L. 80</option>

<option value="Spaghetti">Spaghetti - L. 100</option>

<option value="Pastel">Pastel - L. 55</option>

</select>


<label>🔢 Cantidad:</label>

<input
type="number"
name="cantidad"
min="1"
value="1"
required
>


<label>💳 Forma de pago:</label>

<select name="pago" required>

<option value="">-- Seleccione --</option>

<option value="Efectivo">
💵 Efectivo
</option>

<option value="Transferencia">
🏦 Transferencia bancaria
</option>

</select>


<button type="submit">
🚚 CONFIRMAR PEDIDO
</button>


</form>

</div>


<?php

if (!empty($mensaje_error)) {

?>

<div class="confirmacion" style="background: #fde8e8; border-color: #e63946; color: #7f1d1d;">

<h2>⚠️ ERROR AL PROCESAR EL PEDIDO</h2>

<p><?php echo $mensaje_error; ?></p>

</div>

<?php

}

if ($pedido_realizado) {

?>

<div class="confirmacion">

<h2>✅ ¡PEDIDO RECIBIDO!</h2>

<p>
<strong>ID del pedido:</strong>
<?php echo $pedido_id; ?>
</p>

<p>
<strong>Cliente:</strong>
<?php echo $nombre; ?>
</p>

<p>
<strong>Teléfono:</strong>
<?php echo $telefono; ?>
</p>

<p>
<strong>Producto:</strong>
<?php echo $producto; ?>
</p>

<p>
<strong>Cantidad:</strong>
<?php echo $cantidad; ?>
</p>

<p>
<strong>Dirección:</strong>
<?php echo $direccion; ?>
</p>

<p>
<strong>Forma de pago:</strong>
<?php echo $pago; ?>
</p>

<h2>
💰 Total: L. <?php echo $total; ?>
</h2>

<p>
🚴 Su pedido será preparado y enviado a su domicilio.
</p>

</div>

<?php

}

?>

<div class="qr">
<h2>📲 ACCEDE AL RESTAURANTE CON QR</h2>
<p>
Escanea este código QR para ingresar directamente a la página.
</p>

<img
src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=http://localhost/delivery/"
alt="Código QR del restaurante"
>


</body>
</html>