<?php
	$izb_path = dir_name .'/izb.dat';
	if (!file_exists($izb_path)) {$izbr = array();}
	else {
		$izbr = file_get_contents($izb_path);
		if (strpos($izbr,'array')===false ) $izbr = @gzinflate($izbr);
		//file_put_contents('/tmp/izb',$izbr);
		$izbr = str_replace(array('<?php','?>'),'',$izbr);
		$izbr = trim($izbr);
		eval($izbr);
	}
?>