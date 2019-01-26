<?
/****************************************************************************/
/* 신규 사용자 생성 화면                                                    */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "사용자 등록";
$PAGE_DESC = "새로운 사용자를 등록합니다";

function print_contents()
{
    global $_SESSION;

    if ($_SESSION['user_role'] != "admin")
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        admin 권한이 필요합니다.
      </div>
<?
        return;
    }

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">사용자 정보</h3>
        </div>
        <form id="form" class="form-horizontal" action="" method="post">
        <input type="hidden" name="a" value="user_new_exec">
        <div class="box-body">

          <div class="form-group">
            <label for="user_id" class="col-sm-2 control-label">User ID</label>
            <div class="col-sm-10">
              <input id="user_id" type="text" name="user_id" maxlength="32" class="form-control" placeholder="사용자ID (사번 등)" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="user_pw" class="col-sm-2 control-label">임시 비밀번호</label>
            <div class="col-sm-4">
              <input id="user_pw" type="password" name="user_pw" class="form-control" placeholder="로그인 비밀번호(임시)" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="user_pw2" class="col-sm-2 control-label">임시 비밀번호 확인</label>
            <div class="col-sm-4">
              <input id="user_pw2" type="password" name="user_pw2" class="form-control" placeholder="로그인 비밀번호(임시)" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="user_name" class="col-sm-2 control-label">사용자명</label>
            <div class="col-sm-10">
              <input id="user_name" type="text" name="user_name" maxlength="32" class="form-control" placeholder="사용자명" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="user_role" class="col-sm-2 control-label">권한</label>
            <div class="col-sm-10">
              <select id="user_role" name="user_role" class="form-control">
                <option value="admin">admin: 사용자관리, 루트인증서 관리 (호스트인증서 관리 불가)</option>
                <option value="host_manager" selected>host_manager: 호스트 인증서 관리(신규/해지)</option>
                <option value="guest">guest: 호스트 인증서 조회</option>
              </select>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

        </div>
        </form>
        <div class="box-footer">
          <button id="submit" type="submit" class="btn btn-info pull-right">등록</button>
        </div>
      </div>

<?
}

function footer_scripts()
{
?>

<script src="certmgr_common.js"></script>
<script>
    $(document).ready(function()
    {
        // click on button submit
        $("#submit").on('click', function()
        {
            ajax_send('form',
                '<?=$_SERVER["SCRIPT_NAME"]?>',
                function(result)
                {
                    //alert('success function ' + result);
                    console.log(result);

                    // 송신버튼 클릭불가
                    $("#submit").prop("disabled", true);
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
    });

</script>
<?
}
?>
