/*jshint jquery:true*/

//jQuery('.sp-quantity').data("product-sku");
//jQuery('.sp-quantity').find("input.quantity-input").val();


define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";

    $.widget('mage.subscriptionsEdit', {
        options: {
            shippingAddressSelector: '#shipping_address_id',
            fieldSelector: '.shipping-address .field:not(.do-not-toggle)',
            inputSelector: 'select, input'
        },

        _create: function() {
            var wrapper = this;
            console.log(wrapper);

            $('#meal_plan').on('change', function () {
                wrapper.removeAddedItems($(this).val());
            });

            $( '.edit-class' ).click(function() {
                $('#meal-plan-name-title').replaceWith($('<input id="meal-plan-name-title" value="' + $('#meal-plan-name-title').text() + '" />'));
                $('.save-class').show();
                $('.edit-class').hide();
            });

            $( '.save-class' ).click(function() {
                var title = $('#meal-plan-name-title').val();
                $('#meal-plan-name-title').replaceWith($('<span id="meal-plan-name-title">' + title + '</span>'));
                $('#meal-plan-name').val(title);
                $('.save-class').hide();
                $('.edit-class').show();
            });



            $(":button").not(':input[type=submit]').click(function() {
                var $additem = $(this);
                var name = $additem.closest('.add-plan-item').find('.a-la-carte-item option:selected').text();
                var data = $additem.closest('.add-plan-item').find('.a-la-carte-item option:selected').data("options");
                var appendRow = '<tbody class="sp-quantity" data-product-sku="'+data.sku+'"><tr><td>'+name+'</td><td><div><div class="sp-minus fff"> <a class="ddd" href="#">-</a></div><input type="hidden" class="price-input" value="'+ data.price +'"/> <div class="sp-input"> <input type="text" class="quantity-input" name="items['+data.sku+']" value="1" /> </div> <div class="sp-plus fff"> <a class="ddd" href="#">+</a> </div> </div> </td> <td> <div class="sp-total"> <input type="text" class="total-input" value="'+ data.price +'" readonly /> </div> </td> </tr> </tbody>';
                var newtotalfield = $('.new-total-input').val();

                $('.ddd').unbind('click');
                $('#added-items').append(appendRow);
                $(".ddd").on("click", function (e) {
                    e.preventDefault();
                    var $button = $(this);
                    var oldValue = $button.closest('.sp-quantity').find("input.quantity-input").val();
                    var price = $button.closest('.sp-quantity').find("input.price-input").val();

                    if ($button.text() == "+") {
                        var newVal = parseFloat(oldValue) + 1;
                    } else {
                        if (oldValue > 0) {
                            var newVal = parseFloat(oldValue) - 1;
                        } else {
                            newVal = 0;
                        }
                    }
                    var total = price * newVal;
                    var newTotal = total + parseFloat(newtotalfield);
                    $button.closest('.sp-quantity').find("input.quantity-input").val(newVal);
                    $button.closest('.sp-quantity').find("input.total-input").val(total.toFixed(2));
                    $('.new-total-input').val(newTotal.toFixed(2));
                    $('.subtotal').val(newTotal.toFixed(2));

                });
                $additem.closest('.add-plan-item').find('.a-la-carte-item option:selected').attr('disabled', 'disabled');
                $additem.closest('.add-plan-item').find('.a-la-carte-item option:eq(0)').prop('selected', true);

            });

            $(".ddd").on("click", function (e) {
                e.preventDefault();
                var $button = $(this);
                var oldValue = $button.closest('.sp-quantity').find("input.quantity-input").val();
                var price = $button.closest('.sp-quantity').find("input.addon-orignal-price").val();

                if ($button.text() == "+") {
                    var newVal = parseFloat(oldValue) + 1;
                } else {
                    if (oldValue > 0) {
                        var newVal = parseFloat(oldValue) - 1;
                    } else {
                        newVal = 0;
                    }
                }

                var total = price * newVal;
                $button.closest('.sp-quantity').find("input.quantity-input").val(newVal);
                $button.closest('.sp-quantity').find("input.total-input").val(total.toFixed(2));

                var gTotal = 0;
                var inputs = $(".total-input");
                for(var i = 0; i < inputs.length; i++){
                    var inputValue = parseFloat($(inputs[i]).val());
                    var gTotal = parseFloat(gTotal + inputValue);
                }

               // $('.new-total-input').val(gTotal.toFixed(2));
               // $('.subtotal').val(gTotal.toFixed(2));
            });




            if ($(wrapper.options.shippingAddressSelector).length > 0) {
                // Watch
                $(wrapper.options.shippingAddressSelector).on('change', function () {
                    var fields = wrapper.element.find(wrapper.options.fieldSelector);

                    if (this.value == '') {
                        fields.show();
                        fields.find(wrapper.options.inputSelector).each(function (i, el) {
                            el.disabled = false;
                        });
                    }
                    else {
                        fields.hide();
                        fields.find(wrapper.options.inputSelector).each(function (i, el) {
                            el.disabled = true;
                        });
                    }
                });

                // Initialize
                wrapper.setShippingFieldVisibility();

                // Control -- country field likes to escape
                setInterval(function() {
                    wrapper.setShippingFieldVisibility();
                }, 1000);
            }
        },

        setShippingFieldVisibility: function() {
            var wrapper = this;

            if ($(wrapper.options.shippingAddressSelector).val() > 0) {
                var fields = wrapper.element.find(wrapper.options.fieldSelector);

                fields.hide();
                fields.find(wrapper.options.inputSelector).each(function (i, el) {
                    el.disabled = true;
                });
            }
        },

        myFunction: function() {
            alert();
        },

        removeAddedItems: function(plan){
            if (plan == 'a-la-carte')
            {
                $('#meal-plan-options').hide();
            }
            else
            {
                $('#meal-plan-options').show();
            }
            this.refreshAlacarte(plan);
            $('.sp-quantity').remove();
            $('.add-plan-select').prop("disabled", false);
        },

        refreshAlacarte: function(plan){
            $('#pp-alacarte').hide();
            $('#k-alacarte').hide();
            $('#p-alacarte').hide();
            $('#v-standard-alacarte').hide();
            $('#lc-alacarte').hide();
            $('#a-la-carte-alacarte').hide();
            $('#'+plan+'-alacarte').show();
        },

        updateSubscription: function() {
            $('#added-items > tbody:not(:has(th))').each(function(index){
                var $row = $(this);
                var sku = $row.data("product-sku");
                var qty = $row.find("input.quantity-input").val();
                alert('SKU:'+sku+ ' QTY:' +qty);
            });
            //$.ajax({
            //    method: "POST",
            //    url: "/customer/subscriptions/meal",
            //    data: { name: "John", location: "Boston" }
            //})
            //    .done(function( msg ) {
            //        alert( "Data Saved: " + msg );
            //    });
        }

        });


    return $.mage.subscriptionsEdit;
});
