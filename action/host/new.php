<?php
/****************************************************************************/
/* 신규 호스트 인증서 생성 화면                                             */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "신규 호스트 인증서 생성";
$PAGE_DESC = "호스트(서버) 인증서를 만듭니다";

function print_contents()
{
    global $CERT_DATA;

    if ($_SESSION['user_role'] != "host_manager")
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        host_manager 권한이 필요합니다.
      </div>
<?php
        return;
    }

    if (($rootCaInfo = get_rootca()) === false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        루트 인증서가 없습니다. 루트 인증서를 발급 후 호스트 인증서를 발급하세요.
      </div>
<?php
    }

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">호스트 인증서 발급 정보</h3>
        </div>
        <form id="form" class="form-horizontal" action="" method="post">
        <input type="hidden" name="a" value="host_new_exec">
        <div class="box-body">
          <p>인증서 생성에 필요한 호스트 개인키, 인증요청서, 인증서를 자동으로 생성합니다.</p>

<!-- 인증서 이름 -->
          <div class="form-group">
            <label for="certificateName" class="col-sm-2 control-label">인증서 이름</label>
            <div class="col-sm-10">
              <input id="certificateName" type="text" name="certificateName" maxlength="32" class="form-control" placeholder="IP_hostname recommanded, ex 192_168_0_1_dev" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<!-- 국가, 조직명 => ROOT CA 에서 기본값 추출 -->
          <div class="form-group">
            <label for="countryName" class="col-sm-2 control-label">Country Name</label>
            <div class="col-sm-2">
              <input id="countryName" type="text" name="countryName" maxlength="2" class="form-control" placeholder="Country code" value="<?=$rootCaInfo['countryName']?>" required>
              <span class="help-block hide">Help block with error</span>
            </div>

            <label for="organizationName" class="col-sm-2 control-label">Organization Name</label>
            <div class="col-sm-6">
              <input id="organizationName" type="text" name="organizationName" maxlength="32" class="form-control" placeholder="Company name" value="<?=$rootCaInfo['organizationName']?>" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="organizationalUnitName" class="col-sm-2 control-label">Organizational Unit</label>
            <div class="col-sm-10">
              <input id="organizationalUnitName" type="text" name="organizationalUnitName" maxlength="32" class="form-control" placeholder="hostname or uses" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="commonName" class="col-sm-2 control-label">Common Name</label>
            <div class="col-sm-10">
              <input id="commonName" type="text" name="commonName" maxlength="64" class="form-control" placeholder="Full domain name" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="days" class="col-sm-2 control-label">유효기간(일)</label>
            <div class="col-sm-4">
              <input id="days" type="text" name="days" class="form-control" placeholder="3650 recommanded" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
            <div class="col-sm-6">
            </div>
          </div>

          <div class="form-group">
            <label for="DNS_1" class="col-sm-1 control-label">DNS.1</label>
            <div class="col-sm-3">
              <input id="DNS_1" type="text" name="DNS_1" maxlength="64" class="form-control" placeholder="DNS Name or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="DNS_2" class="col-sm-1 control-label">DNS.2</label>
            <div class="col-sm-3">
              <input id="DNS_2" type="text" name="DNS_2" maxlength="64" class="form-control" placeholder="DNS Name or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="DNS_3" class="col-sm-1 control-label">DNS.3</label>
            <div class="col-sm-3">
              <input id="DNS_3" type="text" name="DNS_3" maxlength="64" class="form-control" placeholder="DNS Name or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="IP_1" class="col-sm-1 control-label">IP.1</label>
            <div class="col-sm-3">
              <input id="IP_1" type="text" name="IP_1" maxlength="64" class="form-control" placeholder="Host IP or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="IP_2" class="col-sm-1 control-label">IP.2</label>
            <div class="col-sm-3">
              <input id="IP_2" type="text" name="IP_2" maxlength="64" class="form-control" placeholder="Host IP or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="IP_3" class="col-sm-1 control-label">IP.3</label>
            <div class="col-sm-3">
              <input id="IP_3" type="text" name="IP_3" maxlength="64" class="form-control" placeholder="Host IP or blank" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

        </div>
        </form>
        <div class="box-footer">
            <div class="col-sm-12">
                유의: IP로 발급할 경우 DNS에도 IP를 중복해서 입력해야 Internet Explorer에서 인증서 검증 오류가 발생하지 않습니다.<br>
                (Internet Explorer는 표준 준수가 미흡하여 IP필드를 인식하지 못하고 DNS필드만 인식하므로 DNS필드에 IP가 있어야 하고,
                다른 브라우저는 정상적으로 IP필드만 인식하므로 IP 인증서는 IP필드와 DNS필드에 중복해서 IP를 입력해야 호환성 오류가 발생하지 않습니다.)
            </div>
            <div class="col-sm-6">
                <button id="id_view_cert" class="btn btn-success hide">
                    <i class="fa fa-lock"></i> 인증서 확인 및 다운로드
                </button>
            </div>
            <div class="col-sm-6">
                <button id="submit" type="submit" class="btn btn-info pull-right">인증서 생성</button>
            </div>

        </div>
      </div>

      <div id="cert_result_log" class="box box-info hide"> <!-- hide -->
        <div class="box-header with-border">
          <h3 class="box-title">인증서 상태</h3>
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
    global $BASE_URL;
?>

<script src="certmgr_common.js"></script>
<script>
    $(document).ready(function()
    {
        // click on button submit
        $("#submit").on('click', function()
        {
            ajax_send('form',
                '<?=$BASE_URL?>',
                function(result)
                {
                    //alert('success function ' + result);
                    //console.log(result);

                    // 송신버튼 클릭불가
                    $("#submit").prop("disabled", true);
                    // 폼의 모든 입력값 입력불가
                    $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );

                    $("#id_log").empty().append(result.log);

                    // 인증서 링크를 활성화하고 다운로드한다.
                    $("#id_view_cert").removeClass("hide");
                    $("#cert_result_log").removeClass("hide");

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
            location.href="<?=$BASE_URL?>?a=host_view&n=" + $("#certificateName").prop("value");
        })

    });


</script>
<?php
}
?>
