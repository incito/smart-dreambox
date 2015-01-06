$(function() {
	$('div.modal .table .tableBody:eq(1) li .btn a').click(function() {
		confirm_click($(this));
	});
	var can_do=true;
	$('div.modal .table .tableFooter .fr a.submit').click(
			function() {
				if(!can_do){
					return false;
				}
				can_do=false;
				var id1 = new Array();
				var id2 = new Array();
				$('div.modal .table .tableBody:eq(1) li .btn a').each(
						function() {
							_this = $(this);
							if (_this.is('.pass')) {
								if (_this.attr('bool') == '0'
										&& _this.is('.active')) {
									id1.push(_this.attr('item_id'));
								}
							} else {
								if (_this.attr('bool') == '0'
										&& _this.is('.active')) {
									id2.push(_this.attr('item_id'));
								}
							}
						});
				if (id1.length > 0 || id2.length > 0) {
					$.post(U('dreambox/LessonFeedback/confirm'), "id1="
							+ id1.join(",") + "&&id2=" + id2.join(","),
							function(data) {
								if(data==1){
										closeConfirm();
								}else{
									alert("操作失败");
								}
								can_do=true;
							}, 'json');
				}
			});
	$("div.modal .table .tableHead span.fr").click(function() {
		closeConfirm();
		$.get(U("dreambox/LessonFeedback/nextFeedback"));
	})
	var terms=new Array();
	var status=new Array();
	$('div.modal .table .tableBody:eq(0) li.odd a').click(function() {
		var _this=$(this);
		var _selected=$("div.modal .table .tableBody:eq(0) li.selectdOption");
		var _class=_this.is(".t_cdt")?"t_cdt":"s_cdt";
		var exists=false;
		$("."+_class,_selected).each(function(){
			if($(this).attr("value")==_this.attr("value")){
				exists=true;
			}
		});
		if(exists){
			return false;
		}
		var _span=$("<span class=\""+_class+"\" value=\""+_this.attr("value")+"\">"+_this.text()+"<i>×</i></span>");
		_span.click(function(){
			_span.remove();
			load();
		})
		if(_class=="t_cdt"){
			_selected.append(_span);
		}else{
			$("i.spera",_selected).before(_span);
		}
		load();
	});
	function load(){
		var _span=$("div.modal .table .tableBody:eq(0) li.selectdOption");
		var status=new Array();
		var terms=new Array();
		$(".s_cdt",_span).each(function(){
			status.push($(this).attr("value"));
		});
		$(".t_cdt",_span).each(function(){
			terms.push($(this).attr("value"));
		});
		$.post(U('dreambox/LessonFeedback/confirm_list'), "type="+status.join(",")+"&terms="+terms.join(","),
				function(data) {
					if (data == 0) {
						alert('empty');
					} else {
						processData(data.data);
					}
				}, 'json');
	}
	function confirm_click(_this) {
		if (!_this.is('.active')) {
			var obj = _this.addClass('active');
			_this.siblings('a').removeClass('active');
		}
	}
	function processData(data) {
		var _table = $('div.modal .table .tableBody:eq(1)');
		_table.empty();
		if(data == null){
			return false;
		}
		for ( var i in data) {
			var tr = "<li" + (i % 2 == 0 ? "" : " class='odd'")
					+ "><div class='fl' title='"+data[i].course_name+"-"+data[i].hours_name+"'>";
			tr += "<strong>" + data[i].realname + "</strong><span>/</span>";
			tr += data[i].lesson_time + "<span>/</span>";
			tr += data[i].grade_name + "<span>/</span>";
			tr += data[i].section_num + "<span>/</span>";
			tr += data[i].course_name + "<span>/</span>";
			tr += data[i].hours_name;
			tr += "</div><div class='fr btn'>";
			if (data[i].status == 1) {
				tr += "<a href='javascript:;' item_id='"
						+ data[i].id
						+ "' bool='1' class='pass active'>通过</a> <a href='javascript:;' item_id='"
						+ data[i].id + "' bool='0' class='rejected'>不通过</a>";
			} else if (data[i].status == 2) {
				tr += "<a href='javascript:;' item_id='"
						+ data[i].id
						+ "' bool='0' class='pass'>通过</a> <a href='javascript:;' item_id='"
						+ data[i].id + "' bool='1' class='rejected active'>不通过</a>";
			} else {
				tr += "<a href='javascript:;' item_id='"
						+ data[i].id
						+ "' bool='0' class='pass'>通过</a> <a href='javascript:;' item_id='"
						+ data[i].id + "' bool='0' class='rejected'>不通过</a>";
			}
			tr += "</div></li>";
			_table.append(tr);
		}
		$('li .btn a', _table).click(function() {
			confirm_click($(this));
		});
	}
	function closeConfirm() {
		$("div.modal").remove();
		$("div.modalMask").remove();
		$("#confirm_js").remove();
	}
})