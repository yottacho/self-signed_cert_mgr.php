<?
/****************************************************************************/
/* 사용자정보 상세보기                                                      */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "사용자 정보";
$PAGE_DESC = "사용자 정보를 조회합니다.";

function print_contents()
{
    global $_REQUEST, $_SESSION;
    global $USER_STORE;

    $user_id = isset($_REQUEST['n']) ? $_REQUEST['n'] : "";

    if (!input_value_check($user_id, '^[a-zA-Z0-9_]*$', 0, 12))
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        접근할 수 없습니다.
      </div>
<?
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
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        다른 사용자 정보는 조회할 수 없습니다.
      </div>
<?
            return;
        }
        // else => 관리자
    }

    // 사용자 정보 조회
    $user_string = @file_get_contents($USER_STORE);
    if (($users = json_decode($user_string, true)) == null)
    {
        die("users.json format error");
    }

    $user = array();
    foreach ($users as $user1)
    {
        if ($user1['user_id'] == $user_id)
        {
            $user = $user1;
            break;
        }
    }
    unset($user1);

    if (count($user) == 0)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        사용자가 존재하지 않습니다.
      </div>
<?
            return;
    }

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">사용자 정보</h3>
        </div>
        <div class="box-body">

          <div class="list-group">
            <div class="list-group-item col-sm-2">ID</div>
            <div class="list-group-item col-sm-10"><?=$user['user_id']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">사용자명</div>
            <div class="list-group-item col-sm-10"><?=$user['user_name']?>
            &nbsp; </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">권한</div>
            <div class="list-group-item col-sm-10"><?=$user['user_role']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">비밀번호 오류횟수</div>
            <div class="list-group-item col-sm-10"><?=$user['pw_err_cnt']?>
            &nbsp; </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-4">최종 로그인 일시</div>
            <div class="list-group-item col-sm-8"><?=$user['last_login_date']?>
            &nbsp; </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-4">최종 비밀번호 변경일</div>
            <div class="list-group-item col-sm-8"><?=$user['last_pw_date']?>
            &nbsp; </div>
          </div>

          <form id="form" class="form-horizontal" action="" method="post">
          <input type="hidden" name="a" value="user_view_exec">
          <input type="hidden" id="func" name="f" value="">
          <input type="hidden" id="user_id" name="user_id" value="">

          <div class="form-group">
            <label for="user_pw" class="col-sm-2 control-label">비밀번호 변경</label>
            <div class="col-sm-4">
              <input id="user_pw" type="password" name="user_pw" class="form-control" placeholder="로그인 비밀번호" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="user_pw2" class="col-sm-2 control-label">비밀번호 재입력</label>
            <div class="col-sm-4">
              <input id="user_pw2" type="password" name="user_pw2" class="form-control" placeholder="로그인 비밀번호" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<?
    if ($_SESSION['user_role'] == "admin")
    {
?>
          <div class="form-group">
            <label for="user_role" class="col-sm-2 control-label">권한 변경</label>
            <div class="col-sm-10">
              <select id="user_role" name="user_role" class="form-control">
                <option value="admin" <?=($user['user_role']=="admin"?"selected":"")?>>admin: 사용자관리, 루트인증서 관리 (호스트인증서 관리 불가)</option>
                <option value="host_manager" <?=($user['user_role']=="host_manager"?"selected":"")?>>host_manager: 호스트 인증서 관리(신규/해지)</option>
                <option value="guest" <?=($user['user_role']=="guest"?"selected":"")?>>guest: 호스트 인증서 조회</option>
              </select>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div id="id_admin_pw" class="form-group hide">
            <label for="admin_pw" class="col-sm-2 control-label"><?=$_SESSION['user_name']?> 비밀번호</label>
            <div class="col-sm-4">
              <input id="admin_pw" type="password" name="admin_pw" class="form-control" placeholder="관리자 패스워드" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <div class="col-sm-6">
            </div>
          </div>

<?
    }
?>
        </form>

        </div>
        <div class="box-footer">
          <center>
          <div class="btn-group">

            <button id="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> 저장
            </button>

<?
    if ($_SESSION['user_role'] == "admin")
    {
?>
            <button id="id_close" class="btn btn-danger">
              <i class="fa fa-times"></i> 계정 폐쇄
            </button>

            <button id="id_back_list" class="btn btn-default">
              <i class="fa fa-arrow-left"></i> 목록
            </button>
<?
    }
?>

          </div>
          </center>
        </div>
      </div>

<script>
    var user_id = "<?=$user['user_id']?>";
</script>

<?
}

function footer_scripts()
{
    global $_SERVER;
?>
<script src="certmgr_common.js"></script>
<script>

    $(document).ready(function()
    {
        // click on button submit
        $("#submit").on('click', function()
        {
            $("#user_id").prop("value", user_id);
            $("#func").prop("value", "modify");

            ajax_send('form',
                '<?=$_SERVER["SCRIPT_NAME"]?>',
                function(result)
                {
                    //alert('success function ' + result);
                    console.log(result);

                    // 송신버튼 클릭불가
                    $("#submit").prop("disabled", true);
                    $("#id_close").prop("disabled", true);
                    // 폼의 모든 입력값 입력불가
                    $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );

                },
                function(err)
                {
                    // json object only
                    if (err == null)
                        return;

                    // 에러가 발생한 필드에 대해 처리
                    for (var i in err.form)
                    {
                        $("#" + i).parent("div").addClass("has-error");
                        $("#" + i).next(".help-block").removeClass("hide").empty().append(err.form[i]);
                    }

                    //alert('error function');
                    //console.log(err);
                }
            );
        })
        $("#id_close").on('click', function()
        {
            $("#user_id").prop("value", user_id);
            $("#func").prop("value", "delete");

            $("#id_admin_pw").removeClass("has-error");
            if ($("#admin_pw").prop("value") == "")
            {
                $("#id_admin_pw").removeClass("hide");
                $("#id_admin_pw").addClass("has-error");
            }
            else
            {
                ajax_send('form',
                    '<?=$_SERVER["SCRIPT_NAME"]?>',
                    function(result)
                    {
                        //alert('success function ' + result);
                        console.log(result);

                        // 송신버튼 클릭불가
                        $("#submit").prop("disabled", true);
                        $("#id_close").prop("disabled", true);
                        // 폼의 모든 입력값 입력불가
                        $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );

                    },
                    function(err)
                    {
                        // json object only
                        if (err == null)
                            return;

                        // 에러가 발생한 필드에 대해 처리
                        for (var i in err.form)
                        {
                            $("#" + i).parent("div").addClass("has-error");
                            $("#" + i).next(".help-block").removeClass("hide").empty().append(err.form[i]);
                        }

                        //alert('error function');
                        //console.log(err);
                    }
                );
            }
        })

        $("#id_back_list").on('click', function()
        {
            location.href = "<?=$_SERVER['SCRIPT_NAME']?>?a=user";
        })

    });

</script>
<?
}
?>
