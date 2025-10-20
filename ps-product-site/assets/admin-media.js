
(function($){
  function bindUploader($wrap){
    $wrap.on('click', '.ps-upload-btn', function(e){
      e.preventDefault();
      var $btn = $(this);
      var $input = $btn.siblings('input[type="text"]');
      var frame = wp.media({
        title: '选择图片',
        button: { text: '使用此图片' },
        multiple: false
      });
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        $input.val(attachment.url).trigger('change');
      });
      frame.open();
    });
  }
  $(function(){
    bindUploader($('.ps-metabox'));
  });
})(jQuery);
