/*
 * Login page – our first AJAX call.
 */
const LoginPage = {
    init: function () {
        $("#login-form").off("submit").on("submit", function (event) {
            event.preventDefault();                       // no page reload!
            const credentials = Utils.formData("#login-form");

            Utils.block();
            RestClient.post("auth/login", credentials, function (response) {
                Utils.unblock();
                if (response.two_factor_required) {      // password OK, but 2FA is on -> ask for the code
                    $("#login-code-group").removeClass("d-none");
                    $("#login-code").trigger("focus");
                    toastr.info("Enter the 6-digit code from your authenticator app.");
                    return;
                }
                Auth.saveToken(response.token);
                $("#login-form")[0].reset();
                toastr.success("Welcome back, " + response.user.first_name + "!");
                $("#login-code-group").addClass("d-none");
                window.location.hash = "#dashboard";
            }, function (message) {
                Utils.unblock();
                toastr.error(message);                   // "Invalid email or password."
            });
        });
    }
};
