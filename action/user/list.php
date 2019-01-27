<?php
/****************************************************************************/
/* 사용자 목록 생성                                                         */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $CERT_DATA, $USER_STORE;

    $user_string = @file_get_contents($USER_STORE);
    if (($users = json_decode($user_string, true)) == null)
    {
        error_exit_json("users.json format error");
    }

    // 출력자료 생성
    $user_list = array('list' => array());

    $idx = 0;
    foreach ($users as $user)
    {
        $idx++;

        $user_info = array(
            'no'              => $idx,
            'user_id'         => $user['user_id'],
            'user_name'       => $user['user_name'],
            'user_role'       => $user['user_role'],
            'last_login_date' => $user['last_login_date'],
            'pw_err_cnt'      => $user['pw_err_cnt'],
            'last_pw_date'    => $user['last_pw_date']
        );

        $user_list['list'][] = $user_info;
    }

    echo json_encode($user_list);
}
?>