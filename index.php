<?php 
$RequestUri = $_SERVER['REQUEST_URI'];
$ScriptName = $_SERVER['SCRIPT_NAME'];
$outcome = strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
$ttlmins = 240; // Minutes of maximum age of messages "TTL"
`find /tmp/msg* -mindepth 1 -mmin +$ttlmins -delete`;

if(!empty($_REQUEST["id"])){
	$id = $_REQUEST["id"];
}
else{

	if ($outcome === false) {
		$id = substr($RequestUri, 1);
	}
	else{
		$abPos = $outcome + strlen($ScriptName);
		$id=  substr($RequestUri, $abPos+1);
	}
}


?>
<h2>Selbstzerstörende Nachrichten</h2>
<i>Hier kannst du Nachrichten hinterlassen. Diese werden beim ersten Abruf gelöscht. <br><small>Falls die Nachricht nicht innerhalb  <?php echo $ttlmins; ?> Minuten gelesen wird, wird sie ebenfalls gelöscht!</small> </i><br>
<br>
<br>

<script src="jquery.min.js"></script>

<Script Language="JavaScript">
$(document).ready(function() {
var copyTextareaBtn = document.querySelector('.js-textareacopybtn');

copyTextareaBtn.addEventListener('click', function(event) {
  var copyTextarea = document.querySelector('.js-copytextarea');
  copyTextarea.focus();
  copyTextarea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copying text command was ' + msg);
  } catch (err) {
    console.log('Oops, unable to copy');
  }
});

});
</Script>

<?php
function tailShell($filepath, $lines = 1) {
		ob_start();
		passthru('tail -'  . $lines . ' ' . escapeshellarg($filepath));
		return trim(ob_get_clean());
	}
$msgdir ="/tmp/msg";
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0C2f ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B )
    );

}

function secure_delete($file_path)
{
    $file_size = filesize($file_path);
    $new_content = str_repeat('0', $file_size);
    file_put_contents($file_path, $new_content);
    unlink($file_path);
}

$path= "https://".$_SERVER["SERVER_NAME"].$_SERVER["CONTEXT_PREFIX"];
if(!empty($id)) {
	$filename ="$msgdir/$id";
		if(file_exists($filename)){
			$fh = fopen($filename, 'r');
			$contents = fread($fh, filesize($filename));
			fclose($fh);
			secure_delete($filename);
			echo "Deine Nachricht lautet: <br><br><textarea style='width:400px;height:150px;'>$contents</textarea>";

		}
		else {
			echo "<div style='position: relative'><img align='center' style='display: flex;'  src='img/warn.png' height='100px'><br />&nbsp;<b>Die Nachricht wurde bereits abgerufen oder du hast einen falschen Link!</b></div><br><br>";
		}

			echo "<br><br><a href='./'>Neue Nachricht eingeben</a>";

}

else
{
	if(!empty($_REQUEST["action"])){
		$action = $_REQUEST["action"];
		if($action="send"){
			$bytes = generate_uuid();
			if (!is_dir("$msgdir/")) {
 			   mkdir($msgdir, 0777, true);
			}
			$filename = "$msgdir/$bytes";
			$fh = fopen($filename, 'w') or die("Can't create file");
			$msg = $_REQUEST["msg"];
			if(is_file($filename)){
			    file_put_contents($filename, $msg);     // Save our content to the file.
			}
			fclose($fh);
			//echo "<b><a href='$path/$bytes' onclick=\"return function() {alert('Wenn du mich anlickst, zerst&ouml;hre ich mich selbst und der eigentliche Empf&auml;nger kann die Nachricht nicht mehr lesen!');};'\"> ICH BIN DEIN LINK</a></b><br />";
			echo "<b> Das ist dein Link. Rufe ihn <u>nicht</u> selber auf. Die Nachricht zerstörtsich beim ersten &Ouml;ffnen selbst!</b><br />";
			echo "<input type='text' class='js-copytextarea' value='$path/$bytes' size='50'>";
			echo "<button class='js-textareacopybtn' style='vertical-align:top;'><img src='img/clipboard-copy-512.png' height='20px'></button><a href='whatsapp://send?text=$path/$bytes' ><img src='Whatsapp-icon.png' height='20px'></a><br><br><br>";
			echo "<br><br><a href='./'>Neue Nachricht erstellen</a></a>";
			
		}


	}
	else{

?>
Bitte gib hier die Nachricht ein, die du gerne versenden m&ouml;chtest.<br>
<small><i>Im nächsten Schritt erhälst du einen Internetadresse, die du an den Empänger der Nachticht senden kannst.<br> Wenn der Empfänger die Adresse aufruft, wird die Nachricht einmalig angezeigt und dann sofort gelöscht.<br> Dadurch ist gewährleistet, dass niemand au&szlig;er euch die Nachricht lesen kann.<br />
<b>Der Quellcode ist open source und auf Github verfügbar <a href=§http://github.com/bla">REPO</a></b></i></small> 	

<form method="post">
<textarea name="msg" style="width:400px;height:150px;"></textarea><br />
<input type="hidden" name="action" value="enc">
<input type="submit" value="Link erzeugen">
</form>
<?php }} ?>
<br><br><br>
<b>Alle Nachrichten werden nach <?php echo $ttlmins; ?> Minuten gelöscht, wenn sie nicht vorher abgerufen wurden. </b>	
