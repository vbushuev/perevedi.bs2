"use strict";
var po = {
    options:{
        fee:{
            amount:{
                min:50,
                max:75000
            },
            min:39,
            percent:.015
        },
        delayed:false
    },
    getoptions:function(o){
        return this.options[o];
    },
    fee:function(a){
        var amt = (a.amount !== undefined)?a.amount:0, opt = this.getoptions("fee"),  res = {
            amount:amt,
            fee:0,
            response:"ok",
            code:0
        };
        // console.debug(opt);
        if( amt < opt.amount.min) {res.response = "less then minimum amount";res.code=-1;return res;}
        if( opt.amount.max< amt) {res.response =  "more then maximum amount";res.code=-2;return res;}
        res.fee_real = amt*opt.percent;
        res.fee = (res.fee_real<opt.min)?opt.min:res.fee_real;
        return res;
    },
    feeback:function(a){
        var amt = (a.amount !== undefined)?a.amount:0, opt = this.getoptions("fee"),  res = {
            amount:amt,
            fee:0,
            response:"ok",
            code:0
        };
        // console.debug(opt);
        res.amt = amt/(1+opt.percent);
        res.fee_real = amt-res.amt;
        res.fee = (res.fee_real<opt.min)?opt.min:res.fee_real;
        res.amt = amt-res.fee;
        return res;
    }
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
                    $("#amount-block").fadeOut();
                    $("#iframeoverlay").fadeOut();
                    $("#fakeform").replaceWith('<iframe src="'+d["redirect-url"]+'"></iframe>')
                }
            }
        });
    });
    $.ajax({
        url:"//perevedi.online/lang",
        dataType:"json",
        crossDomain:true,
        success:function(d){
            console.debug(d);
            $("#"+d.lang).click();
        }
    });
    $(".transfer-amount").on("keyup change",function(e){
        $(this).delay(1000);
        var amt = $(this).val(),r = po.feeback({amount:amt});
        console.debug(r);
    });
});
