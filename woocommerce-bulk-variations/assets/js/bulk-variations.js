(function ($) {
    document.addEventListener('DOMContentLoaded', function() {
        if ($('div#matrix_form').length) {
            let form = document.target;

            if (!$(form).data('bound')) {
                $('.btn_back_to_product').click(function () {
                    $('#matrix_form').slideUp('200', function () {
                        $('div.product').slideDown('400');
                    });
                });

                document.getElementById('wbv-bulk-button').addEventListener('click', function (_event) {
                    $('div.product').slideUp('200', function () {
                        $('#matrix_form').slideDown('400');
                    });
                });

                $('.matrix_form_input').focus(function () {
                    $('tr.info_box', '#matrix_form_table').css('display', 'none');
                    let info_box_id = '#' + $(this).attr('id') + '_info';
                    $(info_box_id).css('display', 'table-row')
                });

            }
        }
    });
})(jQuery);