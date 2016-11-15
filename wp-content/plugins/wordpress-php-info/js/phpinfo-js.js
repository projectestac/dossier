(function($) {
    $(document).ready( function() {
        $("#email_phpinfo").click( function() {
            $("#bgpopup").css("display", "block");
            $("#emailme").css("display", "block");
        });
        $("#closeemail").click( function() {
            $("#bgpopup").css("display", "none");
            $("#emailme").css("display", "none");
        })
    });
})(jQuery);