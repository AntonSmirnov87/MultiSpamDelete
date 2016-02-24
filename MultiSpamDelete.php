<?php

$totalDeletions = 0;

if(!function_exists("array_column")){
    function array_column($array, $column_value, $column_key = null){
    	$tempArray = [];
    	foreach($array as $index){
    		if(!is_null($column_key) && array_key_exists($column_key, $array)){
	    		$tempArray[$index[$column_key]] = $index[$column_value];
	    	}else{
	    		array_push($tempArray, $index[$column_value]);
	    	}
		}
    	return $tempArray;
    }
}

function formEmailArray($emailFolder){
	$emails = [];
	
	if(is_dir($emailFolder))	{
		$email = scandir($emailFolder);
		foreach($email as $fileIndex => $fileName){
			$emailFile = $emailFolder . $fileName;
			if(is_file($emailFile)){
				$emailContents = explode("\n", file_get_contents($emailFile, NULL, NULL, 0, 5000));
				$emailDate = "";
				$emailSubject = "";
				foreach($emailContents as $lineIndex => $lineContents){
					if($emailDate === "" && strpos($lineContents, "Date: ") === 0){
						$emailDate = substr($lineContents, 6, 22);
					}elseif($emailSubject === "" && strpos($lineContents, "Subject: ") === 0){
						$emailSubject = preg_split("/[0-9]{5,8}/", substr($lineContents, 9))[0];
					}
				}
				$emailEntry = [];
				array_push($emailEntry, $fileName, $emailDate, $emailSubject);
				array_push($emails, $emailEntry);
			}
		}
	}
	return $emails;
}

function deleteMultiSpams($folder, $trashFolder, $minuteTolerance){
	global $totalDeletions;
	$multiSpams = [];
	$root = $_SERVER['DOCUMENT_ROOT'];
	$emailFolder = $root . $folder;
	$trashFolder = $root . $trashFolder;
	$emails = formEmailArray($emailFolder);
	$numEmails = count($emails);
	for($i = 0; $i < $numEmails; $i++){
		if(!in_array($emails[$i][0], $multiSpams)){
			$numCopies = 1;
			for($j = $i + 1; $j < $numEmails; $j++){
				# If subjects are identical and date/times are within chosen tolerance minutes of each other
				if($emails[$i][2] === $emails[$j][2] && isTimeWithinRange($emails[$i][1], $emails[$j][1], $minuteTolerance)){
					$numCopies++;
					array_push($multiSpams, $emails[$j][0]);
				}
			}
			if($numCopies > 1){
				array_push($multiSpams, $emails[$i][0]);
				$totalDeletions += $numCopies;
				echo "\t\tDELETED " . $numCopies . " copies of: " . $emails[$i][1] . " - " . $emails[$i][2] . "\n";
			}
		}
	}
	if(count($multiSpams) > 0){
		foreach($multiSpams as $fileName){
			rename($emailFolder . $fileName,  $trashFolder . $fileName);
		}
	}
}

function isTimeWithinRange($origTimeString, $compTimeString, $minuteRange){
	$origTime = convertTimeToDecimal($origTimeString)[0];
	$compTime = convertTimeToDecimal($compTimeString)[0];
	$daysInMonth = convertTimeToDecimal($origTimeString)[1];

	if(round(abs($origTime - $compTime), 10) <= round(($minuteRange / (17280 * $daysInMonth)), 10)){
		return true;
	}else{
		return false;
	}
}

function convertTimeToDecimal($inputTimeString){
	$inputArray = explode(" ", $inputTimeString);
	$year = $inputArray[3];
	$leapYear = ($year % 4 === 0) ? 1 : 0;
	$arrMonth = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	$arrDaysInMonth = array(31,28 + $leapYear,31,30,31,30,31,31,30,31,30,31);
	$monthString = $inputArray[2];
	$monthIndex = array_search($monthString, $arrMonth);
	$month = $monthIndex + 1;
	$day = $inputArray[1];
	$time = preg_split("/:/", $inputArray[4]);
	$hour = $time[0];
	$minute = $time[1];

	$decTime = (((((((($minute / 60) + $hour) / 24) + $day) / $arrDaysInMonth[$monthIndex]) + $month) / 12) + $year);
	return array($decTime, $arrDaysInMonth[$monthIndex]);
}

date_default_timezone_set('America/New_York');
echo "\tFiles deleted on: " . date('Y / m (M) / d (l)  -  g:i A (G:i:s)') . "\n";
deleteMultiSpams("/home/<MAIL FOLDER ON YOUR SERVER>/", "/home/<MAIL FOLDER ON YOUR SERVER>/.Trash/cur/", 3);
echo "\tTotal number of files deleted: " . $totalDeletions . "\n\n";

?>