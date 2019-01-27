<?php
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

function clean_cert($cert_dir, $force = false)
{
    global $CERT_DATA;

    $cert_dir_full = $CERT_DATA."/".$cert_dir;

    $cert_nm = $cert_dir;
    if (substr($cert_dir, -7) == ".closed")
    {
        $cert_nm = substr($cert_dir, 0, strlen($cert_dir) - 7);
    }

    if (!is_dir($cert_dir_full))
    {
        return;
    }

    if (!$force && file_exists($cert_dir_full."/".$cert_nm.".json"))
    {
        return;
    }

    // delete files
    $files = scandir($cert_dir_full);
    foreach ($files as $file)
    {
        if ($file == "." || $file == "..")
        {
            continue;
        }

        @unlink($cert_dir_full.'/'.$file);
    }

    @rmdir($cert_dir_full);

    log_write("clean_cert(".$cert_dir.") = Success", "INFO");
}

function get_ca_master_password($type = "master_pw")
{
    global $CERT_DATA;
    $master_pw_name = "key.json";

    if (!is_file($CERT_DATA."/".$master_pw_name))
    {
        return false;
    }

    if (($master_pw = json_decode(@file_get_contents($CERT_DATA."/".$master_pw_name), true)) == null)
    {
        return false;
    }

    return decrypt($master_pw[$type]);
}

function set_ca_master_password($password, $type = "master_pw")
{
    global $CERT_DATA;
    $master_pw_name = "key.json";

    $master_pw = array();
    if (is_file($CERT_DATA."/".$master_pw_name))
    {
        if (($master_pw = json_decode(@file_get_contents($CERT_DATA."/".$master_pw_name), true)) == null)
        {
            $master_pw = array();
        }
    }

    $master_pw[$type] = encrypt($password);

    file_put_contents($CERT_DATA."/".$master_pw_name, json_encode($master_pw, JSON_PRETTY_PRINT));
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

function strtohex($x)
{
    $s = '';
    foreach (str_split($x) as $c)
    {
        $s .= sprintf("%02X", ord($c));
    }

    return($s);
}

function encrypt($plain)
{
    // TODO 암호화키와 기본 iv 는 외부에서 주입하도록
    $key = "certmgrp";
    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    $encrypted = openssl_encrypt($plain, "aes-256-cbc", $key, 0, $iv);

    // -nopad 옵션은 불필요. -d: 디코딩
    // openssl enc -aes-256-cbc -in t.txt -base64 -nosalt -K ".strtohex($key)." -iv ".strtohex($iv)

    return $encrypted;
}

function decrypt($encrypted_b64)
{
    $key = "certmgrp";
    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    $plain = openssl_decrypt($encrypted_b64, "aes-256-cbc", $key, 0, $iv);

    return $plain;
}

function log_write($message, $level="INFO", $supplement = array())
{
    global $_SESSION;
    global $CERT_DATA;

    $logfnm = $CERT_DATA."/".date('Ymd').".log";

    $trace = debug_backtrace();

    $file = $trace[0]['file'];
    $func = $trace[1]['function'];
    $line = $trace[0]['line'];

    $jlog = array(
        'timestamp' => date('c')
        ,'user'     => $_SESSION['user_name'].'('.$_SESSION['user_id'].')'
        ,'file'     => $file
        ,'function' => $func
        ,'line'     => $line
        ,'level'    => $level
        ,'message'  => $message
        ,'trace'    => $supplement
    );

    file_put_contents($logfnm, json_encode($jlog)."\n", FILE_APPEND | LOCK_EX);
}

?>
