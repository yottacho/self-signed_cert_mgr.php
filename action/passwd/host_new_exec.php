<?php
/****************************************************************************/
/* 인증서 마스터 패스워드 조회API                                           */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_SESSION;

    if ($_SESSION['user_role'] == "guest")
    {
        error_exit_json("guest 는 이용불가한 기능입니다.");
    }

    $error_form = array();
    if (!input_value_check($_POST['hostCertPassword'], '^[\\x20-\\x7E]*$', 1, 32))
    {
        $error_form['hostCertPassword'] = "Error Host Cert Password";
    }

    if (strcmp($_POST['hostCertPassword'], $_POST['hostCertPassword2']) != 0)
    {
        $error_form['hostCertPassword'] = "Host Cert Password missmatch";
        $error_form['hostCertPassword2'] = "Host Cert Password missmatch";
    }

    if (count($error_form) > 0)
    {
        error_exit_json("Input validation check", null, $error_form);
    }

    if (get_ca_master_password() === false)
    {
        set_ca_master_password($_POST['hostCertPassword']);
    }
    else
    {
        error_exit_json("호스트 인증서 암호가 등록되어 있습니다.");
    }

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //
    $output = array();
    $output['result'] = "ok";

    echo json_encode($output);
}
?>
