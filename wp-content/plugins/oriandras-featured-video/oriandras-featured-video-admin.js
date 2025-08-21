(function($){
    $(function(){
        var frame;
        var $select = $('#ori_fv_select_video');
        var $clear = $('#ori_fv_clear_video');
        var $input = $('#ori_fv_attachment_id');
        var $preview = $('#ori_fv_attachment_preview');

        function updatePreview(id, url){
            $input.val(id || '');
            if(id && url){
                $preview.text(url);
                $clear.prop('disabled', false);
            } else {
                $preview.text(oriFvAdmin.noVideoSelected || 'No video selected.');
                $clear.prop('disabled', true);
            }
        }

        $select.on('click', function(e){
            e.preventDefault();
            if(frame){
                frame.open();
                return;
            }
            frame = wp.media({
                title: oriFvAdmin.pickVideo || 'Select a video',
                button: { text: oriFvAdmin.useVideo || 'Use this video' },
                library: { type: 'video' },
                multiple: false
            });

            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                updatePreview(attachment.id, attachment.url);
            });

            frame.open();
        });

        $clear.on('click', function(e){
            e.preventDefault();
            updatePreview('', '');
        });
    });
})(jQuery);
