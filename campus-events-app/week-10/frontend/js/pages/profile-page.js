/*
 * Profile page – GET /auth/me and PUT /auth/me (+ personal numbers for students).
 */
const ProfilePage = {
    init: function () {
        ProfilePage.load();

        $("#twofa-setup-button").off("click").on("click", ProfilePage.setupTwoFactor);
        $("#twofa-enable-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            ProfilePage.enableTwoFactor(Utils.formData("#twofa-enable-form"));
        });
        $("#twofa-disable-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            ProfilePage.disableTwoFactor(Utils.formData("#twofa-disable-form"));
        });

        $("#profile-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            ProfilePage.save(Utils.formData("#profile-form"));
        });
    },

    load: function () {
        RestClient.get("auth/me", function (user) {
            $("#profile-initials").text(Utils.initials(user));
            $("#profile-name").text(user.first_name + " " + user.last_name);
            $("#profile-email-text").text(user.email);
            $("#profile-role").text(user.role);
            $("#profile-joined").text("Member since " + Utils.formatDate(user.created_at.substring(0, 10)));
            $("#profile-faculty").text(user.faculty ? "Faculty: " + user.faculty : "");
            $("#profile-first-name").val(user.first_name);
            $("#profile-last-name").val(user.last_name);
            $("#profile-email").val(user.email);
            $("#profile-password, #profile-password-confirm").val("");
            ProfilePage.renderTwoFactor(Number(user.two_factor_enabled) === 1);
        });

        if (!Auth.isAdmin()) {
            RestClient.get("dashboard", function (stats) {
                $("#profile-upcoming").text(stats.upcoming_registrations);
                $("#profile-reviews").text(stats.reviews);
            });
        }
    },

    save: function (data) {
        RestClient.put("auth/me", data, function (user) {
            toastr.success("Profile saved.");
            // The name inside the token is old now; it is refreshed on the next login.
            $("#user-name").text(user.first_name + " " + user.last_name);
            ProfilePage.load();
        });
    },

    /* ---------- Two-factor authentication ---------- */

    renderTwoFactor: function (enabled) {
        $("#twofa-status").text(enabled ? "on" : "off")
            .toggleClass("text-bg-success", enabled).toggleClass("text-bg-secondary", !enabled);
        $("#twofa-setup-button").toggleClass("d-none", enabled);
        $("#twofa-disable-form").toggleClass("d-none", !enabled);
        $("#twofa-setup").addClass("d-none");
    },

    setupTwoFactor: function () {
        RestClient.post("auth/2fa/setup", null, function (data) {
            $("#twofa-secret").text(data.secret);
            $("#twofa-qr").empty();
            // qrcodejs draws the otpauth:// link as a QR code that authenticator apps can scan
            new QRCode(document.getElementById("twofa-qr"), { text: data.otpauth_uri, width: 160, height: 160 });
            $("#twofa-setup").removeClass("d-none");
            $("#twofa-setup-button").addClass("d-none");
        });
    },

    enableTwoFactor: function (data) {
        RestClient.post("auth/2fa/enable", data, function () {
            toastr.success("Two-factor authentication is on. You will need a code at your next login.");
            ProfilePage.renderTwoFactor(true);
        });
    },

    disableTwoFactor: function (data) {
        RestClient.post("auth/2fa/disable", data, function () {
            toastr.success("Two-factor authentication is off.");
            ProfilePage.renderTwoFactor(false);
        });
    }
};
