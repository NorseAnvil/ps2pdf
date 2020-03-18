<?php
ini_set('max_execution_time',0);

function mediaTimeDeFormater($seconds)
{
    if (!is_numeric($seconds))
        throw new Exception("Invalid Parameter Type!");


    $ret = "";

    $hours = (string )floor($seconds / 3600);
    $secs = (string )$seconds % 60;
    $mins = (string )floor(($seconds - ($hours * 3600)) / 60);

    if (strlen($hours) == 1)
        $hours = "0" . $hours;
    if (strlen($secs) == 1)
        $secs = "0" . $secs;
    if (strlen($mins) == 1)
        $mins = "0" . $mins;

    if ($hours == 0)
        $ret = "The conversion process elapsed for: "."$mins"."m "."$secs"."s \n";
    else
        $ret = "The conversion process elapsed for: "."$hours"."h "."$mins"."m "."$secs"."s \n";

    return $ret;
}

function count_files_in_dir($path){
    $ite=new RecursiveDirectoryIterator($path);
    $bytestotal=0;
    $nbfiles=0;
    foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
        $info = pathinfo($cur);
		$extension = $info['extension'];
		if ($extension == 'ps') {
		$filesize=$cur->getSize();
        $bytestotal+=$filesize;
        $nbfiles++;
		}
    }
    $kilobytestotal=number_format(($bytestotal/1024));
    return array('total_files'=>$nbfiles,'total_size'=>$bytestotal);
}

function outputFiles($path,&$i,&$total){
    if(file_exists($path) && is_dir($path)){
        $result = scandir($path);
		// Filter out the current (.) and parent (..) directories e.g remove first two entries in the array
        $files = array_diff($result, array('.', '..'));
        if(count($files) > 0){
            foreach($files as $file){
				$info = pathinfo("$path/$file");
				$name = $info['filename'];
				$extension = $info['extension'];
                if(is_file("$path/$file")){
					if ($extension == 'ps') {
					$percent = round($i/$total*100,2);
					echo "Converting file: "."$path/$file"." ($percent%)\n";
					$command = 'utils\ghost\gs951w32\bin\gswin32c.exe ^ -sDEVICE=pdfwrite ^ -o "'."$path/$name".'.pdf" ^ "'."$path/$file".'"';
					exec($command);
					unlink("$path/$file");
					$i++;
					}
                } else if(is_dir("$path/$file")){
                    // Recursively call the function if directories found
				    echo "Converting files in folder: "."$path/$file \n";
                    outputFiles("$path/$file",$i,$total);
				}
            }
        } else{
            echo "No .ps files found in directory: "."$path"." \n";
        }
    } else {
        echo "ERROR: The directory: "."$path"." does not exist. \n";
	}
}

$path='files';
$filecount = count_files_in_dir($path);
echo "Total: {$filecount['total_files']} .ps files, {$filecount['total_size']} kilobytes \n";

$total = $filecount['total_files'];
$i = 1;
$startTime = time();

outputFiles($path,$i,$total);

$endTime = time();
$totalTime = $endTime-$startTime;
echo mediaTimeDeFormater($totalTime);
