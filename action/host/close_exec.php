<?
/****************************************************************************/
/* 호스트 인증서 폐기API                                                    */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_POST, $_SESSION;
    global $CERT_DATA, $USER_STORE;

    // role 검증
    if ($_SESSION['user_role'] != "host_manager")
    {
        error_exit_json("host_manager 권한이 필요합니다.");
    }

    $certName = $_POST['cert_name'];
    if (($hostCertInfo = get_cert($certName)) === false)
    {
        error_exit_json("해당 인증서가 없습니다.");
    }

    // --------------------------------------------------------------------- //
    // 입력항목 검증
    // --------------------------------------------------------------------- //
    $error_form = array();
    if (!input_value_check($_POST['user_pw'], '^[\\x20-\\x7E]*$', 1))
    {
        $error_form['user_pw'] = "사용자 비밀번호를 입력하세요.";
    }

    // 사용자 정보
    $user_string = @file_get_contents($USER_STORE);
    if (($users = json_decode($user_string, true)) == null)
    {
        die("users.json format error");
    }

    foreach ($users as $user)
    {
        if ($user['user_id'] == $_SESSION['user_id'])
        {
            if ($user['user_pw'] != crypt($_POST["user_pw"], $user['user_pw']))
            {
                $error_form['user_pw'] = "사용자 비밀번호 오류입니다.";
            }
            break;
        }
    }

    if (count($error_form) > 0)
    {
        error_exit_json("입력한 항목에 오류가 있습니다.", null, $error_form);
    }

    // --------------------------------------------------------------------- //
    // 변수 생성
    // --------------------------------------------------------------------- //
    $dir_cert          = $CERT_DATA."/".$hostCertInfo['certificateName'];

    // --------------------------------------------------------------------- //
    // 정보파일 갱신
    // --------------------------------------------------------------------- //
    $closeDate = date("Y/m/d H:i:sO");

    $hostCertInfo['closed'] = 'Y';
    $hostCertInfo['closeDateLocal'] = $closeDate;
    $hostCertInfo['closeUser'] = $_SESSION['user_name'].'('.$_SESSION['user_id'].')';

    file_put_contents($dir_cert.'/'.$hostCertInfo['certificateName'].".json", json_encode($hostCertInfo, JSON_PRETTY_PRINT));

    // --------------------------------------------------------------------- //
    // 해지처리
    // --------------------------------------------------------------------- //
    $dir_cert_new = $dir_cert.".closed";
    if (@is_dir($dir_cert_new))
    {
        // remove all files
        if ($dirHandle = opendir($dir_cert_new))
        {
            while (($dirName = readdir($dirHandle)) !== false)
            {
                @unlink($dir_cert_new."/".$dirName);
            }
        }
        closedir($dirHandle);

        @rmdir($dir_cert_new);
    }
    @rename($dir_cert, $dir_cert_new); 

    // --------------------------------------------------------------------- //
    // 응답 데이터 생성
    // --------------------------------------------------------------------- //

    $output = array();

    $output['result'] = 'Closed';

    echo json_encode($output);
}
?>
