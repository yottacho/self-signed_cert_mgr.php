<?php
/****************************************************************************/
/* 인증서 암호 관리                                                         */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "인증서 패스워드 관리";
$PAGE_DESC = "인증서 패스워드를 관리합니다.";

function print_contents()
{
    global $_SESSION;

    if ($_SESSION['user_role'] == "guest")
    {
?>
        <div class="alert alert-danger alert-dismissible">
            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
            admin 또는 host_manager 권한이 필요합니다.
        </div>
<?php
        return;
    }

?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">인증서 패스워드 조회</h3>
        </div>
        <div class="box-body">
            <div class="box box-solid">
                <button id="id_show_password" class="btn btn-block btn-default btn-sm">인증서 패스워드 조회</button>
            </div>

            <div id="show_master_pw" class="list-group">
                <div class="list-group-item col-sm-4">호스트 인증서 패스워드</div>
                <div id="master_pw" class="list-group-item col-sm-8">&nbsp;</div>
            </div>

            <div id="show_rootca_pw" class="list-group">
                <div class="list-group-item col-sm-4">루트인증서 패스워드</div>
                <div id="rootca_pw" class="list-group-item col-sm-8">&nbsp;</div>
            </div>
        </div>
        <div class="box-footer">
            <ul>
                <li>루트 인증서 패스워드는 루트 인증서를 생성할 때 사용한 패스워드입니다.</li>
                <li>호스트 인증서 패스워드를 등록해야 호스트 인증서를 생성할 수 있습니다.</li>
            </ul>
        </div>
    </div>

<?php

    if (get_ca_master_password() === false)
    {
?>

        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">호스트 인증서 패스워드 등록</h3>
          </div>
          <form id="form" class="form-horizontal" action="" method="post">
          <input type="hidden" name="a" value="passwd_host_new_exec">

          <div class="box-body">
              <div class="form-group">
                  <label for="hostCertPassword" class="col-sm-2 control-label">Host Password</label>
                  <div class="col-sm-4">
                      <input id="hostCertPassword" type="password" name="hostCertPassword" class="form-control" placeholder="Password for all host certificate" value="" required>
                      <span class="help-block hide">Help block with error</span>
                  </div>

                  <label for="hostCertPassword2" class="col-sm-2 control-label">Retype Password</label>
                  <div class="col-sm-4">
                      <input id="hostCertPassword2" type="password" name="hostCertPassword2" class="form-control" placeholder="Password for all host certificate" value="" required>
                      <span class="help-block hide">Help block with error</span>
                  </div>
              </div>
          </div>
          </form>
          <div class="box-footer">
              <div class="col-sm-6">
                  <button id="submit" type="submit" class="btn btn-info pull-right">Create Host Password</button>
              </div>
          </div>
        </div>

<?php
    }
}

function footer_scripts()
{
    global $BASE_URL;
?>
<script src="certmgr_common.js"></script>
<script>
    $(document).ready(function() {
        $("#id_show_password").on('click', function () {
            ajax_send('form',
                '<?=$BASE_URL?>?a=passwd_show_exec',
                function (result) {
                    //alert('success function ' + result);
                    //console.log(result);

                    $("#master_pw").empty().append(result.master_password);
                    $("#rootca_pw").empty().append(result.rootca_password);

                    // 인증서 링크를 활성화하고 다운로드한다.
                    //$("#id_view_cert").removeClass("hide");
                    //$("#cert_result_log").removeClass("hide");

                },
                function (err) {
                    // json object only
                    if (err == null)
                        return;

                    //alert('error function');
                    //console.log(err);
                }
            );
        })

        // click on button submit
        $("#submit").on('click', function()
        {
            ajax_send('form',
                '<?=$BASE_URL?>',
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

<?php
}
?>