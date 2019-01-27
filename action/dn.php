<?php
/****************************************************************************/
/* 인증서 다운로드                                                          */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_REQUEST;
    global $CERT_DATA;

    $certName = $_REQUEST['n'];

    if (($hostCertInfo = get_cert($certName)) === false)
    {
        error_exit_json("Certificate not found.");
    }

    if (!input_value_check($_REQUEST['f'], '^[a-zA-Z0-9-_\\.]*$', 1))
    {
        error_exit_json("Certificate filename error.");
    }

    $filePathName = $CERT_DATA."/".$certName."/".$_REQUEST['f'];
    $ext = substr($filePathName, -4);

    if ($ext != ".crt" && $ext != ".csr" && $ext != ".key" && $ext != ".pfx")
    {
        error_exit_json("Not certificate file.");
    }

    if (!file_exists($filePathName))
    {
        error_exit_json("Certificate file not found.");
    }

    $fileSize = filesize($filePathName);
    $outFilePathName = mb_basename($filePathName);
    if (is_ie())
        $outFilePathName = utf2euc($outFilePathName);

    header("Pragma: public");
    header("Expires: 0");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".$outFilePathName."\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$fileSize);

    readfile($filePathName);

    // 하드코딩: pfx 파일은 송신 후 삭제
    if (substr($filePathName, -4) == ".pfx")
    {
        @unlink(@$filePathName);
    }

    exit;
}

function mb_basename($path)
{
    return end(explode('/',$path));
} 

function utf2euc($str)
{
    return iconv("UTF-8","cp949//IGNORE", $str);
}
function is_ie() {
    global $_SERVER;

    if (!isset($_SERVER['HTTP_USER_AGENT']))
        return false;
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
        return true; // IE8
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT 6.1') !== false)
        return true; // IE11
    return false;
}
