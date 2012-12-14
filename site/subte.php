<?php

$date = (int) date('H');
$sleeping = false;

if ($date >= 23 || $date < 5) {
    $sleeping = true;
}

function file_get_contents_utf8($fn) {
     $content = file_get_contents($fn);
      return mb_convert_encoding($content, 'UTF-8',
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function parseMessage($str) {
    global $sleeping;
    if ($sleeping) {
        return 'SLEEPING';
    }
    $str = strtolower($str);
    $status = 'UNKNOWN';
    if (strpos($str, 'servicio normal') !== false) {
        $status = 'NORMAL';
    } elseif (
            (strpos($str, 'gremial') !== false) ||
            (strpos($str, 'interrumpido') !== false) ||
            (strpos($str, 'interrupc') !== false) ||
            (strpos($str, 'paro') !== false)
        ) {
            $status = 'CANCELLED';
    } elseif (strpos($str, 'demora') !== false) {
        $status = 'DELAYED';
    } elseif (strpos($str, 'limitado') !== false) {
        $status = 'REDUCED';
    }
    return $status;
}

header('Content-type: application/json; charset=UTF-8');

if (@$_GET['callback']) {
    echo $_GET['callback'] . '(';
}

// Settings
$cachedir = 'cache/';
// every 5 mins
$cachetime = 60 * 5;
$cacheext = 'cache';
// $cachepage = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
// dont cache query string
$cachepage = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$cachefile = $cachedir.md5($cachepage).'.'.$cacheext;

if (@file_exists($cachefile)) {
    $cachelast = @filemtime($cachefile);
} else {
    $cachelast = 0;
}
@clearstatcache();
// Mostramos el archivo si aun no vence
if (time() - $cachetime <$cachelast) {
    // echo 'cache hit';
    echo file_get_contents($cachefile);
    exit();
}
ob_start();

$url = 'http://www.metrovias.com.ar/V2/InfoSubteSplash.asp';
$data = file_get_contents_utf8($url);

//pausecontent[\d+]\s=\s(.*);
// pausecontent[0] = '&nbsp;&nbsp;<b>L?nea A:</b>&nbsp; Servicio normal.';pausecontent[1] =
$results = preg_match_all('/pausecontent\[[\d+]\]\s=\s\'([^\']+)\';/', $data, $matches);

$json = array();

foreach($matches[1] as $line) {
    // echo $line . "\n";
    // $line = str_replace(array('&nbsp;', 'á', 'é', 'í', 'ó', 'ú'), array(' ', '&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;'), $line);
    $line = str_replace(array('&nbsp;'), array(' '), $line);
    // echo $line;
    // $line = str_replace(array('&nbsp;'), array(' '), $line);
    $line = strip_tags(html_entity_decode($line, ENT_QUOTES, 'UTF-8'));
    // preg_match('/nea\s([A-Z]{1}):.*(\s)([A-Za-z]+)\.?$/', $line, $data);
    preg_match('/nea\s([A-Z]{1}):\s\s?(.*)/', $line, $data);
    // echo $line . "\n";
    $json[$data[1]] = array(
        'status' => parseMessage($data[2]),
        'message' => htmlentities($data[2], ENT_QUOTES, 'UTF-8')
    );
    // var_dump($data);
}

echo json_encode($json);

file_put_contents($cachefile, ob_get_contents()); // or var_dump(error_get_last());
ob_end_flush();

if (@$_GET['callback']) {
    echo ');';
}

// echo $data;;