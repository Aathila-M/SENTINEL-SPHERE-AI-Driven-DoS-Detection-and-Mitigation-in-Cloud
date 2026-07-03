[root@kt10ap52 warritest]# cat index.php
<?php
session_start();

/* ================= CONFIG =================
 * Replace YOUR_USERNAME and YOUR_PASSWORD_HASH
 * before deploying this application.
 */
$STATE = "/var/run/nginx_dos.state";
$LOG   = "/var/log/nginx/error.log";
$ATTEMPTS_FILE = "/var/run/nginx_dos_login_attempts.json";

$USER = "YOUR_USERNAME";
$HASH = 'YOUR_PASSWORD_HASH';
$MAX_ATTEMPTS = 3;

/* LOGIN ATTEMPTS */
$attempts = [];
if (file_exists($ATTEMPTS_FILE)) {
    $attempts = json_decode(@file_get_contents($ATTEMPTS_FILE), true) ?: [];
}

/* LOGIN */
if (isset($_POST['login'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!isset($attempts[$ip])) $attempts[$ip] = 0;

    if ($attempts[$ip] >= $MAX_ATTEMPTS) {
        echo "<p>Too many failed attempts. Contact admin.</p>";
        exit;
    }

    if ($_POST['u'] === $USER && password_verify($_POST['p'], $HASH)) {
        $_SESSION['ok'] = true;
        $attempts[$ip] = 0;
    } else {
        $attempts[$ip]++;
        $left = $MAX_ATTEMPTS - $attempts[$ip];
        echo "<p>Invalid credentials. Attempts left: $left</p>";
        file_put_contents($ATTEMPTS_FILE, json_encode($attempts));
        exit;
    }

    file_put_contents($ATTEMPTS_FILE, json_encode($attempts));
}

/* STATUS ENDPOINT */
if (isset($_GET['status'])) {
    echo file_exists($STATE) ? trim(@file_get_contents($STATE)) : "off";
    exit;
}

/* LOGS ENDPOINT */
if (isset($_GET['logs'])) {
    $lines = shell_exec("tail -n 50 " . escapeshellarg($LOG));
    echo htmlspecialchars($lines);
    exit;
}

/* ACTIONS */
if (isset($_SESSION['ok']) && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'on':
            file_put_contents($STATE, "on");
            shell_exec('sudo /usr/local/bin/nginx_dos_secure_on.sh');
            break;

        case 'off':
            file_put_contents($STATE, "off");
            shell_exec('sudo /usr/local/bin/nginx_dos_secure_off.sh');
            break;

        case 'clear':
            // UI-only action — handled in JS
            break;
    }
    echo json_encode(['ok' => true]);
    exit;
}

/* LOGOUT */
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['logout' => true]);
    exit;
}

/* INITIAL STATUS */
$status = (file_exists($STATE) && trim(@file_get_contents($STATE)) === "on")
    ? "ENABLED"
    : "DISABLED";
?>

<!doctype html>
<html>
<head>
<title>NGINX Control</title>
<style>
body{background:#0f172a;color:#e5e7eb;font-family:Arial}
.box{width:480px;margin:80px auto;background:#020617;padding:20px;border-radius:10px;text-align:center}
button{
  padding:10px 16px;
  margin:4px;
  border:none;
  border-radius:4px;
  cursor:pointer;
}
.on{background:#16a34a;color:#fff}
.off{background:#dc2626;color:#fff}
.logout{background:#f59e0b;color:#fff}
.viewlogs{background:#3b82f6;color:#fff}
.on:hover{background:#15803d}
.off:hover{background:#b91c1c}
.logout:hover{background:#d97706}
.viewlogs:hover{background:#2563eb}
pre{background:#000;color:#22c55e;height:220px;overflow:auto;text-align:left;padding:10px;border-radius:6px;margin-top:10px}
p{color:#f87171;margin:5px 0}
</style>
</head>

<body>
<div class="box">

<?php if (!isset($_SESSION['ok'])): ?>

<form method="post">
<h3>Login</h3>
<input name="u" placeholder="User"><br>
<input type="password" name="p" placeholder="Password"><br>
<button name="login" class="on">Login</button>
</form>

<?php else: ?>

<h3>DoS Protection: <b id="status"><?= $status ?></b></h3>

<button onclick="act('on')" class="on">Enable</button>
<button onclick="act('off')" class="off">Disable</button>
<button onclick="act('clear')">Clear Logs</button>
<button onclick="getLogs()" class="viewlogs">View Last 50 Logs</button>
<button onclick="logout()" class="logout">Logout</button>

<pre id="logs"></pre>

<script>
function act(a){
  if (a === 'clear') {
    document.getElementById('logs').innerText = '';
    return;
  }

  document.getElementById('status').innerText = 'APPLYING...';
  fetch("", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "action=" + a
  }).then(() => setTimeout(refreshStatus, 1000));
}

function refreshStatus(){
  fetch("?status=1")
    .then(r => r.text())
    .then(s => {
      document.getElementById('status').innerText =
        (s.trim() === 'on') ? 'ENABLED' : 'DISABLED';
    });
}

function getLogs(){
  fetch("?logs=1")
    .then(r => r.text())
    .then(s => {
      document.getElementById('logs').innerText = s;
    });
}

function logout(){
  fetch("?logout=1")
    .then(r => r.json())
    .then(res => {
      if(res.logout){
        document.getElementById('logs').innerText = '';
        window.location.href = window.location.pathname;
      }
    });
}

setInterval(refreshStatus, 5000);
</script>

<?php endif; ?>

</div>
</body>
</html>

[root@kt10ap52 warritest]#
