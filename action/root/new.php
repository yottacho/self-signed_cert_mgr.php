<?php
/****************************************************************************/
/* 신규 루트 인증서 생성 화면                                             */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "신규 루트 인증서 생성";
$PAGE_DESC = "루트 인증서를 만듭니다";

$is_rootca = false;

function print_contents()
{
    global $_SESSION;
    global $CERT_DATA;
    global $is_rootca;
    global $BASE_URL;

    if ($_SESSION['user_role'] != "admin")
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        admin 권한이 필요합니다.
      </div>
<?php
        return;
    }

    if (($rootCaInfo = get_rootca()) !== false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        이미 발급된 루트 인증서가 있습니다.
      </div>
<?php
        $is_rootca = true;
        //return;
    }

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Root Certificate Information</h3>
        </div>
        <form id="form" class="form-horizontal" action="" method="post">
        <input type="hidden" name="a" value="root_new_exec">
        <div class="box-body">
          <p>호스트 인증서 생성에 필요한 루트 인증서의 개인키, 인증요청서, 인증서를 자동으로 생성합니다.</p>

<!-- 인증서 이름 -->
          <div class="form-group">
            <label for="certificateName" class="col-sm-2 control-label">Certificate Name</label>
            <div class="col-sm-10">
              <input id="certificateName" type="text" name="certificateName" maxlength="32" class="form-control" placeholder="Certificate name" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<!-- 국가, 조직명 -->
          <div class="form-group">
            <label for="countryName" class="col-sm-2 control-label">Country Name</label>
            <div class="col-sm-2">
              <input id="countryName" type="text" name="countryName" maxlength="2" class="form-control" placeholder="Country code" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>

            <label for="organizationName" class="col-sm-2 control-label">Organization Name</label>
            <div class="col-sm-6">
              <input id="organizationName" type="text" name="organizationName" maxlength="32" class="form-control" placeholder="Company name" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<!-- 주 도메인명 -->
          <div class="form-group">
            <label for="organizationalUnitName" class="col-sm-2 control-label">Organizational Unit</label>
            <div class="col-sm-10">
              <input id="organizationalUnitName" type="text" name="organizationalUnitName" maxlength="32" class="form-control" placeholder="(Internet) Domain name" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<!-- CA 명 -->
          <div class="form-group">
            <label for="commonName" class="col-sm-2 control-label">Common Name</label>
            <div class="col-sm-10">
              <input id="commonName" type="text" name="commonName" maxlength="64" class="form-control" placeholder="CA name" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="days" class="col-sm-2 control-label">Valid days</label>
            <div class="col-sm-4">
              <input id="days" type="text" name="days" class="form-control" placeholder="days recommanded 7300 or more" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="serial" class="col-sm-2 control-label">Serial</label>
            <div class="col-sm-4">
              <input id="serial" type="text" name="serial" class="form-control" placeholder="Serial number" value="1" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="rootCertPassword" class="col-sm-2 control-label">Master Password</label>
            <div class="col-sm-4">
              <input id="rootCertPassword" type="password" name="rootCertPassword" class="form-control" placeholder="Password for all certificate" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>

            <label for="rootCertPassword2" class="col-sm-2 control-label">Retype Password</label>
            <div class="col-sm-4">
              <input id="rootCertPassword2" type="password" name="rootCertPassword2" class="form-control" placeholder="Password for all certificate" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

        </div>
        </form>
        <div class="box-footer">
            <div class="col-sm-6">
                <button id="id_view_cert" class="btn btn-success hide">
                    <i class="fa fa-lock"></i> View Certificate
                </button>
            </div>
            <div class="col-sm-6">
                <button id="submit" type="submit" class="btn btn-info pull-right">Create</button>
            </div>
        </div>
      </div>


      <div id="cert_result_log" class="box box-info hide"> <!-- hide -->
        <div class="box-header with-border">
          <h3 class="box-title">Status Log</h3>
        </div>
        <div class="box-body">
          <span id="id_log">
          </span>
        </div>
      </div>

<?php
}

function footer_scripts()
{
    global $is_rootca;
    global $BASE_URL;

    if ($is_rootca == true)
    {
?>
<script>
    $(document).ready(function()
    {
        // 송신버튼 클릭불가
        $("#submit").prop("disabled", true);
        // 폼의 모든 입력값 입력불가
        $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );
        $("#id_view_cert").removeClass("hide");

        // click on button submit
        $("#id_view_cert").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=root";
        })
    });
</script>
<?php
    }
    else
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

                    $("#id_log").empty().append(result.log);

                    // 인증서 링크를 활성화하고 다운로드한다.
                    $("#cert_result_log").removeClass("hide");
                    $("#id_view_cert").removeClass("hide");
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

        // click on button submit
        $("#id_view_cert").on('click', function()
        {
            location.href="<?=$BASE_URL?>?a=root";
        })
    });


</script>
<?php
    }
}
?>
