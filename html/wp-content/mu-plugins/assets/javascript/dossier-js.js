jQuery(document).ready(function () {

    document.forms["setupform"]["blogname"].readOnly = true;
    document.forms["setupform"]["blog_title"].readOnly = true;

    jQuery("#text-terms-of-use").on("click", function (e) {
        jQuery("#conditions-terms-of-use").fadeToggle(500);
    });
});