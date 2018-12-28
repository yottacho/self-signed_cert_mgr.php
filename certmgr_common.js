$(function () {

    // 서버 전송중 다이얼로그
    $("body").append(
'<div id="id_ajax_send" class="modal fade">' +
'  <div class="modal-dialog">' +
'    <div class="modal-content">' +
'      <div class="modal-header">' +
'        <h4 id="id_ajax_send_title" class="modal-title"><i class="fa fa-spin fa-refresh"></i> 처리중입니다.</h4>' +
'      </div>' +
'      <div class="modal-body">' +
'        <p id="id_ajax_send_message">서버 처리중입니다.</p>' +
'      </div>' +
'      <div id="id_ajax_send_close" class="modal-footer">' +
'        <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">닫기</button>' +
'      </div>' +
'    </div>' +
'  </div>' +
'</div>'
    );

/*
// overlay 는 box에 삽입
    $("body").append(
'<div id="id_ajax_send_overay" class="overlay">' +
'  <i class="fa fa-refresh fa-spin"></i>' +
'</div>'
    );
*/

    // 서버 전송중 다이얼로그 설정
    // 이벤트 밖에서 한 번만 셋팅해야 함
    // 이벤트 내에서 셋팅하면 'shown.bs.modal' 이벤트가 체인 형태로 등록되서 실행 버튼을 클릭한 횟수만큼 누적되어 반복 실행됨
    $("#id_ajax_send").on('show.bs.modal', function(e)
    {
        $("#id_ajax_send").removeClass("modal-warning");
        $("#id_ajax_send_close").hide();

        $("#id_ajax_send_title").html('<i class="fa fa-spin fa-refresh"></i> 처리중입니다.');
        $("#id_ajax_send_message").text("서버 처리중입니다 ...");
    })
    .on('shown.bs.modal', function(e)
    {
        $.ajax(_ajax_param);
    });

    // 전송 파라마터
    var _ajax_param;

    // 외부 함수 export
    ajax_send = function (form_id, action_url, callback_success, callback_error)
    {
        //$("#id_ajax_send_overay").removeClass("hide");

        // 송신전 체크
        var requiredError = {
            error: "",
            form: []
        };

        $("#" + form_id).find("input,select").each(function() {
            // required 가 있는데 id 를 셋팅하지 않으면 오류 처리
            if ($(this).prop("required") && $(this).prop("id") == "")
            {
                alert("id not defined! " + $(this).prop("name"));
            }

            $(this).parent("div").removeClass("has-error");
            $(this).next(".help-block").addClass("hide");

            // 필수입력항목 체크
            if ($(this).prop("required") && $(this).prop("value") == "")
            {
                // 필수입력항목 미입력!
                requiredError.error = "Required";
                requiredError.form[$(this).prop("id")] = "Required";

                //$(this).parent("div").addClass("has-error");
                //$(this).next(".help-block").removeClass("hide").text("Required");
            }
        });

        // 필수항목 미입력이 있으면 송신하지 않는다.
        if (requiredError.error != "")
        {
            if (typeof callback_error == "function")
                callback_error(requiredError);
            return;
        }

        _ajax_param = {
            url: action_url,                        // url where to submit the request
            async: 'true',                          // send mode: async
            timeout: 30000,                         // timeout 30 sec
            type: 'POST',                           // type of action POST || GET
            dataType: 'json',                       // data type
            data: $("#" + form_id).serialize(),     // post data || get data
            success : function(result) {
                // you can see the result from the console
                // tab of the developer tools
                console.log(result);

                $("#id_ajax_send").modal('hide');

                if (typeof callback_success == "function")
                    callback_success(result);
            },
            error: function(xhr, resp, text) {
                console.log(xhr, resp, text);

                var errtitle = "";
                var errmsg = "";
                var callback_error_msg = null;
                if (resp == null)
                {
                    errmsg = "Unknown error:" + text;
                }
                // 서버 오류응답(ex 500 Internal server error)
                else if (resp == "error")
                {
                    // 타이틀에는 오류코드와 오류메시지를 넣는다.
                    errtitle = "- " + xhr.status + " " + xhr.statusText;

                    console.log(typeof xhr.responseJSON);

                    // 메시지가 JSON 포맷인가?
                    if (typeof xhr.responseJSON != "undefined")
                    {
                        // 추가 메시지가 json 포맷이면 error 필드가 있는지 확인한다.
                        if (xhr.responseJSON.hasOwnProperty("error"))
                        {
                            // json error 메시지를 표시한다.
                            errmsg = xhr.responseJSON.error;
                            callback_error_msg = xhr.responseJSON;
                        }
                        else
                        {
                            // 추가 메시지 전체를 넣는다.
                            errmsg = "Server message.<br>----------<br>" + xhr.responseText;
                        }
                    }
                    // 서버에서 추가 메시지가 있는가?
                    else if (xhr.responseText != "")
                    {
                        // 추가 메시지 전체를 넣는다.
                        errmsg = "Server message.<br>----------<br>" + xhr.responseText;
                    }
                    else
                    {
                        errmsg = "Response message is blank";
                    }
                }
                // 정상응답인데 응답데이터가 JSON이 아닌 경우
                else if (resp == "parsererror")
                {
                    errmsg = "처리결과 메시지를 해석할 수 없습니다. 처리 결과를 확인하세요.<br>----------<br>" + xhr.responseText;
                }

                // error 모드로 클래스 변경
                $("#id_ajax_send_title").text("Server Error " + errtitle);
                $("#id_ajax_send_message").html(errmsg);
                $("#id_ajax_send").addClass("modal-warning");

                $("#id_ajax_send_close").show();

                // 일반 오류일 경우 null이 들어가고 json 응답이 있으면 해당 메시지가 들어감
                if (typeof callback_error == "function")
                    callback_error(callback_error_msg);
            }
        };

        // 송신레이어 구성
        $("#id_ajax_send").modal('show');
    };

})

