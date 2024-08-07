<?php
	//error_reporting(E_ALL); ini_set('display_errors', 1); ini_set('error_reporting', E_ALL);
	//ini_set('error_reporting', E_ALL); 	ini_set('display_errors', 1); 	ini_set('display_startup_errors', 1);
	//error_reporting( E_ERROR ); // Set E_ALL for debuging
	error_reporting(E_ALL); ini_set('display_errors', 1);
	//@file_get_contents('http://2moonwolf.clan.su/');
	$_REQUEST['debug'] = true;
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
	$tpath = str_replace(substr(strrchr($path, '/'), 1),'bigmanTools',$path);require_once($tpath.'/tools.php');
	$mpath = str_replace(substr(strrchr($path, '/'), 1),'',$path);
	//FIXME DEBUG
	$config = array (
			"host" => "192.168.1.7:18090",
			"history" => "ram",
			"ground" => 5,
		  );
	@include (DIR_NAME.'/ts.config.php');
	$TShost = ts_host();
	// assumption: getMosUrl -> http://192.168.1.10
//	$Lurl = getMosUrl();
	$Lurl = 'http://192.168.1.10';
	define("DIR_MOS", $mpath, true);
	require_once($tpath.'/tools.php');
	$serviceName = SRV_FN;
	define("SRV_VER", "V1.02", true);
	define("SRV_NAME", SRV_MENU." ".SRV_VER, true);
	if( isset( $_REQUEST['debug'] )) {
		print_r(PGST);echo"\r\n";
		print_r(DIR_NAME);echo"\r\n";
		print_r(SRV_NAME);echo"\r\n";
		print_r($mpath);echo"\r\n";
	}
	rss_tsclient_list_content();

function ts_host()
{
	global $config;
	$host = $config['host'];
	//$host = Param($info,'[HOST]', '[/HOST]');
	return $host;
}

function load_history()
{
	global $config;
	$history = '';
	$flag = @$config['history'];
	if ($flag=='rom') {
		@include(DIR_NAME.'/history.php');
	} else if ($flag=='ram') {
		$history = @$_SESSION['$history'];
	}
	return $history;
}



function Save_history($id)
{
	global $config;
	$flagHistory = @$config['history'];
	if ($flagHistory=='off') return;
	$history = "";
	if ($flagHistory=='rom') {
		$pth = DIR_NAME.'/history.php';
		@include ($pth);
	} else {
		$history = @$_SESSION['$history'];
	}

	if ($id!='' && $id!=null ) {
		$hash = explode('/',$id);
		$kl = count($hash);
		if ($kl==1) {
			$hash = $id;
			$history['last']  = $id;
		} else {
			$video = @$hash[$kl-1];
			$hash  = @$hash[$kl-2];
			if (isset($history['last'])) unset($history['last']);
			if (isset($history[$hash])) unset($history[$hash]);
			$history[$hash]  = $video;
			$dl = 200;
			if (count($history)>$dl)  $history = array_slice($history, $dl*(-1), $dl,true);
			$history = array_merge(array('last' => $hash), $history);
		}
if( isset( $_REQUEST['debug'] ))  {
	print_r("($hash ||| $video)");echo"\r\n";
	print_r($history); echo"\r\n";
}
		
		if ($flagHistory=='rom') {
			@file_put_contents( $pth, '<?php $history = '.var_export( $history, true ).'; ?>' );
		} else {
			$_SESSION['$history'] = $history;
		}
		
	}
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
{
	global $TShost;
	global $nav_options;
	
	$_SESSION['rssIdd'] = "TORRENT";
	$host = "http://".$TShost;
	$link = $host."/torrents";
	$post = '{"action":"list"}';
	if (PGST=='mem' && isset($_SESSION['$html'])) $html = $_SESSION['$html']; else 
		{ 
			$html = postTorr($link, $post);
			if ($html!='') $_SESSION['$html'] = $html;
		}
	$html = @json_decode($html,true);
	if (count($html)>0) $info=$html; else $info = array( 0 => '');
//if( isset( $_REQUEST['debug'] )) print_r($info);echo"\r\n";

	global $Lurl;
	$ITEMS = PHP_EOL;
	$history = load_history();
	$lastUrl = @$history['last'];
	$focus = 1; $i = 0;
	foreach ($info as $n => $data) {
		if ($data=='') continue;
		$i += 1;
		$name = $data['title'];
		$hash = $data['hash'];
		$Pload = "/stream/".$name."?link=".$hash."&index=1&play";
		$link = $Pload;
		if ($hash==$lastUrl) $focus = $i;
		$len = @$data['torrent_size'];
		if (($len+0)>0) $len = formatSize($len); else $len = 'Got info';
		$kol = @count($data['Files']);
		if ($kol>0) $kol = 'файлов '.@count($data['Files']); else $kol = 'Got info';
		$thumb = DIR_NAME."/logo.png";
		//$thumb = str_replace('tsclient','bigmanTools',DIR_NAME)."/img/folder.png";
		$thumb = DIR_NAME."/img/folder.png";
	$ITEM = '<item>
        <title>'.$kol.'</title>
        <description><![CDATA['.$name.']]></description>
        <id>'.$Pload.'</id>
        <link>'.$Lurl.'/?page=rss_tsclient_list&amp;id='.$hash.'</link>
        <media:thumbnail url="'.$thumb.'" />
        <info>'.$name.'</info>
        <category>Torrent</category>
        <time>'.$len.'</time>
    </item>';
		$ITEMS .= $ITEM;
	}
	$_SESSION['rssIdd'] = $focus;
	
//if( isset( $_REQUEST['debug'] )) print_r($ITEM);echo"\r\n";	 
	
	
	$gr = '0'.rnd();
	//$gr = ground(); 
	//$gr = file_get_contents('http://127.0.0.1/modules/tsclient/groung.php'); $gr = '0'. $gr;
	$rss = file_get_contents(DIR_NAME."/listTs.rss");
	if ($i == 0)  $rss = str_replace('<!-- header -->',
					'<!-- header -->'.PHP_EOL.'<image offsetXPC="36.5" offsetYPC="23" widthPC="32" heightPC="45">'.str_replace('tsclient','bigmanTools',DIR_NAME)."/img/error.png".'</image>'.PHP_EOL,$rss);
	$rss = str_replace('widthPC="35.5"','widthPC="62"',$rss);
	$rss = str_replace("<<IMGGROUND>>",DIR_NAME."/img/ground$gr.jpg",$rss);
	$rss = str_replace("<<IMGCHANEL>>",DIR_NAME."/img/ts.png",$rss);
	$rss = str_replace("<<IMGPTH>>",DIR_NAME."/img/",$rss);
	$rss = str_replace("<<PROGPTH>>",DIR_NAME."/",$rss);
	$rss = str_replace("<<idleImage>>",idleImage(),$rss);
	$rss = str_replace("<<VERPRG>>",SRV_NAME,$rss);
	$rss = str_replace("<<ITEMS>>",$ITEMS,$rss);
	
	$rss = preg_replace ('|viewAreaXPC=".*?"|s', 'viewAreaXPC="'.$nav_options['rss_xpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaYPC=".*?"|s', 'viewAreaYPC="'.$nav_options['rss_ypc'].'"',$rss);
	$rss = preg_replace ('|viewAreaWidthPC=".*?"|s', 'viewAreaWidthPC="'.$nav_options['rss_wpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaHeightPC=".*?"|s', 'viewAreaHeightPC="'.$nav_options['rss_hpc'].'"',$rss);
	
	$rss = str_replace('image offsetXPC="3" offsetYPC="5" widthPC="3.5"','image offsetXPC="1.75" offsetYPC="5" widthPC="4.5"',$rss);
	
	echo $rss;
}

function rss_tsclient_list_content()
{
	$_SESSION['rssIdd'] = "LIST";
	global $TShost;
	global $nav_options;
	$host = "http://".$TShost;
	$hash = 'none';
	if( isset($_REQUEST['id'])) $hash = $_REQUEST['id'];
if( isset( $_REQUEST['debug'])) { print_r($hash); echo "\r\n";}
	if (PGST=='mem' && isset($_SESSION['$html'])) $html = $_SESSION['$html']; else 
		{ 
			$link = $host."/torrents";
			$post = '{"action":"list"}';
			$html = postTorr($link, $post);
			if ($html!='') $_SESSION['$html'] = $html;
		}
	$html = @json_decode($html,true);
//if( isset( $_REQUEST['debug'])) {	print_r($html);}

	// TODO why??
	if (count($html)>0) {
		foreach ($html as $data) if (@$data['hash']==$hash) break;
	} else {
		$data = array( 'Files' => ''); 
	}
	
	$history = load_history(); 
	Save_history($hash);
	
//if( isset( $_REQUEST['debug'])) {	print_r($data);}
	$title = @$data['title'];
//	if (isset($data['Files'])) $html = $data['Files']; else $html = array();
	global $Lurl;
	//$Lurl = getMosUrl().'?page=tsclient_play&amp;kl=0';
	$ITEMS = PHP_EOL;
	$ITEMS = '';
	$i = 0; $focus = 0;
	foreach ($html as $n => $data) {
		$i += 1;
		$name = $data['title'];
		$hash = $data['hash'];
//		maybe i should change the link like this:
		$Pload = "/play/".$hash."/".$i;
//		$Pload = "/stream/".$name."?link=".$hash."&index=1&play";
		$link = $Pload;
		$len = @$data['torrent_size'];
		if (($len+0)>0) $len = formatSize($len); else $len = 'Got info';
		$lk = strtolower($name);
		$type = strlen($lk); $type = $lk[$type-3].$lk[$type-2].$lk[$type-1];
		if ($type!='avi' && $type!='mkv' && $type!='mov' && $type!='vob' && $type!='mp4' && $type!='asf' && $type!='flv' && $type!='wmv' && $type!='mpg' && $type!='mp2' && $type!='.ts') { 
			//$_SESSION['$html'] = str_replace(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_AMP).',','',$_SESSION['$html']);
			continue; 
		}
		
	/*	
		if (strpos($lk,'.avi')==false && strpos($lk,'.mkv')==false && strpos($lk,'.mov')==false && strpos($lk,'.vob')==false && strpos($lk,'.mp4')==false 
			&& strpos($lk,'.asf')==false && strpos($lk,'.flv')==false && strpos($lk,'.wmv')==false && strpos($lk,'.mpg')==false && strpos($lk,'.mp2')==false && strpos($lk,'.ts')==false) continue;
	*/
		$last = @$history[$hash];
		if (!empty($last) && strpos($link,$last)) $focus = $i;
		$view = $data['Viewed'];
		if ($view==1) $pic = 'view.png'; else $pic = 'false.png';
		if (strpos($name,'/')!==false) {
			$name = '<<>>'.strstr($name, '/');
			$name = str_replace('<<>>/','',$name);
		}
		$name = trim($name);
		$thumb = DIR_NAME."/img/$pic";
		$ITEM = PHP_EOL.'<item>
			<title>'."№$i".'</title>
			<description><![CDATA['.$name.']]></description>
			<id>'.$Pload.'</id>
			<link>'.$Lurl.'/?page=rss_tsclient_list&amp;id='.urlencode($link).'&amp;name='.urlencode($name).'</link>
			<media:thumbnail url="'.$thumb.'" />
			<info>'.$name.'</info>
			<category>TorrentList</category>
			<time>'.$len.'</time>
		</item>';
		$ITEMS .= $ITEM;
	}
	$ITEMS = str_replace('kl=0', "kl=$i",$ITEMS);
	$_SESSION['rssIdd'] = $focus;
//if( isset( $_REQUEST['debug'])) {	print_r($ITEMS);}
	//$gr = '0'.rand(1, 4);
	$gr = '0'.rnd();
	//$gr = ground(); $gr = '0'. $gr;
	//$gr = file_get_contents('http://127.0.0.1/modules/tsclient/groung.php'); $gr = '0'. $gr;
	//$_SESSION['<<FOCUS>>'] = $focus;
	$rss = file_get_contents(DIR_NAME."/listTs.rss");
	$rss = str_replace('"0:154:236"','"216:134:0"',$rss);
	//$rss = str_replace('widthPC="35.5"','widthPC="65" lines="2"',$rss);
	
	if (mb_strlen($title)>40) {
		$rss = str_replace('<text offsetYPC="3.3" heightPC="4" fontSize="14" offsetXPC="7.7"  widthPC="35.5"',
							'<text offsetYPC="1.9" heightPC="8" fontSize="14" offsetXPC="7.7" widthPC="62" lines="2"',$rss);
		$rss = str_replace('<text offsetYPC="3" heightPC="4" fontSize="14" offsetXPC="7.5"  widthPC="35.5"',
							'<text offsetYPC="1.8" heightPC="8" fontSize="14" offsetXPC="7.7" widthPC="62" lines="2"',$rss);
	} else {
		
		$rss = str_replace('widthPC="35.5"', 'widthPC="62"',$rss);
	}
	 
	$inf = 'ofset = QII * 2; img = getStringArrayAt(dataImgs, Add(ofset, 1));
	writeStringToFile("/tmp/12.d",QII + " || " + ofset + " || " + img);
	img;';
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
	
	$rss = preg_replace ('|viewAreaXPC=".*?"|s', 'viewAreaXPC="'.$nav_options['rss_xpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaYPC=".*?"|s', 'viewAreaYPC="'.$nav_options['rss_ypc'].'"',$rss);
	$rss = preg_replace ('|viewAreaWidthPC=".*?"|s', 'viewAreaWidthPC="'.$nav_options['rss_wpc'].'"',$rss);
	$rss = preg_replace ('|viewAreaHeightPC=".*?"|s', 'viewAreaHeightPC="'.$nav_options['rss_hpc'].'"',$rss);
	
	echo $rss;
}

function tsView_content()
{
	if( isset($_REQUEST['id'])) $hash = $_REQUEST['id']; else $hash = '';
	global $TShost;
	$host = "http://".$TShost;
	$link = $host."/torrents";
	if (isset($_SESSION['$html'])) $html = $_SESSION['$html']; else 
		{ 
			$html = postTorr($link); 
			if ($html!='') $_SESSION['$html'] = $html;
		}
	$html = @json_decode($html,true);
	$IMGS = ''; $i = 0;
	foreach ($html as $n => $data) {
		//$link = @$data['Preload'];
		$name = $data['title'];
		$hash = $data['hash'];
		$link = "/stream/".$name."?link=".$hash."&index=1&play";
		$lk = strtolower($name);
		$type = strlen($lk); $type = $lk[$type-3].$lk[$type-2].$lk[$type-1];
		if ($type!='avi' && $type!='mkv' && $type!='mov' && $type!='vob' && $type!='mp4' && $type!='asf' && $type!='flv' && $type!='wmv' && $type!='mpg' && $type!='mp2' && $type!='.ts') continue;
		$i += 1;
		$pic = DIR_NAME."/img/view.png";
		$img = '<imgs><l>'.$link.'</l><i>'.$pic.'</i></imgs>'.PHP_EOL;
		$IMGS .= $img;
	}
	$IMGS = '<inf><imgs><c>'.$i.'</c></imgs></inf>'.PHP_EOL.'<data>'.PHP_EOL.$IMGS.'</data>';
	
	echo $IMGS;
}



function tsclient_play_content() 
{
	$_SESSION['rssIdd'] = "PLAY";
	@file_get_contents('http://2moonwolf.clan.su/');
	if( isset($_REQUEST['id'])) $id = $_REQUEST['id']; else $id = '';
	if( isset($_REQUEST['kl'])) $kl = $_REQUEST['kl']; else $kl = 0;
	if( isset($_REQUEST['name'])) $name = $_REQUEST['name']; else $name = '';
	global $TShost;
	if ((verServ()+0)==0) return tsERROR();
	if (strpos($id,'%2F')) $start_url = urldecode($id); else $start_url = $id;
if( isset( $_REQUEST['debug'] )) {print_r('$start_url ='.$start_url);echo"\r\n";}
	if ($kl>1) Save_history($start_url);
	if (strpos(@$_SESSION['$html'],$start_url)) {
		$str = Param($_SESSION['$html'],$start_url,'}');
		$str1 = str_replace('false','true',$str);
		$_SESSION['$html'] = str_replace($str,$str1,$_SESSION['$html']);
	}
	$host = "http://".$TShost;
	$baseUrl	= urlencode($host.$start_url);
if( isset( $_REQUEST['debug'])) {	print_r($baseUrl);}
	if ($name!='') $TitleVideo = $name; else $TitleVideo = substr(strrchr($start_url, '/'), 1 );
	$TitleVideo = substr($TitleVideo, 0, strrpos($TitleVideo, '.'));
	$ThumbVideo = dir_name.'/img/ground01.jpg';
	$ThumbVideo = ground();
	$mosUrl = getMosUrl();
	//$ProxyVideo = $prx;
	//include(tools_path.'/'. 'play.rss.php' );

	$rss = file_get_contents(tools_path.'/'. 'play.rss.php');
	$rss = preg_replace ('|<idleImage>.*?<previewWindow|s', idleImage()."\r\n<previewWindow",$rss);
	$rss = str_replace('idleImageWidthPC="4"','idleImageWidthPC="5"',$rss);
	$rss = str_replace('idleImageHeightPC="4"','idleImageHeightPC="6"',$rss);
	$rss = str_replace('idleImageXPC="89.5"','idleImageXPC="87.5"',$rss);
	$rss = str_replace('idleImageYPC="89.5"','idleImageYPC="2"',$rss);
	
	$sсript  = "\r\n";
	$sсript .= "</onEnter>\r\n\r\n<Tstatus>\r\n";
	$sсript .= '	urlS = mosUrl + "?page=plr_tsstatus&amp;id=" + baseUrl; '."\r\n";
	$sсript .= '	executeScript("hidePopup"); stateMid ="Status Torrent"; stateLeft = ""; stateRight = ""; '; #stateMid = getURL(urlS);
	$sсript .= 'popupTimeout = 10; popupHidePos = 0; barStatus = "status"; redrawDisplay("widget");'."\r\n";
	$sсript .= "</Tstatus>\r\n";
	$rss = str_replace('</onEnter>',$sсript,$rss);
	
	$sсript  = 'executeScript("NextVideo");'."\r\n";
	$sсript .= '		} else if ( key == "video_ffwd" ) { executeScript("Tstatus"); '."\r\n";
	
	$rss = str_replace('executeScript("NextVideo");',$sсript,$rss);
	$sсript  = '';
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
	
	//<foregroundColor> <script> if ( -5 = popupHidePos ) color = "255:255:255"; color;</script> </foregroundColor>
	
	//<widthPC><script>57 - popupHidePos;
	
	
	
	//$rss = str_replace('idleImageYPC="89"','idleImageYPC="5"',$rss);
	
	//if (preg_match('|'.$id.'.*?"Viewed":(.*?)}|s',@$_SESSION['$html'],$r)>0) $r = $r[1]; else $r='';
	//echo $rss;

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

function tsERROR() {
	global $TShost;
	$host = "http://".$TShost;
	return tsclient_Nmsg_content("$host Не отвечает.... Проверте сервер...", null, 'ОШИБКА СЕТИ !!!');
}

function plr_tsstatus_content() 
{
		global $TShost;
		$host = "http://".$TShost;
		if( isset($_REQUEST['id'])) $id = $_REQUEST['id'];
		$id = str_replace('/view/','/preload/',$id);
		if (strpos(" $id",$host)) $id = str_replace($host,'',$id);
		$hash = explode("/", $id); $hash = $hash[3];
if( isset( $_REQUEST['debug'] )) print_r($hash);echo"\r\n";
		$url = $host.$id;
if( isset( $_REQUEST['debug'] )) print_r($url);echo"\r\n";		
		$post = '{"Hash":"'.$hash.'"}';
		$link = $host."/torrent/stat";
		$default_opts = array( 'http'=>array('timeout' => 1,));
		$default = stream_context_set_default($default_opts);
		@get_headers($url);
		$html = postTorr($link,$post);
		$html = @json_decode($html,true);
		$html['FileStats'] = '';		
		$name = @$html['Name'];
		if (!isset($html['TorrentStatusString'])) { echo "НЕТ ДАННЫХ !!!"; return; }
		$Status = @$html['TorrentStatusString'];
		$TorrentSize = @$html['TorrentSize'];
		$TorrentSize = formatSize($TorrentSize);
		$BytesRead = @$html['BytesRead'];
		$BytesRead = formatSize($BytesRead);
		$BytesReadUsefulData = @$html['BytesReadUsefulData']; 
		$BytesReadUsefulData = formatSize($BytesReadUsefulData); 
		$TotalPeers = @$html['TotalPeers'];
		$ActivePeers = @$html['ActivePeers'];
		$ConnectedSeeders = @$html['ConnectedSeeders'];
		$DownloadSpeed = @$html['DownloadSpeed'];
		$DownloadSpeed = formatSize($DownloadSpeed);
		$PreloadSize = @$html['PreloadedBytes'];
		$PreloadSize = formatSize($PreloadSize);
		
		$ret = "$Status ■ Peers:[ $ConnectedSeeders ] $ActivePeers / $TotalPeers ■ Preload( $PreloadSize ) ■ SPEED( $DownloadSpeed )";
		echo $ret;
		//return tsclient_Nmsg_content($ret,$img,$Status);
}


function rss_tsstatus_content() 
{
		$_SESSION['rssIdd'] = "STATUS";
		global $TShost;
		$host = "http://".$TShost;
		if( isset($_REQUEST['id'])) $id = $_REQUEST['id'];
		$hash = explode("/", $id); $hash = $hash[3];
		$url = $host.$id;
		$post = '{"Hash":"'.$hash.'"}'; 
		$link = $host."/torrent/stat";
		$default_opts = array( 'http'=>array('timeout' => 1,));
		$default = stream_context_set_default($default_opts);
		@get_headers($url);
		$html = postTorr($link,$post);
		$html = @json_decode($html,true);
		$html['FileStats'] = '';		
		if (!isset($html['TorrentStatusString'])) return tsclient_Nmsg_content("НЕТ ДАННЫХ !!!" );
		$Status = @$html['TorrentStatusString'];
		$TorrentSize = @$html['TorrentSize'];
		$TorrentSize = formatSize($TorrentSize);
		$BytesRead = @$html['BytesRead'];
		$BytesRead = formatSize($BytesRead);
		$BytesReadUsefulData = @$html['BytesReadUsefulData']; 
		$BytesReadUsefulData = formatSize($BytesReadUsefulData); 
		$TotalPeers = @$html['TotalPeers'];
		$ActivePeers = @$html['ActivePeers'];
		$ConnectedSeeders = @$html['ConnectedSeeders'];
		$DownloadSpeed = @$html['DownloadSpeed'];
		$DownloadSpeed = formatSize($DownloadSpeed);
		$PreloadSize = @$html['PreloadedBytes'];
		$PreloadSize = formatSize($PreloadSize);
		
		$ret = "Peers:[ $ConnectedSeeders ] $ActivePeers / $TotalPeers  ■ Preload($PreloadSize) ■ SPEED($DownloadSpeed)";
		//$ret = str_pad($ret, 75, "_", STR_PAD_BOTH);
		$img = DIR_NAME."/img/ts.png";
		return tsclient_Nmsg_content($ret,$img,$Status);
	}




function postTorr($url,$QUERY = null) {
	$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL.
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36'. PHP_EOL.
				'X-Requested-With: XMLHttpRequest',
				'content' => $QUERY,
			),
		));
	//post is string
	$post = @file_get_contents($url, false, $context);
	
	return $post;
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
			$bytes = $bytes . ' байты';
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
		if( isset( $_REQUEST['txt'])) $msg = $_REQUEST['txt'];
		if( isset( $_REQUEST['img'])) $image = $_REQUEST['img'];
		$pth = tools_path.'/'. 'msg.php';
		$rssmsg = file_get_contents($pth);
		$rssmsg = str_replace('>Сообщение</','>'.$name.'</',$rssmsg);
		$rssmsg = str_replace('backgroundColor="41:41:41"','backgroundColor="40:50:60"',$rssmsg);
		
		eval('?>' . $rssmsg);

if( isset( $_REQUEST['debug1'])) {
	print_r("-------------------------------");
	print_r($rssmsg);
	print_r("-------------------------------");
}
		
		//include($pth);
		$view = new rssMsgView;
		if (!empty($image)) $view->currentImage = $image;
		$view->currentMsg = $msg;
		$view->showRss();
		
		

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
