<?php
	//error_reporting(E_ALL); ini_set('display_errors', 1); ini_set('error_reporting', E_ALL);
	//ini_set('error_reporting', E_ALL); 	ini_set('display_errors', 1); 	ini_set('display_startup_errors', 1);
	//error_reporting( E_ERROR ); // Set E_ALL for debuging
	error_reporting(E_ALL); ini_set('display_errors', 1);

	if (!function_exists('mb_strlen')) {
	function mb_substr($str, $pos, $kl,$s=null) {
		return  iconv_substr($str,$pos, $kl,'UTF-8');
	}
	function mb_strlen($str, $s=null) {
		return iconv_strlen($str, 'UTF-8');
	}
}

	$stat = @file_get_contents('/tmp/cached/tmp.txt');
	if (strpos($stat,'.dat') || $stat=='') define("PGST", 'load', true); else define("PGST", 'mem', true);
	$ses = md5('TSMOSCLIENTSid123456789'); session_name('TSMOSCLIENT'); session_id($ses); session_start();
	$path = dirname( __FILE__ ); define("DIR_NAME", $path, true);
	$tpath = str_replace(substr(strrchr($path, '/'), 1),'bigmanTools',$path); require_once($tpath.'/tools.php');
	$mpath = str_replace(substr(strrchr($path, '/'), 1),'',$path);
	//FIXME debug
	$config = array (
			"host" => "192.168.1.7:18090",
			"history" => "ram",
			"ground" => 5,
		  );
	@include (DIR_NAME.'/ts.config.php');
	$TShost = ts_host();
	$ctx = stream_context_create(array('http' => array('timeout' => 1)));
	$ServName = file_get_contents("http://".$TShost."/echo", 0, $ctx);
	$listTsRSS = file_get_contents(DIR_NAME."/listTs.rss");
	$playRSS = file_get_contents(tools_path.'/'. 'play.rss.php');
	define("DIR_MOS", $mpath, true);
	require_once($tpath.'/tools.php');
	$serviceName = SRV_FN;
	define("SRV_VER", "V2.0", true);
	define("SRV_NAME", SRV_MENU." ".SRV_VER, true);
	if( isset( $_REQUEST['debug'] )) {
		print_r(PGST);echo"\r\n";
		print_r(DIR_NAME);echo"\r\n";
		print_r(SRV_NAME);echo"\r\n";
		print_r($mpath);echo"\r\n";
	}

//FIXME
//rss_tsclient_content();
//rss_tsclient_list_content();
//tsView_content();
//function getMosUrl() {
//	return "http://192.168.1.10/";
//}
function getTorrents($hash=null)
{
	$host = "http://".ts_host();
	$link = $host."/torrents";
	if ($hash) $post = '{"action":"get","hash":"'.$hash.'"}';
	else $post = '{"action":"list"}';

	$html = postTorr($link, $post);
	$html = json_decode($html,true);
	return $html;
}

function getViewed($hash){
	$post = '{"action": "list","hash": "'.$hash.'"}';
	$link = "http://".ts_host()."/viewed";
	$rows = json_decode(postTorr($link, $post),true);
	$result = array();
	foreach ($rows as $row){
		$result[] = $row['file_index'];
	}
	return $result;
}

function isFilm($name)
{
	$name = strtolower($name);
	$length = strlen($name);
	$type = substr($name, $length-3, $length);
	if ($type!='avi' && $type!='mkv' && $type!='mov' && $type!='vob' && $type!='mp4' && $type!='asf' && $type!='flv' && $type!='wmv' && $type!='mpg' && $type!='mp2' && $type!='.ts') {
		return false;
	}
	return true;
}

function getKol($data)
{
	$files = $data['TorrServer']['Files'];
	$count = 0;
	foreach ($files as $file){
		if(isFilm($file['path'])){
			$count++;
		}
	}
	return $count;
}

function logger($info)
{
	file_put_contents(__DIR__.'/log.txt', date('Y-m-d H:i:s')." ".$info."\n", FILE_APPEND);
}

function ts_host()
{
	global $config;
	$host = $config['host'];
	//$host = Param($info,'[HOST]', '[/HOST]');
	return $host;
}

function rnd() 
{
	global $config;
	$ret = $config['ground'];
	if ($ret>4) {
		list($usec, $sec) = explode(" ", microtime());
		$ret = floor(($usec*100)/25)+1;
	}
	return $ret;
}

function tsRssIdd_content() {
	if (isset($_SESSION['rssIdd']) ) $rssIdd = $_SESSION['rssIdd']; else $rssIdd = 'UNKNOWN';
	echo $rssIdd;
}

function tsground_content()
{
	$gr = rnd();
	$img = DIR_NAME."/img/ground0$gr.jpg";
	echo $img;
}	

function tsseconds_content()
{
echo time();
}

function idleImage()
{

	$idle = "\r\n";
	$pth = str_replace('tsclient','iptvlist',DIR_NAME);
	$pth = DIR_MOS."iptvlist";
	if (!file_exists("$pth/im1ages/POPUP_LOADING_01.png")) {
		//for ($i=1; $i<9; $i++) $idle =$idle . "<idleImage>".DIR_MOS."core/rss/images/idle0".$i.".png</idleImage>\r\n";
		for ($i=1; $i<9; $i++) $idle =$idle . "<idleImage>".DIR_NAME."/idle/idle".$i.".png</idleImage>\r\n";
	} else {
		for ($i=1; $i<9; $i++) $idle =$idle . "<idleImage>$pth/images/POPUP_LOADING_0$i.png</idleImage>\r\n";
	}
	
	//for ($i=1; $i<21; $i++) { if ($i<10) $ii = "0$i"; else $ii = $i; $idle =$idle . "<idleImage>".DIR_NAME."/img/idle/idle".$ii.".png</idleImage>\r\n"; }
	return $idle;
}

function rss_tsclient_content()
	// List of torrents
{
	global $nav_options;
	global $ServName;
	$_SESSION['rssIdd'] = "TORRENT";
	$html = getTorrents();
	$Lurl = getMosUrl().'?page=rss_tsclient_list';
	$ITEMS = PHP_EOL;
	$i=1;
	// $n is index of the line
	foreach ($html as $n => $torrent) {
		$name 	= $torrent['title'];
		$hash 	= $torrent['hash'];
		$data = @json_decode($torrent['data'], true);
		$len = formatSize($torrent['torrent_size']);
		$thumb = DIR_NAME."/img/folder.png";
	$ITEM = '<item>
        <title>'.getKol($data).' series</title>
        <description><![CDATA['.$name.']]></description>
		<link>'.$Lurl.'&amp;id='.$i.'&amp;name='.urlencode($name).'&amp;hash='.$hash.'</link>
        <media:thumbnail url="'.$thumb.'" />
        <info>'.$name.'</info>
        <category>Torrent</category>
        <time>'.$len.'</time>
    </item>';
		$ITEMS .= $ITEM;
		$i += 1;
	}

	$gr = '0'.rnd();
	global $listTsRSS;
	$rss = $listTsRSS;
	if ($i == 0)  $rss = str_replace('<!-- header -->',
					'<!-- header -->'.PHP_EOL.'<image offsetXPC="36.5" offsetYPC="23" widthPC="32" heightPC="45">'.str_replace('tsclient','bigmanTools',DIR_NAME)."/img/error.png".'</image>'.PHP_EOL,$rss);
	$rss = str_replace('widthPC="35.5"','widthPC="62"',$rss);
	$rss = str_replace("<<IMGGROUND>>",DIR_NAME."/img/ground$gr.jpg",$rss);
	$rss = str_replace("<<IMGCHANEL>>",DIR_NAME."/img/ts.png",$rss);
	$rss = str_replace("<<IMGPTH>>",DIR_NAME."/img/",$rss);
	$rss = str_replace("<<PROGPTH>>",DIR_NAME."/",$rss);
	$rss = str_replace("<<idleImage>>",idleImage(),$rss);
	$rss = str_replace("<<VERPRG>>",SRV_NAME,$rss);
	$rss = str_replace("<<HOST>>","http://".ts_host()." ".$ServName,$rss);
	$rss = str_replace("<<ITEMS>>",$ITEMS,$rss);
	
	$rss = preg_replace ('|viewAreaXPC=".*?"|s', 'viewAreaXPC="'.$nav_options['rss_xpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaYPC=".*?"|s', 'viewAreaYPC="'.$nav_options['rss_ypc'].'"',$rss);
	$rss = preg_replace ('|viewAreaWidthPC=".*?"|s', 'viewAreaWidthPC="'.$nav_options['rss_wpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaHeightPC=".*?"|s', 'viewAreaHeightPC="'.$nav_options['rss_hpc'].'"',$rss);
	
	$rss = str_replace('image offsetXPC="3" offsetYPC="5" widthPC="3.5"','image offsetXPC="1.75" offsetYPC="5" widthPC="4.5"',$rss);
	echo $rss;
}

function rss_tsclient_list_content()
	//List of series in torrent
{
	$_SESSION['rssIdd'] = "LIST";
	$hash = $_REQUEST['hash'];
	global $nav_options;
	$html = getTorrents($hash);
	$Lurl = getMosUrl().'?page=tsclient_play';
	$ITEMS = PHP_EOL;
	$files = json_decode($html['data'], true)['TorrServer']['Files'];
	$viewed = getViewed($hash);
	$i = 0;
	foreach ($files as $file) {
		$name = $file['path'];
		if (!isFilm($name)) continue;
		$i += 1;
		$len = $file['length'];
		if (($len+0)>0) $len = formatSize($len); else $len = 'No info';
		$pic = 'false.png';
		if (in_array($i, $viewed)) $pic = 'view.png';
		if (strpos($name,'/')!==false) {
			$name = '<<>>'.strstr($name, '/');
			$name = str_replace('<<>>/','',$name);
		}
		$name = trim($name);
		$thumb = DIR_NAME."/img/$pic";
		$ITEM = PHP_EOL.'<item>
			<title>'.$name.'</title>
			<description><![CDATA['.$name.']]></description>
			<link>'.$Lurl.'&amp;id='.$i.'&amp;name='.urlencode($name).'&amp;hash='.$hash.'&amp;catalog_id='.$_REQUEST['id'].'</link>
			<media:thumbnail url="'.$thumb.'" />
			<info>'.$name.'</info>
			<category>TorrentList</category>
			<time>'.$len.'</time>
		</item>';
		$ITEMS .= $ITEM;
	}
//if( isset( $_REQUEST['debug'])) {	print_r($ITEMS);}
	//$gr = '0'.rand(1, 4);
	$gr = '0'.rnd();
	global $listTsRSS;
	$rss = $listTsRSS;
	$rss = str_replace('"0:154:236"','"216:134:0"',$rss);
	//$rss = str_replace('widthPC="35.5"','widthPC="65" lines="2"',$rss);
	$title = $_REQUEST['name'];
	if (mb_strlen($title)>40) {
		$rss = str_replace('<text offsetYPC="3.3" heightPC="4" fontSize="14" offsetXPC="7.7"  widthPC="35.5"',
							'<text offsetYPC="1.9" heightPC="8" fontSize="14" offsetXPC="7.7" widthPC="62" lines="2"',$rss);
		$rss = str_replace('<text offsetYPC="3" heightPC="4" fontSize="14" offsetXPC="7.5"  widthPC="35.5"',
							'<text offsetYPC="1.8" heightPC="8" fontSize="14" offsetXPC="7.7" widthPC="62" lines="2"',$rss);
	} else {
		
		$rss = str_replace('widthPC="35.5"', 'widthPC="62"',$rss);
	}
	$rss = str_replace("<<HASH>>",$hash,$rss);
	//$rss = str_replace('getItemInfo("media:thumbnail")',$inf,$rss);
	$rss = str_replace('"TORENT"','"LIST"',$rss);
	$rss = str_replace("<<IMGGROUND>>",DIR_NAME."/img/ground$gr.jpg",$rss);
	//$rss = str_replace("<<IMGCHANEL>>",str_replace('tsclient','bigmanTools',DIR_NAME)."/img/folder.png",$rss);
	$rss = str_replace("<<IMGCHANEL>>",DIR_NAME."/img/folder.png",$rss);
	$rss = str_replace("<<IMGPTH>>",DIR_NAME."/img/",$rss);
	$rss = str_replace("<<PROGPTH>>",DIR_NAME."/",$rss);
	$rss = str_replace("<<idleImage>>",idleImage(),$rss);
	$rss = str_replace("<<VERPRG>>",SRV_NAME,$rss);
	$rss = str_replace("<<HOST>>",$title,$rss);
	$rss = str_replace("<<ITEMS>>",$ITEMS,$rss);
	$rss = str_replace("<<ID>>",$_REQUEST['id'],$rss);
	$rss = preg_replace ('|viewAreaXPC=".*?"|s', 'viewAreaXPC="'.$nav_options['rss_xpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaYPC=".*?"|s', 'viewAreaYPC="'.$nav_options['rss_ypc'].'"',$rss);
	$rss = preg_replace ('|viewAreaWidthPC=".*?"|s', 'viewAreaWidthPC="'.$nav_options['rss_wpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaHeightPC=".*?"|s', 'viewAreaHeightPC="'.$nav_options['rss_hpc'].'"',$rss);
	echo $rss;
}

function tsView_content()
{
	$hash = $_REQUEST['hash'];
	$html = getTorrents($hash);
	$files = json_decode($html['data'],true)['TorrServer']['Files'];
	$viewed = getViewed($hash);
	$IMGS = ''; $i = 1;
	foreach ($files as $file) {
		$pic = 'false.png';
		if (in_array($i, $viewed)) $pic = 'view.png';
		$pic = DIR_NAME."/img/$pic";
		$link = "/play/".$hash."/".$i;
		$img = '<imgs><l>'.$link.'</l><i>'.$pic.'</i></imgs>'.PHP_EOL;
		$IMGS .= $img;
		$i += 1;
	}
	$IMGS = '<inf><imgs><c>'.$i.'</c></imgs></inf>'.PHP_EOL.'<data>'.PHP_EOL.$IMGS.'</data>';
	echo $IMGS;
}
function tsclient_play_content() 
{
	$_SESSION['rssIdd'] = "PLAY";
	$id = $_REQUEST['id'];
	$name = $_REQUEST['name'];
	$hash = $_REQUEST['hash'];
	global $TShost;
	$host = "http://".$TShost;
	$baseUrl	= urlencode($host."/play/".$hash."/".$id);
	// TODO NB: this is necessary, even it is not in use, dont delete this
	$TitleVideo = $name;
	$TitleVideo = substr($TitleVideo, 0, strrpos($TitleVideo, '.'));
	$ThumbVideo = dir_name.'/img/ground01.jpg';
	$mosUrl = getMosUrl();
	//$ProxyVideo = $prx;
	//include(tools_path.'/'. 'play.rss.php' );
	global $playRSS;
	$rss = $playRSS;
	$rss = preg_replace ('|<idleImage>.*?<previewWindow|s', idleImage()."\r\n<previewWindow",$rss);
	$rss = str_replace('idleImageWidthPC="4"','idleImageWidthPC="5"',$rss);
	$rss = str_replace('idleImageHeightPC="4"','idleImageHeightPC="6"',$rss);
	$rss = str_replace('idleImageXPC="89.5"','idleImageXPC="87.5"',$rss);
	$rss = str_replace('idleImageYPC="89.5"','idleImageYPC="2"',$rss);

	$sсript  = "\r\n";
	$sсript .= "</onEnter>\r\n\r\n<Tstatus>\r\n";
	$sсript .= '	urlS = mosUrl + "?page=plr_tsstatus&amp;hash="+"'.$hash.'"; '."\r\n";
	$sсript .= '	executeScript("hidePopup"); stateMid ="Status Torrent"; stateLeft = ""; stateRight = ""; '; #stateMid = getURL(urlS);
	$sсript .= 'popupTimeout = 10; popupHidePos = 0; barStatus = "status"; redrawDisplay("widget");'."\r\n";
	$sсript .= "</Tstatus>\r\n";
	$rss = str_replace('</onEnter>',$sсript,$rss);

	$sсript  = 'executeScript("NextVideo");'."\r\n";
	$sсript .= '		} else if ( key == "video_ffwd" ) { executeScript("Tstatus"); '."\r\n";

	$rss = str_replace('executeScript("NextVideo");',$sсript,$rss);
	$sсript  = '<foregroundColor> <script> clr = "100:115:130"; if (barStatus == "status" ) clr = "50:200:255"; clr; </script> </foregroundColor>'."\r\n";
	$sсript .= '		<fontSize><script> font = 12; if ( barStatus == "status" ) font = 13; font</script></fontSize>'."\r\n";
	$sсript .= '		<offsetXPC><script> XPC = 22; if ( barStatus == "status" ) XPC = 9; XPC;</script></offsetXPC>'."\r\n";
	$sсript .= '		<widthPC><script> wPC = 57 - popupHidePos; if ( barStatus == "status" ) wPC = 80; wPC;';
	$rss = str_replace('<widthPC><script>57 - popupHidePos;',$sсript,$rss);

	$rss = str_replace('|| prgbarStatus == "buffering"','|| prgbarStatus == "buffering" || barStatus == "status" ',$rss);
	$rss = str_replace('stateMid;','if ( barStatus == "status" ) stateMid = getURL(urlS); stateMid;',$rss);
	$rss = str_replace('<hidePopup>','<hidePopup>'."\r\n".'	barStatus = "offstatus";',$rss);
	$rss = str_replace('key == "display"))
		{','key == "display"))
		{'."\r\n".'			if (barStatus == "status" ) executeScript("hidePopup");',$rss);

	eval('?>' . $rss);
	
}

function tsclient_get_content()
{
	if( isset($_REQUEST['id'])) $id = $_REQUEST['id']; else $id = '';
	if (strpos($id,'%2F')) $id = urldecode($id);
	$url = parse_url($id);
	$path = $url['path'];
	$Npath = urlencode($path);
	$Npath = str_replace('%2F','/',$Npath);
	$id = str_replace($path,$Npath,$id);
if( isset( $_REQUEST['debug'])) {	print_r($path); echo "\r\n";}
	echo $id.PHP_EOL.'0'.PHP_EOL;
}


function plr_tsstatus_content()
{
	$html = getTorrents($_REQUEST['hash']);
	if (!isset($html['total_peers'])) { echo "НЕТ ДАННЫХ !!!"; return; }
	$TotalPeers = @$html['total_peers'];
	$ActivePeers = @$html['active_peers'];
	$ConnectedSeeders = @$html['connected_seeders'];
	$DownloadSpeed = @$html['download_speed'];
	$DownloadSpeed = formatSize($DownloadSpeed);
	$PreloadSize = @$html['preloaded_bytes'];
	$PreloadSize = formatSize($PreloadSize);
	$ret = "Peers:[ $ConnectedSeeders ] $ActivePeers / $TotalPeers ■ Preload( $PreloadSize ) ■ SPEED( $DownloadSpeed )";
	echo $ret;
}

function postTorr($url,$QUERY = null) {
	$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL.
				'X-Requested-With: XMLHttpRequest',
				'content' => $QUERY,
			),
		));
	return file_get_contents($url, false, $context);
}

function tsinfo_content() 
{
global $nav_options;
if( isset( $_REQUEST['debug'] )) print_r($nav_options);echo"\r\n";
if( isset( $_REQUEST['debug'] )) print_r($_SESSION);echo"\r\n";
//if( isset( $_REQUEST['debug'] )) print_r($_ENV);echo"\r\n";

}

function formatSize($bytes) 
{
		$bytes = $bytes+0;
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}

		elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}

		elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}

		elseif ($bytes > 1) {
			$bytes = round($bytes, 2) . ' байты';
		}

		elseif ($bytes == 1) {
			$bytes = $bytes . ' байт';
		}

		else {
			$bytes = '0 байтов';
		}

		return $bytes;
}

function tsclient_msg_content($msg = null, $image = null)
{
		$_SESSION['rssIdd'] = "MSG";
		if (empty($msg)) $msg = 'Видео недоступно или удалено!!!';
		if( isset( $_REQUEST['txt'])) $msg = $_REQUEST['txt'];
		if( isset( $_REQUEST['img'])) $image = $_REQUEST['img'];
		include(tools_path.'/'. 'msg.php' );
		$view = new rssMsgView;
		if (!empty($image)) $view->currentImage = $image;
		$view->currentMsg = $msg;
		$view->showRss();
}

function tsclient_Nmsg_content($msg = null, $image = null, $name = "Сообщение")
{
	$_SESSION['rssIdd'] = "MSG";
	if (empty($msg)) $msg = 'Ошибка доступа';
	if (isset($_REQUEST['txt'])) $msg = $_REQUEST['txt'];
	if (isset($_REQUEST['img'])) $image = $_REQUEST['img'];
	$pth = tools_path . '/' . 'msg.php';
	$rssmsg = file_get_contents($pth);
	$rssmsg = str_replace('>Сообщение</', '>' . $name . '</', $rssmsg);
	$rssmsg = str_replace('backgroundColor="41:41:41"', 'backgroundColor="40:50:60"', $rssmsg);

	eval('?>' . $rssmsg);
}

function tsbackup_content()
{

	if( isset( $_REQUEST['mg'])) $mg = $_REQUEST['mg'];
	if( isset( $_REQUEST['nm'])) $nm = $_REQUEST['nm'];
	if (!empty($nm) && !empty($mg)) {
		$pth = dir_name.'/backup.php';
		@include ($pth);
		$backup[$mg] = $nm;
		@file_put_contents( $pth, '<?php $backup = '.var_export( $backup, true ).'; ?>' );
	}

}


function tsclient_gpio_content()
{
	$output = shell_exec("gpio 121 0");
	$output = shell_exec("gpio 121 1");
}

?>
