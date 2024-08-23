<?php
require_once(DIR_NAME.'/def_rss.php');
	foreach ($nav_rss as $info) {
		$Tmenu = $info['title'];
		$Slogo = $info['icon'];
		$Fname = $info['module'];
	}
	define("SRV_MENU", $Tmenu, true);
	define("SRV_LOGO", DIR_NAME."/$Slogo", true);
	define("SRV_FN", $Fname, true);
	define("TOOLS_PATH", str_replace('/'.SRV_FN,'/bigmanTools',dir_name), true);

	if (!function_exists('mb_strlen')) {
		function mb_substr($str, $pos, $kl,$s=null) {
			return  iconv_substr($str,$pos, $kl,'UTF-8');
		}
		function mb_strlen($str, $s=null) {
			return iconv_strlen($str, 'UTF-8');
		}
	}

	function Tokens($scp, $prf1, $suf1, $prf2 = null, $suf2 = null) {
		$tokens = array();
		while (true) {
			$start = strpos($scp, $prf1);
			if (false === $start) { break;}
			$start = $start + strlen($prf1);
			$stop  = strpos($scp, $suf1, $start);
			if (false === $stop) {break;}
			$token1 = substr($scp, $start, $stop - $start );
			$scp = substr($scp, $stop + strlen($suf1));
			if (null == $prf2 || null == $suf2) { $tokens[] = $token1; continue;}
			$start = strpos($scp, $prf2);
			if (false === $start) { break;}
			$start = $start + strlen($prf2);
			$stop  = strpos($scp, $suf2, $start);
			if (false === $stop) { break;}
			$token2 = substr($scp, $start, $stop - $start );
			$scp = substr($scp, $stop + strlen($suf2));
			$tokens[$token1] = $token2;
		}
		return $tokens;
	}

	function Param($scp, $prf, $suf, $dft = null, $occ = 1) {
		if (! isset($scp) || ! is_string($scp)) { return $dft; }
		for ($start = 0; $occ > 0; $occ--) {
			$start = null == $prf || '' == $prf ? 0 : strpos($scp, $prf, $start);
			if (false === $start) { return $dft;}
			$start = $start + strlen($prf);
		}
		$stop =  null == $suf || '' == $suf ? strlen($scp) : strpos($scp, $suf, $start);
		if (false === $stop) { return $dft;}
		return substr($scp, $start, $stop - $start);
	}

	function cachePic($seri) {
		$file = $seri;
		$file = str_replace(array('/',':','-'),'_',$file);
		$file = "/tmp/cached/$file";
		if (!file_exists($file)) {
			$ff = file_get_contents($seri);
			file_put_contents($file,$ff);
			$ff = '';
		}
	}

	function GetGzip($url,$qzip=false, $prx=0) {
if( isset( $_REQUEST['debug'] )) {echo"url="; print_r($url); echo "\n";}
		$context = array(  'http' => array(
				'method' => 'GET',
				'header'=>"Accept-Encoding: gzip\r\nReferer: $url\r\n"
			 ) );
		if ($prx != 0) {
			if ($prx == true) $proxy = 'tcp://gw2.anticenz.org:8080'; else $proxy = $prx;
			if (strpos(" $proxy",'tcp://')) {
				$context['http']['proxy'] = $proxy;
				$context['http']['request_fulluri'] = true;
				$context['http']['ignore_errors'] = 1;
			}
		}
if( isset( $_REQUEST['debug'] )) {echo"context="; print_r($context); echo "\n";}
		$context = stream_context_create($context);
		$html = @file_get_contents($url, false ,$context);
		if (strpos($html,'http:')===false && $qzip==false) $html = @gzinflate(substr($html, 10));
		return $html;
	}


	function GetRequest($url,$header='', $hd = false) {
		$context = stream_context_create(
			array(
				'http'=>array( 'header' => $header, 'method' => 'GET',))
				);
		$contents = '';
		$contents = @file_get_contents($url, false ,$context);
		if ($hd==false) return $contents;
		$hd = @$http_response_header;
		return array($contents,$hd);
	}

	function PostRequest($url,$header, $hd = false) {
		$parsedUrl = parse_url($url);
		$host	= @$parsedUrl['host'];
		$ph		= @$parsedUrl['path'];
		if (isset($parsedUrl['query'])) $params = $parsedUrl['query']; else $params = null;
		$context = stream_context_create(array(
				'http' => array(
				'method'  => 'POST',
				'content' => $params,
				'header'  => $header)));
		$url = str_replace("?$params", '', $url);
		$contents = @file_get_contents($url, false, $context);
		if ($hd==false) return $contents;
		$hd = @$http_response_header;
		return array($contents,$hd);
	}

	function cutStrWord( $s, $count ) {
		$len = mb_strlen( $s, 'utf8' );
		if( $len <= $count ) return $s;
		$pos = $count;
		while(( $pos >=0 )&&( mb_substr( $s, $pos, 1, 'utf8' ) <> ' ' )) $pos -= 1;
		return mb_substr( $s, 0, $pos, 'utf8' );
	}

	function spec_sym($str) {
		$sym = array(	'&quot;'=>'"',
					'&amp;'=>'&',
					'&lt;'=>'<',
					'&gt;'=>'>',
					'&nbsp;'=>' ',
					'&ndash;'=>'–',
					'&mdash;'=>'–',
					'&brvbar;'=>'¦',
					'&sect;'=>'§',
					'&copy;'=>'©',
					'&laquo;'=>'«',
					'&raquo;'=>'»',
					'&not;'=>'¬',
					'&reg;'=>'®',
					'&deg;'=>'°',
					'&plusm;'=>'±',
					'&micro;'=>'µ',
					'&para;'=>'¶',
					'&middot;'=>'·',
					'&times;'=>'×',
					'÷'=>'&divide;',
		);
		foreach ($sym as $sm =>$csm) $str = str_replace($sm,$csm,$str);
		return $str;
	}

	function F_count($str)
{
	$m_country = array (
  'Вьетнам' => 'vn',
  'Россия' => 'ru',
  'СССР' => '<<>>',
  'Малайзия' => 'my',
  'Корея Северная' => 'kp',
  'Гонконг' => 'hk',
  'Германия' => 'de',
  'Чехия' => 'cz',
  'Сербия и Черногория' => 'rs',
  'Сербия' => 'rs',
  'Конго' => 'cg',
  'США' => 'us',
  'Дания' => 'dk',
  'Швеция' => 'se',
  'Канада' => 'ca',
  'Финляндия' => 'fi',
  'Франция' => 'fr',
  'Япония' => 'jp',
  'Бразилия' => 'br',
  'Великобритания' => 'gb',
  'Великобритания' => 'uk',
  'Нидерланды' => 'nl',
  'Италия' => 'it',
  'Испания' => 'es',
  'Мексика' => 'mx',
  'Алжир' => 'dz',
  'Швейцария' => 'ch',
  'Монако' => 'mc',
  'Перу' => 'pe',
  'Аргентина' => 'ar',
  'Австралия' => 'au',
  'Корея Южная' => 'kr',
  'Тайвань' => 'tw',
  'Индия' => 'in',
  'ЮАР' => 'za',
  'Китай' => 'cn',
  'Польша' => 'pl',
  'Норвегия' => 'no',
  'Новая Зеландия' => 'nz',
  'Португалия' => 'pt',
  'Исландия' => 'is',
  'Ирландия' => 'ie',
  'Босния-Герцеговина' => 'ba',
  'Словения' => 'si',
  'Бельгия' => 'be',
  'Израиль' => 'il',
  'Марокко' => 'ma',
  'Таиланд' => 'th',
  'Румыния' => 'ro',
  'Филиппины' => 'ph',
  'Иран' => 'ir',
  'Венгрия' => 'hu',
  'Тунис' => 'tn',
  'Чили' => 'cl',
  'Эстония' => 'ee',
  'Латвия' => 'lv',
  'Греция' => 'gr',
  'Колумбия' => 'co',
  'Австрия' => 'at',
  'Молдова' => 'md',
  'Люксембург' => 'lu',
  'Грузия' => 'ge',
  'Украина' => 'ua',
  'Болгария' => 'bg',
  'Кипр' => 'cy',
  'Сенегал' => 'sn',
  'Мавритания' => 'mr',
  'Турция' => 'tr',
  'Беларусь' => 'by',
  'Таджикистан' => 'tj',
  'Узбекистан' => 'uz',
  'Венесуэла' => 've',
  'Индонезия' => 'id',
  'Пакистан' => 'pk',
  'Бангладеш' => 'bd',
  'Куба' => 'cu',
  'Чад' => 'td',
  'Оккупированная Палестинская территория' => 'ps',
  'Уругвай' => 'uy',
  'Македония' => 'mk',
  'Мозамбик' => 'mz',
  'Пуэрто Рико' => 'pr',
  'Камбоджа' => 'kh',
  'Хорватия' => 'hr',
  'Кыргызстан' => 'kg',
  'Эритрея' => 'er',
  'Тринидад и Тобаго' => 'tt',
  'Армения' => 'am',
  'Ирак' => 'iq',
  'Намибия' => 'na',
  'Буркина-Фасо' => 'bf',
  'Ямайка' => 'jm',
  'Словакия' => 'sk',
  'Камерун' => 'cm',
  'Эквадор' => 'ec',
  'Ливан' => 'lb',
  'Сирия' => 'sy',
  'Гаити' => 'ht',
  'Кения' => 'ke',
  'Египет' => 'eg',
  'Мартиника' => 'mq',
  'Руанда' => 'rw',
  'Зимбабве' => 'zw',
  'Барбадос' => 'bb',
  'Непал' => 'np',
  'Панама' => 'pa',
  'Шри-Ланка' => 'lk',
  'Берег Слоновой кости' => 'ci',
  'Нигерия' => 'ng',
  'Мальта' => 'mt',
  'Гондурас' => 'hn',
  'Афганистан' => 'af',
  'Бутан' => 'bt',
  'Маврикий' => 'mu',
  'Гвинея-Бисау' => 'gw',
  'Гренландия' => 'gl',
  'Боливия' => 'bo',
  'ОАЭ' => 'ae',
  'Албания' => 'al',
  'Сальвадор' => 'sv',
  'Казахстан' => 'kz',
  'Литва' => 'lt',
  'Багамы' => 'bs',
  'Лихтенштейн' => 'li',
  'Ливия' => 'ly',
  'Габон' => 'ga',
  'Доминикана' => 'do',
  'Гвинея' => 'gn',
  'Танзания' => 'tz',
  'Коста-Рика' => 'cr',
  'Монголия' => 'mn',
  'Замбия' => 'zm',
  'Гватемала' => 'gt',
  'Азербайджан' => 'az',
  'Никарагуа' => 'ni',
  'Ангола' => 'ao',
  'Бенин' => 'bj',
  'ЦАР' => 'cf',
  'Гваделупа' => 'gp',
  'Парагвай' => 'py',
  'Гана' => 'gh',
  'Ботсвана' => 'bw',
  'Кабо-Верде' => 'cv',
  'Кувейт' => 'kw',
  'Мьянма' => 'mm',
  'Лаос' => 'la',
  'Мадагаскар' => 'mg',
  'Мали' => 'ml',
  'Туркменистан' => 'tm',
  'Макао' => 'mo',
  'Иордания' => 'jo',
  'Нигер' => 'ne',
  'Саудовская Аравия' => 'sa',
  'Андорра' => 'ad',
  'Сомали' => 'so',
  'Того' => 'tg',
  'Бурунди' => 'bi',
  'Папуа - Новая Гвинея' => 'pg',
  'Бахрейн' => 'bh',
  'Гайана' => 'gy',
  'Фиджи' => 'fj',
  'Судан' => 'sd',
  'Эфиопия' => 'et',
  'Йемен' => 'ye',
  'Суринам' => 'sr',
  'Уганда' => 'ug',
  'Белиз' => 'bz',
  'Аруба' => 'aw',
  'Либерия' => 'lr',
  'Катар' => 'qa',
  'Оман' => 'om',
  'Бермуды' => 'bm',
  'Новая Каледония' => 'nc',
  'Фарерские острова' => 'fo',
  'Антильские Острова' => 'an',
  'Сейшельские острова' => 'sc',
  'Сан-Марино' => 'sm',
  'Тонга' => 'to',
  'Конго (ДРК)' => 'cd',
  'Лесото' => 'ls',
  'Шпицберген и Ян-Майен' => '',
  'Гибралтар' => 'gi',
  'Сьерра-Леоне' => 'sl',
  'Кирибати' => 'ki',
  'Малави' => 'mw',
  'Американские Виргинские острова' => 'vi',
  'Джибути' => 'dj',
  'Свазиленд' => 'sz',
  'Антигуа и Барбуда' => 'ag',
  'Доминика' => 'dm',
  'Французская Гвиана' => 'gf',
  'Реюньон' => 're',
  'Федеративные Штаты Микронезии' => 'fm',
  'Самоа' => 'as',
  'Гамбия' => 'gm',
  'Восточная Сахара' => 'eh',
  'Антарктида' => 'aq',
  'Гуам' => 'gu',
  'Французская Полинезия' => 'pf',
  'Сент-Люсия ' => 'lc',
  'Мальдивы' => 'mv',
  'Каймановы острова' => 'ky',
  'Остров Мэн' => 'im',
  'Тувалу' => 'tv',
  'Сент-Винсент и Гренадины' => 'vc',
  'Палау' => 'pw',
  'Коморы' => 'km',
  'Вануату' => 'vu',
  'Гренада' => 'gd',
  'Острова Кука' => 'ck',
  'Бруней-Даруссалам' => 'bn',
  'Маршалловы острова' => 'mh',
);
	$str = explode(',',$str); $str = trim($str[0]);
	if (@$m_country[$str]!='') $ret = "http://st.kp.yandex.net/images/flags_new/".@$m_country[$str].".png"; else $ret  = 'http://inbaknowledge.weebly.com/uploads/3/7/2/6/37263301/4732985.png';
	//if (@$m_country[$str]=='<<>>') $ret = "http://ru.wargaming.net/clans/media/clans/emblems/cl_347/107347/emblem_195x195.png";
	if (@$m_country[$str]=='<<>>') $ret = "http://www.fordesigner.com/imguploads/Image/cjbc/zcool/png20080526/1211805936.png";

	return $ret;
}

//==============Home_menu=============
	function itemMarker($menu, $item=SRV_FN) {
		if (!empty($item) && preg_match("|.*?$item(.*?)</item>|s",$menu,$item)>0) $item = $item[0]; else return '';
		$pos = strrpos($item, '<item>');
		$item = substr($item, $pos); ;
		return $item;
	}

	function menu_dey($dey) {
		$item = '<item>
<title>'.SRV_MENU.'</title>
<imagePath> /usr/local/etc/mos/iconmenu/images/'.SRV_FN.' </imagePath>
<onClick>
	<script>
		APName = "IMSAP";
		MenuType = "TopMenu";
		MenuLink = "http://127.0.0.1/?page=rss_'.SRV_FN.'";
		menuCmd(APName, MenuType, MenuLink, BltType);
		null;
	</script>
</onClick>
</item>
';
	$url = menu_url(); $menu = file_get_contents($url);
	if ($dey=='on' && !strpos($menu, 'page=rss_'.SRV_FN)) {
		$marker = itemMarker($menu,'rss_peerstv');
		$menu = str_replace($marker,$item.$marker,$menu);
		$pic_url = str_replace('HomeMenu.rss','images/'.SRV_FN.'.fsp',$url);
		$pic = file_get_contents(dir_name.'/'.SRV_FN.'.png');
		file_put_contents($pic_url,$pic);
	} else if ( $dey!='on' && strpos($menu, 'page=rss_'.SRV_FN)){
		$marker = itemMarker($menu);
		$menu = str_replace(array($marker."\r\n",$marker."\n",$marker),'',$menu);
	}
	file_put_contents($url,$menu);
	}

	function menu_check($url) {
		$menu = file_get_contents($url);
		//if (strpos($menu, '<title>'.SRV_MENU)!==false) {
		if (strpos($menu, '?page=rss_'.SRV_FN)!==false) {
			$title = 'Выкл. в Меню';
			$link = getMosUrl().'?page='.SRV_FN.'_list&amp;menu=off';
		} else {
			$title = 'Вкл. в Меню';
			$link = getMosUrl().'?page='.SRV_FN.'_list&amp;menu=on';
		}
		return array($title,$link);
	}

	function menu_url() {
		$url = $_SERVER['LD_LIBRARY_PATH'];
		$url = str_replace('lib:/lib','',$url);
		$url = $url.'iconmenu/HomeMenu.rss';
		return $url;
	}

	function favorite($id = null, $per = null) {
		if (empty($id) || empty($per)) return;
		$izb = @file_get_contents("/tmp/put.dat"); $izb = explode(chr(10),$izb);
		$key = array_search($id, $izb, true);
		if (($key+0)==0) return;
		$per = explode(',',$per);
		$image = $per[0]; unset($per[0]);
		$image = @$izb[$image+$key];
		if (!strpos(" $image",'http')) $image = SRV_LOGO;
		$msg = 'Видео удалено из избранного'; $izb_path = dir_name .'/izb.dat';
		$funct = srv_fn.'_config'; global $$funct; $config = $$funct; $type = $config['type'];
		$izbr = array(); if(file_exists($izb_path)) include(tools_path.'/'.'izb.php');
		if (isset($izbr[$id])) unset($izbr[$id]);
		if ($type!='favourites') {
			$item = array();
			foreach ($per as $i => $name) $item[$id][$name] = $izb[$i+$key];
			$izbr = $item + $izbr;
			$msg = 'Видео добавлено в избранное';
		}
if( isset( $_REQUEST['debug'])) {echo "izbr="; print_r($izbr); echo "\n";}
		if (count($izbr)>0) {
			$izbr = '$izbr = '.var_export( $izbr, true ).';';
			$izbr = gzdeflate($izbr, 9);
			file_put_contents($izb_path , $izbr);
		} else unlink($izb_path);
		include(tools_path.'/'. 'msg.php' );
		$view = new rssMsgView;
		$view->currentImage = $image;
		$view->currentMsg = $msg;
		$view->showRss();
	}
//----------------------
?>