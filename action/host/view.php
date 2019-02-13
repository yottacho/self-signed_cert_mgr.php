<?php
/****************************************************************************/
/* 호스트 인증서 상세보기                                                   */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "호스트 인증서 보기";
$PAGE_DESC = "호스트 인증서 정보를 확인합니다.";

function print_contents()
{
    global $_REQUEST;
    global $CERT_DATA;
    global $BASE_URL;

    $certName = $_REQUEST['n'];

    if (($rootCaInfo = get_rootca()) === false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        발급된 루트 인증서가 없습니다.
      </div>
<?php
        return;
    }

    if (($hostCertInfo = get_cert($certName)) === false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        지정한 이름의 호스트 인증서가 없습니다.
      </div>
<?php
        return;
    }
?>

      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">호스트 인증서 정보</h3>
        </div>
        <div class="box-body">

          <div class="list-group">
            <div class="list-group-item col-sm-2">인증서 이름</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['certificateName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">발급자</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['organizationName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">조직</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['organizationalUnitName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">발급대상</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['commonName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">국가</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['countryName']?>
            </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-2">유효기간</div>
            <div class="list-group-item col-sm-10"><?=explode(' ', $hostCertInfo['startDateLocal'])[0]?> ~
              <?=explode(' ', $hostCertInfo['endDateLocal'])[0]?>
              (<?=$hostCertInfo['days']?> days)
            </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-2">발급사용자</div>
            <div class="list-group-item col-sm-10"><?=$hostCertInfo['user']?>
            </div>
          </div>

<?php
    if (isset($hostCertInfo['closed']) && $hostCertInfo['closed'] == "Y")
    {
?>

          <div class="list-group">
            <div class="list-group-item col-sm-2"><span class="text-red">폐기일자</span></div>
            <div class="list-group-item col-sm-10"><span class="text-red"><?=$hostCertInfo['closeDateLocal']?></span>
            &nbsp; </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-2"><span class="text-red">폐기사용자</span></div>
            <div class="list-group-item col-sm-10"><span class="text-red"><?=$hostCertInfo['closeUser']?></span>
            &nbsp; </div>
          </div>

<?php
    }

    if ($_SESSION['user_role'] == "host_manager")
    {
?>
          <form id="form" class="form-horizontal" action="" method="post">
          <input type="hidden" id="id_a" name="a" value="">
          <input type="hidden" id="id_cert_name" name="cert_name" value="">

          <!-- close certificate -->
          <div id="id_user_pw" class="form-group hide">
            <label for="user_pw" class="col-sm-2 control-label"><?=$_SESSION['user_name']?> 비밀번호</label>
            <div class="col-sm-10">
              <input id="user_pw" type="password" name="user_pw" class="form-control" placeholder="사용자 비밀번호" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          </form>

          <div class="col-sm-12 text-center">
            <button id="id_valid_cert" class="btn btn-primary">
              <i class="fa fa-lock"></i> 인증서 검증
            </button>
<?php
        if (!isset($hostCertInfo['closed']) || $hostCertInfo['closed'] != "Y")
        {
?>
            <button id="id_close_cert" class="btn btn-danger">
              <i class="fa fa-lock"></i> 인증서 폐기
            </button>
          </div>
<?php
        }
        else
        {
            // 이미 폐기된 인증서의 경우 인증서 삭제버튼
?>
            <button id="id_close_cert" class="btn btn-danger">
                <i class="fa fa-lock"></i> 인증서 삭제
            </button>
        </div>
<?php

        }
    }
?>

        </div>
        <div class="box-footer text-center">
          <div class="btn-group">

              <form id="form2" class="form-horizontal" action="" method="post">
                  <input type="hidden" id="id_a2" name="a" value="">
                  <input type="hidden" id="id_cert_name2" name="cert_name" value="">

                  <!-- close certificate -->
                  <div id="id_pkcs_pw" class="form-group hide">
                      <label for="pkcs_pw" class="col-sm-3 control-label">pkcs 비밀번호</label>
                      <div class="col-sm-3">
                          <input id="pkcs_pw" type="password" name="pkcs_pw" class="form-control" placeholder="" value="">
                          <span class="help-block hide">Help block with error</span>
                      </div>
                      <label for="pkcs_pw2" class="col-sm-3 control-label">비밀번호 확인</label>
                      <div class="col-sm-3">
                          <input id="pkcs_pw2" type="password" name="pkcs_pw2" class="form-control" placeholder="" value="">
                          <span class="help-block hide">Help block with error</span>
                      </div>
                  </div>

              </form>

<?php
    if (!isset($hostCertInfo['closed']) || $hostCertInfo['closed'] != "Y")
    {
?>
            <button id="id_pkcs12_download" class="btn btn-primary">
              <i class="fa fa-lock"></i> pkcs12 인증서
            </button>

            <button id="id_private_key_download" class="btn btn-primary">
              <i class="fa fa-lock"></i> 개인(비밀)키
            </button>

            <button id="id_crt_download" class="btn btn-success">
              <i class="glyphicon glyphicon-floppy-disk"></i> 호스트 인증서
            </button>

            <button id="id_root_crt_download" class="btn btn-default">
              <i class="fa fa-bolt"></i> 루트인증서
            </button>

            <button id="id_csr_download" class="btn btn-default">
              <i class="fa fa-user-secret"></i> 호스트 인증요청서
            </button>
<?php
    }

?>

            <button id="id_back_list" class="btn btn-default">
              <i class="fa fa-arrow-left"></i> 목록
            </button>

          </div>
        </div>
      </div>

      <div id="id_cert_detal" class="box hide">
        <div class="box-header with-border">
          <h3 class="box-title">인증서 검증결과</h3>
        </div>
        <div class="box-body">
          <p id="id_log">&nbsp;</p>
        </div>
      </div>


<script>
    var name = "<?=$_REQUEST['n']?>";
    var privateKey = "<?=$hostCertInfo['privKeyFile']?>";
    var crtFile = "<?=$hostCertInfo['crtFile']?>";
    var csrFile = "<?=$hostCertInfo['csrFile']?>";
    var rootCrtFile = "<?=$rootCaInfo['crtFile']?>";
</script>

<?php
}

function footer_scripts()
{
    global $BASE_URL;
?>
<script src="certmgr_common.js"></script>
<script>

    $(document).ready(function()
    {
        // 인증서 폐기
        $("#id_close_cert").on('click', function()
        {
            $("#id_a").prop("value", "host_close_exec");
            $("#id_cert_name").prop("value", name);

            $("#id_user_pw").removeClass("has-error");
            $("#id_user_pw").removeClass("hide");
            if ($("#user_pw").prop("value") == "")
            {
                $("#id_user_pw").addClass("has-error");
            }
            else
            {
                ajax_send('form',
                    '<?=$BASE_URL?>',
                    function(result)
                    {
                        //alert('success function ' + result);
                        //console.log(result);

                        // 송신버튼 클릭불가
                        $("#id_close_cert").prop("disabled", true);
                        $("#id_valid_cert").prop("disabled", true);
                        $("#id_private_key_download").prop("disabled", true);
                        $("#id_crt_download").prop("disabled", true);
                        $("#id_root_crt_download").prop("disabled", true);
                        $("#id_csr_download").prop("disabled", true);
                        $("#id_pkcs12_download").prop("disabled", true);

                        // 폼의 모든 입력값 입력불가
                        $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );
                        $("#form2").find("*").prop("disabled", true);
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
        });

        // 인증서 검증
        $("#id_valid_cert").on('click', function()
        {
            $("#id_a").prop("value", "host_view_detail_exec");
            $("#id_cert_name").prop("value", name);

            $("#id_user_pw").removeClass("has-error");
            $("#id_user_pw").addClass("hide");
            $("#user_pw").prop("value", "");

            ajax_send('form',
                '<?=$BASE_URL?>',
                function(result)
                {
                    //alert('success function ' + result);
                    //console.log(result);

                    $("#id_cert_detal").removeClass("hide");
                    $("#id_log").empty().append(result.log);
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
                    console.log(err);
                }
            );

        });

        // pkcs12 다운로드
        $("#id_pkcs12_download").on('click', function()
        {
            $("#id_a2").prop("value", "host_pkcs12_exec");
            $("#id_cert_name2").prop("value", name);

            $("#id_user_pw").removeClass("has-error");
            $("#id_user_pw").addClass("hide");
            $("#user_pw").prop("value", "")

            $("#id_pkcs_pw").addClass("has-error");
            $("#id_pkcs_pw").removeClass("hide");
            if ($("#pkcs_pw").prop("value") == "")
            {
                $("#pkcs_pw2").prop("value", "");
            }
            else
            {
                ajax_send('form2',
                    '<?=$BASE_URL?>',
                    function(result)
                    {
                        //alert('success function ' + result);
                        //console.log(result);

                        $("#id_pkcs_pw").removeClass("has-error");
                        $("#id_pkcs_pw").addClass("hide");
                        $("#pkcs_pw").prop("value", "");
                        $("#pkcs_pw2").prop("value", "");

                        location.href="<?=$BASE_URL?>?a=dn&n=" + name + "&f=" + result.pkcs12_file;
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
        });

        // click on button submit
        $("#id_private_key_download").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=dn&n=" + name + "&f=" + privateKey;
        });
        $("#id_crt_download").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=dn&n=" + name + "&f=" + crtFile;
        });

        $("#id_root_crt_download").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=dn&n=rootca&f=" + rootCrtFile;
        });

        $("#id_csr_download").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=dn&n=" + name + "&f=" + csrFile;
        });

        $("#id_back_list").on('click', function()
        {
            location.href = "<?=$BASE_URL?>?a=host";
        });

    });

</script>
<?php
}
?>
