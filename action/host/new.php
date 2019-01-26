<?
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
<?
        return;
    }

    if (($rootCaInfo = get_rootca()) === false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        루트 인증서가 없습니다. 루트 인증서를 발급 후 호스트 인증서를 발급하세요.
      </div>
<?
    }

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">호스트 인증서 발급 정보(자동)</h3>
        </div>
        <form id="form" class="form-horizontal" action="" method="post">
        <input type="hidden" name="a" value="host_new_exec">
        <div class="box-body">
          <p>인증서 생성에 필요한 호스트 개인키, 인증요청서, 인증서를 자동으로 생성합니다.</p>

<!-- 인증서 이름 -->
          <div class="form-group">
            <label for="certificateName" class="col-sm-2 control-label">인증서 이름</label>
            <div class="col-sm-10">
              <input id="certificateName" type="text" name="certificateName" maxlength="32" class="form-control" placeholder="인증서 이름 (IP_hostname, 예 192_168_0_1_dev)" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

<!-- 국가, 조직명 => ROOT CA 에서 기본값 추출 -->
          <div class="form-group">
            <label for="countryName" class="col-sm-2 control-label">Country Name</label>
            <div class="col-sm-2">
              <input id="countryName" type="text" name="countryName" maxlength="2" class="form-control" placeholder="국가코드 (2자리)" value="<?=$rootCaInfo['countryName']?>" required>
              <span class="help-block hide">Help block with error</span>
            </div>

            <label for="organizationName" class="col-sm-2 control-label">Organization Name</label>
            <div class="col-sm-6">
              <input id="organizationName" type="text" name="organizationName" maxlength="32" class="form-control" placeholder="회사명" value="<?=$rootCaInfo['organizationName']?>" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="organizationalUnitName" class="col-sm-2 control-label">Organizational Unit</label>
            <div class="col-sm-10">
              <input id="organizationalUnitName" type="text" name="organizationalUnitName" maxlength="32" class="form-control" placeholder="서버용도, 서버명" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="commonName" class="col-sm-2 control-label">Common Name</label>
            <div class="col-sm-10">
              <input id="commonName" type="text" name="commonName" maxlength="64" class="form-control" placeholder="도메인명" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="days" class="col-sm-2 control-label">유효기간(일)</label>
            <div class="col-sm-4">
              <input id="days" type="text" name="days" class="form-control" placeholder="유효기간" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
            <div class="col-sm-6">
            </div>
          </div>

          <div class="form-group">
            <label for="DNS_1" class="col-sm-1 control-label">DNS.1</label>
            <div class="col-sm-3">
              <input id="DNS_1" type="text" name="DNS_1" maxlength="64" class="form-control" placeholder="실제 DNS" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="DNS_2" class="col-sm-1 control-label">DNS.2</label>
            <div class="col-sm-3">
              <input id="DNS_2" type="text" name="DNS_2" maxlength="64" class="form-control" placeholder="실제 DNS" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="DNS_3" class="col-sm-1 control-label">DNS.3</label>
            <div class="col-sm-3">
              <input id="DNS_3" type="text" name="DNS_3" maxlength="64" class="form-control" placeholder="실제 DNS" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="IP_1" class="col-sm-1 control-label">IP.1</label>
            <div class="col-sm-3">
              <input id="IP_1" type="text" name="IP_1" maxlength="64" class="form-control" placeholder="서버 IP" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="IP_2" class="col-sm-1 control-label">IP.2</label>
            <div class="col-sm-3">
              <input id="IP_2" type="text" name="IP_2" maxlength="64" class="form-control" placeholder="서버 IP" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="IP_3" class="col-sm-1 control-label">IP.3</label>
            <div class="col-sm-3">
              <input id="IP_3" type="text" name="IP_3" maxlength="64" class="form-control" placeholder="서버 IP" value="">
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="hostCertPassword" class="col-sm-2 control-label">인증서 비밀번호</label>
            <div class="col-sm-4">
              <input id="hostCertPassword" type="password" name="hostCertPassword" class="form-control" placeholder="인증서 비밀번호" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
            <label for="hostCertPassword2" class="col-sm-2 control-label">비밀번호 재입력</label>
            <div class="col-sm-4">
              <input id="hostCertPassword2" type="password" name="hostCertPassword2" class="form-control" placeholder="인증서 비밀번호" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>
          </div>

          <div class="form-group">
            <label for="rootCertPassword" class="col-sm-2 control-label">루트인증서 비밀번호</label>
            <div class="col-sm-4">
              <input id="rootCertPassword" type="password" name="rootCertPassword" class="form-control" placeholder="루트인증서 패스워드" value="" required>
              <span class="help-block hide">Help block with error</span>
            </div>

            <label for="rootCertName" class="col-sm-2 control-label">루트인증서 이름</label>
            <div class="col-sm-4">
              <?=$rootCaInfo['certificateName']?>
            </div>
          </div>

        </div>
        </form>
        <div class="box-footer">
          <button id="submit" type="submit" class="btn btn-info pull-right">인증서 생성</button>
        </div>
      </div>

      <div id="cert_result" class="box hide"> <!-- hide -->
        <div class="box-header with-border">
          <h3 class="box-title">인증서 다운로드</h3>
        </div>
        <div class="box-body">

          <center>
            <p>호스트 인증서는 개인(비밀)키 파일과 crt 파일을 웹서버에 배포합니다.<br>
            루트 인증서의 <code><?=$rootCaInfo['crtFile']?></code>도 함께 배포합니다.</p>
          <div class="btn-group">

            <button id="id_private_key_download" class="btn btn-primary">
              <i class="fa fa-lock"></i> 개인(비밀)키
            </button>

            <button id="id_private_key2_download" class="btn btn-success" data-toggle="modal" data-target="#modal-passwd-input">
              <i class="fa fa-unlock"></i> 개인(비밀)키 (for web server)
            </button>

            <button id="id_crt_download" class="btn btn-success">
              <i class="fa fa-file-text"></i> 호스트 인증서 (.crt)
            </button>

            <button id="id_root_crt_download" class="btn btn-success">
              <i class="fa fa-bolt"></i> 루트 인증서 (.crt)
            </button>

            <button id="id_csr_download" class="btn btn-default">
              <i class="fa fa-user-secret"></i> 호스트 인증요청서 (csr)
            </button>

          </div>
          </center>
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

<script>
    var certName = "";
    var privateKey = "";
    var crtFile = "";
    var csrFile = "";
    var rootCrtFile = "";
</script>

<div class="modal fade" id="modal-passwd-input">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Usage</h4>
      </div>
      <div class="modal-body">
        <b>Private key 암호 해제:</b>
        <p>&gt; openssl rsa -in private.key -out private2.key<br>
        Enter pass phrase for private.key: 비밀번호 입력</p>
      </div>
      <div class="modal-footer">
        <!--
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        -->
        <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


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
                    //console.log(result);

                    // 송신버튼 클릭불가
                    $("#submit").prop("disabled", true);
                    // 폼의 모든 입력값 입력불가
                    $("#form").find("*").prop("disabled", true); //.each(function() { $(this).prop("disabled", true) } );

                    $("#id_log").empty().append(result.log);
                    certName    = result.certificateName;
                    privateKey  = result.privKeyFile;
                    crtFile     = result.crtFile;
                    csrFile     = result.csrFile;
                    rootCrtFile = result.rootCrtFile;

                    // 인증서 링크를 활성화하고 다운로드한다.
                    $("#cert_result").removeClass("hide");
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
        $("#id_private_key_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + certName + "&f=" + privateKey;
        })
        $("#id_private_key2_download").on('click', function()
        {
            //location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=rootca&f=" + privateKey;
            //var hostpw = $("#hostCertPassword").prop("value");
            //alert('개발중... ' + hostpw);
        })
        $("#id_crt_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + certName + "&f=" + crtFile;
        })
        $("#id_root_crt_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=rootca&f=" + rootCrtFile;
        })
        $("#id_csr_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + certName + "&f=" + csrFile;
        })

    });


</script>
<?
}
?>
