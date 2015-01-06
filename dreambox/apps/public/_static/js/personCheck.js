function clearErrorMsg() {
	$('.err-msg').remove();
}

function addErrorMsg(name, msg) {
	var ele = "input[name=" + name + "]";
	_div = $('<div class="err-msg">请输入正确的姓名！</div>');
	_div.text(msg);
	$(ele).parents('td').append(_div);
}