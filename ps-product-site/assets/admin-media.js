
(function($){$(function(){
  $(document).on('click','.ps-upload-btn',function(e){
    e.preventDefault();
    var $btn=$(this),$input=$btn.siblings('input[type="text"]');
    var frame=wp.media({title:'选择图片',button:{text:'使用此图片'},multiple:false});
    frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();$input.val(a.url).trigger('change');});
    frame.open();
  });
});})(jQuery);
