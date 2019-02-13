<?php
/****************************************************************************/
/* 호스트 인증서 목록                                                       */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "호스트 인증서 목록";
$PAGE_DESC = "호스트(서버) 인증서 조회";

function print_contents()
{
?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">호스트 인증서 목록</h3>
        </div>
        <div class="box-body">
          <table id="host_cert" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>No</th>
              <th>인증서 이름</th>
              <th>Organizational Unit</th>
              <th>Common Name</th>
              <th>발급일</th>
              <th>만료일</th>
              <th>작업자</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
            <!--
            <tfoot>
            <tr>
              <th>Rendering engine</th>
              <th>Browser</th>
              <th>Platform(s)</th>
              <th>Engine version</th>
              <th>CSS grade</th>
              <th>Created</th>
            </tr>
            </tfoot>
            -->
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
              <i class="glyphicon glyphicon-search"></i> 보기
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
    var table = $('#host_cert').DataTable({
        ajax: {
            url: '<?=$BASE_URL?>?a=host_list',
            dataSrc: 'list'
        },
        columns: [
            { data: 'no' },
            { data: 'name' },
            { data: 'subjectOU' },
            { data: 'subjectCN' },
            { data: 'startDate' },
            { data: 'endDate' },
            { data: 'user' }
        ],
        columnDefs: [
          {
              render: function(data, type, row)
              {
                  if (data.endsWith(".closed"))
                  {
                      data = '<span class="text-red">' + data + '</span>';
                  }

                  return data;
              },
              targets: 1
          }
        ]
    });

    $('#host_cert tbody').on('click', 'tr', function()
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

        location.href="<?=$BASE_URL?>?a=host_view&n=" + rowData.name;
    });

    /* $("#host_cert_load").hide() */
  })

if (!String.prototype.endsWith) {
    String.prototype.endsWith = function(search, this_len) {
        if (this_len === undefined || this_len > this.length) {
            this_len = this.length;
        }
        return this.substring(this_len - search.length, this_len) === search;
    };
}
</script>
<?php
}
?>
