<?
/****************************************************************************/
/* 루트 인증서 조회 화면                                                    */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "루트 인증서 조회";
$PAGE_DESC = "루트 인증서 상태를 조회합니다";

function print_contents()
{
    global $_SESSION;
    global $CERT_DATA;

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

    if (($rootCaInfo = get_rootca()) === false)
    {
?>
      <div class="alert alert-danger alert-dismissible">
        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
        발급된 루트 인증서가 없습니다.
      </div>
<?
    }
?>

      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">루트 인증서 정보</h3>
        </div>
        <div class="box-body">

          <div class="list-group">
            <div class="list-group-item col-sm-2">인증서 이름</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['certificateName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">발급자</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['organizationName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">조직</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['organizationalUnitName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">발급대상</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['commonName']?>
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">국가</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['countryName']?>
            </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-2">유효기간</div>
            <div class="list-group-item col-sm-10"><?=explode(' ', $rootCaInfo['startDateLocal'])[0]?> ~
              <?=explode(' ', $rootCaInfo['endDateLocal'])[0]?>
              (<?=$rootCaInfo['days']?> days)
            </div>
          </div>
          <div class="list-group">
            <div class="list-group-item col-sm-2">발급일련번호</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['serial']?>
            </div>
          </div>

          <div class="list-group">
            <div class="list-group-item col-sm-2">발급자</div>
            <div class="list-group-item col-sm-10"><?=$rootCaInfo['user']?>
            </div>
          </div>

        </div>
        <div class="box-footer">
          <center>
            <p>루트 인증서는 <code><?=$rootCaInfo['crtFile']?></code> 파일을 웹서버와 PC에 배포합니다.<br>
            Private Key 파일 또는 CSR파일은 배포하지 않습니다.</p>
          <div class="btn-group">

            <button id="id_private_key_download" class="btn btn-primary">
              <i class="fa fa-lock"></i> Private key
            </button>

            <button id="id_crt_download" class="btn btn-success">
              <i class="fa fa-bolt"></i> Root Certificate
            </button>

            <button id="id_csr_download" class="btn btn-default">
              <i class="fa fa-user-secret"></i> Certificate Signing Request (CSR)
            </button>

          </div>
          </center>
        </div>
      </div>

<script>
    var name = "rootca";
    var privateKey = "<?=$rootCaInfo['privKeyFile']?>";
    var crtFile = "<?=$rootCaInfo['crtFile']?>";
    var csrFile = "<?=$rootCaInfo['csrFile']?>";
</script>

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
        $("#id_private_key_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + name + "&f=" + privateKey;
        })
        $("#id_crt_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + name + "&f=" + crtFile;
        })
        $("#id_csr_download").on('click', function()
        {
            location.href="<?=$_SERVER['SCRIPT_NAME']?>?a=dn&n=" + name + "&f=" + csrFile;
        })

    });

</script>
<?
}
?>
