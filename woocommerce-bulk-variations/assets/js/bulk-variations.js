(function ($) {
        $(document).ready(function (e) {
            if ($('div#matrix_form').length) {
                let form = e.target;

                if (!$(form).data('bound')) {
                    $('.btn-back-to-product').click(function () {
                        $('#matrix_form').slideUp('200', function () {
                            $('div.product').slideDown('400');
                        });
                    });

                    $('.btn-bulk').click(function () {
                        $('div.product').slideUp('200', function () {
                            $('#matrix_form').slideDown('400');
                        });

                    });

                    $('.qty_input').focus(function () {
                        $('tr.info_box', '#matrix_form_table').css('display', 'none');
                        let info_box_id = '#' + $(this).attr('id') + '_info';
                        $(info_box_id).css('display', 'table-row')
                    });

                }
            }
    });


})(jQuery);