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
    global $_POST, $_SESSION;
    global $USER_STORE, $CRYPT_RSALT;

    if ($_SESSION['user_role'] != "admin")
    {
        error_exit_json("admin 권한이 필요합니다.");
    }

    // --------------------------------------------------------------------- //
    // 사용자 정보
    // --------------------------------------------------------------------- //
    $user_string = @file_get_contents($USER_STORE);
    if (($users = json_decode($user_string, true)) == null)
    {
        error_exit_json("users.json format error");
    }

    // --------------------------------------------------------------------- //
    // 입력항목 검증
    // --------------------------------------------------------------------- //
    $error_form = array();
    if (!input_value_check($_POST['user_id'], '^[a-zA-Z0-9_]*$', 1, 12))
    {
        $error_form['user_id'] = "알파벳, 숫자, 언더바(_)만 가능합니다.";
    }

    if (!input_value_check($_POST['user_pw'], '^[\\x20-\\x7E]*$', 1, 32))
    {
        $error_form['user_pw'] = "비밀번호를 입력하세요.";
    }

    if (strcmp($_POST['user_pw'], $_POST['user_pw2']) != 0)
    {
        $error_form['user_pw'] = "비밀번호가 일치하지 않습니다.";
        $error_form['user_pw2'] = "비밀번호가 일치하지 않습니다.";
    }

    if (!input_value_check($_POST['user_name'], '', 1, 32))
    {
        $error_form['user_name'] = "이름을 입력하세요.";
    }

    if ($_POST['user_role'] != 'admin' && $_POST['user_role'] != 'host_manager' && $_POST['user_role'] != 'guest')
    {
        $error_form['user_role'] = "권한을 선택하세요.";
    }

    // ID 중복 체크
    foreach ($users as $user)
    {
        if ($user['user_id'] == $_POST['user_id'])
        {
            $error_form['user_id'] = "이미 사용중인 ID입니다.";
            break;
        }
    }

    if (count($error_form) > 0)
    {
        error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
    }

    $passwd = crypt($_POST['user_pw'], $CRYPT_RSALT);

    $user = array(
        'user_id' => $_POST['user_id'],
        'user_pw' => $passwd,
        'user_name' => $_POST['user_name'],
        'user_role' => $_POST['user_role'],
        'pw_err_cnt' => 0,
        'last_login_date' => '',
        'last_pw_date' => '',
        'user_pw_bak1' => '',
        'user_pw_bak2' => '',
        'user_pw_bak3' => '');

    $users[] = $user;

    // write info
    if (file_put_contents($USER_STORE, json_encode($users, JSON_PRETTY_PRINT)) === FALSE)
    {
        error_exit_json("Can't update users.json");
    }

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //
    $output = array();
    $output['result'] = 'Created!';

    echo json_encode($output);
}
?>
