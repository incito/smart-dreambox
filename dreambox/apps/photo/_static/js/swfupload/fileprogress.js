/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.fileProgressWrapper = $("#"+this.fileProgressID);
	if (this.fileProgressWrapper.length<=0) {
		this.fileProgressWrapper=$("<li id='"+this.fileProgressID+"'></li>")
		this.fileProgressWrapper.append("<label class='file-name'>"+file.name+"</label>");
		this.fileProgressWrapper.append("<label class='status wait fr'>等待上传</label>");
		this.fileProgressWrapper.append("<a href='javascript:;' class='close s'>x</a>");

		this.fileProgressWrapper.hover(function(){
			var _close=$(this).find('.close');
			if(_close.hasClass('s')){
				_close.show();
			}
		},function(){
			$(this).find('.close').hide();
		});
		$("#"+targetID).append(this.fileProgressWrapper);
	} 
}
FileProgress.prototype.setProgress = function (percentage) {
	this.setStatus("进度:"+percentage + "%");
};
FileProgress.prototype.setComplete = function () {
	this.setStatus("<i class='p-icons u-s'></i> 上传成功");	
//	this.disappear(10000);
};
FileProgress.prototype.setError = function () {
	this.setStatus("<i class='p-icons u-f'></i>上传失败");
//	this.disappear(5000);
};
FileProgress.prototype.setCancelled = function () {
	this.setStatus("<i class='p-icons u-f'></i>取消上传");
//	this.disappear(2000);
};
FileProgress.prototype.setStatus = function (status) {
	this.fileProgressWrapper.find('.status').html(status);
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressWrapper.find('.close').removeClass('h s').addClass(show?'s':'h');	
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		var _this=this;
		this.fileProgressWrapper.find('.close').click(function () {
			swfUploadInstance.cancelUpload(fileID);
			_this.toggleCancel(false);
			return false;
		});
	}
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function (time) {
	if(!arguments[0]){
		time=2000;
	}
	var wrapper=this.fileProgressWrapper;
	setTimeout(function(){
		wrapper.fadeOut(1000,function(){
			$(this).remove();
		});		
	},time);
};