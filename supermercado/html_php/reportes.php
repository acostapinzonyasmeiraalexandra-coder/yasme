<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["cargo"])) {
    header("Location: index.html");
    exit();
}

if ($_SESSION["cargo"] != 1 && $_SESSION["cargo"] != 2) {
    header("Location: menu.php");
    exit();
}

include("conexion.php");

if (!isset($conn)) {
    $conn = $conexion;
}

function e($texto) {
    return htmlspecialchars($texto);
}

$hasta = date("Y-m-d");
$desde = date("Y-m-d", strtotime("-29 days"));

if (isset($_GET["desde"]) && strtotime($_GET["desde"])) {
    $desde = date("Y-m-d", strtotime($_GET["desde"]));
}
if (isset($_GET["hasta"]) && strtotime($_GET["hasta"])) {
    $hasta = date("Y-m-d", strtotime($_GET["hasta"]));
}
if ($desde > $hasta) {
    $temp = $desde;
    $desde = $hasta;
    $hasta = $temp;
}

$sql = "SELECT p.cod_producto, p.nombre, p.categoria,
        (SELECT COALESCE(SUM(cantidad), 0) FROM mov_entrada
         WHERE cod_producto = p.cod_producto) AS entradas,
        (SELECT COALESCE(SUM(d.cantidad), 0) FROM detalle_salida d
         INNER JOIN factura_salida f ON f.cod_movimiento = d.cod_movimiento
         WHERE d.cod_producto = p.cod_producto) AS salidas
        FROM producto p
        ORDER BY p.nombre";
$res = $conn->query($sql);

$productos = [];
$total_stock = 0;
$agotados = 0;
$bajos = 0;
$categorias = [];

while ($fila = $res->fetch_assoc()) {
    $stock = $fila["entradas"] - $fila["salidas"];
    if ($stock < 0) {
        $stock = 0;
    }
    $fila["stock"] = $stock;
    $productos[] = $fila;

    $total_stock = $total_stock + $stock;
    if ($stock == 0) {
        $agotados++;
    }
    if ($stock <= 5) {
        $bajos++;
    }

    $categoria = $fila["categoria"];
    if ($categoria == "") {
        $categoria = "Sin categoría";
    }
    if (!isset($categorias[$categoria])) {
        $categorias[$categoria] = ["productos" => 0, "stock" => 0];
    }
    $categorias[$categoria]["productos"]++;
    $categorias[$categoria]["stock"] = $categorias[$categoria]["stock"] + $stock;
}

$top5 = $productos;
usort($top5, function ($a, $b) {
    return $b["stock"] - $a["stock"];
});
$top5 = array_slice($top5, 0, 5);

$total_productos = count($productos);

$res = $conn->query("SELECT COUNT(*) AS total FROM proveedor");
$total_proveedores = $res->fetch_assoc()["total"];

$res = $conn->query("SELECT COALESCE(SUM(cantidad), 0) AS total, COUNT(*) AS registros
                     FROM mov_entrada WHERE fecha BETWEEN '$desde' AND '$hasta'");
$fila = $res->fetch_assoc();
$unid_entran = $fila["total"];
$reg_entradas = $fila["registros"];

$res = $conn->query("SELECT COALESCE(SUM(d.cantidad), 0) AS total, COUNT(DISTINCT f.cod_movimiento) AS facturas
                     FROM factura_salida f
                     INNER JOIN detalle_salida d ON d.cod_movimiento = f.cod_movimiento
                     WHERE f.fecha BETWEEN '$desde' AND '$hasta'");
$fila = $res->fetch_assoc();
$unid_salen = $fila["total"];
$facturas = $fila["facturas"];

$lista_entradas = $conn->query("SELECT me.cod_movimiento, me.fecha, p.nombre, me.cantidad
                                FROM mov_entrada me
                                INNER JOIN producto p ON p.cod_producto = me.cod_producto
                                WHERE me.fecha BETWEEN '$desde' AND '$hasta'
                                ORDER BY me.fecha DESC
                                LIMIT 10");

$lista_salidas = $conn->query("SELECT f.cod_movimiento, f.fecha, f.cedula_cliente, SUM(d.cantidad) AS cantidad
                               FROM factura_salida f
                               INNER JOIN detalle_salida d ON d.cod_movimiento = f.cod_movimiento
                               WHERE f.fecha BETWEEN '$desde' AND '$hasta'
                               GROUP BY f.cod_movimiento, f.fecha, f.cedula_cliente
                               ORDER BY f.fecha DESC
                               LIMIT 10");

$ent_dia = [];
$res = $conn->query("SELECT fecha, SUM(cantidad) AS total FROM mov_entrada
                     WHERE fecha BETWEEN '$desde' AND '$hasta' GROUP BY fecha");
while ($fila = $res->fetch_assoc()) {
    $ent_dia[$fila["fecha"]] = $fila["total"];
}

$sal_dia = [];
$res = $conn->query("SELECT f.fecha, SUM(d.cantidad) AS total
                     FROM factura_salida f
                     INNER JOIN detalle_salida d ON d.cod_movimiento = f.cod_movimiento
                     WHERE f.fecha BETWEEN '$desde' AND '$hasta' GROUP BY f.fecha");
while ($fila = $res->fetch_assoc()) {
    $sal_dia[$fila["fecha"]] = $fila["total"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Supermercado La Granja</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; margin: 15px; }
    table { border-collapse: collapse; margin-bottom: 15px; width: auto; }
    th, td { border: 1px solid #999; padding: 2px 6px; text-align: left; font-size: 13px; }
    th { background: #e5e5e5; }
    .verde { color: green; font-weight: bold; }
    .rojo { color: red; font-weight: bold; }
</style>
</head>
<body>

<h1>Reportes de inventario</h1>
  <form action="menu.php" method="GET">
        <button type="submit">Menú principal</button>
    </form><br><br>
<button onclick="window.print()">Imprimir</button>

<form method="GET" action="reportes.php">
    Desde: <input type="date" name="desde" value="<?php echo $desde; ?>" required>
    Hasta: <input type="date" name="hasta" value="<?php echo $hasta; ?>" required>
    <button type="submit">Aplicar fechas</button>
    <a href="reportes.php">Últimos 30 días</a>
</form>

<h2>Resumen general</h2>
<table>
    <tr><th>Indicador</th><th>Valor</th></tr>
    <tr><td>Total de productos</td><td><?php echo $total_productos; ?></td></tr>
    <tr><td>Stock total</td><td><?php echo $total_stock; ?></td></tr>
    <tr><td>Proveedores</td><td><?php echo $total_proveedores; ?></td></tr>
    <tr><td>Productos agotados</td><td><?php echo $agotados; ?></td></tr>
    <tr><td>Stock bajo (5 o menos)</td><td><?php echo $bajos; ?></td></tr>
</table>

<h2>Productos y unidades por categoría</h2>
<table>
    <tr><th>Categoría</th><th>Productos</th><th>Unidades</th></tr>
    <?php foreach ($categorias as $nombre => $info) { ?>
    <tr>
        <td><?php echo e($nombre); ?></td>
        <td><?php echo $info["productos"]; ?></td>
        <td><?php echo $info["stock"]; ?></td>
    </tr>
    <?php } ?>
</table>

<h2>Top 5 productos con más stock</h2>
<table>
    <tr><th>Puesto</th><th>Producto</th><th>Categoría</th><th>Stock</th></tr>
    <?php $puesto = 1; foreach ($top5 as $p) { ?>
    <tr>
        <td><?php echo $puesto; ?></td>
        <td><?php echo e($p["nombre"]); ?></td>
        <td><?php echo e($p["categoria"]); ?></td>
        <td><?php echo $p["stock"]; ?></td>
    </tr>
    <?php $puesto++; } ?>
</table>

<h2>Existencias por producto</h2>
<table>
    <tr>
        <th>Código</th><th>Producto</th><th>Categoría</th>
        <th>Entradas</th><th>Salidas</th><th>Stock</th><th>Estado</th>
    </tr>
    <?php foreach ($productos as $p) { ?>
    <tr>
        <td><?php echo $p["cod_producto"]; ?></td>
        <td><?php echo e($p["nombre"]); ?></td>
        <td><?php echo e($p["categoria"]); ?></td>
        <td><?php echo $p["entradas"]; ?></td>
        <td><?php echo $p["salidas"]; ?></td>
        <td><?php echo $p["stock"]; ?></td>
        <td>
            <?php if ($p["stock"] == 0) { ?>
                <span class="rojo">Agotado</span>
            <?php } elseif ($p["stock"] <= 5) { ?>
                <span class="rojo">Stock bajo</span>
            <?php } else { ?>
                <span class="verde">Disponible</span>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<h2>Últimas entradas</h2>
<table>
    <tr><th>Código</th><th>Fecha</th><th>Producto</th><th>Cantidad</th></tr>
    <?php while ($fila = $lista_entradas->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $fila["cod_movimiento"]; ?></td>
        <td><?php echo $fila["fecha"]; ?></td>
        <td><?php echo e($fila["nombre"]); ?></td>
        <td><?php echo $fila["cantidad"]; ?></td>
    </tr>
    <?php } ?>
</table>

<h2>Últimas salidas</h2>
<table>
    <tr><th>Código</th><th>Fecha</th><th>Cliente</th><th>Cantidad</th></tr>
    <?php while ($fila = $lista_salidas->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $fila["cod_movimiento"]; ?></td>
        <td><?php echo $fila["fecha"]; ?></td>
        <td><?php echo e($fila["cedula_cliente"]); ?></td>
        <td><?php echo $fila["cantidad"]; ?></td>
    </tr>
    <?php } ?>
</table>

<h2>Entradas y salidas por día</h2>
<table>
    <tr><th>Fecha</th><th>Entradas</th><th>Salidas</th><th>Balance</th></tr>
    <?php
    $dia = $desde;
    while ($dia <= $hasta) {
        $ent = 0;
        $sal = 0;
        if (isset($ent_dia[$dia])) {
            $ent = $ent_dia[$dia];
        }
        if (isset($sal_dia[$dia])) {
            $sal = $sal_dia[$dia];
        }
    ?>
    <tr>
        <td><?php echo date("d/m/Y", strtotime($dia)); ?></td>
        <td><?php echo $ent; ?></td>
        <td><?php echo $sal; ?></td>
        <td><?php echo $ent - $sal; ?></td>
    </tr>
    <?php
        $dia = date("Y-m-d", strtotime($dia . " +1 day"));
    }
    ?>
</table>

<h2>Resumen del periodo</h2>
<table>
    <tr><th>Unidades que entraron</th><td><?php echo $unid_entran; ?></td></tr>
    <tr><th>Unidades que salieron</th><td><?php echo $unid_salen; ?></td></tr>
    <tr><th>Balance de unidades</th><td><?php echo $unid_entran - $unid_salen; ?></td></tr>
    <tr><th>Registros de entrada</th><td><?php echo $reg_entradas; ?></td></tr>
    <tr><th>Facturas de salida</th><td><?php echo $facturas; ?></td></tr>
</table>

<?php if ($unid_salen > $unid_entran) { ?>
    <p class="rojo">Alerta: las salidas superan las entradas del periodo.</p>
<?php } else { ?>
    <p class="verde">Las entradas son iguales o superiores a las salidas.</p>
<?php } ?>

</body>
</html>
<?php
$conn->close();
=======
<?php
session_start();

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["cargo"])) {
    header("Location: index.html");
    exit();
}

$cargo = (string) $_SESSION["cargo"];
if ($cargo !== "1" && $cargo !== "2") {
    header("Location: menu.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include("conexion.php");

if (!isset($conn) && isset($conexion)) {
    $conn = $conexion;
}
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error: no se pudo conectar con la base de datos.");
}
$conn->set_charset("utf8mb4");

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}
function numero($valor) {
    return number_format((float)$valor, 0, ",", ".");
}
function pesos($valor) {
    return "$" . number_format((float)$valor, 0, ",", ".");
}
function obtenerFilas($conn, $sql, $tipos = "", $params = []) {
    $stmt = $conn->prepare($sql);
    if ($tipos !== "") {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $filas = [];
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $stmt->close();
    return $filas;
}
function obtenerValor($conn, $sql, $tipos = "", $params = []) {
    $filas = obtenerFilas($conn, $sql, $tipos, $params);
    if (!$filas) return 0;
    return array_values($filas[0])[0] ?? 0;
}

/* Filtros: por defecto muestra el periodo de los últimos 30 días.
   Los KPI de productos, proveedores y stock muestran el estado actual;
   los movimientos y las gráficas de actividad usan el rango seleccionado. */
$fechaFin = $_GET["hasta"] ?? date("Y-m-d");
$fechaInicio = $_GET["desde"] ?? date("Y-m-d", strtotime("-29 days"));

$fechaValida = function($fecha) {
    $d = DateTime::createFromFormat("Y-m-d", $fecha);
    return $d && $d->format("Y-m-d") === $fecha;
};
if (!$fechaValida($fechaInicio)) $fechaInicio = date("Y-m-d", strtotime("-29 days"));
if (!$fechaValida($fechaFin)) $fechaFin = date("Y-m-d");
if ($fechaInicio > $fechaFin) {
    $temporal = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temporal;
}

$errorReporte = "";
try {
    // Existencias actuales calculadas con las tablas reales de entradas y salidas.
    $sqlStock = "
        SELECT p.cod_producto, p.nombre, p.categoria, p.precio,
               COALESCE(ent.total_entradas, 0) AS entradas,
               COALESCE(sal.total_salidas, 0) AS salidas,
               GREATEST(COALESCE(ent.total_entradas, 0) - COALESCE(sal.total_salidas, 0), 0) AS stock
        FROM producto p
        LEFT JOIN (
            SELECT cod_producto, SUM(cantidad) AS total_entradas
            FROM mov_entrada
            GROUP BY cod_producto
        ) ent ON ent.cod_producto = p.cod_producto
        LEFT JOIN (
            SELECT d.cod_producto, SUM(d.cantidad) AS total_salidas
            FROM detalle_salida d
            INNER JOIN factura_salida f ON f.cod_movimiento = d.cod_movimiento
            GROUP BY d.cod_producto
        ) sal ON sal.cod_producto = p.cod_producto
        ORDER BY p.nombre ASC";
    $productos = obtenerFilas($conn, $sqlStock);

    $totalProductos = (int) obtenerValor($conn, "SELECT COUNT(*) FROM producto");
    $totalProveedores = (int) obtenerValor($conn, "SELECT COUNT(*) FROM proveedor");
    $stockTotal = 0;
    $productosBajo = [];
    $productosAgotados = 0;
    foreach ($productos as $p) {
        $stock = (int)$p["stock"];
        $stockTotal += $stock;
        if ($stock <= 0) $productosAgotados++;
        if ($stock <= 5) $productosBajo[] = $p;
    }

    $totalEntradasPeriodo = (int) obtenerValor(
        $conn, "SELECT COALESCE(SUM(cantidad),0) FROM mov_entrada WHERE fecha BETWEEN ? AND ?",
        "ss", [$fechaInicio, $fechaFin]
    );
    $totalSalidasPeriodo = (int) obtenerValor(
        $conn, "SELECT COALESCE(SUM(d.cantidad),0)
                FROM detalle_salida d
                INNER JOIN factura_salida f ON f.cod_movimiento=d.cod_movimiento
                WHERE f.fecha BETWEEN ? AND ?",
        "ss", [$fechaInicio, $fechaFin]
    );
    $movimientosEntrada = (int) obtenerValor(
        $conn, "SELECT COUNT(*) FROM mov_entrada WHERE fecha BETWEEN ? AND ?",
        "ss", [$fechaInicio, $fechaFin]
    );
    $movimientosSalida = (int) obtenerValor(
        $conn, "SELECT COUNT(DISTINCT f.cod_movimiento)
                FROM factura_salida f
                WHERE f.fecha BETWEEN ? AND ?",
        "ss", [$fechaInicio, $fechaFin]
    );
    $totalMovimientos = $movimientosEntrada + $movimientosSalida;

    // Resumen por categoría.
    $categorias = [];
    foreach ($productos as $p) {
        $categoria = trim((string)$p["categoria"]);
        if ($categoria === "") $categoria = "Sin categoría";
        if (!isset($categorias[$categoria])) {
            $categorias[$categoria] = ["productos" => 0, "stock" => 0];
        }
        $categorias[$categoria]["productos"]++;
        $categorias[$categoria]["stock"] += (int)$p["stock"];
    }
    ksort($categorias, SORT_NATURAL | SORT_FLAG_CASE);

    // Cinco productos con más unidades disponibles.
    $topStock = $productos;
    usort($topStock, function($a, $b) {
        return (int)$b["stock"] <=> (int)$a["stock"];
    });
    $topStock = array_slice($topStock, 0, 5);

    // Actividad reciente combinando entradas y salidas.
    $actividad = obtenerFilas(
        $conn,
        "SELECT tipo, codigo, fecha, descripcion, cantidad FROM (
            SELECT 'Entrada' AS tipo, me.cod_movimiento AS codigo, me.fecha AS fecha,
                   CONCAT('Entrada de ', p.nombre, ' (proveedor: ', COALESCE(pr.nombre, 'No indicado'), ')') AS descripcion,
                   me.cantidad AS cantidad
            FROM mov_entrada me
            LEFT JOIN producto p ON p.cod_producto = me.cod_producto
            LEFT JOIN proveedor pr ON pr.cod_proveedor = me.cod_proveedor
            WHERE me.fecha BETWEEN ? AND ?
            UNION ALL
            SELECT 'Salida' AS tipo, f.cod_movimiento AS codigo, f.fecha AS fecha,
                   CONCAT('Venta/salida para cliente ', COALESCE(CAST(f.cedula_cliente AS CHAR), 'No indicado')) AS descripcion,
                   COALESCE(ds.total, 0) AS cantidad
            FROM factura_salida f
            LEFT JOIN (
                SELECT cod_movimiento, SUM(cantidad) AS total
                FROM detalle_salida GROUP BY cod_movimiento
            ) ds ON ds.cod_movimiento = f.cod_movimiento
            WHERE f.fecha BETWEEN ? AND ?
        ) movimientos
        ORDER BY fecha DESC, codigo DESC
        LIMIT 12",
        "ssss", [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]
    );

    // Series diarias de entradas y salidas, rellenando los días sin actividad con cero.
    $entradasPorDia = obtenerFilas(
        $conn, "SELECT fecha, SUM(cantidad) AS cantidad
                FROM mov_entrada WHERE fecha BETWEEN ? AND ?
                GROUP BY fecha ORDER BY fecha",
        "ss", [$fechaInicio, $fechaFin]
    );
    $salidasPorDia = obtenerFilas(
        $conn, "SELECT f.fecha, SUM(d.cantidad) AS cantidad
                FROM factura_salida f
                INNER JOIN detalle_salida d ON d.cod_movimiento=f.cod_movimiento
                WHERE f.fecha BETWEEN ? AND ?
                GROUP BY f.fecha ORDER BY f.fecha",
        "ss", [$fechaInicio, $fechaFin]
    );
    $mapEntradas = [];
    foreach ($entradasPorDia as $r) $mapEntradas[$r["fecha"]] = (int)$r["cantidad"];
    $mapSalidas = [];
    foreach ($salidasPorDia as $r) $mapSalidas[$r["fecha"]] = (int)$r["cantidad"];

    $labelsFechas = [];
    $datosEntradas = [];
    $datosSalidas = [];
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin);
    $dias = (int)$inicio->diff($fin)->days;
    // Limitar el gráfico a 90 días para que siga siendo legible.
    if ($dias > 90) {
        $inicio = (clone $fin)->modify("-90 days");
        $fechaGraficaInicio = $inicio->format("Y-m-d");
    } else {
        $fechaGraficaInicio = $fechaInicio;
    }
    $cursor = new DateTime($fechaGraficaInicio);
    while ($cursor <= $fin) {
        $dia = $cursor->format("Y-m-d");
        $labelsFechas[] = $cursor->format("d/m");
        $datosEntradas[] = $mapEntradas[$dia] ?? 0;
        $datosSalidas[] = $mapSalidas[$dia] ?? 0;
        $cursor->modify("+1 day");
    }
} catch (Throwable $ex) {
    $errorReporte = "No se pudieron cargar los reportes. Verifique que las tablas y columnas coincidan con la base de datos del proyecto.";
    // Detalle técnico disponible solo en el registro del servidor, no para usuarios.
    error_log("Error reportes.php: " . $ex->getMessage());
    $totalProductos = $totalProveedores = $stockTotal = $totalMovimientos = 0;
    $totalEntradasPeriodo = $totalSalidasPeriodo = $movimientosEntrada = $movimientosSalida = 0;
    $productos = $productosBajo = $topStock = $actividad = [];
    $categorias = [];
    $productosAgotados = 0;
    $labelsFechas = $datosEntradas = $datosSalidas = [];
}

$labelsCategorias = array_keys($categorias);
$valoresCategorias = [];
$valoresStockCategoria = [];
foreach ($categorias as $info) {
    $valoresCategorias[] = (int)$info["productos"];
    $valoresStockCategoria[] = (int)$info["stock"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reportes - Supermercado La Granja</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<div>
    <header>
        <div>
            <h1>Reportes de inventario</h1>
            <p>Supermercado La Granja · Datos consultados desde la base de datos</p>
        </div>
        <div>
            <a href="menu.php">Volver al menú</a>
            <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
        </div>
    </header>

    <?php if ($errorReporte !== ""): ?>
        <div><strong>Error al generar los reportes</strong><p><?php echo e($errorReporte); ?> Revise los nombres de las tablas y columnas en MySQL.</p></div>
    <?php else: ?>

    <form method="GET" action="reportes.php">
        <div><label for="desde">Desde</label><input type="date" id="desde" name="desde" value="<?php echo e($fechaInicio); ?>" required></div>
        <div><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" value="<?php echo e($fechaFin); ?>" required></div>
        <button type="submit">Aplicar fechas</button>
        <a href="reportes.php">Últimos 30 días</a>
    </form>

    <section>
        <article><div>Total de productos</div><div><?php echo numero($totalProductos); ?></div><div>Productos registrados</div></article>
        <article><div>Stock total</div><div><?php echo numero($stockTotal); ?></div><div>Unidades disponibles calculadas</div></article>
        <article><div>Proveedores</div><div><?php echo numero($totalProveedores); ?></div><div>Proveedores registrados</div></article>
        <article><div>Movimientos del periodo</div><div><?php echo numero($totalMovimientos); ?></div><div><?php echo numero($movimientosEntrada); ?> entradas · <?php echo numero($movimientosSalida); ?> salidas</div></article>
    </section>

    <section>
        <div>
            <strong>Productos agotados: <?php echo numero($productosAgotados); ?></strong>
            <p><?php echo $productosAgotados ? "Hay productos sin unidades disponibles. Revise el listado de alertas." : "No hay productos agotados según las entradas y salidas registradas."; ?></p>
        </div>
        <div>
            <strong>Stock bajo: <?php echo numero(count($productosBajo)); ?> productos</strong>
            <p>Se considera stock bajo tener 5 unidades o menos. Puede revisar los nombres en la pestaña Productos.</p>
        </div>
    </section>

    <nav aria-label="Secciones de reportes">
        <strong>Ir a:</strong>
        <a href="#general">General</a> |
        <a href="#productos">Productos</a> |
        <a href="#movimientos">Movimientos</a>
    </nav>
    <hr>

    <section id="general">
        <div>
            <article>
                <h2>Productos por categoría</h2>
                <div><canvas id="graficaCategorias" width="600" height="280"></canvas></div>
            </article>
            <article>
                <h2>Actividad reciente</h2>
                <div>
                <table>
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Movimiento</th><th>Cantidad</th></tr></thead>
                    <tbody>
                    <?php if (!$actividad): ?><tr><td colspan="4">No hay movimientos en este periodo.</td></tr>
                    <?php else: foreach ($actividad as $a): ?>
                    <tr>
                        <td><?php echo e($a["fecha"]); ?></td>
                        <td><spantipo"] === "Salida" ? "rojo" : "verde"; ?>"><?php echo e($a["tipo"]); ?></span></td>
                        <td>#<?php echo e($a["codigo"]); ?><br><small><?php echo e($a["descripcion"]); ?></small></td>
                        <td><?php echo numero($a["cantidad"]); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
                </div>
            </article>
        </div>
    </section>

    <section id="productos">
        <div>
            <article>
                <h2>Unidades disponibles por categoría</h2>
                <div><canvas id="graficaStockCategoria" width="600" height="280"></canvas></div>
            </article>
            <article>
                <h2>Top 5 productos con más stock</h2>
                <div><canvas id="graficaTopStock" width="600" height="280"></canvas></div>
            </article>
        </div>
        <article>
            <h2>Alertas y existencias por producto</h2>
            <div>
            <table>
                <thead><tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Entradas</th><th>Salidas</th><th>Stock actual</th><th>Estado</th></tr></thead>
                <tbody>
                <?php if (!$productos): ?><tr><td colspan="7">No hay productos registrados.</td></tr>
                <?php else: foreach ($productos as $p): $stock=(int)$p["stock"]; ?>
                <tr>
                    <td><?php echo e($p["cod_producto"]); ?></td><td><?php echo e($p["nombre"]); ?></td><td><?php echo e($p["categoria"] ?: "Sin categoría"); ?></td>
                    <td><?php echo numero($p["entradas"]); ?></td><td><?php echo numero($p["salidas"]); ?></td><td><strong><?php echo numero($stock); ?></strong></td>
                    <td><?php if ($stock <= 0): ?><span>Agotado</span><?php elseif ($stock <= 5): ?><span>Stock bajo</span><?php else: ?><span>Disponible</span><?php endif; ?></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </article>
    </section>

    <section id="movimientos">
        <div>
            <article>
                <h2>Entradas frente a salidas por día</h2>
                <p>Las cantidades corresponden a unidades registradas en el periodo. La gráfica muestra como máximo 91 días.</p>
                <div><canvas id="graficaMovimientos" width="600" height="280"></canvas></div>
            </article>
            <article>
                <h2>Resumen del periodo</h2>
                <table>
                    <tbody>
                    <tr><th>Unidades que entraron</th><td><?php echo numero($totalEntradasPeriodo); ?></td></tr>
                    <tr><th>Unidades que salieron</th><td><?php echo numero($totalSalidasPeriodo); ?></td></tr>
                    <tr><th>Balance de unidades</th><td><strong><?php echo numero($totalEntradasPeriodo - $totalSalidasPeriodo); ?></strong></td></tr>
                    <tr><th>Registros de entrada</th><td><?php echo numero($movimientosEntrada); ?></td></tr>
                    <tr><th>Facturas de salida</th><td><?php echo numero($movimientosSalida); ?></td></tr>
                    <tr><th>Movimientos totales</th><td><?php echo numero($totalMovimientos); ?></td></tr>
                    </tbody>
                </table>
                <p><?php if ($totalSalidasPeriodo > $totalEntradasPeriodo): ?><span>Alerta: las salidas superan las entradas del periodo.</span><?php else: ?><span>Las entradas del periodo son iguales o superiores a las salidas.</span><?php endif; ?></p>
                <p>El balance del periodo no es el stock actual. El stock actual se calcula usando todas las entradas y salidas registradas históricamente.</p>
            </article>
        </div>
    </section>

    <p>Periodo seleccionado: <?php echo e($fechaInicio); ?> a <?php echo e($fechaFin); ?>. Los datos se consultan al cargar esta página; vuelva a aplicar los filtros para actualizarlos.</p>
    <?php endif; ?>
</div>

<script>
const datosCategorias = <?php echo json_encode($valoresCategorias, JSON_UNESCAPED_UNICODE); ?>;
const labelsCategorias = <?php echo json_encode($labelsCategorias, JSON_UNESCAPED_UNICODE); ?>;
const datosStockCategoria = <?php echo json_encode($valoresStockCategoria, JSON_UNESCAPED_UNICODE); ?>;
const labelsTopStock = <?php echo json_encode(array_map(function($p){return $p["nombre"];}, $topStock), JSON_UNESCAPED_UNICODE); ?>;
const valoresTopStock = <?php echo json_encode(array_map(function($p){return (int)$p["stock"];}, $topStock), JSON_UNESCAPED_UNICODE); ?>;
const labelsFechas = <?php echo json_encode($labelsFechas, JSON_UNESCAPED_UNICODE); ?>;
const datosEntradas = <?php echo json_encode($datosEntradas, JSON_UNESCAPED_UNICODE); ?>;
const datosSalidas = <?php echo json_encode($datosSalidas, JSON_UNESCAPED_UNICODE); ?>;

const verde = "#059669";
// Colores diferentes para que cada categoría y producto se distinga.
const coloresGraficas = ["#2E86AB", "#F18F01", "#6A994E", "#BC4B51", "#8E6CBE", "#17A2A4", "#E76F51", "#D4A72C", "#577590", "#D45087"];
const verdes = coloresGraficas;
const comunes = {responsive:false, maintainAspectRatio:true};
if (document.getElementById("graficaCategorias")) {
    new Chart(document.getElementById("graficaCategorias"), {
        type:"pie",
        data:{labels:labelsCategorias,datasets:[{data:datosCategorias,backgroundColor:verdes,borderColor:"#fff",borderWidth:2}]},
        options:{...comunes,plugins:{legend:{position:"bottom"}}}
    });
}
if (document.getElementById("graficaStockCategoria")) {
    new Chart(document.getElementById("graficaStockCategoria"), {
        type:"bar",
        data:{labels:labelsCategorias,datasets:[{label:"Unidades disponibles",data:datosStockCategoria,backgroundColor:labelsCategorias.map((_,i)=>coloresGraficas[i % coloresGraficas.length]),borderRadius:4}]},
        options:{...comunes,scales:{y:{beginAtZero:true,ticks:{precision:0}}},plugins:{legend:{display:false}}}
    });
}
if (document.getElementById("graficaTopStock")) {
    new Chart(document.getElementById("graficaTopStock"), {
        type:"bar",
        data:{labels:labelsTopStock,datasets:[{label:"Stock",data:valoresTopStock,backgroundColor:labelsTopStock.map((_,i)=>coloresGraficas[i % coloresGraficas.length]),borderRadius:4}]},
        options:{...comunes,indexAxis:"y",scales:{x:{beginAtZero:true,ticks:{precision:0}}},plugins:{legend:{display:false}}}
    });
}
if (document.getElementById("graficaMovimientos")) {
    new Chart(document.getElementById("graficaMovimientos"), {
        type:"line",
        data:{labels:labelsFechas,datasets:[
            {label:"Entradas",data:datosEntradas,borderColor:"#059669",backgroundColor:"rgba(5,150,105,.12)",tension:.25,fill:true},
            {label:"Salidas",data:datosSalidas,borderColor:"#dc2626",backgroundColor:"rgba(220,38,38,.08)",tension:.25,fill:false}
        ]},
        options:{...comunes,interaction:{mode:"index",intersect:false},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
    });
}

</script>
</body>
</html>
<?php
$conn->close();
>>>>>>> c31e4164d2ca80f67e8f61220605109cf5de031e
?>