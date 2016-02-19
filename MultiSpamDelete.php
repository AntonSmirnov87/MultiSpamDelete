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
				array_push($emailEntry, $fileName, $emailDate . "  -  " . $emailSubject);
				array_push($emails, $emailEntry);
			}
		}
	}
	return $emails;
}
function deleteMultiSpams($folder, $trashFolder){
	$root = $_SERVER['DOCUMENT_ROOT'];
	$emailFolder = $root . $folder;
	$trashFolder = $root . $trashFolder;
	$emails = formEmailArray($emailFolder);
	global $totalDeletions;

	$emailSubjects = array_column($emails, 1, 0);
	$subjectCount = array_count_values($emailSubjects);
	$repeatedSubjects = [];
	foreach($subjectCount as $subject => $count){
		if($count > 1){
			$totalDeletions += $count;
			echo "\t\tDELETED " . $count . " copies of: " . $subject . "\n";
			array_push($repeatedSubjects, $subject);
		}
	}
	$multiSpams = [];
	$multiSpams = array_keys(array_intersect($emailSubjects, $repeatedSubjects));
	if(count($multiSpams) > 0){
		foreach($multiSpams as $fileName){
			rename($emailFolder . $fileName,  $trashFolder . $fileName);
		}
	}
}

date_default_timezone_set('America/New_York');
echo "\tFiles deleted on: " . date('Y / m (M) / d (l)  -  g:i A (G:i:s)') . "\n";
deleteMultiSpams("/home/<MAIL FOLDER ON YOUR SERVER>/", "/home/<MAIL FOLDER ON YOUR SERVER>/.Trash/cur/");
echo "\tTotal number of files deleted: " . $totalDeletions . "\n\n";

?>