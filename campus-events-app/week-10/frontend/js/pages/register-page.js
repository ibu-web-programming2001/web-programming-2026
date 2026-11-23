/*
 * Register page – creates a student account and logs in immediately.
 */
const RegisterPage = {
    init: function () {
        $("#register-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            const user = Utils.formData("#register-form");

            Utils.block();
            RestClient.post("auth/register", user, function (response) {
                Utils.unblock();
                Auth.saveToken(response.token);
                $("#register-form")[0].reset();
                toastr.success("Account created. Welcome, " + response.user.first_name + "!");
                window.location.hash = "#dashboard";
            }, function (message) {
                Utils.unblock();
                toastr.error(message);
            });
        });
    }
};
