<?
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

    if (!input_value_check($_REQUEST['n'], '^[a-zA-Z0-9-_]*$', 1))
    {
        error_exit_json("인증서 이름 지정 오류입니다.");
    }

    if (!input_value_check($_REQUEST['f'], '^[a-zA-Z0-9-_\\.]*$', 1))
    {
        error_exit_json("인증서 파일명 지정 오류입니다.");
    }

    $filePathName = $CERT_DATA."/".$_REQUEST['n']."/".$_REQUEST['f'];
    if (!file_exists($filePathName))
    {
        error_exit_json("인증서 파일을 찾을 수 없습니다.");
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
