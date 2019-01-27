<?php
/****************************************************************************/
/* 사용자 정보변경 API                                                      */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_POST, $_SESSION;
    global $USER_STORE, $CRYPT_RSALT;

    $user_id = isset($_REQUEST['n']) ? $_REQUEST['n'] : "";

    if (!input_value_check($user_id, '^[a-zA-Z0-9_]*$', 0, 12))
    {
        error_exit_json("user_id validation error");
        return;
    }

    // 사용자ID 가 없는 경우 로그인한 사용자 id
    if ($user_id == "")
    {
        $user_id = $_SESSION['user_id'];
    }

    // 권한 검증
    // 로그인한 사용자와 불일치할 경우
    if ($_SESSION['user_id'] != $user_id)
    {
        // 관리자 권한이 아닌 경우
        if ($_SESSION['user_role'] != "admin")
        {
            error_exit_json("접근이 제한되었습니다.");
        }
        // else => 관리자
    }

    // --------------------------------------------------------------------- //
    // 사용자 정보
    // --------------------------------------------------------------------- //
    $user_string = @file_get_contents($USER_STORE);
    if (($users = json_decode($user_string, true)) == null)
    {
        error_exit_json("users.json format error");
    }

    $user = array();
    foreach ($users as &$user1)
    {
        if ($user1['user_id'] == $user_id)
        {
            $user = &$user1;
            break;
        }
    }
    unset($user1);

    if (count($user) == 0)
    {
        error_exit_json("사용자가 없습니다.");
    }

    // --------------------------------------------------------------------- //
    // 입력항목 검증
    // --------------------------------------------------------------------- //
    $error_form = array();
    // 관리자가 아닌 사용자의 경우 패스워드 입력 필수
    if ($_SESSION['user_role'] != "admin" && $_POST['user_pw'] == "")
    {
        $error_form['user_pw'] = "비밀번호를 입력하세요.";
    }

    if (!input_value_check($_POST['user_pw'], '^[\\x20-\\x7E]*$', 0, 32))
    {
        $error_form['user_pw'] = "비밀번호를 입력하세요.";
    }

    if ($_POST['user_pw'] != "")
    {
        if (strcmp($_POST['user_pw'], $_POST['user_pw2']) != 0)
        {
            $error_form['user_pw'] = "비밀번호가 일치하지 않습니다.";
            $error_form['user_pw2'] = "비밀번호가 일치하지 않습니다.";
        }
    }

    if ($_SESSION['user_role'] == 'admin')
    {
        if ($_POST['user_role'] != 'admin' && $_POST['user_role'] != 'host_manager' && $_POST['user_role'] != 'guest')
        {
            $error_form['user_role'] = "권한을 선택하세요.";
        }

    }

    if (count($error_form) > 0)
    {
        error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
    }

    // --------------------------------------------------------------------- //
    // 데이터 처리
    // --------------------------------------------------------------------- //

    // change password or role
    if ($_POST['f'] == "modify")
    {
        if ($_POST['user_pw'] != "")
        {
            $cdate = date("Y/m/d");
            $passwd = crypt($_POST['user_pw'], $CRYPT_RSALT);

            if ($_SESSION['user_role'] != "admin")
            {
                $passwdChk = crypt($_POST['user_pw'], $user['user_pw']);
                if ($passwdChk == $user['user_pw'] ||
                    $passwdChk == $user['user_pw_bak1'] ||
                    $passwdChk == $user['user_pw_bak2'] ||
                    $passwdChk == $user['user_pw_bak3'])
                {
                    $error_form['user_pw'] = "최근 사용한 비밀번호입니다.";
                    error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
                }

                $user['last_pw_date'] = $cdate;

                // 비밀번호 변경하면 세션값 갱신
                $_SESSION['pw_expired'] = "N";
            }
            // 관리자가 강제 변경한 패스워드는 유효기간 expire해서 다시 설정하도록 한다.
            else
            {
                // 자기 자신의 비밀번호를 바꾸는 경우는 변경일시 입력
                if ($user_id == $user['user_id'])
                {
                    $user['last_pw_date'] = $cdate;
                    // 비밀번호 변경하면 세션값 갱신
                    $_SESSION['pw_expired'] = "N";
                }
                else
                {
                    $user['last_pw_date'] = "";
                }
            }

            $user['user_pw_bak3'] = $user['user_pw_bak2'];
            $user['user_pw_bak2'] = $user['user_pw_bak1'];
            $user['user_pw_bak1'] = $user['user_pw'];
            $user['user_pw']      = $passwd;
            $user['pw_err_cnt']   = 0;
        }

        if ($_POST['user_pw'] == "" && $user['user_role'] == $_POST['user_role'])
        {
            error_exit_json("No changes");
        }

        if ($_SESSION['user_role'] == 'admin')
        {
            $user['user_role'] = $_POST['user_role'];
        }
    }
    // delete user
    else if ($_POST['f'] == "delete")
    {
        // 사용자 삭제는 관리자 패스워드 확인
        if ($_POST['admin_pw'] == "")
        {
            $error_form['admin_pw'] = "관리자 비밀번호를 입력하세요.";
            error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
        }

        // 관리자 패스워드와 비교
        foreach ($users as $user2)
        {
            if ($user2['user_id'] == $_SESSION['user_id'])
            {
                $passwd = crypt($_POST['admin_pw'], $user2['user_pw']);
                if ($user2['user_pw'] != $passwd)
                {
                    $error_form['admin_pw'] = "관리자 비밀번호 오류입니다.";
                    error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
                }
                break;
            }
        }

        $idx = 0;
        for ($idx = 0; $idx < count($users); $idx++)
        {
            if ($users[$idx]['user_id'] == $user_id)
            {
                unset($users[$idx]);
                break;
            }
        }
    }
    else
    {
        error_exit_json("Action error");
    }

    {
        $admin_check = false;
        foreach ($users as $user1)
        {
            if ($user1['user_role'] == 'admin')
            {
                $admin_check = true;
            }
        }
        if ($admin_check == false)
        {
            error_exit_json("admin 권한을 가진 사용자가 아무도 없습니다. admin 권한을 가진 사용자는 하나 이상 있어야 합니다.");
        }
    }

    // write info
    if (file_put_contents($USER_STORE, json_encode($users, JSON_PRETTY_PRINT)) === FALSE)
    {
        error_exit_json("Can't update users.json");
    }

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //
    $output = array();
    $output['result'] = 'Success!';

    echo json_encode($user);
}
?>
