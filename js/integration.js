"use strict";
var po = {

};
$(document).ready(function(){
    $("#continue").on("click",function(e){
        $.ajax({
            url:"/request",
            type:"post",
            dataType:"json",
            data:{
                amount:$("[name=amount]").val(),
                currency:$("[name=currency]").val()
            },
            success:function(d){
                console.debug(d);
                if(typeof(d["error-code"])=="undefined"){
                    $("#amount-block").css("opacity","0");
                    $("#iframeoverlay").css("opacity","0");
                    $("#fakeform").replaceWith('<iframe src="'+d["redirect-url"]+'"></iframe>')
                }
            }
        });
    });
});
