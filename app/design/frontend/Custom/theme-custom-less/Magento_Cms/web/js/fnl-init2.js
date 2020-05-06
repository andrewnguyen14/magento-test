//create a module

define(["jquery"], function ($) {
    $("#init2").on("click", "li", function () {
        console.log(this);
    });
});
