<?php
//test
// devuelve la version de SINCRM desde JSON
function getVersion(){
    $versionData = json_decode(file_get_contents(dirname(__DIR__) . '/version.json'), true);
    return $versionData['full_version'] ?? 'v4.0.0-prerelease';
}

// devuelve los detalles completos de la version
function getVersionDetails(){
    return json_decode(file_get_contents(dirname(__DIR__) . '/version.json'), true);
}

// modifica la version de SINCRM (ahora actualiza el JSON)
function setVersion($aV){
    $data = getVersionDetails();
    $data['full_version'] = $aV;
    $data['updated_at'] = date('Y-m-d');
    file_put_contents('version.json', json_encode($data, JSON_PRETTY_PRINT));
}

//obtiene el tamaño de un archivo remoto
function remote_filesize($url) {
    static $regex = '/^Content-Length: *+\K\d++$/im';
    if (!$fp = @fopen($url, 'rb')) {
        return false;
    }
    if (
        isset($http_response_header) &&
        preg_match($regex, implode("\n", $http_response_header), $matches)
    ) {
        return (int)$matches[0];
    }
    return strlen(stream_get_contents($fp));
}

//convierte el tamaño de un archivo de bytes a unidades legibles por humanos
function fileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

//devuleve la fecha en formato español partiendo de un date
function dateAFecha ($date){
	if($date != '')
		return date("d-m-Y", strtotime($date));
	else
		return '';
}

//devuleve un date partiendo de fecha en formato español
function fechaADate ($fecha){
	if($fecha != '')
		return date("Y-m-d", strtotime(substr($fecha,6,9).'-'.substr($fecha,3,-5).'-'.substr($fecha,0,-8)));
	else
		return '';
}

//enmascara el numero de licencia
function enmascaraLicencia ($licencia){
    $licencia = substr_replace($licencia, str_repeat('X',$GLOBALS["enmascarar_licencia"]), sizeof($licencia)-$GLOBALS['enmascarar_licencia']-1);
    return $licencia;

}

function mysqli_result($res,$row=0,$col=0){
    $numrows = mysqli_num_rows($res);
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}
