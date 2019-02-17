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

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //
    $output = array();
    $output['role'] = $_SESSION['user_role'];

    // 호스트 인증서 패스워드 조회
    $output['master_password'] =  get_ca_master_password();

    // 관리자는 루트인증서 패스워드 조회
    if ($_SESSION['user_role'] == "admin")
    {
        $output['rootca_password'] = get_ca_master_password('rootca_pw');
    }
    // 이용자는 루트인증서 패스워드 조회 불가
    else
    {
        $output['rootca_password'] = "****";
    }

    echo json_encode($output);
}
?>
