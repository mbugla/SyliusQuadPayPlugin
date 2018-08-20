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

                    let minAmount = $('#qp-descrip-min-amount').data('min-amount') / 100;
                    let maxAmount = $('#qp-descrip-max-amount').data('max-amount') / 100;

                    $('#qp-descrip__price').text($('#product-price').text());

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
