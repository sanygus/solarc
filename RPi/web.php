<?php

$fdir="cam";//директория видео и фото
$fosv="/tmp/osv";//освещение
$fvlt="/tmp/vlt";//вольтаж

function btst($vpost)
{
  if($vpost==$_POST['act']){echo(" style=\"font-weight:bold;\"");};
  
  switch($_POST['act'])
  {
    case 'rec':
      if(($vpost=='cast')or($vpost=='photo')){echo(" disabled");};
      break;
    case 'cast':
      if(($vpost=='rec')or($vpost=='photo')){echo(" disabled");};
      break;
    case 'off':
      echo(" disabled");
      break;
  }
}


if($_POST['act']=='rec')
{
  if(!($_POST['ref']=='true')){$log .= shell_exec('/home/pi/rec');};
  $mess .= "recording...";
}
elseif($_POST['act']=='cast')
{
  if(!($_POST['ref']=='true')){$log .= shell_exec('/home/pi/h264_v4l2_rtspserver/h264_v4l2_rtspserver > /dev/null 2>&1 &');};
  $mess .= "stream: rtsp://solarcomp.cloudapp.net:89/unicast";
}
elseif($_POST['act']=='cast2')
{
#  if(!($_POST['ref']=='true')){$log .= shell_exec('/home/pi/app > /dev/null 2>&1 &');};
#  $mess .= "stream: rtsp://192.168.0.110:8554/pi_encode.h264";
}
elseif($_POST['act']=='photo')
{
  if(!($_POST['ref']=='true')){$log .= shell_exec('raspistill -t 1 -n -o /tmp/cam/img'.date("YmdHis").'.jpg');};
  $mess .= "Photo OK";
}
elseif($_POST['act']=='stop')
{
  if(!($_POST['ref']=='true')){$log .= shell_exec('sudo killall raspivid h264_v4l2_rtspserver');};
  $mess .= "stop";
}
elseif($_POST['act']=='off')
{
  if(!($_POST['ref']=='true')){$log .= shell_exec('sudo shutdown -h now');};
  $mess .= "bye";
}
elseif(!($_POST['delfile']==''))
{
  unlink($fdir."/".$_POST['delfile']);//NOT SECURE!
  $mess .= "File '".$_POST['delfile']."' deleted";
};


$flistarr=scandir($fdir,1);
$flistvid="";
$flistimg="";
foreach($flistarr as $flistv)
{
 if(preg_match("~^.+\.h264$~",$flistv)){$flistvid .= "<a href=\"$fdir/$flistv\" download>".$flistv."</a>&emsp;".date("H:i:s d-m-Y",filectime($fdir."/".$flistv))."&emsp;<a href=\"$fdir/$flistv\" download>".round((filesize($fdir."/".$flistv)/1024/1024),3)." MB</a>&emsp;<form style=\"display:inline;\"><button type=\"submit\" formaction=\"/\" formmethod=\"POST\" name=\"delfile\" value=\"$flistv\" class=\"mdl-button mdl-js-button mdl-button--icon\"><img src=\"mdl/delete.png\"></button></form><br><br>";};
 if(preg_match("~^.+\.jpg$~",$flistv)){$flistimg .= "<a href=\"$fdir/$flistv\" target=\"_blank\"><img src=\"$fdir/$flistv\" width=\"80px\" height=\"60px\" style=\"vertical-align:middle;\"></a>&emsp;".date("H:i:s d-m-Y",filectime($fdir."/".$flistv))."&emsp;<a href=\"$fdir/$flistv\" download>".round((filesize($fdir."/".$flistv)/1024/1024),3)." MB</a>&emsp;<form style=\"display:inline;\"><button type=\"submit\" formaction=\"/\" formmethod=\"POST\" name=\"delfile\" value=\"$flistv\" class=\"mdl-button mdl-js-button mdl-button--icon\"><img src=\"mdl/delete.png\"></button></form><br><br>";};
};
unset($flistarr,$flistv);
if($flistvid==""){$flistvid="No videos";};
if($flistimg==""){$flistimg="No photo";};


//info
$temperaturec = shell_exec("/opt/vc/bin/vcgencmd measure_temp");

$cpu_load = shell_exec("top -b -n 1");
preg_match_all("/ni, \d{1,2}\.\d id,/",$cpu_load,$cpu_load_matches);
$cpu_load = str_replace("ni, ","",$cpu_load_matches[0][0]);
$cpu_load = str_replace(" id,","",$cpu_load);
unset($cpu_load_matches);
$cpu_load = 100 - $cpu_load;

if(file_exists($fosv)){
  $light_sensor=file_get_contents($fosv);
  $light_sensor=round(($light_sensor-85)/1.65);
}else{$light_sensor="Not found";};

if(file_exists($fvlt)){
  $voltage_sensor=file_get_contents($fvlt);
  $voltage_sensor=$voltage_sensor * 4 * 0.0049;
}else{$voltage_sensor="Not found";};

?>                                                                                     

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>RPi</title>
    <link rel="stylesheet" href="mdl/material.min.css">
    <script src="mdl/material.min.js"></script>
  </head>
  <body>
      <center><br><div class="mdl-spinner mdl-js-spinner" id="loadspinner"></div><br><br><br>
        <form action="/" method="POST">
          <button type=submit name=act value=rec onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button"<?php btst("rec"); ?>>Запись</button>&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=cast onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button"<?php btst("cast"); ?>>Поток</button>&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=photo onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button"<?php btst("photo"); ?>>Фото</button>&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=stop onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button"<?php btst("stop"); ?>>Стоп</button>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=off onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button"<?php btst("off"); ?>>Off</button>
        </form><br><br><br><br>

<?php
echo($mess);
?>

<br><br><br><br><br><br>
<form action="/" method="POST"><input type=hidden name=act value=<?php echo($_POST['act']); ?>><button type=submit name=ref value=true onclick="document.getElementById('loadspinner').className += ' is-active';" class="mdl-button mdl-js-button mdl-button--icon"><img src="mdl/refresh.png"></button></form>
<br><br>

<table style="border-spacing:200px 5px;"><tr align=center><td>Видео</td><td>Фото</td></tr><tr><td>
<?php
echo($flistvid);
?>
</td><td>
<?php
echo($flistimg);
?>
</td></tr></table> 
<?php
if(!($log=='')){echo("<br><br><hr><br>log:<i>".$log."</i><br>");};
?>
<div>
<hr>
<?php
echo("<b>RPi:</b>&emsp;CPU: ".$cpu_load."%&emsp;".$temperaturec."<br><br><b>Arduino:</b>&emsp;Освещение ".$light_sensor."&emsp;Напряжение ".$voltage_sensor." V");
?>
 </div>     
      </center>
  </body>
</html>
