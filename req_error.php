<?php
/****************************************************************************/
/* 오류처리화면                                                             */
/****************************************************************************/
// php파일을 직접호출 금지
if ($PROGRAM_NAME == "") die("ERROR");

$PAGE_TITLE = "Error";
$PAGE_DESC = "";

function print_contents()
{
    global $ERROR_TITLE;
    global $ERROR_MESSAGE;

if ($ERROR_TITLE == "")
    $ERROR_TITLE = "미지정 오류입니다.";
if ($ERROR_MESSAGE == "")
    $ERROR_MESSAGE = "에러메시지가 셋팅되지 않은 오류입니다.";

?>
<div class="alert alert-danger alert-dismissible">
  <!--
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  -->
  <h4><i class="icon fa fa-ban"></i> <?=$ERROR_TITLE?></h4>
    <?=$ERROR_MESSAGE?>
</div>

<?
}
?>
