<?
require_once("env.php");

/****************************************************************************/
/* 공용 변수                                                                */
/****************************************************************************/
$PAGE_TITLE = "";       // 페이지 제목(include 한 페이지에서 셋팅할 것)
$PAGE_DESC = "";        // 페이지 소제목(include 한 페이지에서 셋팅할 것)
$API_RESPONSE = "";     // API 응답일 경우(include 한 페이지에서 "Y"로 셋팅하면 html출력하지 않고 응답)

$USER_STORE = $CERT_DATA."/users.json"; // 사용자 로그인 정보를 저장하는 파일

$REQUEST_ACTION = isset($_REQUEST['a']) ? $_REQUEST['a'] : "";   // 액션 수행값

/****************************************************************************/
/* 시작전 체크                                                              */
/****************************************************************************/
require_once("com_func.php");           // 공통함수

// 기본 액션
if ($REQUEST_ACTION === null || $REQUEST_ACTION == "")
{
    $REQUEST_ACTION = "home";
}

if (!input_value_check($REQUEST_ACTION, '^[a-zA-Z0-9_]*$', 1))
{
    die("액션 오류입니다");
}

// 출력버퍼 셋팅
ob_start();

// date 함수용 기본 Timezone Set
date_default_timezone_set($TIMEZONE);

// 임시 디렉터리 체크
if (!is_dir($CERT_DATA))
{
    die("오류: `CERT_DATA`는 디렉터리가 아닙니다");
}
if (!is_writable($CERT_DATA))
{
    die("오류: `CERT_DATA` 디렉터리에 기록할 수 없습니다");
}

// 세션 시작
session_start();

// 로그인 체크
require_once("req_login.php");

/****************************************************************************/
/* 액션 핸들러                                                              */
/****************************************************************************/
if ($REQUEST_ACTION == "home")
{
    require_once("action/home.php");
}
else if ($REQUEST_ACTION == "logout")
{
    session_destroy();
    header("Location: ".$_SERVER["SCRIPT_NAME"]);
    exit;
}
else if ($REQUEST_ACTION == "error")
{
    $ERROR_TITLE = "오류";
    $ERROR_MESSAGE = "오류가 발생했습니다";
    require_once("req_error.php");
}
// 미정의 액션=오류처리
else
{
    // 오토액션매핑
    $script_root_dir = dirname($_SERVER["SCRIPT_FILENAME"]);

    // 스크립트 파일명 검증(영문 소문자, 숫자, 언더바만 허용)
    if (preg_match("/^[a-z0-9_]+$/", $REQUEST_ACTION) != 1)
    {
        $ERROR_TITLE = "액션 오류";
        $ERROR_MESSAGE = "액션에 허용되지 않은 문자가 있습니다";
        require_once("req_error.php");
    }
    else
    {
        $category = "";
        $action = "";
        $sep_idx = strpos($REQUEST_ACTION, "_");

        // 첫번째 '_'가 있으면 _ 앞은 카테고리로, 뒤는 액션으로 분리한다.
        if ($sep_idx !== false)
        {
            $category = substr($REQUEST_ACTION, 0, $sep_idx);
            $action = substr($REQUEST_ACTION, $sep_idx + 1);
        }
        // '_'가 없으면 액션은 카테고리명으로 셋팅
        else
        {
            $category = $REQUEST_ACTION;
            $action = "index";
        }

        $script_pathname = $script_root_dir . "/action/" . $category . "/" . $action . ".php";

        if (file_exists($script_pathname))
        {
            require_once($script_pathname);
        }
        else
        {
            $ERROR_TITLE = "액션 오류";
            $ERROR_MESSAGE = "해당 기능이 없습니다.";
            require_once("req_error.php");
        }
    }
}

// 서브모듈에서 필수함수 구현했는지 체크
if (!function_exists("print_contents"))
{
    die("서브 모듈 오류입니다. `print_contents` 함수가 정의되지 않았습니다.");
}
if ($API_RESPONSE == "Y")
{
    // api 응답
    print_contents();
    exit;
}
if ($PAGE_TITLE == "")
{
    die("서브모듈 오류입니다 `PAGE_TITLE` 값이 정의되지 않았습니다.");
}

// 트리메뉴 열기
$sidebar_treemenu = array('host','root','user');

$sidebar_menu_active = "    \$('#id_menu_".$REQUEST_ACTION."').addClass('active')\n";
foreach ($sidebar_treemenu as $menu_name)
{
    if (strncmp($REQUEST_ACTION, $menu_name, strlen($menu_name)) == 0)
    {
        $sidebar_menu_active .= "    \$('#id_menu_tree_".$menu_name."').addClass('active')\n";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?=$PAGE_TITLE?> | <?=$PROGRAM_NAME?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="css/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="css/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="css/bower_components/Ionicons/css/ionicons.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="css/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="css/dist/css/skins/_all-skins.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="css/html5shiv.min.js"></script>
  <script src="css/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <!--
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  -->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <header class="main-header">
    <!-- Logo -->
    <a href="<?=$_SERVER['SCRIPT_NAME']?>?a=home" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>C</b>M</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><?=$PROGRAM_NAME?></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!--
              <img src="css/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
              -->
              <span class="hidden-xs"><?=$_SESSION['user_name']?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
              <!--
                <img src="css/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
              -->
                <p>
                  <?=$_SESSION['user_name']?>(<?=$_SESSION['user_id']?>)
                  <small>권한: <?=$_SESSION['user_role']?></small>
                </p>
              </li>
              <!-- Menu Body -->
              <!--
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Friends</a>
                  </div>
                </div>
              </li>
              -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="<?=$_SERVER['SCRIPT_NAME']?>?a=user_view" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?=$_SERVER['SCRIPT_NAME']?>?a=logout" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <!--
      <div class="user-panel">
        <div class="pull-left image">
          <img src="css/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?=$_SESSION['user_name']?></p>
        </div>
      </div>
      -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN MENU</li>

        <li id="id_menu_home">
          <a href="<?=$_SERVER['SCRIPT_NAME']?>?a=home">
            <i class="glyphicon glyphicon-home"></i> <span>Home</span>
          </a>
        </li>

        <li id="id_menu_tree_host" class="treeview">
          <a href="#">
            <i class="fa fa-server"></i> <span>호스트 인증서 관리</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li id="id_menu_host"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=host"><i class="fa fa-circle-o"></i> 호스트 인증서 목록</a></li>
<?
if ($_SESSION['user_role'] == "host_manager")
{
?>

            <li id="id_menu_host_new"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=host_new"><i class="fa fa-circle-o"></i> 호스트 인증서 생성</a></li>
<?
}
?>
          </ul>
        </li>

<?
if ($_SESSION['user_role'] == "admin")
{
?>

        <li id="id_menu_tree_root" class="treeview">
          <a href="#">
            <i class="fa fa-shield"></i> <span>루트 인증서 관리</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li id="id_menu_root"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=root"><i class="fa fa-circle-o"></i> 루트 인증서 조회</a></li>
            <li id="id_menu_root_new"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=root_new"><i class="fa fa-circle-o"></i> 루트 인증서 생성</a></li>
<!--
            <li id="id_menu_root_close"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=root_close"><i class="fa fa-circle-o"></i> Close Root Certificate</a></li>
-->
          </ul>
        </li>

<?
}

if ($_SESSION['user_role'] == "admin")
{
?>
        <li id="id_menu_tree_user" class="treeview">
          <a href="#">
            <i class="fa fa-shield"></i> <span>사용자 관리</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li id="id_menu_user"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=user"><i class="fa fa-circle-o"></i> 사용자 목록</a></li>
            <li id="id_menu_user_new"><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=user_new"><i class="fa fa-circle-o"></i> 사용자 등록</a></li>
          </ul>
        </li>
<?
}
?>

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$PAGE_TITLE?>
        <small><?=$PAGE_DESC?></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?=$_SERVER['SCRIPT_NAME']?>?a=home"><i class="glyphicon glyphicon-home"></i> Home</a></li>
        <!--
        <li><a href="#">Examples</a></li>
        -->
        <li class="active"><?=$PAGE_TITLE?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- 여기에 내용 삽입 -->
      <?print_contents()?>
      <!-- 여기에 내용 삽입(끝) -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 0.1.0a
    </div>
    <strong>Copyright &copy; 2018 yotta.</strong> All rights reserved.
  </footer>

  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="css/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="css/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="css/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="css/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="css/dist/js/adminlte.min.js"></script>
<script>
  $(document).ready(function () {
    $('.sidebar-menu').tree()

<?=$sidebar_menu_active?>

    // 'skin-green-light'
    $('body').addClass('skin-green')
  })
</script>

<?
if (function_exists("footer_scripts"))
{
    echo footer_scripts();
}
?>

</body>
</html>
