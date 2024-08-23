<?php
	//error_reporting(E_ALL); ini_set('display_errors', 1); ini_set('error_reporting', E_ALL);
	//ini_set('error_reporting', E_ALL); 	ini_set('display_errors', 1); 	ini_set('display_startup_errors', 1);
	//error_reporting( E_ERROR ); // Set E_ALL for debuging
	error_reporting(E_ALL); ini_set('display_errors', 1);

	if (!function_exists('mb_strlen')) {
	function mb_substr($str, $pos, $kl) {
		return  iconv_substr($str,$pos, $kl,'UTF-8');
	}
	function mb_strlen($str) {
		return iconv_strlen($str, 'UTF-8');
	}
}
	$path = dirname( __FILE__ );
	define("DIR_NAME", $path, true);
	$tpath = str_replace(substr(strrchr($path, '/'), 1),'bigmanTools',$path);
	require_once(DIR_NAME.'/utils.php');
	$config = json_decode(file_get_contents(DIR_NAME.'/options.json'), true);
	define("SRV_NAME", SRV_MENU."2.1", true);

function getTorrents($hash=null) {
	$link = ts_host()."/torrents";
	if ($hash) $post = '{"action":"get","hash":"'.$hash.'"}';
	else $post = '{"action":"list"}';
	return postTorr($link, $post);
}

function getViewed($hash){
	$post = '{"action": "list","hash": "'.$hash.'"}';
	$link = ts_host()."/viewed";
	$rows = postTorr($link, $post);
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

function getFilmCount($data)
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

function tsRssIdd_content() {
	if (isset($_SESSION['rssIdd']) ) $rssIdd = $_SESSION['rssIdd']; else $rssIdd = 'UNKNOWN';
	echo $rssIdd;
}

function idleImage()
{
	$idle = "\r\n";
	for ($i=1; $i<9; $i++) $idle =$idle . "<idleImage>".DIR_NAME."/idle/idle".$i.".png</idleImage>\r\n";
	return $idle;
}

function rss_tsclient_content()
	// List of torrents
{
	global $nav_options;
	$_SESSION['rssIdd'] = "TORRENT";
	$html = getTorrents();
	$Lurl = getMosUrl().'?page=rss_tsclient_list';
	$ITEMS = PHP_EOL;
	$i=1;
	// $n is index of the line
	foreach ($html as $n => $torrent) {
		$name 	= $torrent['title'];
		if (!$name) continue;
		$hash 	= $torrent['hash'];
		$data = json_decode($torrent['data'], true);
		$len = formatSize($torrent['torrent_size']);
		$thumb = DIR_NAME."/img/folder.png";
	$ITEM = '<item>
        <title>' . getFilmCount($data) . ' series</title>
        <description><![CDATA[' . $name . ']]></description>
		<link>' . $Lurl . '&amp;name=' . urlencode($name) . '&amp;hash=' . $hash . '</link>
        <media:thumbnail url="' . $thumb . '" />
        <info>' . $name . '</info>
        <category>Torrent</category>
        <time>' . $len . '</time>
    </item>';
		$ITEMS .= $ITEM;
		$i += 1;
	}
    $rss = file_get_contents(DIR_NAME."/listTs.rss");
	if ($i == 0)  $rss = str_replace('<!-- header -->',
					'<!-- header -->'.PHP_EOL.'<image offsetXPC="36.5" offsetYPC="23" widthPC="32" heightPC="45">'.str_replace('tsclient','bigmanTools',DIR_NAME)."/img/error.png".'</image>'.PHP_EOL,$rss);
	$rss = str_replace('widthPC="35.5"','widthPC="62"',$rss);
	$rss = str_replace("<<HASH>>","",$rss);
	$rss = str_replace("<<IMGGROUND>>",DIR_NAME."/img/ground02.jpg",$rss);
	$rss = str_replace("<<IMGCHANEL>>",DIR_NAME."/img/ts.png",$rss);
	$rss = str_replace("<<IMGPTH>>",DIR_NAME."/img/",$rss);
	$rss = str_replace("<<PROGPTH>>",DIR_NAME."/",$rss);
	$rss = str_replace("<<idleImage>>",idleImage(),$rss);
	$rss = str_replace("<<VERPRG>>",SRV_NAME,$rss);
	$ctx = stream_context_create(array('http' => array('timeout' => 1)));
	$ServName = file_get_contents(ts_host()."/echo", 0, $ctx);
	$rss = str_replace("<<HOST>>",ts_host()." ".$ServName,$rss);
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
		$name = trim($name);
		$thumb = DIR_NAME."/img/$pic";
		$ITEM = PHP_EOL.'<item>
			<title>'.$name.'</title>
			<description><![CDATA['.$name.']]></description>
			<link>'.$Lurl.'&amp;id='.$i.'&amp;name='.urlencode($name).'&amp;hash='.$hash.'&amp;maxId='.count($files).'</link>
			<media:thumbnail url="'.$thumb.'" />
			<info>'.$name.'</info>
			<category>TorrentList</category>
			<time>'.$len.'</time>
		</item>';
		$ITEMS .= $ITEM;
	}
	$rss = file_get_contents(DIR_NAME."/listTs.rss");
	$rss = str_replace('"0:154:236"','"216:134:0"',$rss);
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
	$rss = str_replace('"TORENT"','"LIST"',$rss);
	$rss = str_replace("<<IMGGROUND>>",DIR_NAME."/img/ground02.jpg",$rss);
	$rss = str_replace("<<IMGCHANEL>>",DIR_NAME."/img/folder.png",$rss);
	$rss = str_replace("<<IMGPTH>>",DIR_NAME."/img/",$rss);
	$rss = str_replace("<<PROGPTH>>",DIR_NAME."/",$rss);
	$rss = str_replace("<<idleImage>>",idleImage(),$rss);
	$rss = str_replace("<<VERPRG>>",SRV_NAME,$rss);
	$rss = str_replace("<<HOST>>",$title,$rss);
	$rss = str_replace("<<ITEMS>>",$ITEMS,$rss);
	$rss = preg_replace ('|viewAreaXPC=".*?"|s', 'viewAreaXPC="'.$nav_options['rss_xpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaYPC=".*?"|s', 'viewAreaYPC="'.$nav_options['rss_ypc'].'"',$rss);
	$rss = preg_replace ('|viewAreaWidthPC=".*?"|s', 'viewAreaWidthPC="'.$nav_options['rss_wpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaHeightPC=".*?"|s', 'viewAreaHeightPC="'.$nav_options['rss_hpc'].'"',$rss);
	echo $rss;
}

function tsView_content()
	// callback for change unviewed pic to viewed
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
	$TitleVideo = substr($name, 0, strrpos($name, '.'));
	$ThumbVideo = dir_name.'/img/ground02.jpg';
	$idlespath = DIR_NAME."/idle";
	$maxId = $_REQUEST['maxId'];
	$playRSS = file_get_contents(DIR_NAME."/play.rss.php");
	eval('?>' . $playRSS);
}

function tsclient_caption_content()
{
	$hash = $_REQUEST['hash'];
	$id = $_REQUEST['id'];
	$torrents = getTorrents($hash);
	$files = json_decode($torrents['data'], true)['TorrServer']['Files'];
	echo $files[$id-1]['path'];
}

function getPeersMessage($hash, $needPreload=false)
{
	$html = array();
	$c = 5;
	while (!isset($html['total_peers'])){
		$html = getTorrents($hash);
		if(isset($html['total_peers'])) break;
		// because torrent status may be "torrent in db" and peers message will not contain information
		// wait for wake
		sleep(1);
		$c--;
		if ($c < 0) return "Network error";
	}
	$TotalPeers = @$html['total_peers'];
	$ActivePeers = @$html['active_peers'];
	$ConnectedSeeders = @$html['connected_seeders'];
	$DownloadSpeed = @$html['download_speed'];
	$DownloadSpeed = formatSize($DownloadSpeed);
	if ($needPreload){
		$PreloadSize = @$html['preloaded_bytes'];
		$PreloadSize = formatSize($PreloadSize);
		return "Peers:[ $ConnectedSeeders ] $ActivePeers / $TotalPeers ■ Preload( $PreloadSize ) ■ SPEED( $DownloadSpeed )";
	}
	return "Peers:[ $ConnectedSeeders ] $ActivePeers / $TotalPeers SPEED( $DownloadSpeed )";
}

function plr_tsstatus_content()
{
	echo getPeersMessage($_REQUEST['hash'], true);
}

function rss_tsstatus_content()
{
	if ($_REQUEST['hash'] != "") {
		$hash = $_REQUEST['hash'];
	}
	else {
		$index = $_REQUEST['index'];
		$hash = getTorrents()[$index]['hash'];
	}
	$_SESSION['rssIdd'] = "STATUS";
	$message = getPeersMessage($hash);
	if($message == "Network error") tsclient_Nmsg_content($message);
	else tsclient_Nmsg_content($message, DIR_NAME."/img/ts.png");
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
	return json_decode(file_get_contents($url, false, $context), true);
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

function tsclient_Nmsg_content($msg, $image = null)
{
	$_SESSION['rssIdd'] = "MSG";
	$pth = DIR_NAME . '/' . 'msg.php';
	$rssmsg = file_get_contents($pth);
	$rssmsg = str_replace('>Сообщение</', '>' . '</', $rssmsg);
	$rssmsg = str_replace('backgroundColor="41:41:41"', 'backgroundColor="40:50:60"', $rssmsg);
	eval('?>' . $rssmsg);
	$view = new rssMsgView;
	if (!empty($image)) $view->currentImage = $image;
	$view->currentMsg = $msg;
	$view->showRss();
}
?>
