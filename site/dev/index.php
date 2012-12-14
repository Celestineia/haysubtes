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
            $status = 'Interrumpida';

            break;
        case 'DELAYED':
            $status = 'Demorada';

            break;
        case 'REDUCED':
            $status = 'Limitada';

            break;
        case 'SLEEPING':
            $status = 'Durmiendo';
            break;
    }
    return $status;
}

function getStationsForLine($name) {

	$estaciones = array();

	$file_handle = fopen("estaciones.txt", "rb");

	while (!feof($file_handle) ) {

		$line_of_text = fgets($file_handle);
		$parts = explode(',', $line_of_text);
		
		if (strcmp($parts[0], $name) == 0) {
			//Removing line name
			unset($parts[0]);
			$parts = array_values($parts);
			$estaciones = $parts;
		}

	}

	fclose($file_handle);
	
	return $estaciones;
}

function getDescriptionText($line, $name = '') {
    $status = '';
    $data = array();
    switch ($line->status) {
        case 'REDUCED':

        	preg_match('/estaciones: (.*?) y (.*?)\s\d/', $line->message, $data);

            //No estoy orgulloso de esto.
            if (empty($data[1])) {
            	preg_match('/ENTRE (.*?) Y (.*?)\s\d/', $line->message, $data);
            }

            if (empty($data[1])) {
            	preg_match('/estaciones (.*?) Y (.*?)\s\d/', $line->message, $data);
            }
            
            if (empty($data[1])) {
            
				//Si las expresiones regulares fallan, se busca el nombre entre la lista de estaciones
				$estaciones = getStationsForLine($name);
				//Saco acentos
				array_push($data, '');
				$line->message = strtr($line->message,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
				$line->message = strtolower($line->message);
				foreach ($estaciones as $estacion) {
					$estacion = strtr(utf8_decode($estacion), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
					$estacion = strtolower($estacion);
					if (strpos($line->message, $estacion) !== false) {
						//Si encuentro la estacion en el mensaje la agrego a la lista
						array_push($data, $estacion);
					}
				}
			}

            break;
    }
    
    //Capitalizing
	$data[1] = ucwords(strtolower($data[1]));
	$data[2] = ucwords(strtolower($data[2]));
    $status = $data[1] . "<br/>" . $data[2];
    
    return $status;
}
function getGlobalStatus($data) {
    $status = 'S&iacute; :)';
    $funcionando = 0;

    foreach ($data as $line => $obj) {
        if ($line == 'U') {
    		break;
    	}
        if ($obj->status === 'NORMAL') {
            $funcionando++;
        }
        if ($obj->status === 'DELAYED') {
            $status = 'Casi :/';
            $funcionando++;
        }
        if ($obj->status === 'CANCELLED') {
            $status = 'Casi :/';
            $funcionando++;
        }
        if ($obj->status === 'REDUCED') {
            $status = 'Casi :/';
            $funcionando++;
        }
        if ($obj->status === 'SLEEPING') {
            $status = 'Shh...';
            $funcionando++; // Asi el checkeo despues no da 'NO'
        }
    }

    if ($funcionando == 0) {
      $status = 'No :(';
    }

    return $status;
}

function getTweetText($data) {
    $status = '&iexcl;YAY! Todos los subtes funcionan con normalidad :D';
    $funcionando = 0;

    foreach ($data as $line => $obj) {
    	if ($line == 'U') {
    		break;
    	}
        if ($obj->status === 'NORMAL') {
            $funcionando++;
        }
        if ($obj->status === 'DELAYED') {
            $status = 'Mmmh, algunos subtes andan... otros no :/';
            $funcionando++;
        }
        if ($obj->status === 'CANCELLED') {
            $status = 'Mmmh, algunos subtes andan... otros no :/';
            $funcionando++;
        }
        if ($obj->status === 'REDUCED') {
            $status = 'Mmmh, algunos subtes andan... otros no :/';
            $funcionando++;
        }
        if ($obj->status === 'SLEEPING') {
            $status = '&iexcl;Oh! Los subtes est&aacute;n durmiendo';
            $funcionando++;
        }
    }

    if ($funcionando == 0) {
      $status = 'Buuh, todos los subtes están interrumpidos :C';
    }

    return $status;
}

$data = json_decode(file_get_contents('http://haysubtes.com/subte.php'));

//Testing objects
$interrumpido = new stdClass();
$interrumpido->{'status'} = 'CANCELLED';
$interrumpido->{'message'} = 'asdasdasd';

$reduced = new stdClass();
$reduced->{'status'} = 'REDUCED';
$reduced->{'message'} = 'Limitado LOS INCAS CALLAO 09:10 hs.';


$data = json_decode(file_get_contents('http://haysubtes.com/subte.php'));
$data->A = $interrumpido;
$data->B = $reduced;

?><!DOCTYPE html>
<html>
  <head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <title>&iquest;Hay subtes? | Estado del subte de Buenos Aires. Lineas A B C D E H P</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.3.0/build/cssreset/reset-min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    
	<link rel="apple-touch-icon" href="images/fblogo.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/fblogo.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/fblogo.png">
    
	<link rel="stylesheet" href="css/add2home.css">
	<script type="text/javascript" src="js/add2home.js" charset="utf-8"></script>
	
    <link rel="stylesheet" type="text/css" href="css/responsive.css">

    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <meta name="description" content="Conocé el estado del Subte de Buenos Aires. Lineas A B C D E H y Premetro. Se actualiza cada 2 minutos."/>
    <meta property="og:title" content="&iquest;Hay subtes? | Estado del subte de Buenos Aires. Lineas A B C D E H P"/>
    <meta property="og:type" content="website"/>
    <meta property="og:image" content="http://www.haysubtes.com/images/fblogo.png"/>
    <meta property="og:url" content="http://www.haysubtes.com/"/>
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
      <?php echo getGlobalStatus($data); ?>
    </div>

    <div class="lineas">
      <ul>
        <li class="divider"></li>
        <li class="linea a<?php echo getCSS($data->A); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-A.png" alt="Estado subte linea A" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->A); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->A, 'A'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea b<?php echo getCSS($data->B); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-B.png" alt="Estado subte linea B" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->B); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->B, 'B'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea c<?php echo getCSS($data->C); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-C.png" alt="Estado subte linea C" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->C); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->C, 'C'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea d<?php echo getCSS($data->D); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-D.png" alt="Estado subte linea D" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->D); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->D, 'D'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea e<?php echo getCSS($data->E); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-E.png" alt="Estado subte linea E" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->E); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->E, 'E'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea h<?php echo getCSS($data->H); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-H.png" alt="Estado subte linea H" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->H); ?></div>
          <div class="descripcion detalle"><?php echo getDescriptionText($data->H, 'H'); ?></div>
        </li>
        <li class="divider"></li>
        <li class="linea p<?php echo getCSS($data->P); ?>">
          <div class="icono logo">
            <img src="images/icons/Linea-P.png" alt="Estado subte linea premetro" />
          </div>
          <div class="icono estado"></div>
          <div class="descripcion estado"><?php echo getStatusText($data->P); ?></div>
		  <div class="descripcion detalle"><?php echo getDescriptionText($data->P, 'P'); ?></div>
        </li>
        <li class="divider"></li>
      </ul>
    </div>

	<!--
	<div class="advertencia">
		<div class="logoLeft"></div>
		<div class="info"><span class="title">Paro escalonado 06/12:</span><span class="lineaA"> Linea A:</span> 08 - 10 hs. | <span class="lineaB">Linea B:</span> 10 - 12 hs. | <span class="lineaC">Linea C:</span> 12 - 14 hs. |  <span class="lineaD">Linea D:</span> 14 - 16 hs. | <span class="lineaE">Linea E:</span> 16 - 18 hs. | <span class="lineaH">Linea H:</span> 16 - 18 hs. | <span class="lineaP">Premetro:</span> 16 - 18 hs.</div>
		<div style="clear: both"></div>
	</div>
	-->

	<div style="clear: both"></div>
    <footer>
      <div class="social">
        <a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo getTweetText($data); ?>" data-lang="es">Twittear</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="http://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        <div class="fb-like" data-href="http://www.haysubtes.com" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true"></div>
      </div>

      <div class="texto-bonito"><p>El estado de subtes de <a href="http://www.haysubtes.com">haysubtes.com</a> se actualiza cada 2 minutos. :)</p></div>

      <div class="quote"><a href="http://www.twitter.com/celestineia" target="_blank">Cerebro</a> - <a href="http://www.twitter.com/aguagraphics" target="_blank">Art</a> - <a href="http://www.twitter.com/blaquened" target="_blank">Layout</a> - Pinky - <a href="http://www.twitter.com/chompas" target="_blank">Master Shake</a> </div>
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