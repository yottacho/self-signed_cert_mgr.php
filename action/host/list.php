<?php
/****************************************************************************/
/* 호스트 인증서 목록 생성                                                  */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") error_exit_json("CALL ERROR");

// API 응답
$API_RESPONSE = "Y";

function print_contents()
{
    global $_SESSION;
    global $CERT_DATA;

    // 인증서 조회하면서 매번 openssl 을 수행하면 느려지므로 캐시파일을 이용한다.
    $dirNames = array();
    if ($dirHandle = opendir($CERT_DATA))
    {
        while (($dirName = readdir($dirHandle)) !== false)
        {
            // 디렉터리만 포함, rootca 디렉터리는 제외
            if ($dirName != "." && $dirName != ".." && is_dir($CERT_DATA."/".$dirName) &&
                $dirName != "rootca")
            {
                $dirNames[] = $dirName;
            }
        }
        closedir($dirHandle);
    }
    else
    {
        error_exit_json("Error: opendir()");
    }

    // count($dirNames) => 데이터 갯수 체크
    sort($dirNames);

    // 출력자료 생성
    $host_list = array('list' => array());

    $idx = 0;
    foreach ($dirNames as $dirName)
    {
        $idx++;

        $certInfo = get_cert($dirName);

        $user = $certInfo['user'];
        $endDate = explode(' ', $certInfo['endDateLocal'])[0];

        if (isset($certInfo['closed']) && $certInfo['closed'] == "Y")
        {
            // 게스트는 폐기인증서를 조회하지 않음
            if ($_SESSION['user_role'] == "guest")
                continue;

            $user = $certInfo['closeUser'];
            $endDate = explode(' ', $certInfo['closeDateLocal'])[0];
        }

        $host_info = array(
            'no'        => $idx,
            'name'      => $dirName,
            //'name'      => $certInfo['certificateName'],
            'subjectOU' => $certInfo['organizationalUnitName'],
            'subjectCN' => $certInfo['commonName'],
            'startDate' => explode(' ', $certInfo['startDateLocal'])[0],
            'endDate'   => $endDate,
            'user'      => $user
        );

        $host_list['list'][] = $host_info;
    }

    echo json_encode($host_list);
}
?>