<?php
    // Variables dinámicas (PHP) - Requisito 1
    $titulo_app = "TRUE2FK Inventory";
    $usuario_logueado = "Josue Padron"; 
    $fecha_actual = date("d/m/Y");
    $mensaje = "";

    // Simular que se presionó el botón y mostrar el mensaje dinámico[cite: 3]
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $mensaje = "¡Se ha guardado con éxito! ✅";
    }

    // Lista de datos en PHP (para no depender del app.js)[cite: 3]
    $inventario = [
        ["producto" => "Baggy Jeans", "precio" => 450],
        ["producto" => "Camiseta Y2K", "precio" => 250],
        ["producto" => "Gorra", "precio" => 150]
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Requisito 2[cite: 3] -->
    <title><?php echo $titulo_app; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="app-header">
        <h1><?php echo $titulo_app; ?></h1>
        <div class="user-info">
            <span>Bienvenido, <strong><?php echo $usuario_logueado; ?></strong></span>
            <small><?php echo $fecha_actual; ?></small>
        </div>
    </header>

    <main class="app-container">
        <!-- Convertimos la sección en un formulario PHP real -->
        <form method="POST" class="formulario">
            <input type="text" name="nombre" placeholder="Producto..." required>
            <input type="number" name="precio" placeholder="Precio $" required>
            <button type="submit" class="btn-main">Añadir al Stock</button>
        </form>

        <!-- Aquí sale el mensaje justo debajo del formulario (Mensaje dinámico)[cite: 3] -->
        <?php if($mensaje != ""): ?>
            <div class="mensaje-alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="tabla-inventario">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- La tabla se llena con la lista de datos de PHP[cite: 3] -->
                    <?php foreach($inventario as $item): ?>
                    <tr>
                        <td data-label="Producto"><?php echo $item['producto']; ?></td>
                        <td data-label="Precio">$<?php echo $item['precio']; ?></td>
                        <td data-label="Acción">
                            <button type="button" class="btn-edit">Editar</button>
                            <button type="button" class="btn-delete">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>