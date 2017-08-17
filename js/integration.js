"use strict";
var po = {
    options:{
        fee:{
            indoor:{
                amount:{
                    min:50,
                    max:75000
                },
                min:50,
                percent:.015
            },
            crossboard:{
                amount:{
                    min:50,
                    max:75000
                },
                min:200,
                percent:.02
            }
        },
        rates:{
            RUB:1,
            GRN:.43014217,
            USD:.016822
        },
        symbols:{
            GRN:'<b>&#8372;</b>',
            RUB:'<b>&#8381;</b>',
            USD:'<b>$</b>'
        },
        delayed:false
    },
    transferType:function(){
        var res=(($("[name=currency]").val() == $("[name=currencyto]").val())?"indoor":"crossboard");
        // console.debug("transferType()="+res);
        return res;
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
        opt = opt[this.transferType()];
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
        res.amount = amt/(1+opt.percent);
        res.fee_real = amt-res.amt;
        res.fee = (res.fee_real<opt.min)?opt.min:res.fee_real;
        res.amount = amt-res.fee;
        return res;
    },
    exrate:function(a){
        var amt = (a.amount !== undefined)?a.amount:0, opt = this.getoptions("rates"),  res = {
            amount:amt,
            from:amt,
            exrate:1,
            response:"ok",
            code:0
        },currency = $("[name=currencyto]").val();
        res.exrate = opt[currency];
        res.amount = opt[currency]*res.from;
        res.amount = parseFloat(res.amount.toFixed(2));
        return res;
    },
    setDataText:function(fee,exr){
        console.debug(fee,exr);
        var $c = $(".fee"),s='',syms = this.getoptions("symbols"),currency={from:$("[name=currency]").val(),to:$("[name=currencyto]").val()};
        s+='<p>Комиссия за перевод: <code>'+fee.fee+syms[currency.from]+'</code></p>';
        (this.transferType()=="crossboard")?s+='<p>По курсу: <code>1'+syms[currency.from]+' = '+exr.exrate+syms[currency.to]+'</code></p>':{};
        s+='<p>Сумма получателя: <code>'+exr.amount+syms[currency.to]+'</code></p>';
        s+='<p>Сумма списания: <code>'+(parseFloat(exr.from)+parseFloat(fee.fee))+syms[currency.from]+'</code></p>';
        $(".transfer-amount-to").val(exr.amount);
        $c.html(s);
    }
};
$(document).ready(function(){
    $("#continue").on("click",function(e){
        var f = po.fee({amount:$(".transfer-amount-from").val()});
        $.ajax({
            url:"/request",
            type:"post",
            dataType:"json",
            data:{
                amount:(parseFloat(f.amount)+parseFloat(f.fee)).toFixed(2),
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
    $(".transfer-amount-from").on("keyup change",function(e){
        $(this).delay(1000);
        var amt = {amount:$(this).val()},f = po.fee(amt), r= po.exrate(amt);
        po.setDataText(f,r);
    });
    $("[name=currencyto], [name=currency]").on("change",function(){
        $(".transfer-amount-from").change();
    });
    $(".transfer-amount-from").change();
    $(".transfer-amount-to").on("keyup change",function(e){
        $(this).delay(1000);
        var amt = $(this).val(),r = po.feeback({amount:amt});
        console.debug(r);
    });

});
