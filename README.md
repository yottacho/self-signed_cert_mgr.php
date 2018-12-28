# Self-signed Certificate manager for private networks

사설망용 사설인증서 관리자

### 적당한 관리시스템이 없어서 만들어 봄

기업 사설망에서 여러 서버를 운영해 보니,
사설망용 서버용 공인인증서를 발급받기는 어렵고
자체 인증서를 발급해서 적용하니 브라우저에서 경고하는 불편함이 있어
루트인증서와 서버인증서를 관리하는 프로그램을 만들었다.

하나의 사설 루트 인증서를 PC에 설치해두면 사설 서버 인증서 관리가 수월하다.

### 환경

* Linux (Windows 에서는 테스트하지 않음)
* Apache + php 또는 Nginx + php-fpm
* php-json 확장
* openssl (php 모듈이 아니라 openssl 명령어를 직접 호출함)
* tested php 5.4, php7.3
* DB는 사용하지 않음

### 설치방법

1. 웹서버를 설치하고 php가 동작하도록 설정한다.
   * php 설정에서 short_open_tag = On 을 셋팅한다.
   * 디버깅하려면 display_errors = On 을 셋팅한다.
   * index.php 를 Index File에 추가한다.
2. 프로그램을 설치한다.
3. `index.php`를 편집한다.
   * `$OPENSSL_EXEC` 에 `openssl`의 위치를 지정한다. openssl은 `which openssl`로 확인할 수 있다.
   * 적당한 디렉터리를 생성하고 `$CERT_DATA`에 지정한다.
4. `$CERT_DATA` 디렉터리 권한을 rwxrwxrwx(777) 또는 rwxrwxrwt(1777)로 설정한다.
5. SELinux가 설정되어 있으면 권한을 부여하거나 SELinux를 해제한다.
6. 브라우저로 설치한 위치에 접속한다.
    * ID와 비밀번호는 `admin`이며 로그인 후 비밀번호를 변경해야 다른 메뉴를 사용할 수 있다.

### 도움받은곳

* [lesstif.com OpenSSL 로 ROOT CA 생성 및 SSL 인증서 발급](https://www.lesstif.com/pages/viewpage.action?pageId=6979614)
* 사이트 디자인 템플리트: [AdminLTE](https://adminlte.io/) (MIT License)

