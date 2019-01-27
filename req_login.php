<?php
/****************************************************************************/
/* 로그인 세션 처리                                                         */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$CRYPT_OPTS = "\$5\$rounds=5000\$";
$CRYPT_RSALT = $CRYPT_OPTS.md5(time())."\$";

// --------------------------------------------------------------------- //
// 로그인 세션 존재하는지 체크
// --------------------------------------------------------------------- //
if (!isset($_SESSION['user_id']))
{
    // 로그인 요청 거래인지 체크
    // POST 만 허용
    if (isset($_POST['a']) && $_POST['a'] == "login")
    {
        // 입력값 체크
        if (!isset($_POST["user_id"]) || strlen($_POST["user_id"]) == 0 ||
            !isset($_POST["user_pw"]) || strlen($_POST["user_pw"]) == 0)
        {
            // 로그인 오류 => 로그인 페이지 템플리트 출력
            show_login_page(true);
            exit;
        }

        // 사용자 파일이 없으면 기본 관리자ID 생성
        if (!file_exists($USER_STORE))
        {
            $defaultAdminUser = 'admin';
            $defaultAdminPw = crypt('admin', $CRYPT_RSALT);

            $defaultUser = array();
            $defaultUser[] = array(
                'user_id' => $defaultAdminUser,
                'user_pw' => $defaultAdminPw,
                'user_name' => 'Administrator',
                'user_role' => 'admin',
                'pw_err_cnt' => 0,
                'last_login_date' => '',
                'last_pw_date' => '',
                'user_pw_bak1' => '',
                'user_pw_bak2' => '',
                'user_pw_bak3' => '');

            if (file_put_contents($USER_STORE, json_encode($defaultUser, JSON_PRETTY_PRINT)) === FALSE)
            {
                die("Can't create users.json");
            }
        }

        // 사용자 정보
        $user_string = @file_get_contents($USER_STORE);
        if (($users = json_decode($user_string, true)) == null)
        {
            die("users.json format error");
        }

        $login_error = "1";
        foreach ($users as &$user)
        {
            $cdate = date("Y/m/d H:i:sO");
            $pw_err_cnt = $user['pw_err_cnt'] * 1;

            if ($user['user_id'] == $_POST["user_id"])
            {
                // 비밀번호 5회 이상 오류는 항상 오류
                if ($pw_err_cnt >= 5)
                {
                    $login_error = "1";
                    // 로그인 오류 => 로그인 페이지 템플리트 출력
                    show_login_page(true, "User locked. Please contact administrator.");
                    exit;
                }

                // php 5.6+
                // if (hash_equals($user['user_pw'], crypt($_POST["user_pw"], $user['user_pw'])))
                // php < 5.6
                // 관리자 role의 경우, 비밀번호가 없으면 bypass 한다. (비상용)
                else if (($user['user_role'] == 'admin' && $user['user_pw'] == "") ||
                    $user['user_pw'] == crypt($_POST["user_pw"], $user['user_pw']))
                {
                    // Login success!
                    $user['last_login_date'] = $cdate;
                    $user['pw_err_cnt'] = 0;

                    // 사용자정보 세션 생성
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['user_role'] = $user['user_role'];

                    $login_error = "0";

                    // 패스워드 만료여부
                    $_SESSION['pw_expired'] = "N";
                    if ($user['last_pw_date'] == "")
                    {
                        $_SESSION['pw_expired'] = "Y";
                    }
                    else
                    {
                        // 만료일자 계산
                        $last_pw_date = $user['last_pw_date'];
                        $dt_calc = date_create($last_pw_date);
                        $dt_calc->add(date_interval_create_from_date_string("3 month"));

                        $dt_interval = date_diff($dt_calc, date_create());
                        // invert == 1 : 만료일 > 현재일
                        // invert == 0 : 만료일 < 현재일
                        if ($dt_interval->invert == 0 && $dt_interval->days > 0)
                        {
                            $_SESSION['pw_expired'] = "Y";
                        }
                    }
                }
                else
                {
                    // login failure
                    $user['pw_err_cnt'] = ($user['pw_err_cnt'] * 1) + 1;
                    $login_error = "1";
                }

                // write info
                if (file_put_contents($USER_STORE, json_encode($users, JSON_PRETTY_PRINT)) === FALSE)
                {
                    die("Can't update users.json");
                }
            }
        }

        // 로그인 체크
        if ($login_error == "1")
        {
            // 로그인 오류 => 로그인 페이지 템플리트 출력
            show_login_page(true);
            exit;
        }

        // 새로고침 했을때 로그인 데이터를 재전송하지 않도록 리다이렉트 처리
        if ($_SESSION['pw_expired'] != "Y")
        {
            header("Location: ".$_SERVER["SCRIPT_NAME"]."?a=home");
        }
        else
        {
            header("Location: ".$_SERVER["SCRIPT_NAME"]."?a=user_view");
        }
        exit;
    }
    else
    {
        // 세션이 없음 => 로그인 페이지 템플리트 출력
        show_login_page(false);
        exit;
    }
}
// --------------------------------------------------------------------- //
// 로그인 세션이 있음
// --------------------------------------------------------------------- //
else
{
    if ($_SESSION['pw_expired'] == "Y")
    {
        if ($REQUEST_ACTION != "logout" && $REQUEST_ACTION != "user_view" && $REQUEST_ACTION != "user_view_exec")
        {
            header("Location: ".$_SERVER["SCRIPT_NAME"]."?a=user_view");
            exit;
        }
    }
}


function show_login_page($login_error_yn, $msg = "")
{
    global $_SERVER;
    global $PROGRAM_NAME;

    $login_error_msg = "";
    $login_error_class = "";

    if ($login_error_yn == true)
    {
        $login_error_class = "has-error";
        if ($msg != "")
            $login_error_msg = $msg;
        else
            $login_error_msg = "Id or Password missmatch!";
    }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>로그인 | <?=$PROGRAM_NAME?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="css/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="css/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="css/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="css/plugins/iCheck/square/blue.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <!--
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  -->
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    SSL 인증서 관리자
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">로그인</p>

    <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post">
      <input type="hidden" name="a" value="login">
      <div class="form-group <?=$login_error_class?>">
        <div class="form-group has-feedback">
          <input type="text" name="user_id" class="form-control" placeholder="Id">
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input type="password" name="user_pw" class="form-control" placeholder="Password">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>

        <span class="help-block"><?=$login_error_msg?></span>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary btn-block btn-flat">로그인</button>
        </div>
      </div>
    </form>

<!--
신규 사용자등록 또는 비밀번호 분실은 관리자(admin) Role이 있는 사용자만 가능
    <div class="row">
      <div class="col-xs-12">
        <a href="#">I forgot my password</a><br>
        <a href="#" class="text-center">Register a new membership</a>
      </div>
    </div>
-->

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 3 -->
<script src="css/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="css/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html> 
<?php
}   // show_login_page()
?>
