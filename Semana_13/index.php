<?php
session_start();
date_default_timezone_set('America/Mexico_City');

function e($t){ return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }
function m($n){ return '$' . number_format((float)$n, 2, '.', ','); }

if (!isset($_SESSION['bank'])) {
    $_SESSION['bank'] = [
        'nombre' => 'Josue',
        'clabe' => '646180123456789012',
        'saldo' => 0,
        'claves' => [],
        'apartados' => [],
        'movs' => []
    ];
}
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = '';

$b = &$_SESSION['bank'];
$view = $_GET['view'] ?? 'home';
$sec  = $_GET['sec'] ?? 'ingresar';
$flash = $_SESSION['flash'];
$_SESSION['flash'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['a'] ?? '';

    if ($a === 'ingresar') {
        $x = (float)($_POST['monto'] ?? 0);
        if ($x > 0) {
            $b['saldo'] += $x;
            array_unshift($b['movs'], ['t'=>'Ingreso','d'=>'Dinero ingresado','m'=>$x,'f'=>date('d/m/Y H:i')]);
            $_SESSION['flash'] = 'Ingreso realizado con éxito.';
        } else $_SESSION['flash'] = 'Monto inválido.';
        header('Location: ?view=acciones&sec=ingresar'); exit;
    }

    if ($a === 'transferir') {
        $c = trim($_POST['clabe'] ?? '');
        $n = trim($_POST['nombre'] ?? '');
        $x = (float)($_POST['monto'] ?? 0);
        $g = isset($_POST['guardar']);

        if (!preg_match('/^\d{18}$/', $c)) {
            $_SESSION['flash'] = 'La CLABE debe tener 18 dígitos.';
        } elseif ($x <= 0) {
            $_SESSION['flash'] = 'Monto inválido.';
        } elseif ($x > $b['saldo']) {
            $_SESSION['flash'] = 'No tienes saldo suficiente.';
        } else {
            $b['saldo'] -= $x;
            if ($g) {
                $b['claves'][$c] = ['clabe'=>$c, 'nombre'=>$n !== '' ? $n : 'Sin nombre'];
            }
            array_unshift($b['movs'], ['t'=>'Transferencia','d'=>'A '.($n !== '' ? $n : $c),'m'=>$x,'f'=>date('d/m/Y H:i')]);
            $_SESSION['flash'] = 'Transferencia realizada con éxito.';
        }
        header('Location: ?view=acciones&sec=transferir'); exit;
    }

    if ($a === 'pagar') {
        $s = trim($_POST['servicio'] ?? '');
        $x = (float)($_POST['monto'] ?? 0);
        if ($s === '') {
            $_SESSION['flash'] = 'Selecciona un servicio.';
        } elseif ($x <= 0) {
            $_SESSION['flash'] = 'Monto inválido.';
        } elseif ($x > $b['saldo']) {
            $_SESSION['flash'] = 'No tienes saldo suficiente.';
        } else {
            $b['saldo'] -= $x;
            array_unshift($b['movs'], ['t'=>'Pago','d'=>'Pago de '.$s,'m'=>$x,'f'=>date('d/m/Y H:i')]);
            $_SESSION['flash'] = 'Pago realizado con éxito.';
        }
        header('Location: ?view=acciones&sec=pagos'); exit;
    }

    if ($a === 'apartar') {
        $n = trim($_POST['nombre'] ?? '');
        $x = (float)($_POST['monto'] ?? 0);
        if ($n === '') {
            $_SESSION['flash'] = 'Escribe un nombre.';
        } elseif ($x <= 0) {
            $_SESSION['flash'] = 'Monto inválido.';
        } elseif ($x > $b['saldo']) {
            $_SESSION['flash'] = 'No tienes saldo suficiente.';
        } else {
            $b['saldo'] -= $x;
            $b['apartados'][] = ['nombre'=>$n, 'monto'=>$x, 'fecha'=>date('d/m/Y H:i')];
            array_unshift($b['movs'], ['t'=>'Apartado','d'=>'Para '.$n,'m'=>$x,'f'=>date('d/m/Y H:i')]);
            $_SESSION['flash'] = 'Apartado guardado con éxito.';
        }
        header('Location: ?view=acciones&sec=apartados'); exit;
    }
}

$apartTotal = 0;
foreach ($b['apartados'] as $a) $apartTotal += (float)$a['monto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banca simple</title>
<style>
*{box-sizing:border-box} body{margin:0;font-family:Arial,sans-serif;background:#f4f7ff;color:#1e2540}
a{text-decoration:none;color:inherit} .app{max-width:460px;margin:0 auto;padding:16px 14px 88px;min-height:100vh}
.top{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px}
.h{font-size:1.6rem;font-weight:800;color:#1f3f8f}.small{font-size:.9rem;color:#6c7284}
.card{background:#fff;border-radius:18px;padding:16px;box-shadow:0 10px 22px rgba(0,0,0,.07);margin-bottom:12px}
.row{display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap}
.pill{background:#4bb05f;color:#fff;padding:7px 12px;border-radius:999px;font-size:.85rem;font-weight:700}
.balance{font-size:2.6rem;font-weight:900;margin:10px 0 12px}
.link{display:flex;justify-content:space-between;align-items:center;background:#eef2fb;padding:12px 14px;border-radius:12px;font-weight:700;color:#1f3f8f}
.notice{background:#dff7e6;color:#245a33;border-radius:12px;padding:12px 14px;margin-top:12px;display:flex;justify-content:space-between;gap:10px}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:14px}
.short{background:#fff;border:1px solid #e5eaf8;border-radius:14px;padding:12px;text-align:center;font-weight:700;color:#244fb3}
.circle{width:52px;height:52px;margin:0 auto 6px;border-radius:50%;border:3px solid #2f57b8;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.tarjeta{margin-top:14px;border-radius:16px;background:linear-gradient(135deg,#14306f,#2248a5);color:#fff;height:140px;padding:14px;display:flex;flex-direction:column;justify-content:space-between}
.tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.tab{flex:1 1 calc(33.33% - 8px);min-width:90px;padding:9px 8px;text-align:center;border-radius:12px;background:#eef2fb;font-size:.85rem;font-weight:800;color:#33406a}
.tab.on{background:#1f3f8f;color:#fff}
label{display:block;margin:10px 0 6px;font-weight:800;font-size:.92rem}
input,select{width:100%;padding:13px 14px;border:1px solid #d7def0;border-radius:12px;font-size:1rem}
.btn{width:100%;border:0;padding:13px 14px;border-radius:12px;background:#2f66dc;color:#fff;font-weight:800;margin-top:12px}
.msg{background:#e9f3ff;border:1px solid #cfe3ff;color:#163a7a;padding:12px 14px;border-radius:12px;font-weight:700;margin-bottom:12px}
.item{background:#f6f8fd;border:1px solid #e5eaf8;border-radius:12px;padding:12px;margin-top:10px}
.muted{color:#6c7284}.title{margin:0 0 10px;font-size:1.15rem;color:#18306f}
.check{display:flex;gap:10px;align-items:center;margin-top:10px;font-weight:700}
.bottom{position:fixed;left:50%;transform:translateX(-50%);bottom:0;width:100%;max-width:460px;background:#fff;border-top:1px solid #e6ebf8;display:grid;grid-template-columns:repeat(5,1fr);gap:6px;padding:10px 10px 12px}
.nav{text-align:center;font-size:.75rem;color:#52607f;font-weight:700}
.nav span{display:block;font-size:1.02rem;margin-bottom:3px}
.nav.on{color:#1f3f8f}
.center span{width:52px;height:52px;margin:-20px auto 3px;border-radius:50%;background:#244fb3;color:#fff;display:flex;align-items:center;justify-content:center}
@media(max-width:420px){.h{font-size:1.4rem}.balance{font-size:2.25rem}.grid{grid-template-columns:repeat(2,1fr)}.tab{flex:1 1 calc(50% - 8px)}}
</style>
</head>
<body>
<div class="app">

<?php if ($view === 'home'): ?>
    <div class="top">
        <div class="h">Hola, <?= e($b['nombre']) ?> ›</div>
        <div class="small">🔔 ❓</div>
    </div>

    <?php if ($flash): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="card">
        <div class="row"><strong>Disponible</strong><div class="pill">13% anual</div></div>
        <div class="balance"><?= m($b['saldo']) ?></div>
        <a class="link" href="?view=acciones&sec=ingresar"><span>Cómo funcionan tus ganancias</span><span>›</span></a>
        <div class="notice"><span>¡Felicidades! Disfruta hasta 13% anual.</span><span>×</span></div>
        <div class="grid">
            <a class="short" href="?view=acciones&sec=ingresar"><div class="circle">⬇</div>Ingresar</a>
            <a class="short" href="?view=acciones&sec=transferir"><div class="circle">↗</div>Transferir</a>
            <a class="short" href="?view=acciones&sec=pagos"><div class="circle">💳</div>Pagos</a>
            <a class="short" href="?view=acciones&sec=clabe"><div class="circle">🪪</div>CLABE</a>
        </div>
        <div class="tarjeta"><strong>∞</strong><div><?= e($b['nombre']) ?></div></div>
    </div>

    <div class="card">
        <div class="row"><h2 class="title" style="margin:0;">Apartados</h2><a href="?view=acciones&sec=apartados">›</a></div>
        <div class="balance" style="font-size:1.9rem;margin:8px 0 0;"><?= m($apartTotal) ?></div>
    </div>

<?php else: ?>
    <div class="top">
        <a class="small" href="?view=home" style="font-weight:800;color:#1f3f8f;">← Regresar</a>
        <div class="small" style="font-weight:700;">Banca móvil</div>
    </div>

    <?php if ($flash): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="tabs">
        <a class="tab <?= $sec==='ingresar'?'on':'' ?>" href="?view=acciones&sec=ingresar">Ingresar</a>
        <a class="tab <?= $sec==='transferir'?'on':'' ?>" href="?view=acciones&sec=transferir">Transferir</a>
        <a class="tab <?= $sec==='pagos'?'on':'' ?>" href="?view=acciones&sec=pagos">Pagos</a>
        <a class="tab <?= $sec==='clabe'?'on':'' ?>" href="?view=acciones&sec=clabe">CLABE</a>
        <a class="tab <?= $sec==='apartados'?'on':'' ?>" href="?view=acciones&sec=apartados">Apartados</a>
    </div>

    <div class="card">
        <?php if ($sec==='ingresar'): ?>
            <h2 class="title">Ingresar dinero</h2>
            <p class="small">El saldo inicia en 0.</p>
            <form method="post">
                <input type="hidden" name="a" value="ingresar">
                <label>Monto</label>
                <input type="number" name="monto" min="1" step="0.01" placeholder="Ej. 500">
                <button class="btn">Ingresar dinero</button>
            </form>
        <?php elseif ($sec==='transferir'): ?>
            <h2 class="title">Transferir dinero</h2>
            <form method="post">
                <input type="hidden" name="a" value="transferir">
                <label>CLABE destino</label>
                <input type="text" name="clabe" maxlength="18" placeholder="18 dígitos">
                <label>Nombre opcional</label>
                <input type="text" name="nombre" placeholder="Ej. Mamá">
                <label>Monto</label>
                <input type="number" name="monto" min="1" step="0.01" placeholder="Ej. 200">
                <label class="check"><input type="checkbox" name="guardar" style="width:auto;">Guardar esta CLABE</label>
                <button class="btn">Transferir</button>
            </form>
            <h3 class="title" style="font-size:1rem;margin-top:16px;">CLABEs guardadas</h3>
            <?php if (empty($b['claves'])): ?>
                <div class="item muted">Aún no hay claves guardadas.</div>
            <?php else: foreach ($b['claves'] as $c): ?>
                <div class="item"><strong><?= e($c['nombre']) ?></strong><div class="muted"><?= e($c['clabe']) ?></div></div>
            <?php endforeach; endif; ?>
        <?php elseif ($sec==='pagos'): ?>
            <h2 class="title">Pagos</h2>
            <form method="post">
                <input type="hidden" name="a" value="pagar">
                <label>Servicio</label>
                <select name="servicio">
                    <option value="">Selecciona uno</option>
                    <option>Luz</option><option>Agua</option><option>Internet</option><option>Otro</option>
                </select>
                <label>Monto</label>
                <input type="number" name="monto" min="1" step="0.01" placeholder="Ej. 350">
                <button class="btn">Pagar servicio</button>
            </form>
        <?php elseif ($sec==='clabe'): ?>
            <h2 class="title">Tu CLABE</h2>
            <div class="item">
                <strong><?= e($b['nombre']) ?></strong>
                <div class="muted">CLABE: <?= e($b['clabe']) ?></div>
                <div class="muted">Saldo: <?= m($b['saldo']) ?></div>
            </div>
        <?php elseif ($sec==='apartados'): ?>
            <h2 class="title">Apartados</h2>
            <form method="post">
                <input type="hidden" name="a" value="apartar">
                <label>Nombre</label>
                <input type="text" name="nombre" placeholder="Ej. Viaje">
                <label>Monto</label>
                <input type="number" name="monto" min="1" step="0.01" placeholder="Ej. 100">
                <button class="btn">Guardar apartado</button>
            </form>
            <h3 class="title" style="font-size:1rem;margin-top:16px;">Tus apartados</h3>
            <?php if (empty($b['apartados'])): ?>
                <div class="item muted">Aún no tienes apartados.</div>
            <?php else: foreach (array_reverse($b['apartados']) as $a): ?>
                <div class="item"><strong><?= e($a['nombre']) ?></strong><div class="muted"><?= m($a['monto']) ?> · <?= e($a['fecha']) ?></div></div>
            <?php endforeach; endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="title">Movimientos</h2>
        <?php if (empty($b['movs'])): ?>
            <div class="item muted">Todavía no hay movimientos.</div>
        <?php else: foreach ($b['movs'] as $mv): ?>
            <div class="item">
                <strong><?= e($mv['t']) ?> · <?= m($mv['m']) ?></strong>
                <div class="muted"><?= e($mv['d']) ?></div>
                <div class="muted"><?= e($mv['f']) ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
<?php endif; ?>

</div>

<div class="bottom">
    <a class="nav <?= $view==='home'?'on':'' ?>" href="?view=home"><span>🏠</span>Inicio</a>
    <a class="nav <?= ($view==='acciones'&&$sec==='ingresar')?'on':'' ?>" href="?view=acciones&sec=ingresar"><span>🧾</span>Ingreso</a>
    <a class="nav center <?= ($view==='acciones'&&$sec==='transferir')?'on':'' ?>" href="?view=acciones&sec=transferir"><span>↗</span>Transferir</a>
    <a class="nav <?= ($view==='acciones'&&$sec==='pagos')?'on':'' ?>" href="?view=acciones&sec=pagos"><span>💳</span>Pagos</a>
    <a class="nav <?= ($view==='acciones'&&$sec==='apartados')?'on':'' ?>" href="?view=acciones&sec=apartados"><span>☰</span>Más</a>
</div>
</body>
</html>