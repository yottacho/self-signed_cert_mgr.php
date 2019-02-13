<?php
/****************************************************************************/
/* 신규 사용자 생성API                                                      */
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
        error_exit_json("guest는 이용불가한 기능입니다.");
    }

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //
    $output = array();
    $output['role'] = $_SESSION['user_role'];

    $output['master_password'] =  get_ca_master_password();

    if ($_SESSION['user_role'] == "admin")
    {
        $output['rootca_password'] = get_ca_master_password('rootca_pw');
    }
    else
    {
        $output['rootca_password'] = "****";
    }

    echo json_encode($output);
}
?>
