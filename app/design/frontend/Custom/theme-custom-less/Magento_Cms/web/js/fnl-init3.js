//create a module

define(["jquery"], function ($) {
    $("#init3").on("click", "li", function () {
        console.log(this);
    });
});
