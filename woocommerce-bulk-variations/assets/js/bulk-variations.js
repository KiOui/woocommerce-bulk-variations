(function ($) {
        jQuery(document).on('wc_variation_form', function (e) {

        if ($('div#matrix_form').length) {
            let form = e.target;

            $(form).addClass('is_bulk');

            if (!$(form).data('bound')) {

                $('.btn-single').click(function () {
                    $('form.variations_form').slideDown();
                });

                $('.btn-back-to-single').click(function () {
                    $('#matrix_form').slideUp('200', function () {
                        $('div.product').slideDown('400', function () {
                            $('.variations_form').slideDown();
                        });
                    });
                });

                $('.btn-back-to-product').click(function () {
                    $('#matrix_form').slideUp('200', function () {
                        $('div.product').slideDown();
                    });
                });

                $('.btn-bulk').click(function () {
                    $('div.product').slideUp('200', function () {
                        $('.variations_form').hide();
                        $('#matrix_form').slideDown('400', function () {
                            $('#qty_input_0').focus();
                        });
                    });

                });

                $('.qty_input').focus(function () {

                    $('tr.info_box', '#matrix_form_table').css('display', 'none');
                    console.log($('tr.info_box', '#matrix_form_table'));
                    let info_box_id = '#' + $(this).attr('id') + '_info';
                    console.log(info_box_id);
                    $(info_box_id).css('display', 'block')

                });

            }
        }
    });


})(jQuery);