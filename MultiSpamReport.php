<?php

function reportMultiSpams($file){
	$root = $_SERVER['DOCUMENT_ROOT'];
	$reportFile = $root . $file;
	echo "Report Sent: " . date('Y / m (M) / d (l)  -  g:i A (G:i:s)') . "\n\n";
	if(is_file($reportFile)){
		$reportContents = file_get_contents($reportFile);
		echo $reportContents;
		unlink($reportFile);
	}else{
		echo "Cannot find spam deletion report. Please contact your webmaster.";
	}
}

reportMultiSpams("/home/<LOG FOLDER ON YOUR SERVER>/MultiSpamReport.log");

?>