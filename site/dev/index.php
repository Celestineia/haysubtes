<?php

function getCSS($line) {
    $class = '';
    switch ($line->status) {
        case 'NORMAL':
            $class = ' normal';
            break;
        case 'CANCELLED':
            $class = ' interrumpido';
            break;
        case 'DELAYED':
            $class = ' demorado';
            break;
        case 'SLEEPING':
            $class = ' sleeping';
            break;
        case 'REDUCED':
            $class = ' limitado';
            break;
        default:
            $class = ' unknown';
            break;
    }
    return $class;
}

function getStatusText($line) {
    $status = '';
    switch ($line->status) {
        case 'NORMAL':
            $status = 'Normal';
            break;
        case 'CANCELLED':
            $status = 'Interrumpido';
            break;
        case 'DELAYED':
            $status = 'Demorado';
            break;
        case 'REDUCED':
            $status = 'Limitado';
            break;
        case 'SLEEPING':
            $status = 'Durmiendo';
            break;
    }
    return $status;
}

function getDescriptionText($line) {
    $status = 'asdasdasads';
    switch ($line->status) {
        case 'REDUCED':
        	preg_match('/estaciones (.*?) y (.*?)\s\d/', $line->message, $data);
            $status = $data[1] . "<br/>" . $data[2];
            break;
    }
    return $status;
}

function getGlobalStatus($data) {
    $status = 'S&iacute; :)';
    foreach ($data as $line => $obj) {
        if ($obj->status === 'DELAYED') {
            $status = 'Casi :/';
        }
        if ($obj->status === 'REDUCED') {
            $status = 'Casi :/';
        }
        if ($obj->status === 'CANCELLED') {
            $status = 'No :(';
            break;
        }
        if ($obj->status === 'SLEEPING') {
            $status = 'Shh...';
            break;
        }
    }
    return $status;
}

function getTweetText($data) {
    $status = '&iexcl;YAY! Todos los subtes funcionan con normalidad :D';
    foreach ($data as $line => $obj) {
        if ($obj->status === 'DELAYED') {
            $status = 'Mmmh, algunos subtes andan... otros no :/';
        }
        if ($obj->status === 'REDUCED') {
            $status = 'Mmmh, algunos subtes andan... otros no :/';
        }
        if ($obj->status === 'CANCELLED') {
            $status = 'Buuh, todos los subtes están interrumpidos :C';
            break;
        }
        if ($obj->status === 'SLEEPING') {
            $status = '&iexcl;Oh! Los subtes est&aacute;n durmiendo';
            break;
        }
    }
    return $status;
}

//Testing objects
$interrumpido = new stdClass();
$interrumpido->{'status'} = 'CANCELLED';
$interrumpido->{'message'} = 'asdasdasd'; 

$reduced = new stdClass();
$reduced->{'status'} = 'REDUCED';
$reduced->{'message'} = 'Servicio limitado entre estaciones CARABOBO y PIEDRAS 09:10 hs.'; 
 

$data = json_decode(file_get_contents('http://haysubtes.com/subte.php'));

?><!DOCTYPE html>
<html>
  <head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <title>&iquest;Hay subtes?</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.3.0/build/cssreset/reset-min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  <meta name="description" content="Enterate del estado de todas las l&iacute;neas de subtes"/>
    <meta property="og:title" content="&iquest;Hay subtes?"/>
    <meta property="og:type" content="website"/>
    <meta property="og:image" content="http://www.haysubtes.com/images/fblogo.png"/>
    <meta property="og:url" content="http://www.haysubtes.com/dev"/>
    <meta property="og:site_name" content="HaySubtes.com"/>
    <meta property="fb:app_id" content="474696555902114"/>
  </head>

  <body>
    <!-- Facebook JS SDK -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "http://connect.facebook.net/en_US/all.js#xfbml=1&appId=474696555902114";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>

    <header>
      <h1 class="titulo">&iquest;Hay subtes?</h1>
    </header>

    <div class="estado-general">
      <?php echo getGlobalStatus((array)$data); ?>
    </div>
	
    <div class="lineas">
      <ul>
        <li class="divider"></li>
        <li class="linea a<?php echo getCSS($interrumpido); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($interrumpido); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea b<?php echo getCSS($reduced); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($reduced); ?></div>
          <div class="descripcion estado"><?php echo getDescriptionText($reduced); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea c<?php echo getCSS($data->C); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->C); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea d<?php echo getCSS($data->D); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->D); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea e<?php echo getCSS($data->E); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->E); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea h<?php echo getCSS($data->H); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->H); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea p<?php echo getCSS($data->P); ?>">
          <div class="icono logo"></div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->P); ?></div>
        </li>
        <li class="divider"></li>
      </ul>
    </div>

    <footer>
      <div class="social">
        <a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo getTweetText($data); ?>" data-lang="es">Twittear</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="http://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        <div class="fb-like" data-href="http://www.haysubtes.com" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true"></div>
      </div>

      <div class="texto-bonito"><p>haysubtes.com se actualiza cada 5 minutos. :)</p></div>

      <div class="quote"><a href="http://www.twitter.com/celestineia" target="_blank">Cerebro</a> - <a href="http://www.twitter.com/aguagraphics" target="_blank">Art</a> - <a href="http://www.twitter.com/blaquened" target="_blank">Layout</a> - <a href="http://www.twitter.com/tomasdev" target="_blank">Pinky</a></div>
    </footer>
  </body>

  <script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-36787848-1']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

  </script>
</html>