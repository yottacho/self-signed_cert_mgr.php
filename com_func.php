<?
/****************************************************************************/
/* 공통함수                                                                 */
/****************************************************************************/

function error_exit_json($error_msg, $http_code = 500, $form_id = array())
{
    $error = array();
    $error["error"] = $error_msg;
    $error["form"] = $form_id;

    if (!headers_sent())
    {
        if ($http_code === null || $http_code == 0)
            $http_code = 500;

        header("HTTP/1.0 ".$http_code." Internal Server Error", true, $http_code);
    }

    echo json_encode($error);

    ob_flush();
    exit;
}

function get_rootca()
{
    global $CERT_DATA;

    if (!is_dir($CERT_DATA."/rootca"))
    {
        return false;
    }

    if (!file_exists($CERT_DATA."/rootca/rootca.json"))
    {
        return false;
    }
    
    if (($rootCaInfo = json_decode(@file_get_contents($CERT_DATA."/rootca/rootca.json"), true)) == null)
    {
        return false;
    }

    return $rootCaInfo;
}

function get_cert($cert_dir)
{
    global $CERT_DATA;

    if (!input_value_check($cert_dir, '^[a-zA-Z0-9-_\\.]*$', 1))
    {
        return false;
    }

    $cert_nm = $cert_dir;
    if (substr($cert_dir, -7) == ".closed")
    {
        $cert_nm = substr($cert_dir, 0, strlen($cert_dir) - 7);
    }

    if (!is_dir($CERT_DATA."/".$cert_dir))
    {
        //if (is_dir($CERT_DATA."/".$cert_dir.".closed"))
        //{
        //    $cert_dir .= ".closed";
        //}
        //else
        {
            return false;
        }
    }

    if (!file_exists($CERT_DATA."/".$cert_dir."/".$cert_nm.".json"))
    {
        return false;
    }
    
    if (($certInfo = json_decode(@file_get_contents($CERT_DATA."/".$cert_dir."/".$cert_nm.".json"), true)) == null)
    {
        return false;
    }

    return $certInfo;
}


function input_value_check($value, $matchPattern = "", $minLength = 0, $maxLength = 0)
{
    if ($minLength > 0 && strlen($value) < $minLength)
    {
        return false;
    }

    if ($maxLength > 0 && strlen($value) > $maxLength)
    {
        return false;
    }

    if ($minLength == 0 && strlen($value) == 0)
    {
        return true;
    }

    if (strlen($matchPattern) > 0 && preg_match('/'.$matchPattern.'/', $value) == 0)
    {
        return false;
    }

    return true;
}


function log_write($message, $level="INFO", $file=__FILE__, $func=__FUNCTION__, $line=__LINE__)
{
    global $_SESSION;
    global $CERT_DATA;

    $logfnm = $CERT_DATA.date('Ymd').".log";
    //var_dump(debug_backtrace());

    $jlog = array(
        'timestamp' => date('c')
        ,'user'     => $_SESSION['user_name'].'('.$_SESSION['user_id'].')'
        ,'file'     => $file
        ,'function' => $func
        ,'$line'    => $line
        ,'level'    => $level
        ,'message'  => $message
    );

    file_put_contents(logfnm, json_encode($jlog), FILE_APPEND | LOCK_EX);
}

?>
