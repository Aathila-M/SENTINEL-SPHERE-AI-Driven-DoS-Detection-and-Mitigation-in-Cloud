[root@kt10ap51 ab]# cat index.php
<?php
session_start();

/* ================= CONFIG ================= 
 * Replace YOUR_USERNAME and YOUR_PASSWORD_HASH
 * before deploying this application.
 */

$RUN_DIR = "/var/run/ab";
$CONTROL = "$RUN_DIR/ab_dos_control";
$STATE   = "$RUN_DIR/ab_dos.state";
$LOG     = "/var/log/ab_dos.log";

$USER = "YOUR_USERNAME";
$HASH = 'YOUR_PASSWORD_HASH';

/* ================= ENSURE RUN DIR ================= */

if (!is_dir($RUN_DIR)) {
    mkdir($RUN_DIR, 0755, true);
}

/* ================= LOGIN ================= */

if (!isset($_SESSION['ok']) && isset($_POST['login'])) {
    if ($_POST['u'] === $USER && password_verify($_POST['p'], $HASH)) {
        session_regenerate_id(true);
        $_SESSION['ok'] = true;
        header("Location: /ab/index.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}

/* ================= ACTIONS ================= */

if (isset($_SESSION['ok']) && isset($_POST['action'])) {
    header("Content-Type: application/json");

    $action = $_POST['action'];

    if (!is_writable($RUN_DIR)) {
        echo json_encode([
            "ok" => false,
            "error" => "RUN_DIR not writable: $RUN_DIR"
        ]);
        exit;
    }

    $data = ($action === "start") ? "on\n" : "off\n";

    $bytes = @file_put_contents($CONTROL, $data);

    if ($bytes === false) {
        $err = error_get_last();
        echo json_encode([
            "ok" => false,
            "error" => "file_put_contents failed",
            "path" => $CONTROL,
            "php_error" => $err
        ]);
        exit;
    }

    echo json_encode([
        "ok" => true,
        "written_bytes" => $bytes,
        "path" => $CONTROL
    ]);
    exit;
}

/* ================= STATUS ================= */

if (isset($_GET['status'])) {
    echo file_exists($STATE) ? trim(file_get_contents($STATE)) : "off";
    exit;
}

/* ================= LOG FETCH (TAIL -50) ================= */

if (isset($_GET['logs'])) {
    if (!file_exists($LOG)) {
        echo "No logs available";
        exit;
    }

    $lines = file($LOG, FILE_IGNORE_NEW_LINES);
    $tail  = array_slice($lines, -50);
    echo htmlspecialchars(implode("\n", $tail));
    exit;
}

/* ================= LOGOUT ================= */

if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p["path"], $p["domain"], $p["secure"], $p["httponly"]
        );
    }
    session_destroy();
    header("Location: /ab/index.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<title>AB DoS Control</title>
<style>
body{background:#0f172a;color:#e5e7eb;font-family:Arial}
.box{width:520px;margin:80px auto;background:#020617;padding:20px;border-radius:10px;text-align:center}
button{padding:10px 16px;margin:4px;border:none;border-radius:4px;cursor:pointer}
.start{background:#dc2626;color:#fff}
.stop{background:#16a34a;color:#fff}
.fetch{background:#3b82f6;color:#fff}
.clear{background:#f59e0b;color:#fff}
.logout{background:#fbbf24;color:#000}
pre{background:#000;color:#22c55e;height:220px;overflow:auto;text-align:left;padding:10px;border-radius:6px;margin-top:10px}
p{color:#f87171}
input{margin:6px 0;padding:8px;width:80%;border-radius:4px;border:1px solid #555;background:#111;color:#fff}
</style>
</head>
<body>
<div class="box">

<?php if (!isset($_SESSION['ok'])): ?>
<form method="post">
<h3>Login</h3>
<input name="u" placeholder="User"><br>
<input type="password" name="p" placeholder="Password"><br>
<button name="login" class="start">Login</button>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
</form>

<?php else: ?>
<h3>AB DoS Status: <b id="status">CHECKING...</b></h3>

<button onclick="act('start')" class="start">Start DoS</button>
<button onclick="act('stop')" class="stop">Stop DoS</button>
<button onclick="fetchLogs()" class="fetch">Fetch Logs</button>
<button onclick="clearLogs()" class="clear">Clear Logs</button>
<button onclick="logout()" class="logout">Logout</button>

<pre id="logs"></pre>

<script>
function act(a){
  fetch("/ab/index.php", {
    method:"POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body: "action=" + a
  })
  .then(r => {
    if(!r.ok) throw new Error("HTTP " + r.status);
    return r.json();
  })
  .then(j => {
    if(!j.ok){
      alert("ERROR:\n" + JSON.stringify(j, null, 2));
    } else {
      alert("SUCCESS:\n" + JSON.stringify(j, null, 2));
    }
    refresh();
  })
  .catch(e => {
    alert("FETCH FAILED: " + e.message);
  });
}

function refresh(){
  fetch("/ab/index.php?status=1")
    .then(r=>r.text())
    .then(s=>{
      document.getElementById('status').innerText =
        (s.trim() === "on") ? "RUNNING" : "STOPPED";
    });
}

function fetchLogs(){
  fetch("/ab/index.php?logs=1")
    .then(r=>r.text())
    .then(t=>document.getElementById('logs').innerText = t);
}

function clearLogs(){
  document.getElementById('logs').innerText = "";
}

function logout(){
  window.location = "?logout=1";
}

refresh();
</script>
<?php endif; ?>

</div>
</body>
</html>

[root@kt10ap51 ab]#
