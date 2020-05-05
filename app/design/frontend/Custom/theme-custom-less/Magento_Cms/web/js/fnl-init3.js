//create a module

define(["jquery"], function ($) {
    $("ol").on("click", "li", function () {
        console.log(this);
    });
});
