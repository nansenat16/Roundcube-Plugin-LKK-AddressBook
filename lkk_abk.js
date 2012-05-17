
function show_lkk(target){
	if($.inArray(target,['_to','_cc','_bcc'])==-1){
		return false
	}
	window.open('./?_task=mail&_action=plugin.lkk_abk_show&t='+target,'lkk','height=500,width=550,scrollbars=yes,focus=yes');
}

rcmail.addEventListener('init',function(evt){
	var link=$('.title label');
	link.click(function(){
		show_lkk($(this).attr('for'))
	});
});
