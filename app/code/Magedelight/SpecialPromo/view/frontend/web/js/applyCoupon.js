/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SpecialPromo
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage'
    ],
    function ($, modal, quote, resourceUrlManager, storage) {
        'use strict';

        return function (config) {
            var options = {
                type: 'popup',
                responsive: true,
                modalClass: 'md-promo-popup',
                innerScroll: true,
                title: 'Apply a Coupon',
                buttons: []
            };
            var popup = modal(options, $('#apply_coupon_form'));
            $("#apply_coupon_button").on('click', function (e) {
                e.stopPropagation();
                $('body.checkout-cart-index .coupon_action_button').attr('disabled', false);
                $("#apply_coupon_form").modal("openModal");
            });
            $('#md-discount-coupon-form').on('change', '#md_coupon_code_text', function () {
                var couponCode = $('#md_coupon_code_text').val();
                $('#coupon_code').val(couponCode);
            });
            $('#md-discount-coupon-form').on('change', '.md_coupon_code_radio', function () {
                if ($(this).is(":checked")) var couponCode = $(this).val();
                $('#coupon_code').val(couponCode);
            });

            $('body').on('click', '.coupon_action_button', function () {
                if ($(this).attr('id') === 'coupon_apply_button' && $('#remove-coupon').val() === '1') {
                    $('#remove-coupon').val('0');
                }
            });
            
            var appendData;
            if($('body').hasClass('checkout-index-index')){
                appendData = '&page_type=checkout';
            }else if($('body').hasClass('checkout-cart-index')){
                $("#checkout-messages").remove();
                appendData = '&page_type=cart';
            }            
            
            $('#md-discount-coupon-form').on("submit", function (e) {
                e.preventDefault();
                $('body.checkout-cart-index .coupon_action_button').attr('disabled', true);
                if ($('#md-discount-coupon-form .messages').length) {
                    $('#md-discount-coupon-form .messages').remove();
                }
                
                if (!$('#coupon_code').val() && $("input[name='md_coupon_code']:checked").val()) {
                    $('#coupon_code').val($("input[name='md_coupon_code']:checked").val());
                }

                if ($('#coupon_code').val()) {
                    var url = $(this).attr('action');
                    var loader = '<div data-role="loader" class="loading-mask" style="position: absolute;"><div class="loader"><img src="' + config.loader + '" alt="Loading..." style="position: absolute;"></div></div>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        async: false,
                        data: $(this).serialize() + appendData,
                        dataType: 'json',
                        beforeSend: function () {
                            $('.special_promo_loader').show();
                            $('.md_discount_coupon_section').addClass('processing');
                        },
                        success: function (data) {
                            $('.special_promo_loader').hide();
                            $('.md_discount_coupon_section').removeClass('processing');
                            $("#apply_coupon_form").modal("closeModal");
                            $(".md_discount_coupon_section").html(data.couponsection);
                            if (data.status === 'success') {
                                $('#cart-totals .table-wrapper').addClass('_block-content-loading');
                                $('#cart-totals .table-wrapper').append(loader);
                                storage.get(
                                    resourceUrlManager.getUrlForCartTotals(quote),
                                    false
                                ).done(function (response) {
                                    $('#cart-totals .table-wrapper').removeClass('_block-content-loading');
                                    $('#cart-totals .table-wrapper .loading-mask').remove();
                                    quote.setTotals(response);
                                });
                                if(data.message){
                                    $("#checkout-messages .message").removeClass("message-error error");
                                    $("#checkout-messages .message").addClass("message-success success");
                                    $("#checkout-messages .message-text").text(data.message);
                                    $("#checkout-messages").show();
                                    $('#checkout-messages').delay(3000).slideUp();
                                }                 
                            }else if(data.status === 'error'){
                                if(data.message){
                                    $("#checkout-messages .message").removeClass("message-success success");
                                    $("#checkout-messages .message").addClass("message-error error");
                                    $("#checkout-messages .message-text").text(data.message);
                                    $("#checkout-messages").show();
                                    $('#checkout-messages').delay(3000).slideUp();
                                }
                            }
                            if (data.couponstatus) {
                                $('#apply_coupon_button span').text('Edit Coupon');
                            } else {
                                $('#apply_coupon_button span').text('Apply Coupon');
                            }
                        }
                    });
                } else {
                    $('body.checkout-cart-index .coupon_action_button').attr('disabled', false);
                    var error_msg = '<div class="messages"><div class="message-error error message"><div>Please enter/select coupon code.</div></div></div>';
                    $('#md-discount-coupon-form').prepend(error_msg);
                }
            });
        };
    }
);