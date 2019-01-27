<?php
/****************************************************************************/
/* 호스트 인증서를 pkcs12 포맷으로 컨버전                                   */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_POST, $_SESSION;
    global $CERT_DATA, $OPENSSL_EXEC;

    // --------------------------------------------------------------------- //
    // 루트인증서 체크
    // --------------------------------------------------------------------- //
    if (($rootCaInfo = get_rootca()) === false)
    {
        error_exit_json("루트 인증서가 없습니다.");
    }

    $certName = $_POST['cert_name'];
    if (($hostCertInfo = get_cert($certName)) === false)
    {
        error_exit_json("인증서를 찾을 수 없습니다.");
    }

    // 폐기된 인증서는 제외
    if ($hostCertInfo['closed'] == 'Y')
    {
        error_exit_json("폐기된 인증서입니다.");
    }

    // --------------------------------------------------------------------- //
    // 입력항목 검증
    // --------------------------------------------------------------------- //
    $error_form = array();
    if (!input_value_check($_POST['pkcs_pw'], '^[\\x20-\\x7E]*$', 1, 32))
    {
        $error_form['pkcs_pw'] = "Error Password";
    }

    if (strcmp($_POST['pkcs_pw'], $_POST['pkcs_pw2']) != 0)
    {
        $error_form['pkcs_pw'] = "Password missmatch";
        $error_form['pkcs_pw2'] = "Password missmatch";
    }

    if (count($error_form) > 0)
    {
        error_exit_json("Input validation check", null, $error_form);
    }

    // --------------------------------------------------------------------- //
    // 변수 생성
    // --------------------------------------------------------------------- //
    $createDate = date("YmdHis");

    $dir_cert          = $CERT_DATA."/".$hostCertInfo['certificateName'];
    $pfx_file_name     = $hostCertInfo['certificateName']."_".$createDate.".pfx";

    if (isset($hostCertInfo['closed']) && $hostCertInfo['closed'] == "Y")
    {
        $dir_cert .= ".closed";
    }

    $file_cert_encpw   = $dir_cert."/encpw.txt";
    $file_pkcs_encpw   = $dir_cert."/encpw2.txt";

    // password file 생성
    file_put_contents($file_cert_encpw, get_ca_master_password());
    file_put_contents($file_pkcs_encpw, $_POST['pkcs_pw']);
    //@unlink($file_cert_encpw);
    //@unlink($file_pkcs_encpw);

    // --------------------------------------------------------------------- //
    // pfx 생성 (인증서 + 개인키) (Windwos Server 또는 java)
    // --------------------------------------------------------------------- //

    $pfx_exec = $OPENSSL_EXEC.' pkcs12 -export '.
        '-in "'.$dir_cert.'/'.$hostCertInfo['crtFile'].'" '.
        '-inkey "'.$dir_cert.'/'.$hostCertInfo['privKeyFile'].'" '.
        '-passin "file:'.$file_cert_encpw.'" '.
        '-passout "file:'.$file_pkcs_encpw.'" '.
        '-name "'.$hostCertInfo['certificateName'].'" '.
        '-out "'.$dir_cert.'/'.$pfx_file_name.'" '.
        //'-certfile "'.$CERT_DATA.'/rootca/'.$rootCaInfo['crtFile'].'" '.
        '-CAfile "'.$CERT_DATA.'/rootca/'.$rootCaInfo['crtFile'].'" -chain';

    exec($pfx_exec.' 2>&1', $pfx_out, $result);
    if ($result != 0)
    {
        @unlink($file_cert_encpw);
        @unlink($file_pkcs_encpw);

        log_write($hostCertInfo['certificateName'].": pkcs12: ".$pfx_exec, "ERROR", $pfx_out);
        error_exit_json("<b>pkcs12 pfx 인증서 생성 오류입니다.</b>\n".
            "<p>".$pfx_exec."</p>\n<p>".implode("<br>\n", $pfx_out)."</p>");
    }

    log_write($hostCertInfo['certificateName'].": pkcs12 created. (".$pfx_file_name.") = Success");

    @unlink($file_cert_encpw);
    @unlink($file_pkcs_encpw);

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //

    $output = array();

    $output['pkcs12_file'] = $pfx_file_name;

    echo json_encode($output);
}
?>
