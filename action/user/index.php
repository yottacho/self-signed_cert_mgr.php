<?php
/****************************************************************************/
/* 사용자 목록                                                              */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "사용자 목록";
$PAGE_DESC = "사용자 목록 조회";

function print_contents()
{
    global $_SESSION;
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

?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">사용자 목록</h3>
        </div>
        <div class="box-body">
          <table id="user_list" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>No</th>
              <th>ID</th>
              <th>이름</th>
              <th>권한</th>
              <th>최종 로그인</th>
              <th>패스워드 오류횟수</th>
              <th>패스워드 변경일</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
          </table>

        </div>
        <!--
        <div id="host_cert_load" class="overlay">
          <i class="fa fa-refresh fa-spin"></i>
        </div>
        -->
        <div class="box-footer">
          <div class="btn-group">

            <button id="id_view_detail" class="btn btn-primary">
              <i class="glyphicon glyphicon-search"></i> View
            </button>

          </div>
        </div>

      </div>
<?php
}

function footer_scripts()
{
    global $BASE_URL;
?>
<!-- DataTables -->
<script src="css/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="css/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script>
  $(document).ready(function()
  {
    var table = $('#user_list').DataTable({
        ajax: {
            url: '<?=$BASE_URL?>?a=user_list',
            dataSrc: 'list'
        },
        columns: [
            { data: 'no' },
            { data: 'user_id' },
            { data: 'user_name' },
            { data: 'user_role' },
            { data: 'last_login_date' },
            { data: 'pw_err_cnt' },
            { data: 'last_pw_date' }
        ]
    });

    $('#user_list tbody').on('click', 'tr', function()
    {
        if ($(this).hasClass('label-primary'))
        {
            $(this).removeClass('label-primary');
        }
        else
        {
            table.$('tr.label-primary').removeClass('label-primary');
            $(this).addClass('label-primary');
        }
    })/*.on('dblclick', 'tr', function()
    {
        console.log(        $(this)     );

        //alert(rowData.name);

    })*/;

    $('#id_view_detail').click(function ()
    {
        if (table.row('.label-primary').length == 0)
            return;

        var rowData = table.row('.label-primary').data();

        //console.log(table.row('.label-primary').data());

        location.href="<?=$BASE_URL?>?a=user_view&n=" + rowData.user_id;
    });

    /* $("#host_cert_load").hide() */
  })
</script>
<?php
}
?>
