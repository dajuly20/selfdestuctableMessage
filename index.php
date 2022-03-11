
<?php 
	require('link-preview-detector/LinkPreviewOrigin.php');
		$fp = fopen("/tmp/useragents", "a");
		$dataHeaders = getallheaders();
		$ua = $dataHeaders['User-Agent'];
		
	if(LinkPreviewOrigin::isForLinkPreview()){
		$bla = "JAAA";
		die("Preview Flytrap ;-))");

	}
	else{
	$bla ="NEIN";
	}
	
		fwrite($fp, "Gottcha..$bla ".$ua."\n\n\n");
		fclose($fp);

	$completeUri = $_SERVER['REQUEST_URI'];
	$pysicalUri = $_SERVER['SCRIPT_NAME'];
	$virtualUri = strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
	$path= "https://".$_SERVER["SERVER_NAME"].$_SERVER["CONTEXT_PREFIX"];
	$maxFileAgeMinutes = 240;
	$maxFileAgeIpBlock = 48 * 60;
	$msgdir ="/tmp/msg";
	$ipFilterDir="/tmp/msgIps";
	$ipAddr = $_SERVER["REMOTE_ADDR"];

	 if (!is_dir("$ipFilterDir/")) {
         	mkdir($msgdir, 0777, true);
         }

	//Deletng files that are older than $maxFileAgeMinutes
	`find /tmp/msg* -mindepth 1 -mmin +$maxFileAgeMinutes -delete`; 
	`find /tmp/msgIps* -mindepth 1 -mmin +$maxFileAgeIpBlock -delete`; 
	function touchIpForWrongId($IP){
		$ipCounterPath="./countIp.sh $IP";
		$y= exec($ipCounterPath, $output);
		if($y >= 400) die("Die IP Adresse $IP wurde aufgrund zu vieler Fehlversuche dauerhaft blockiert");
	}
	function tailShell($filepath, $lines = 1) {
		ob_start();
		passthru('tail -'  . $lines . ' ' . escapeshellarg($filepath));
		return trim(ob_get_clean());
	}

	function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0C2f ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B )
		);

	}

	function secure_delete($file_path) {
		$file_size = filesize($file_path);
		$new_content = str_repeat('0', $file_size);
		file_put_contents($file_path, $new_content);
		return unlink($file_path);
	}
?>
<!doctype html>

<html lang="de">
<head>
<title>Selbstzerstörende Nachrichten</title>

	<script src="jquery.min.js"></script>
	<script Language="JavaScript">
		$(document).ready(function() {
			var copyTextareaBtn = document.querySelector('.js-textareacopybtn');
			$(".button-link").click(function(event) {
			myHref = $(this).attr("href");
			window.location.href = myHref;
			});
			copyTextareaBtn.addEventListener('click', function(event) {
				var copyTextarea = document.querySelector('.js-copytextarea');
				copyTextarea.focus();
				copyTextarea.select();

				try {
					var successful = document.execCommand('copy');
					var msg = successful ? 'successful' : 'unsuccessful';

				} catch (err) {

				}
			});
		});
	</script>
	
	<h2>Selbstzerstörende Nachrichten</h2>
	<i>Hier kannst du Nachrichten hinterlassen. Diese werden beim ersten Abruf gelöscht. <br><small>Falls die Nachricht nicht innerhalb  <?php echo $maxFileAgeMinutes; ?> Minuten gelesen wird, wird sie ebenfalls gelöscht!</small> </i><br>
	<br>
	<br>
</head>
<body>

<?php	

	//Request parameter domain.tld?id=XXX is prefered over SEO friendly URL domain.tld/XXX 
	if(!empty($_REQUEST["id"])){
		$messageId = $_REQUEST["id"];
	}
	else {
		if ($virtualUri === false) {
			$messageId = substr($completeUri, 1);
		}
		else {
			$positionStartVirtualUri = $virtualUri + strlen($pysicalUri);
			$messageId = substr($completeUri, $positionStartVirtualUri+1);
		}
		$needle =  strstr($messageId, "?", true); // ?GET Shizzle is not handled in that SEO friendly sh*t ... �-- 
		$messageId = $needle ? $needle : $messageId; // If needle false, use whole message Id.. 
	}	
	
	if(!empty($messageId)) {
		$filename ="$msgdir/$messageId";
		if(file_exists($filename)){
			$fh = fopen($filename, 'r');
			$contents = fread($fh, filesize($filename));
			fclose($fh);
			if(secure_delete($filename)){
				echo "Die Nachricht lautet: <br><br><textarea style='width:400px;height:150px;'>$contents</textarea>";
			}
			else{
				echo "Fehler: Die Nachricht konnte nicht gelöscht werden, also wird sie auch nicht ausgegeben (sonst könnte sie mehrfach abgerufen werden)";
			}

		}
		else {
			
			touchIpForWrongId($ipAddr);

			echo "<div style='position: relative'><img align='center' style='display: flex;'  src='img/warn.png' height='100px'><br />&nbsp;<b>Die Nachricht wurde bereits abgerufen oder du hast einen falschen Link!</b></div><br><br>";
		}

		echo "<br><br><a href='./'>Neue Nachricht eingeben</a>";

	}
	else {
		if(!empty($_REQUEST["action"])){
			$action = $_REQUEST["action"];
			if($action="send"){
				$messageId = generate_uuid();
				if (!is_dir("$msgdir/")) {
				   mkdir($msgdir, 0777, true);
				}
				$filename = "$msgdir/$messageId";
				$fh = fopen($filename, 'w') or die("Can't create file");
				$msg = $_REQUEST["msg"];
				if(is_file($filename)){
					file_put_contents($filename, $msg);
				}
				fclose($fh);
				echo "<b> Das ist dein Link. Rufe ihn <u>nicht</u> selber auf. Die Nachricht zerst&oumlrt sich beim ersten &Ouml;ffnen selbst!</b><br />";
				echo "<input type='text' class='js-copytextarea' value='$path/$messageId' size='50'>&nbsp;&nbsp;";
				echo "<button class='js-textareacopybtn' style='vertical-align:top;'><img src='img/clipboard-copy-512.png' height='20px'></button>&nbsp;&nbsp;";
			        echo "<button class='button-link' href='sms:?body=$path/$messageId' style='vertical-align:top;'><img src='img/sms.png' height='20px'></button>&nbsp&nbsp; ";
			        echo "<button class='button-link' href='mailto://?body=$path/$messageId' style='vertical-align:top;'><img src='img/email.png' height='20px'></button>&nbsp&nbsp; ";
			        echo "<button class='button-link' href='whatsapp://send?text=$path/$messageId' style='vertical-align:top;'><img src='img/Whatsapp-icon.png' height='20px'></button>&nbsp&nbsp; ";
			        echo "<button class='button-link' href='http://t.me/share/url?url=$path/$messageId' style='vertical-align:top;'><img src='img/telegram.png' height='20px'></button>&nbsp&nbsp; ";
			        echo "<button class='button-link' href='fb-messenger://share/?link=$path/$messageId' style='vertical-align:top;'><img src='img/fb.png' height='20px'></button>&nbsp&nbsp; ";
				echo "<br><br><a href='./'>Neue Nachricht erstellen</a></a>";
			}
		}
		else { 
?>

		Bitte gib hier die Nachricht ein, die du gerne versenden m&ouml;chtest.<br>
		<small><i>Im nächsten Schritt erhälst du einen Internetadresse, die du an den Empänger der Nachticht senden kannst.<br> Wenn der Empfänger die Adresse aufruft, wird die Nachricht einmalig angezeigt und dann sofort gelöscht.<br> Dadurch ist gewährleistet, dass niemand au&szlig;er euch die Nachricht lesen kann.<br />
		<b>Der Quellcode ist open source und auf <a href='https://github.com/dajuly20/selfdestuctableMessage/' target='_new'">GitHub <img src='img/git.png' height="20px"></a> verf&uuml;gbar.</b></i></small> 	

		<form method="post">
			<textarea name="msg" style="width:400px;height:150px;"></textarea><br />
			<input type="hidden" name="action" value="enc">
			<input type="submit" value="Link erzeugen">
		</form>
<?php 
		}
	} 
echo "<br>Aktuell ";
echo (shell_exec("ls -la /tmp/msg | wc -l") -3);
echo " ungelesene nachrichten";
?>

	</body>
</html>
