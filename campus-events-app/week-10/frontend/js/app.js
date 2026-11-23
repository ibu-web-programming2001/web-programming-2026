/*
 * Application bootstrap.
 *
 * spapp shows one <section> per URL hash (#events, #clubs...). For every view we register
 * an onReady callback: it runs EVERY time the view is shown and loads fresh data with AJAX.
 */
$(document).ready(function () {
    const PUBLIC_VIEWS = ["login", "register"];     // no login needed, full-screen layout
    const ADMIN_VIEWS = ["users"];                  // admins only

    // view id -> page object with an init() function
    const PAGES = {
        "login": LoginPage,
        "register": RegisterPage,
        "dashboard": DashboardPage,
        "events": EventsPage,
        "event-details": EventDetailsPage,
        "clubs": ClubsPage,
        "categories": CategoriesPage,
        "registrations": RegistrationsPage,
        "discover": DiscoverPage,
        "users": UsersPage,
        "profile": ProfilePage
    };

    toastr.options = { positionClass: "toast-bottom-right", timeOut: 3500 };

    const app = $.spapp({
        templateDir: "./views/",
        defaultView: "dashboard",
        pageNotFound: "error_404"
    });

    /** Is the current user allowed to see this view? If not, send them somewhere else. */
    function canOpen(view) {
        if (PUBLIC_VIEWS.includes(view)) {
            if (Auth.isLoggedIn()) {                 // already logged in -> no login page
                window.location.hash = "#dashboard";
                return false;
            }
            return true;
        }
        if (!Auth.isLoggedIn()) {
            window.location.hash = "#login";
            return false;
        }
        if (ADMIN_VIEWS.includes(view) && !Auth.isAdmin()) {
            toastr.error("This page is only available to administrators.");
            window.location.hash = "#dashboard";
            return false;
        }
        return true;
    }

    Object.keys(PAGES).forEach(function (view) {
        app.route({
            view: view,
            onReady: function () {
                if (canOpen(view)) PAGES[view].init();
            }
        });
    });

    /** Layout, menu and user chip – depend on the view and on the logged-in user. */
    function updateLayout() {
        const view = location.hash.slice(1) || "dashboard";
        const user = Auth.getUser();

        $("body")
            .toggleClass("auth-mode", PUBLIC_VIEWS.includes(view))
            .toggleClass("role-admin", user !== null && user.role === Constants.ADMIN_ROLE)
            .toggleClass("role-student", user !== null && user.role === Constants.STUDENT_ROLE);

        if (user) {
            $("#user-initials").text(Utils.initials(user));
            $("#user-name").text(user.first_name + " " + user.last_name);
            $("#user-role").text(user.role);
        }

        $("#main-nav .nav-link").removeClass("active").filter('[href="#' + view + '"]').addClass("active");
        const title = $('#main-nav .nav-link[href="#' + view + '"]').text().trim();
        $("#page-title").text(title || "CampusEvents");

        const sidebar = bootstrap.Offcanvas.getInstance("#sidebar");
        if (sidebar) sidebar.hide();
        window.scrollTo(0, 0);
    }

    window.addEventListener("hashchange", updateLayout);
    updateLayout();

    app.run();
});
