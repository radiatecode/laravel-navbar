// Nabbar item active
<script type="text/javascript">
    
    let currentUrl = window.location.href;

    // active single navigation
    $("ul.nav-sidebar a").filter(function() {
        if (this.href !== currentUrl) return;
        $(this).addClass("active");
    });

    // active tree view navigation
    $("ul.nav-treeview a").filter(function() {
        if (this.href !== currentUrl) return;
        $(this).addClass("active");
        $(this).parentsUntil(".nav-sidebar > .nav-treeview")
            .addClass("menu-open").prev("a").addClass("active");
    });
   
</script> // ../end Nabbar item active
