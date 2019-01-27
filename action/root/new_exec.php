<?php
/****************************************************************************/
/* 호스트 인증서 생성API                                                    */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_POST, $_SESSION;
    global $CERT_DATA, $OPENSSL_EXEC;

    if ($_SESSION['user_role'] != "admin")
    {
        error_exit_json("admin role only");
    }

    $dir_rootca = $CERT_DATA."/rootca";
    $file_openssl_conf = $CERT_DATA."/rootca/rootca_openssl.conf";
    $file_rootca_encpw = $CERT_DATA."/rootca/rootca_encpw.txt";
    $file_rootca_ref   = $CERT_DATA."/rootca/rootca.json";

/*
    $_POST['certificateName']               인증서 이름
    $_POST['countryName']                   국가
    $_POST['organizationName']              회사명
    $_POST['organizationalUnitName']        서버용도 또는 서버명
    $_POST['commonName']                    도메인명

    $_POST['days']                          유효기간
    $_POST['serial']                        일련번호

    $_POST['rootCertPassword']              루트인증서비밀번호
    $_POST['rootCertPassword2']             인증서비밀번호확인

*/

    // --------------------------------------------------------------------- //
    // 이미 인증서가 발급되어 있음
    // --------------------------------------------------------------------- //
    if (get_rootca() !== false)
    {
        error_exit_json("루트 인증서가 이미 생성되어 있습니다.");
    }

    // 다른 오류가 발생해서 생성 도중에 멈춘경우 clear
    clean_cert("rootca");

    // --------------------------------------------------------------------- //
    // 입력항목 검증
    // --------------------------------------------------------------------- //
    $error_form = array();
    if (!input_value_check($_POST['certificateName'], '^[a-zA-Z0-9-_]*$', 1, 64))
    {
        $error_form['certificateName'] = "Alphabet, number, hyphen(-), underbar(_) only. (Can't use space or special characters.)";
    }

    if (!input_value_check($_POST['countryName'], '^[A-Z]*$', 2, 2))
    {
        $error_form['countryName'] = "Use two letter code (ISO 3166-1 alpha-2)";
    }

    if (!input_value_check($_POST['organizationName'], '^[\\x20-\\x7E]*$', 1, 64))
    {
        $error_form['organizationName'] = "Error Organization Name";
    }

    if (!input_value_check($_POST['organizationalUnitName'], '^[\\x20-\\x7E]*$', 1, 64))
    {
        $error_form['organizationalUnitName'] = "Error Organizational Unit Name";
    }

    if (!input_value_check($_POST['commonName'], '^[\\x20-\\x7E]*$', 1, 64))
    {
        $error_form['commonName'] = "Error Common Name";
    }

    if (!input_value_check($_POST['days'], '^[0-9]*$', 1, 5))
    {
        $error_form['days'] = "Numbers only";
    }

    if (!input_value_check($_POST['serial'], '^[0-9]*$', 1, 5))
    {
        $error_form['serial'] = "Numbers only";
    }

    if (!input_value_check($_POST['rootCertPassword'], '^[\\x20-\\x7E]*$', 1, 32))
    {
        $error_form['rootCertPassword'] = "Error Root Cert Password";
    }

    if (strcmp($_POST['rootCertPassword'], $_POST['rootCertPassword2']) != 0)
    {
        $error_form['rootCertPassword'] = "Root Cert Password missmatch";
        $error_form['rootCertPassword2'] = "Root Cert Password missmatch";
    }

    if (count($error_form) > 0)
    {
        error_exit_json("Input validation check", null, $error_form);
    }

    // --------------------------------------------------------------------- //
    // rootca 디렉터리 생성
    // --------------------------------------------------------------------- //
    if (!is_dir($dir_rootca) && !mkdir($dir_rootca))
    {
        error_exit_json("Can't create directory");
    }

    // --------------------------------------------------------------------- //
    // 인증서 발급용 openssl config 생성
    // --------------------------------------------------------------------- //
    $openssl_rootca_config = array(
        '[ req ]',
        'default_bits            = 2048',
        'default_md              = sha1',
        'default_keyfile         = '.$_POST['certificateName'].'RootCa.key',
        'distinguished_name      = req_distinguished_name',
        'extensions              = v3_ca',
        'req_extensions          = v3_ca',
        '',
        '[ v3_ca ]',
        'basicConstraints       = critical, CA:TRUE, pathlen:0',
        'subjectKeyIdentifier   = hash',
        'keyUsage               = keyCertSign, cRLSign',
        'nsCertType             = sslCA, emailCA, objCA',
        '',
        '[req_distinguished_name ]',
        'countryName                     = Country Name (2 letter code)',
        'countryName_default             = '.$_POST['countryName'],
        'countryName_min                 = 2',
        'countryName_max                 = 2',
        '',
        'organizationName                = Organization Name (eg, company)',
        'organizationName_default        = '.$_POST['organizationName'],
        '',
        'organizationalUnitName          = Organizational Unit Name (eg, section)',
        'organizationalUnitName_default  = '.$_POST['organizationalUnitName'],
        '',
        'commonName                      = Common Name (eg, your name or your server\'s hostname)',
        'commonName_default              = '.$_POST['commonName'],
        'commonName_max                  = 64',
        ''
    );
    file_put_contents($file_openssl_conf, implode("\n", $openssl_rootca_config));

    // password file 생성
    file_put_contents($file_rootca_encpw, $_POST['rootCertPassword']);
    //@unlink($file_rootca_encpw);

    // --------------------------------------------------------------------- //
    // 참조 파일 정보 생성
    // --------------------------------------------------------------------- //
    $dt_calc = date_create();
    $dt_calc->add(date_interval_create_from_date_string($_POST['days']." days"));

    $startDate = date("Y/m/d H:i:sO");
    $endDate   = $dt_calc->format("Y/m/d H:i:sO");

    $certmgr_ref = array(
         'certificateName'        => $_POST['certificateName'].'RootCa'
        ,'countryName'            => $_POST['countryName']
        ,'organizationName'       => $_POST['organizationName']
        ,'organizationalUnitName' => $_POST['organizationalUnitName']
        ,'commonName'             => $_POST['commonName']
        ,'days'                   => ($_POST['days'] * 1)
        ,'startDateLocal'         => $startDate
        ,'endDateLocal'           => $endDate
        ,'serial'                 => $_POST['serial']
        ,'user'                   => $_SESSION['user_name'].'('.$_SESSION['user_id'].')'
        ,'privKeyFile'            => $_POST['certificateName'].'RootCa.key'
        ,'csrFile'                => $_POST['certificateName'].'RootCa.csr'
        ,'crtFile'                => $_POST['certificateName'].'RootCa.crt'
    );

    // --------------------------------------------------------------------- //
    // Keypair 생성 (genrsa)
    // --------------------------------------------------------------------- //
    $genrsa_exec = $OPENSSL_EXEC.' genrsa -aes256 -passout "file:'.$file_rootca_encpw.'" '.
        '-out "'.$dir_rootca.'/'.$certmgr_ref['privKeyFile'].'" 2048';

    exec($genrsa_exec.' 2>&1', $genrsa_out, $result);
    if ($result != 0)
    {
        // genrsa 명령이 뭔가 잘못돼도 result=0 인 경우가 있고, 이 경우 CSR 생성단계에서 오류 발생
        @unlink($file_rootca_encpw);

        log_write("RootCA: KEY: ".$genrsa_exec, "ERROR", $genrsa_out);
        error_exit_json("<b>Can't create keypair</b>\n".
            "<p>".$genrsa_exec."</p>\n<p>".implode("<br>\n", $genrsa_out)."</p>");
    }

    // --------------------------------------------------------------------- //
    // 인증서요청정보 생성(csr)
    // --------------------------------------------------------------------- //
    $csr_req_exec = $OPENSSL_EXEC.' req -batch -new -key "'.$dir_rootca.'/'.$certmgr_ref['privKeyFile'].'" '.
        '-passin "file:'.$file_rootca_encpw.'" -out "'.$dir_rootca.'/'.$certmgr_ref['csrFile'].'" '.
        '-config "'.$file_openssl_conf.'"';

    exec($csr_req_exec.' 2>&1', $csr_req_out, $result);
    if ($result != 0)
    {
        @unlink($file_rootca_encpw);

        log_write("RootCA: CSR: ".$csr_req_exec, "ERROR", $csr_req_out);
        error_exit_json("<b>Can't create csr(Certificate Signing Request) file!</b>\n".
            "<p>".$csr_req_exec."</p>\n<p>".implode("<br>\n", $csr_req_out)."</p>");
    }

    // --------------------------------------------------------------------- //
    // x509 인증서 생성
    // --------------------------------------------------------------------- //
    $x509_exec = $OPENSSL_EXEC.' x509 -req -days '.$certmgr_ref['days'].' -extensions v3_ca '.
        '-set_serial '.$certmgr_ref['serial'].' -in "'.$dir_rootca.'/'.$certmgr_ref['csrFile'].'" '.
        '-signkey "'.$dir_rootca.'/'.$certmgr_ref['privKeyFile'].'" '.
        '-passin "file:'.$file_rootca_encpw.'" '.
        '-out "'.$dir_rootca.'/'.$certmgr_ref['crtFile'].'" '.
        '-extfile "'.$file_openssl_conf.'"';

    exec($x509_exec.' 2>&1', $x509_out, $result);
    if ($result != 0)
    {
        @unlink($file_rootca_encpw);

        log_write("RootCA: x509: ".$x509_exec, "ERROR", $x509_out);
        error_exit_json("<b>Can't create x509 certificate file!</b>\n".
            "<p>".$x509_exec."</p>\n<p>".implode("<br>\n", $x509_out)."</p>");
    }

    // 루트패스워드 임시파일 삭제
    @unlink($file_rootca_encpw);

    // 마스터 패스워드 저장
    set_ca_master_password($_POST['rootCertPassword'], "rootca_pw");

    // 모든게 완료되면 참조파일 기록
    file_put_contents($file_rootca_ref, json_encode($certmgr_ref, JSON_PRETTY_PRINT));

    log_write("RootCA: ".$certmgr_ref['certificateName']." created. = Success");

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //

    $log = "<p>".implode("<br>\n", $genrsa_out)."</p><br>\n";
    $log .= "<p>".implode("<br>\n", $csr_req_out)."</p><br>\n";
    $log .= "<p>".implode("<br>\n", $x509_out)."</p>";

    $output = array();
    $output['privKeyFile'] = $certmgr_ref['privKeyFile'];
    $output['csrFile'] = $certmgr_ref['csrFile'];
    $output['crtFile'] = $certmgr_ref['crtFile'];

    $output['log'] = $log;

    echo json_encode($output);
}
?>
