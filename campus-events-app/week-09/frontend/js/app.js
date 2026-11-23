/*
 * Application bootstrap.
 *
 * spapp turns every <section> inside <main id="spapp"> into a "page".
 * The URL hash decides which page is shown (#events, #clubs, ...),
 * so navigation happens WITHOUT reloading the whole page (Single Page Application).
 */
$(document).ready(function () {
    // Pages that use the full-screen "auth" layout (no sidebar, no top bar)
    const AUTH_VIEWS = ["login", "register"];

    const app = $.spapp({
        templateDir: "./views/",
        defaultView: "dashboard",
        pageNotFound: "error_404"
    });

    // Called every time the URL hash changes (also on first load)
    function onViewChange() {
        const view = location.hash.slice(1) || "dashboard";

        // 1. switch between the normal layout and the auth layout
        $("body").toggleClass("auth-mode", AUTH_VIEWS.includes(view));

        // 2. highlight the active link in the sidebar
        $("#main-nav .nav-link")
            .removeClass("active")
            .filter('[href="#' + view + '"]')
            .addClass("active");

        // 3. update the title in the top bar
        const title = $('#main-nav .nav-link[href="#' + view + '"]').text().trim();
        $("#page-title").text(title || "CampusEvents");

        // 4. close the sidebar on mobile after a link was clicked
        const sidebar = bootstrap.Offcanvas.getInstance("#sidebar");
        if (sidebar) sidebar.hide();

        window.scrollTo(0, 0);
    }

    window.addEventListener("hashchange", onViewChange);
    onViewChange();

    app.run();
});
