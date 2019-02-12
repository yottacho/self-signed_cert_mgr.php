<?php
// env.sample.php 를 env.php 로 저장합니다.

/****************************************************************************/
/* 환경 설정                                                                */
/****************************************************************************/
$OPENSSL_EXEC = "/usr/bin/openssl";
// 웹에서 접근이 불가능한 디렉터리를 지정
$CERT_DATA = "./cert";
$TIMEZONE = "Asia/Seoul";

$PROGRAM_NAME = "Certificate manager";

// 브라우저 접속 경로
$BASE_URL = $_SERVER['SCRIPT_NAME'];

?>