<?php
include('security.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
// =========== https://github.com/ttodua/useful-php-scripts ================
// =========================================================================
//     zip_folder(__DIR__.'/path/to/input/folder',   __DIR__.'/path/to/output_zip_file.zip') ;
// =========================================================================
$id_competicion = $_GET['id_competicion'];




function zip_folder ($input_folder, $output_zip_file) {
	echo 'in';
    $zipClass = new ZipArchive();
    $input_folder = realpath($input_folder);
    $addDirDo = static function($input_folder, $name) use ($zipClass, &$addDirDo ) {
        $name .= '/';
        $input_folder .= '/';
        // Read all Files in Dir
        $dir = opendir ($input_folder);
        while ($item = readdir($dir))    {
            if ($item == '.' || $item == '..') continue;
            $itemPath = $input_folder . $item;
            if (filetype($itemPath) == 'dir') {
                $zipClass->addEmptyDir($name . $item);
                $addDirDo($input_folder . $item, $name . $item);
            } else {
                $zipClass->addFile($itemPath, $name . $item);
            }
        }
    };
    if($input_folder !== false && $output_zip_file !== false)
    {
        $res = $zipClass->open($output_zip_file, \ZipArchive::CREATE);
        if($res === true)   {
            $zipClass->addEmptyDir(basename($input_folder));
            $addDirDo($input_folder, basename($input_folder));
            $zipClass->close();
        }
        else   { exit ('Could not create a zip archive, migth be write permissions or other reason.'); }
    }
}

$path = './public/music/';
$path_competicion = $path.$id_competicion.'/';
$path_competicion_ordenado = $path.$id_competicion.' ordenado/';

// Construct the iterator
$it = new RecursiveDirectoryIterator($path);
	// Loop through files
	foreach(new RecursiveIteratorIterator($it) as $file) {
		if ($file->getExtension() == 'mp3') {
			$filename = str_replace($path_competicion,'',$file);
			$folder = explode(" - ", $file);
			$folder1 = $folder[1];
			$folder2 = explode("/",$folder[0])[4];

			$query= "SELECT orden FROM rutinas WHERE music_name like '%$filename%'";
			echo '<br>'.$file.'<br>';
			echo $query;
			$orden = mysqli_fetch_assoc(mysqli_query($connection, $query))['orden'];
			$new_file = $path_competicion_ordenado.$folder1.'/'.$folder2.'/'.$orden.' -'.$filename;
			if (!file_exists($path_competicion_ordenado.$folder1.'/'.$folder2.'/')) {
        		mkdir($path_competicion_ordenado.$folder1.'/'.$folder2.'/', 0777, true);
    		}
			copy($file, $new_file);

		}
	}








?>
