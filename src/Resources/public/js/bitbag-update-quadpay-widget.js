(function ( $ ) {
    'use strict';

    $.fn.extend({
        updateQuadPayWidget: function() {
            return this.each(function() {
                return $(this).bind('DOMSubtreeModified', function(){
                    let price = parseFloat($('#product-price').text().match(/\d+(?:\.\d+)?/g));

                    if (isNaN(price)) {

                        return;
                    }

                    Number.prototype.format = function(n, x) {
                        let re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
                        return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
                    };

                    let minAmount = $('#qp-descrip-min-amount').data('min-amount') / 100;
                    let maxAmount = $('#qp-descrip-max-amount').data('max-amount') / 100;

                    $('#qp-descrip__price span.qp-descrip__price').text('$' + (price / 4).format(2));

                    if (minAmount > price) {
                        $('#qp-descrip-min-amount').show();
                        $('#qp-descrip-max-amount, #qp-descrip__price').hide();
                    } else if (maxAmount < price) {
                        $('#qp-descrip-max-amount').show();
                        $('#qp-descrip-min-amount, #qp-descrip__price').hide();
                    } else {
                        $('#qp-descrip__price').show();
                        $('#qp-descrip-min-amount, #qp-descrip-max-amount').hide();
                    }
                });
            });
        }
    });
})( jQuery );

(function($) {
    $(document).ready(function () {
        $('#product-price').updateQuadPayWidget();

        $('a.header').click(function () {
            $(this).closest('.item').find('input[type="radio"]').prop("checked", true);
        });
    });
})(jQuery);
