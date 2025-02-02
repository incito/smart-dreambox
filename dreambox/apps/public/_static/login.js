// 提交表单
$(document).ready(function() { 
    $('#ajax_login_form').submit(function(){
        $(this).ajaxSubmit({
            beforeSubmit:  checkLoginForm, 
            success:       loginCallback,
            dataType: 'json'
        }); 
        return false; 
    });
    // 提交发布前验证
    var checkLoginForm = function() {
        if($('#user').val().length == 0) {
            $('#user').focus();
            return false;
        }
        if($('#password').val().length == 0) {
            $('#password').focus();
            return false;
        }
        return true;
    };
    // 成功后的回调函数
    var loginCallback = function(i) {
        // var i = eval("("+e+")");
        if(i.status==1){
            $('#js_login_input').html('<p>'+i.info+'</p>').show();    
            if(i.data==0){
                window.location.href = U('public/Index/index');  
            }else{
                window.location.href = i.data;            
            }
        }else{
            $('#js_login_input').html('<p>'+i.info+'</p>').show();
        }
    };
}); 

// 登录验证
var loginCheck = {};
// 绑定验证事件
loginCheck.bindKeyEvent = function(objInput, blurObjInput, objLabel) {
    objInput.bind('keydown', function(event) {
        if(event.which == 13) {
            var aLen = blurObjInput.val().length;
            var pLen = objInput.val().length;
            if(aLen == 0) {
                objInput.blur()
                blurObjInput.focus();    
            } else if(aLen != 0 && pLen == 0) {
                objInput.focus();
            } else if(aLen != 0 && pLen != 0) {
                $('#ajax_login_form').submit();
            }
        }
    }); 
}
// 输入框验证
loginCheck.inputChecked = function(objDiv, objInput, objLabel, blurObjInput) {
    objInput.bind({
        focus: function() {
            var len = $(this).val().length;
            len == 0 && objDiv.attr('class', 'input1');
            typeof(blurObjInput) != "undefined" && loginCheck.bindKeyEvent(objInput, blurObjInput, objLabel);
        },
        keydown: function() {
            objLabel.css('display', 'none');
        },
        keyup: function() {
            var len = $(this).val().length;
            len == 0 && objLabel.css('display', '');
        },
        blur: function() {
            var len = $(this).val().length;
            if(len == 0) {
                objDiv.attr('class', 'input');
                objLabel.css('display', '');
            }
        }
    });
};


/**
 * 登录流程，JQuery插件，用于显示感知框
 */
(function($) {
    $.fn.extend({
        changeTips: function(value) {
            // 初始化选择的类名
            value = $.extend({
                divTip: ""
            }, value);

            var $this = $(this);
            var indexLi = 0;
            // 绑定li点击事件
            $(document).click(function(event) {
                if($(event.target).is("li") && typeof($(event.target).attr('email')) != "undefined") {
                    var liVal = $(event.target).text();
                    $this.val(liVal);
                    blus();
                } else {
                    blus();
                }
            });
            // 下拉框消失
            var blus = function() {
                $(value.divTip).hide();
            }
            // 选中上下移动
            var keyChange = function(up) {
                if(up == "up") {
                    if(indexLi == 0) {
                        indexLi = $(value.divTip).find('li[rel="show"]').length - 1;
                    } else {
                        indexLi--;
                    }
                } else {
                    if(indexLi == $(value.divTip).find('li[rel="show"]').length - 1) {
                        indexLi = 0;
                    } else {
                        indexLi++;
                    }
                }
                $(value.divTip).find('li[rel="show"]').eq(indexLi).addClass("current").siblings().removeClass(); 
            }
            // 改变输入框中的值
            var valChange = function() {
                var tex = $this.val();
                var fronts = "";
                var af = /@/;
                var regMail = new RegExp(tex.substring(tex.indexOf("@")));
                if($this.val() == "") {
                    blus();
                } else {
                    $(value.divTip).show().find('li').each(function(index) {
                        var valAttr = $(this).attr("email");
                        if(index == 0) {
                            $(this).text(tex).addClass('current').siblings().removeClass();
                        }
                        if(index > 0) {
                            if(af.test(tex)) {
                                fronts = tex.substring(tex.indexOf("@"), 0);
                                $(this).text(fronts + valAttr);
                                if(regMail.test($(this).attr("email"))) {
                                    $(this).attr('rel', 'show');
                                    $(this).css({position:'static', visibility:'inherit'});
                                } else {
                                    if(index > 0) {
                                        $(this).attr('rel', 'hide');
                                        $(this).css({position:'absolute', visibility:'hidden'});
                                    }
                                }
                            } else {
                                $(this).text(tex + valAttr);
                            }
                        }
                    });
                }
            }
            // 浏览器的输入的兼容性
            if($.browser.msie && $.browser.version != '9.0') {
                $(this).bind("propertychange", function() {
                    valChange();
                });
            } else {
                $(this).bind("input", function() {
                    valChange();
                });
            }
            // 触碰后的样式
            $(value.divTip).find('li').hover(function() {
                $(this).addClass("current").siblings().removeClass();
            });
            // 绑定按键事件
            $this.keydown(function(event) {
                if(event.which == 38) {
                    // 按上
                    keyChange("up");
                } else if(event.which == 40) {
                    // 按下
                    keyChange();
                } else if(event.which == 13) {
                    // 按回车
                    var liVal = $(value.divTip).find('li[rel="show"]').eq(indexLi).text();
                    $this.val(liVal);
                    blus();
                    // 焦点定位
                    typeof(value.nextFocus) != "undefined" && (value.focusInput.val().length != 0) && value.nextFocus.focus();
                } else if(event.which == 9) {
                    blus();
                }
            });
        }
    });
})(jQuery);

// 页面在载入设置可视窗口宽度与高度
$(function () {
    changeSize();
});

// 改变浏览器可视窗口宽度与高度
$(window).resize(function () {
    changeSize();
});

var changeSize = function () {
    var cssObj = {};
    cssObj.width = $(this).width();
    // cssObj.height = $(this).height();
    $('#login_bg').css(cssObj);
};