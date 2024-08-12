<?php
	$mosUrl = @getMosUrl();
	header( "Content-type: text/plain" );
	echo '<?xml version="1.0" encoding="utf-8"?>' .PHP_EOL;
	echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' .PHP_EOL;
	if (!isset($hash)) return;
    if (!isset($idlespath)) return;
	if (!isset($baseUrl)) {
		$baseUrl	= 'http%3A%2F%2F37.220.36.15%2Fvideo%2F327a2776bfd23ed2%2Fiframe';
		$TitleVideo = 'Пластилиновая ворона (ТВ)';
		$ThumbVideo = 'http://foma.ru/fotos/journal/93/5051_1.jpg';
	}
	if (!isset($TitleVideo))	$TitleVideo = 'None title';
	if (!isset($ThumbVideo))	$ThumbVideo = 'http://readmas.ru/wp-content/filesall/2012/07/18.jpg';
	if (!isset($ProxyVideo))	$ProxyVideo = 0;
	if (!isset($mosUrl))		$mosUrl = "http://127.0.0.1/";
	$servicename = trim(substr(strrchr(dir_name, '/'), 1 ));
	$logo = dir_name . "/$servicename.png";
?> 

<onEnter>
	bufMaxMb	= Integer(1000 / 1024);
	bufMinBytes	= 384 * 1024;
	baseUrl		= "<?= $baseUrl ?>";
	caption		= "<?= $TitleVideo ?>";
	preview		= "<?= $ThumbVideo ?>";
	mosUrl		= "<?= $mosUrl ?>";
	srvName		= "<?= $servicename ?>";
	proxy		=  <?= $ProxyVideo ?>; if ( proxy == 1 ) captionColor = "0:255:255"; else captionColor = "255:230:0";
	logo		= "<?= $logo ?>";
    hash		= "<?= $hash ?>";
	doseek		=  0;
	isNSRtr		=  0;
	executeScript("initData");
	setRefreshTime(200);
	cancelIdle();

</onEnter>

<Tstatus>
	urlS = mosUrl + "?page=plr_tsstatus&amp;hash="+ hash;
	executeScript("hidePopup"); stateMid ="Status Torrent"; stateLeft = ""; stateRight = ""; popupTimeout = 10; popupHidePos = 0; barStatus = "status"; redrawDisplay("widget");
</Tstatus>


<SecondToString>
	file = SecondTime;
	x = Integer(SecondTime / 60); h = Integer( SecondTime / 3600);
	s = Integer(SecondTime - (x * 60)); m = Integer(x - (h * 60));
	if(h &lt; 10)  SecondTime = "0" + sprintf("%s:", h); else SecondTime = sprintf("%s:", h);
	if(m &lt; 10)  SecondTime += "0"; SecondTime += sprintf("%s:", m);
	if(s &lt; 10)  SecondTime += "0"; SecondTime += sprintf("%s", s);
	if (1 == 2) writeStringToFile("/tmp/tim"+file+".txt", SecondTime + " x=" + x + " m=" + m + " s=" + s + " -----h=" + h);
</SecondToString>

<initData>
		urlD = mosUrl + "?page=" + srvName + "_get&amp;id=" + baseUrl;
		urlD = getURL(urlD);
		nxtBt = getStringArrayAt(urlD, 1); videoUrl = getStringArrayAt(urlD, 0);
		if (videoUrl == null || videoUrl == "") {
			postMessage("return");
		}
		if ( proxy==1 ) { 
			writeStringToFile("/tmp/doseek", doseek);
			writeStringToFile("/tmp/proxylink.txt", videoUrl);
			videoUrl = "http://127.0.0.1:1000/";
		}
		if (bufMaxMb &gt; 0) {
				videoUrl = videoUrl + " fileCache=/tmp/videoCachefile cacheSize=" + bufMaxMb;
		}
		if ( 1==2 ) writeStringToFile("/tmp/videoUrl.dat", baseUrl + " " + videoUrl);
		prgbarStatus = "buffering";
		lastFullness		= 0;
		sameFullnessCount	= 0;
		sameFullnessLimit	= 10;
		previewHidePos		= 0;
		playElapsed			= 0;
		totalSec			= 0;
		bufferedSec			= 0;
		playStatus			= 0;
		curFullness			= 0;
		isFirstStart		= 1;
		videoPaused			= 1;
		firstBuf			= 1;
		print("Opening:", videoUrl);
		executeScript("hidePopup");
		executeScript("showBufferingPopup");
		SwitchViewer(0);
</initData>

<onExit>
	playItemURL(-1, 1);
	setRefreshTime(-1);
	writeStringToFile("/tmp/doseek", 0);
	if (null != originalAspectRatio) {
		setAspectRatio(originalAspectRatio);
	}
	cancelIdle();
</onExit>


<onBuffering>
	bufProgress = getCachedStreamDataSize(0, bufMinBytes);
	curFullness = getStringArrayAt(bufProgress, 0);
	param1      = getStringArrayAt(bufProgress, 1);
	param2      = getStringArrayAt(bufProgress, 2);
	EOFFlag     = getStringArrayAt(bufProgress, 3);
	print("Buffer state:", curFullness, param1, param2, EOFFlag);
	executeScript("showBufferingPopup");

	if (EOFFlag == 1 &amp;&amp; curFullness &gt; 0) {
		print("End of stream is reached");
		prgbarStatus = "preparing";
	} else if (EOFFlag == 2) {
		print("Network is down!");
		postMessage("return");
	} else if (curFullness &gt; 95) {
		print("Buffer is ready to be shown");
		prgbarStatus = "preparing";
	} else if (lastFullness != curFullness) {
		lastFullness = curFullness;
		sameFullnessCount = 0;
	} else if (lastFullness != 0) {
		sameFullnessCount = Add(sameFullnessCount, 1);
		if (sameFullnessCount &gt; sameFullnessLimit) {
			prgbarStatus = "preparing";
			print("Nothing comes, stop buffering!");
		}
	}
</onBuffering>

<onPreparing>
	rebufferSec = bufferedSec / 4;
	print("rebufferSec adjusted:", rebufferSec, bufferedSec);

	executeScript("hidePopup");
	if (firstBuf == 1) {
		playAtTime(0);
		videoPaused = 0;
	} else {
		postMessage("video_play");
	}
	firstBuf     = 0;
	prgbarStatus = "playing";
	}
</onPreparing>

<onPlaying>
	restSec = totalSec - playElapsed;
	if (bufferedSec &lt; rebufferSec &amp;&amp; restSec &gt; rebufferSec) {
		print("Starting rebuffering");
		postMessage("video_pause");
		lastFullness      = 0;
		sameFullnessCount = 0;
		prgbarStatus = "buffering";
	}
</onPlaying>


<onRefresh>
	if (isFirstStart == 1) {
		showIdle();
		isFirstStart = 0;
		setRefreshTime(1000);
		playItemURL(-1, 1);
		playItemURL(videoUrl, 3, bufMinBytes, "mediaDisplay", "previewWindow");
	} else {
		videoProgress = getPlaybackStatus();
		playElapsed   = getStringArrayAt(videoProgress, 0);
		totalSec      = getStringArrayAt(videoProgress, 1);
		bufferedSec   = getStringArrayAt(videoProgress, 2);
		playStatus    = getStringArrayAt(videoProgress, 3);
		
		if ( proxy == 1) {
			current = readStringFromFile("/tmp/current.seg"); 
			totalSec = getStringArrayAt(current, 1); 
			playElapsed = getStringArrayAt(current, 0);
		}
		print("Player status:", prgbarStatus, playStatus, "Time:", playElapsed, "/", totalSec, "(", bufferedSec, ")", rebufferSec);

		if (playStatus == 0) {
			postMessage("return");
		} else {
			if (prgbarStatus == "playing") {
				executeScript("onPlaying");
			}
			if (prgbarStatus == "buffering") {
				executeScript("onBuffering");
			}
			if (prgbarStatus == "preparing") {
				executeScript("onPreparing");
			}
		}
	}

	if (popupTimeout &gt; 0) {
		popupTimeout = Minus(popupTimeout, 1);
		if (popupTimeout &lt; 1) {
			executeScript("hidePopup");
		}
	}
</onRefresh>

<!-- GUI elements subroutines -->
<showBufferingPopup>
	progressColor = "0:127:255";
	popupTimeout  = 5;
	popupHidePos  = 0;

	if ((curFullness &gt; 75) &amp;&amp; (previewHidePos != 100)) {
		previewHidePos = 100;
	}

	if (playStatus == 2) {
		progressValue = curFullness;
		stateMid	  = "";
		stateLeft	  = "Кеширование...";
		stateRight	  = Integer(progressValue) + "%";
	} else {
		stateMid	  = "Соединение...";
		stateLeft	  = "";
		stateRight	  = "";
	}
	redrawDisplay("widget");
</showBufferingPopup>

<showSeekingPopup>
	SecondTime = playElapsed; executeScript("SecondToString"); stateLeft = SecondTime;
	if (popupTimeout == 0) {
		progressColor = "200:200:200";
		if (proxy == 1) {
			isSeekable = "1";
		} else {
		    isSeekable = videoIsSeekable();
		}
		if (isSeekable == null) {
			print("Cannot seek!\n");
			stateMid   = "Это видео нельзя перемотать";
			SecondTime = totalSec; executeScript("SecondToString"); stateRight = SecondTime;
		} else {
			wishedPos  = playElapsed;
			stateMid   = "";
			SecondTime = totalSec; executeScript("SecondToString"); stateRight = SecondTime;
			executeScript("updateSeekingPopup");
		}
	}
	popupTimeout = 5;
	popupHidePos = 0;
	redrawDisplay("widget");
</showSeekingPopup>

<updateSeekingPopup>
	if (isSeekable != null &amp;&amp; totalSec != 0 &amp;&amp; wishedPos != -1) {
		progressValue	= wishedPos * 100 / totalSec;
		progressCurr	= playElapsed * 100 / totalSec;
		SecondTime = wishedPos; executeScript("SecondToString"); stateLeft = SecondTime;
	}
	redrawDisplay("widget");
</updateSeekingPopup>

<hidePopup>
	barStatus = "offstatus";
	popupTimeout  = 0;
	popupHidePos  = 100;
	wishedPos     = -1;
	stateMid      = "";
	stateLeft     = "";
	stateRight    = "";
	progressValue = 0;
	progressCurr  = -1;
	redrawDisplay("widget");
</hidePopup>

<toggleAspectRatio>
	currentAspectRatio = getCurrentSetting("$[ASPECT_RATIO]");
	if (null == originalAspectRatio) { originalAspectRatio = currentAspectRatio; }
	if 			(currentAspectRatio=="$[PAN_SCAN_4_BY_3]")		{ setAspectRatio("$[LETTER_BOX_4_BY_3]");
	} else if	(currentAspectRatio=="$[LETTER_BOX_4_BY_3]")	{ setAspectRatio("$[WIDE_16_BY_9]");
	} else if	(currentAspectRatio=="$[WIDE_16_BY_9]")			{ setAspectRatio("$[WIDE_16_BY_10]");
	} else if	(currentAspectRatio=="$[WIDE_16_BY_10]")		{ setAspectRatio("$[PAN_SCAN_4_BY_3]");
	}
</toggleAspectRatio>

<VideoCompleted>	def = 2;  isNSRtr = 1; executeScript("GetVideo"); </VideoCompleted>
<PrevVideo>			def = -1; isNSRtr = 0; executeScript("GetVideo"); </PrevVideo>
<NextVideo>			def = 1;  isNSRtr = 0; executeScript("GetVideo"); </NextVideo>

<GetVideo>
	if (nxtBt == 1) {
		showIdle();
		setRefreshTime(-1);
		df = def; if (df == 2 ) df = 1;
		urln = mosUrl + "?page=" + srvName + "_next" + "&amp;next=" + df + "&amp;id=" + baseUrl;
		urln = getUrl(urln);
		if (null != urln)) {
			newCaption	= getStringArrayAt(urln, 3);
			newImage	= getStringArrayAt(urln, 2);
			newCaption	= getStringArrayAt(urln, 0);
			newVideoUrl	= getStringArrayAt(urln, 1);
			if (newCaption != null &amp;&amp; newCaption != "" &amp;&amp; newCaption != "none") caption  = newCaption;
			if (newImage != null &amp;&amp; newImage != "" &amp;&amp; newImage != "none") preview = newImage;
			if (newVideoUrl != null &amp;&amp; newVideoUrl != "" &amp;&amp; newVideoUrl != "none") {
				playItemURL(-1, 1);
				baseUrl = newVideoUrl;
				isNSRtr = 0;
				doseek = 0;
				executeScript("initData");
			}
		}
		if (isNSRtr == 1) postMessage("return");
		setRefreshTime(1000);
	}
	if (isNSRtr == 1) postMessage("return");
</GetVideo>

<mediaDisplay name="threePartsView" idleImageXPC="87.5" idleImageYPC="89" idleImageWidthPC="5" idleImageHeightPC="6" itemPerPage="0">
	<!-- idleImage -->

<idleImage><?= $idlespath ?>/idle1.png</idleImage>
<idleImage><?= $idlespath ?>/idle2.png</idleImage>
<idleImage><?= $idlespath ?>/idle3.png</idleImage>
<idleImage><?= $idlespath ?>/idle4.png</idleImage>
<idleImage><?= $idlespath ?>/idle5.png</idleImage>
<idleImage><?= $idlespath ?>/idle6.png</idleImage>
<idleImage><?= $idlespath ?>/idle7.png</idleImage>
<idleImage><?= $idlespath ?>/idle8.png</idleImage>


<previewWindow windowColor="0:0:0" offsetXPC="0" offsetYPC="0" widthPC="100" heightPC="100"></previewWindow>
	<-- preview --->
	<image redraw="yes"> <offsetXPC>10</offsetXPC> <offsetYPC>12</offsetYPC> <heightPC>72</heightPC>
		<widthPC><script>80 - previewHidePos;</script></widthPC>
		<script>if (preview == "http://st.kp.yandex.net/images/movies/poster_none.png") preview = logo; 
				preview;
		</script>
	</image>

	<text redraw="yes" offsetXPC="0" offsetYPC="86.5" widthPC="100" heightPC="14" backgroundColor="0:0:0">
		<widthPC><script>100 - popupHidePos;</script></widthPC>
		<script>"";</script>
	</text>
	<text redraw="yes" offsetXPC="0" offsetYPC="86" widthPC="100" heightPC="0.8" backgroundColor="14:27:33">
		<widthPC><script>100 - popupHidePos;</script></widthPC>
		<script>"";</script>
	</text>
	<text redraw="yes" offsetXPC="5" offsetYPC="86.5" widthPC="90" heightPC="5.5" foregroundColor="255:230:0" fontSize="15" align="center">
		<widthPC><script>90 - popupHidePos;</script></widthPC>
		<foregroundColor><script>captionColor;</script></foregroundColor>
		<script>caption;</script>
	</text>
	<!-- progressbar -->
	<text redraw="yes" offsetXPC="22" offsetYPC="92.8" widthPC="55" heightPC="1.6" backgroundColor="30:62:77">
		<widthPC><script>if (stateMid != "") -1; else 55 - popupHidePos;</script></widthPC>
		<script>"";</script>
	</text>
	<text redraw="yes" offsetXPC="22" offsetYPC="92.8" widthPC="55" heightPC="1.6">
		<backgroundColor><script>progressColor;</script></backgroundColor>
		<widthPC><script>if (stateMid != "") -1; else 55 * progressValue / 100 - popupHidePos;</script></widthPC>
		<script>"";</script>
	</text>
	<text redraw="yes" offsetXPC="22" offsetYPC="92.0" widthPC="0.3" heightPC="3.2" backgroundColor="255:0:0">
		<offsetXPC>
			<script>
				if (progressCurr &gt; 0 || progressCurr == 0) progressCurr = playElapsed * 100 / totalSec;
				if (progressCurr &lt; 0) -1; else 55 * progressCurr / 100 - -22 - popupHidePos;
			</script></offsetXPC>
		<script>"";</script>
	</text>

	<!-- statuses / time stamps -->
	<text redraw="yes" offsetXPC="4" offsetYPC="92.0" widthPC="22" fontSize="12" heightPC="3.5" foregroundColor="255:255:255" align="right">
		<widthPC><script>17 - popupHidePos;</script></widthPC>
		<script>
				SecondTime = playElapsed; executeScript("SecondToString"); stateCur = SecondTime;
				if (prgbarStatus == "preparing" || prgbarStatus == "buffering" || barStatus == "status" ) stateLeft;
					else
				if ( diff == 0) stateCur; else stateCur + "●" + stateLeft;
		</script>
	</text>
	<text redraw="yes" offsetXPC="78" offsetYPC="92.0" widthPC="22" fontSize="12" heightPC="3.5" foregroundColor="255:255:255" align="left">
		<widthPC><script>17 - popupHidePos;</script></widthPC>
		<script>stateRight;</script>
	</text>
	<text redraw="yes" offsetXPC="22" offsetYPC="92.0" widthPC="55" fontSize="12" heightPC="3.5" foregroundColor="100:115:130" align="center">
		<foregroundColor> <script> clr = "100:115:130"; if (barStatus == "status" ) clr = "50:200:255"; clr; </script> </foregroundColor>
		<fontSize><script> font = 12; if ( barStatus == "status" ) font = 13; font</script></fontSize>
		<offsetXPC><script> XPC = 22; if ( barStatus == "status" ) XPC = 9; XPC;</script></offsetXPC>
		<widthPC><script> wPC = 57 - popupHidePos; if ( barStatus == "status" ) wPC = 80; wPC;</script></widthPC>
		<script>if ( barStatus == "status" ) stateMid = getURL(urlS); stateMid;</script>
	</text>

<onUserInput>
	key = currentUserInput();
	res = "true";
	print("Got input:", key);

	if (1 != 1) {
		if (key == "video_stop") {
			postMessage("return");
		} else if (key == "return" || key == "video_volume_up" || key == "video_volume_down" || key == "video_volume_mute") {
			res = "false";
		}
	} else {
		if (key == "video_pause") {
			executeScript("showSeekingPopup");
			videoPaused = 1;
			res = "false";
		} else if (key == "video_play") {
			if (videoPaused == 1) {
				executeScript("hidePopup");
				videoPaused = 0;
				res = "false";
			} else {
				executeScript("showSeekingPopup");
				videoPaused = 1;
				postMessage("video_pause");
			}
		} else if (key == "setup" || key == "guide") {
			videoPaused = 1;
			res = "false";
		} else if (key == "pageup" &amp;&amp; nxtBt == 1) {
			executeScript("PrevVideo");
		} else if (key == "pagedown" &amp;&amp; nxtBt == 1 ) {
			executeScript("NextVideo");
		} else if (key == "display") { executeScript("Tstatus");
		} else if (key == "menu") {
			if (previewHidePos == 0) previewHidePos = 100; else previewHidePos = 0;
		} else if (key == "video_frwd" || key == "zoom") {
			executeScript("toggleAspectRatio");
		} else if (key == "video_stop") {
			postMessage("return");
		} else if (key == "video_completed") {
			executeScript("VideoCompleted");
		} else if (popupTimeout &gt; 0 &amp;&amp; (key == "enter" || key == "return" || key == "display")) {
			if (key == "enter" &amp;&amp; wishedPos &gt; -1 &amp;&amp; wishedPos != playElapsed) {
				if ( proxy == 1) {
					doseek = wishedPos;
					playItemURL(-1, 1);
					executeScript("initData");
				} else {
					playAtTime(wishedPos);
					videoPaused = 0;
				}
			}
			executeScript("hidePopup");
		} else if (playStatus == 2 &amp;&amp; prgbarStatus == "playing" &amp;&amp; 
				(key == "right" || key == "left" || key == "up" || key == "down" || key == "enter" || key == "display"))
		{
			executeScript("showSeekingPopup");
			if (isSeekable != null) {
				if (key == "right") {		diff = 30;
				} else if (key == "left") {	diff = -10;
				} else if (key == "up") {	diff = 180;
				} else if (key == "down") {	diff = -60;
				} else {					diff = 0;
				}

				wishedPos = Add(wishedPos, diff);
				if (wishedPos &lt; 0) {
					wishedPos = 0;
				} else if (wishedPos &gt; totalSec) {
					wishedPos = totalSec;
				}
			}
			executeScript("updateSeekingPopup");
		} else if (key == "return" || key == "video_frwd"  || key == "video_ffwd"  || key == "video_volume_up" || key == "video_volume_down" || key == "menu" || key == "video_volume_mute")
		{
			res = "false";
		}

		if (res == "true") {
			redrawDisplay("widget");
		}
	}
	res;
</onUserInput>
</mediaDisplay>
</rss>
<?php
?> 