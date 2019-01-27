<?php
/****************************************************************************/
/* 호스트 인증서 검증API                                                    */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_POST, $_SESSION;
    global $CERT_DATA, $OPENSSL_EXEC;

    // role 검증 없음

    $certName = $_POST['cert_name'];
    if (($hostCertInfo = get_cert($certName)) === false)
    {
        error_exit_json("인증서를 찾을 수 없습니다.");
    }

    // --------------------------------------------------------------------- //
    // 변수 생성
    // --------------------------------------------------------------------- //
    $dir_cert          = $CERT_DATA."/".$hostCertInfo['certificateName'];

    if (isset($hostCertInfo['closed']) && $hostCertInfo['closed'] == "Y")
    {
        $dir_cert .= ".closed";
    }

    $file_cert_encpw   = $dir_cert."/".$hostCertInfo['certificateName']."_encpw.txt";

    // password file 생성
    file_put_contents($file_cert_encpw, get_ca_master_password());
    //@unlink($file_cert_encpw);

    // --------------------------------------------------------------------- //
    // Private key 검증
    // --------------------------------------------------------------------- //
    $rsa_exec = $OPENSSL_EXEC.' rsa -in "'.$dir_cert.'/'.$hostCertInfo['privKeyFile'].'" '.
        '-passin "file:'.$file_cert_encpw.'"';

    exec($rsa_exec.' 2>&1', $rsa_out, $result);
    if ($result != 0)
    {
        @unlink($file_cert_encpw);
        error_exit_json("<b>키를 검증할 수 없습니다.</b>\n".
            "<p>".$rsa_exec."</p>\n<p>".implode("<br>\n", $rsa_out)."</p>");
    }

    // --------------------------------------------------------------------- //
    // 인증서요청정보 검증
    // --------------------------------------------------------------------- //
    $csr_exec = $OPENSSL_EXEC.' req -text -in "'.$dir_cert.'/'.$hostCertInfo['csrFile'].'" ';

    exec($csr_exec.' 2>&1', $csr_out, $result);
    if ($result != 0)
    {
        @unlink($file_cert_encpw);
        error_exit_json("<b>인증요청서를 검증할 수 없습니다.</b>\n".
            "<p>".$csr_exec."</p>\n<p>".implode("<br>\n", $csr_out)."</p>");
    }

    // --------------------------------------------------------------------- //
    // 인증서 검증
    // --------------------------------------------------------------------- //
    $x509_exec = $OPENSSL_EXEC.' x509 -text -in "'.$dir_cert.'/'.$hostCertInfo['crtFile'].'" ';
    exec($x509_exec.' 2>&1', $x509_out, $result);
    if ($result != 0)
    {
        @unlink($file_cert_encpw);
        error_exit_json("<b>인증서를 검증할 수 없습니다.</b>\n".
            "<p>".$x509_exec."</p>\n<p>".implode("<br>\n", $x509_out)."</p>");
    }

    @unlink($file_cert_encpw);

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //

    $log = "<b>개인(비밀)키:</b><pre>".implode("\n", $rsa_out)."</pre><br>\n";
    $log .= "<b>인증요청서(CSR):</b><pre>".implode("\n", $csr_out)."</pre><br>\n";
    $log .= "<b>인증서(X509 Certificate):</b><pre>".implode("\n", $x509_out)."</pre>";

    $output = array();

    $output['log'] = $log;

    echo json_encode($output);
}
?>
