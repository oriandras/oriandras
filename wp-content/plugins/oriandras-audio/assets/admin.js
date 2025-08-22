(function($){
    $(function(){
        var frame;
        $('#ori_audio_select').on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Select or Upload Audio',
                button: { text: 'Use this audio' },
                library: { type: ['audio'] },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                if(attachment && attachment.url){
                    $('#ori_audio_url').val(attachment.url);
                    $('#ori_audio_attachment_id').val(attachment.id);
                }
            });
            frame.open();
        });
    });
})(jQuery);