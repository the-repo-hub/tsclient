<?php
###################################################
# Author: FarVoice  						2015  #
# Adaptive from HLS proxy seek BigmanAlexey 2016  #
###################################################
const proxyLink  = '/tmp/proxylink.txt';
const doseekLink = '/tmp/doseek';
set_time_limit(12000);
//
// ------------------------------------
$debug = false;
$headerSent = false;
$rmRange    = false;
//
// ------------------------------------
function sendChunk( $s )
{
	echo dechex( strlen( $s ))."\r\n$s\r\n";
}
//
// ------------------------------------
function sendHttpHeader( $hs )
{
	foreach( $hs as $s ) echo "$s\r\n";
	echo "\r\n";
}
//
// ------------------------------------
function toDebug()
{
global $debug;

	$a = func_get_args();
	$s = "\n";
	foreach( $a as $v )
	 if( is_string( $v )) $s .= $v;
	 else $s .= var_export( $v, true );
	$s .= "\n";

	if( $debug ) sendChunk( $s );
#	else file_put_contents('/dev/console', $s );
}
//
// ------------------------------------
function getContentType( $head )
{
	$ret = '';
	foreach( $head as $s )
	{
		if( strpos( $s, 'Content-Type:' ) !== 0 ) continue;

		$a = explode( ':', $s, 2 );
		$a = explode(';', $a[1] );
		$ret = trim( strtolower( $a[0] ));
	}
	return $ret;
}
//
// ------------------------------------
function openUrl( $url, &$headers )
{
global $rmRange;
global $reqHeaders;

	$headers = array(
		'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.78.1 (KHTML like Gecko) Version/7.0.6 Safari/537.78.1',
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	);

	if( ! $rmRange )
	{
		if( isset( $reqHeaders['Accept-Ranges'] )) $headers['Accept-Ranges'] = $reqHeaders['Accept-Ranges'];
		if( isset( $reqHeaders['Range'] )) $headers['Range'] = $reqHeaders['Range'];
	}

	$s = '';
	foreach( $headers as $k => $v ) $s .= $k .': '. $v ."\r\n";

	$opts = array(
		'http' => array(
			'method' => 'GET',
			'header' => $s,
		)
	);
	$context = stream_context_create($opts);

	toDebug("openUrl::request = $url");
	toDebug("openUrl::request header = ", $headers );

	$f = @fopen( $url, 'r', false, $context );
	if( $f === false ) return false;

	toDebug("openUrl::raw responce header = ", $http_response_header );

	// get last headers
	$headers = array();
	$ok = false;
	foreach( $http_response_header as $s )
	{
		if( strpos( $s, ' 200 OK' ) !== false
		||  strpos( $s, ' 206 Partial Content' ) !== false ) $ok = true;
		if( $ok ) $headers[] = trim( $s );
	}
	toDebug("openUrl::response header = ", $headers );

	return $f;
}
//
// ------------------------------------
function sendHeaders( $headers )
{
global $debug;

global $headerSent;
global $rmRange;

	if( $headerSent )
	{
		toDebug("sendHeaders:: already sent");
		return;
	}

	$heads = array();
	foreach( $headers as $s )
	 if( ! $rmRange || (
	      strpos( $s, 'Content-Length:') === false
	  &&  strpos( $s, 'Cache-Control:' ) === false
	  &&  strpos( $s, 'Accept-Ranges:' ) === false
	 ))
	  $heads[] = $s;

	toDebug("sendHeaders::headers = ", $heads );

	if( ! $debug ) sendHttpHeader( $heads );

	$headerSent = true;
}
//
// ------------------------------------
function sendFile( $f )
{
global $debug;

	toDebug("sendFile");

	if( ! $debug )
	 while( ! feof( $f ))
	 {
		$s = fread( $f, 2048 );
		echo $s;
		flush();
		if( checkStdin()) break;
	 }

	fclose( $f );
}
//
// ------------------------------------
function getContents( $f )
{
	$s = '';
	while( ! feof( $f )) $s .= fread( $f, 2048 );
	fclose( $f );

	return $s;
}
//
// ------------------------------------
function getValueFromExt( $s )
{
#EXTINF:-1 cn-id=29598845, Курай
	$a = explode( ',', $s );
	$a = explode( ':', $a[0] );
	$a = explode( ' ', $a[1] );
	return $a[0];
}
//
// ------------------------------------
function sendList( $url, $file, &$headers)
{
	// m3u file
	$last = '';
	$update = false;
	global $doseek;

	toDebug("doseek= ".(@$doseek+0));
	
	do {
		toDebug("sendList::last = $last");

		$s = getContents( $file );
		//file_put_contents("/tmp/m3u8.dat",$s);

		// parse m3u
		$dur = 5;
		
		$idx = -1;
		$i = 0;
		$list = array();
		$timeSeg = array();
		if (strpos($s,'#EXT-X-ENDLIST')) $endHls = true; else $endHls = false;
		$a = explode("\n", str_replace("\r", '', $s ));
		foreach( $a as $s )
		{
			$s = trim( $s );
			if( $s == '' ) continue;

			if( strpos( $s, '#EXT-X-VERSION') === 0 )
			{
				$update = true;
				continue;
			}
			if( strpos( $s, '#EXT-X-ENDLIST') === 0 )
			{
				$update = false;
				continue;
			}
			if( strpos( $s, '#EXT-X-TARGETDURATION:') === 0 )
			{
				
				$dur = getValueFromExt( $s );
				continue;
			}
			if( strpos( $s, '#EXTINF:') === 0 )
			{
				$seg = getValueFromExt( $s );
				if ( $endHls ) $timeSeg[$i] =  floor($seg);
				continue;
			}
			if( strpos( $s, '#') === 0 ) continue;

			if( $s == $last ) $idx = $i;

			$list[ $i++ ] = $s;
		}
		toDebug("sendList::update = ", $update );
		toDebug("sendList::list = ", $list );
		if ($endHls) $fullTime = array_sum($timeSeg); else $fullTime = 0;
		//file_put_contents("/tmp/dannye.dat",var_export(array ( 'full'	=> $fullTime, 'list'	=> $list, 'time'	=> $timeSeg, 'update' => $update), true));
		// play next files
		$i = $idx + 1;
		if( $i == count( $list ))
		{
			if( $update )
			{
				toDebug("sendList::delay = $dur");
				sleep( $dur );
			}
		}
		else
		{
			$sec =0;
			while( $i < count( $list ))
			{
				$u = $list[ $i ];
				$in = array_search($u, $list);
				if ($endHls) { $sec = array_slice($timeSeg, 0, $in); $sec = array_sum($sec);}
				toDebug("sendList::currentUrl = $u");
				$last = $u;
				if( strpos( $u, '://' ) === false )
				{
					// path without server
					if( substr( $u, 0, 1 ) == '/' )
					{
						// absolute path
						if(( $p = strpos( $url, '://')) !== false )
						 if(( $p = strpos( $url, '/', $p + 3 )) !== false )
						  $u = proxy . phpsubstr($url, 0, $p) . $u;
					}
					else
					{
						// relative path
						$u = dirname($url) . 'proxy.php/' .$u;
					}
				}
				if ($doseek<=$sec) { 
					file_put_contents('/tmp/current.seg',$sec.PHP_EOL.$fullTime);
					sendUrl( $u );
				}
				$i++;
			}
		}

		if( $update )
		{
			$file = openUrl( $url, $headers );
			if( $file === false ) exit;
		}

	} while( $update );
}
//
// ------------------------------------
function sendUrl( $url )
{
global $rmRange;

	toDebug("sendUrl::url = $url");

	$headers = null;
	$f = openUrl( $url, $headers );
	if( $f === false ) exit;

	$type = getContentType( $headers );
	toDebug("sendUrl::contentType = $type");

	if( $type == 'application/x-mpegurl'
	||  $type == 'application/vnd.apple.mpegurl'
	||  $type == 'application/video.mpegURL'
	||  $type == 'application/video.mpegurl' )
	{
		$rmRange = true;
		sendList( $url, $f, $headers );
	}
	else
	{
		// other types of file
	/*
		foreach ($headers as $i =>$info) {
			if (strpos($info,'ontent-Length:')) {
				//unset($headers[$i]);
				$headers[$i] =  'Content-Length: 1033248000';
				break;
			}
		}
	*/
		//$name = time();file_put_contents("/tmp/sh_$name.dat",var_export($headers, true));
		sendHeaders( $headers );
		sendFile( $f );
	}
}
//
// ------------------------------------
function checkStdin()
{
global $stdin;

	$read   = array( $stdin );
	$write  = NULL;
	$except = NULL;

	if(( $num = stream_select($read, $write, $except, 0)) === false ) $ret = false;
	elseif( $num > 0) $ret = true;
	else $ret = false;

#	toDebug('checkStdin::', $ret );

	return $ret;
}
//
// ====================================
//
if( file_exists( proxyLink )) $url = file_get_contents( proxyLink ); else $url = '';
if( file_exists( doseekLink )) $doseek = file_get_contents( doseekLink ); else $doseek = 0;

if(( $stdin = @fopen('php://stdin', 'r')) === false ) exit;

while( true )
{
	// get request headers

	$reqHeaders = array();
	$reqVals = array();
	$name = time(); 
	while( $s = fgets( $stdin ))
	{
		$s = trim( $s );
		if( $s == '' ) break;
		if( preg_match('|GET .*?\?(.+) HTTP.*|', $s, $a ) > 0 )
		{
			// parse request variables
			parse_str( $a[1], $reqVars );
			$reqHeaders[ trim( $s )] = '';
		}
		else
		{
			$a = explode(':', $s, 2 );
			if( ! isset( $a[1] )) $a[1] = '';
			$reqHeaders[ trim( $a[0] )] = trim( $a[1] );
		}
	}
	// debug header

	if( isset( $reqVars['debug'] ))
	{
		$debug = true;

		sendHttpHeader( array(
			'HTTP/1.1 200 OK',
			'Connection: close',
			'Content-type: text/plain; charset=utf-8',
			'Transfer-Encoding: chunked',
		));
	}

	toDebug( 'request header:', $reqHeaders );
	toDebug( 'request vars:', $reqVars );

	if( isset( $reqVars['url'] )) $url = $reqVars['url'];
	if( isset( $reqVars['doseek'] )) $doseek = $reqVars['doseek'];
	$url = str_replace('.m3u9','.m3u8',$url);
	if( $url == '' ) break;
	//file_put_contents("/tmp/url_$name.dat",var_export($url, true).chr(10).'$reqVars='.var_export($reqVars, true).chr(10).'$reqHeaders='.var_export($reqHeaders, true));
	sendUrl( $url );

	if( ! checkStdin()) break;
}

fclose( $stdin );

if( $debug ) sendChunk('');

?>