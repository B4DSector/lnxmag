(function($) {

    $(document).ready(function($) {


        /* Image select option */

        $('body').on('click', 'img.gridlove-img-select', function(e) {
            e.preventDefault();
            $(this).closest('ul').find('img.gridlove-img-select').removeClass('selected');
            $(this).addClass('selected');
            $(this).closest('ul').find('input').removeAttr('checked');
            $(this).closest('li').find('input').attr('checked', 'checked');

        });

        /* Color picker metabox handle */

        if ($('.gridlove-colorpicker').length) {
            $('.gridlove-colorpicker').wpColorPicker();

            $('a.gridlove-rec-color').click(function(e) {
                e.preventDefault();
                $('.gridlove-colorpicker').val($(this).attr('data-color'));
                $('.gridlove-colorpicker').change();
            });
        }

        gridlove_toggle_color_picker();

        $("body").on("click", "input.color-type", function(e) {
            gridlove_toggle_color_picker();
        });


        function gridlove_toggle_color_picker() {
            var picker_value = $('input.color-type:checked').val();
            if (picker_value == 'custom') {
                $('#gridlove-color-wrap').show();
            } else {
                $('#gridlove-color-wrap').hide();
            }

        }

    });

})(jQuery);