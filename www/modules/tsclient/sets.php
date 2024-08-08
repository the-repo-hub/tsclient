<?php
error_reporting( E_ERROR ); // Set E_ALL for debuging

$path = dirname( __FILE__ ); define("DIR_NAME", $path, true);
include 'init.php';
//FIXME debug
$config = array (
	"host" => "192.168.1.7:18090",
	"history" => "ram",
	"ground" => 5,
);

include (DIR_NAME.'/ts.config.php');

//
// ====================================
function tsclient_set_content()
{
	global $config;
	header( "Content-type: text/plain; charset=utf-8" );
	$icomhomePth = str_replace('/www/modules/tsclient','',DIR_NAME)."/iconmenu/HomeMenu.rss";
	$icomhomeImg = str_replace('/www/modules/tsclient','',DIR_NAME)."/iconmenu/images/tsclient.fsp";
	global $config;
	if( isset( $_REQUEST['host'] )) $config['host'] = $_REQUEST['host'];
	if( isset( $_REQUEST['history'] )) $config['history'] = $_REQUEST['history'];
	if( isset( $_REQUEST['ground'] )) $config['ground'] = $_REQUEST['ground'];
	if( isset( $_REQUEST['icon'] ) && file_exists($icomhomePth)) {
		$HomeRss = file_get_contents($icomhomePth);
		$add = '<item>
<title>TorrServe MOS client</title>
<imagePath> /usr/local/etc/mos/iconmenu/images/tsclient </imagePath>
<onClick>
	<script>
		APName = "IMSAP";
		MenuType = "TopMenu";
		MenuLink = "http://127.0.0.1/?page=rss_tsclient";
		menuCmd(APName, MenuType, MenuLink, BltType);
		null;
	</script>
</onClick>
</item>';
		if ($_REQUEST['icon']=='on' && !strpos($HomeRss,'rss_tsclient') && strpos($HomeRss,'rss_peerstv')) {
			if (preg_match('|rss_peerstv(.*?)</item>|s',$HomeRss,$metka)>0) $metka = $metka[0]; else $metka='';
			$HomeRss = str_replace($metka, $metka.PHP_EOL.$add.PHP_EOL,$HomeRss);
			if (!file_exists($icomhomeImg)) {
				$img = file_get_contents(DIR_NAME."/tsclient.png");
				file_put_contents($icomhomeImg,$img);
			}
		} else if ($_REQUEST['icon']=='off'){
			$HomeRss = str_replace(PHP_EOL.$add.PHP_EOL,'',$HomeRss);
		}
		file_put_contents($icomhomePth,$HomeRss);
	}

	
	file_put_contents( DIR_NAME.'/ts.config.php', '<?php $config = '.var_export( $config, true ).'; ?>' );
	echo 'ok';
}

// ------------------------------------
function showOption( $opt, $val, $title )
{
	if( $opt == $val ) $sel = ' selected'; else $sel = '';
	echo '<option value="'. $val .'"'. $sel .'>'. getMsg( $title) ."</option>\n";
}

//
// ====================================
function tsclient_sets_head()
{

?>
<link rel="stylesheet" href="/modules/core/css/buttons.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="/modules/core/css/sets.css" type="text/css" media="screen" charset="utf-8">
<style type="text/css">

.form-text
{
	width:270px;
}
</style>

<script type="text/javascript">

var set_http;

function getXmlHttpRequestObject() {

	if (window.XMLHttpRequest) return new XMLHttpRequest();	// code for IE7+, Firefox, Chrome, Opera, Safari
	else return new ActiveXObject("Microsoft.XMLHTTP");	// code for IE6, IE5
}

function handleSets() {

	if(set_http.readyState==4) {

		if(set_http.status != 200)
		 alert("Error:"+ set_http.status);
		else {
			var val = users_http.responseText;
			if( val != "ok" ) alert("Respond:"+ val);
		}
	}
}

function getOption( sel ) {

	for (var i = 0; i < sel.options.length; i++) {

		var opt = sel.options[i];
		if(opt.selected) {

			return opt.value;
			break;
		}
	}
}

function sendSets( form ) {

	url = "?page=tsclient_set"
	+ "&host="  + form.elements.host.value
	+ "&history=" + getOption( form.elements.hystory )
	+ "&icon="     + getOption( form.elements.icon )
	+ "&ground="     + form.elements.ground.value;

	set_http = getXmlHttpRequestObject();
	set_http.onreadystatechange = handleSets;
	set_http.open("GET", url ,true );
	set_http.setRequestHeader("If-Modified-Since","0");
	set_http.setRequestHeader("Cache-Control","no-cache");
	set_http.send( null );
}

</script>

<?php
//http://192.168.0.87/?host=192.168.0.88&hystory=rom&icon=on&cast=normal
}
//
// ------------------------------------
function tsclient_sets_body()
{
global $config;
$icomhomeRss = str_replace('/www/modules/tsclient','',DIR_NAME)."/iconmenu/HomeMenu.rss";
$icon = file_get_contents($icomhomeRss);
if (strpos($icon,'rss_tsclient')) $icon = 'on'; else $icon = 'off';
//

?>
<div id="container">
<h3>Настройки модуля TorrServe MOS Client</h3>
<div class="set_card">

<table class="set_list" border="0" cellspacing="0" cellpadding="8">

<form name="one" onsubmit="sendSets( document.forms.one );return false">

<tr><td>TorrServer</td>
<img src="/modules/tsclient/img/ts.png" height="30px" align="center" /></div>
&nbsp;&nbsp;<a href="https://4pda.ru/forum/index.php?showtopic=889960" target="_blank"><b>Посмотреть про сам TorrServe и выбрать подходящий для вашего внешнего устройтва можно тут....</b></a>
<td>
<input size=34 id="host" name="host" type="text" value="
<?php 
echo $config['host'];
?>
"/> IP и порт адрес внешнего (PC или Android) устройства c установленым TorrServer - например 192.168.0.55:8090
</td>
</tr>

<tr><td>История просмотра</td>
<td><select name="hystory" size=1>
<?php
	showOption( $config['history'], 'off' ,  'Отключена' );
	showOption( $config['history'], 'rom',  'Постоянная' );
	showOption( $config['history'], 'ram' ,  'Временная' );
?>
</select> Временная работает до выключения приставки. Постоянная сохраняется все время.</td></tr>

<tr><td>Иконка</td>
<td><select name="icon" size=1>
<?php
	showOption( $icon, 'on',  'Включена' );
	showOption( $icon, 'off',  'Отключена' );

?>
</select> Иконка в Home menu replacement</td></tr>

<tr><td class="ground">Задний фон</td>
<td class="ground" >
<?php
	for ($i = 1; $i <= 5; $i++) {
		if ($i==$config['ground']) $sy = ' checked'; else $sy = '';
		if ($i==4) echo "</br>";
		if ($i<5) {
			echo '<input type="radio" name="ground" value="'.$i.'"'. $sy .'/>'. $i ."\n";
			echo '<img src="/modules/tsclient/img/ground0'.$i.'.jpg" width="250"/>';
		} else {
			echo '<input type="radio" name="ground" value="'.$i.'"'. $sy .'/>'.'Random'."\n";
			echo '<img src="https://lh3.googleusercontent.com/fNT1t9wwCvvahvVX024icbdpEQZ5u4VtmQ6pJ59REFFf8ZViuarvO6YYYs-386-Vbbw" width="170"/>';
		}
	}

?></td></tr>

<tr><td colspan="2" align="right">
<button class="buttons" type="submit"><?= getMsg( 'coreCmSave') ?></button>
</td></tr></form>

</table>
</div>
</div>

<?php

}
?>
