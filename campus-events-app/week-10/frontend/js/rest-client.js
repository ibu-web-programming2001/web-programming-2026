/*
 * RestClient – the ONLY place that calls $.ajax.
 *
 *   RestClient.get("events", function (events) { ... });
 *   RestClient.post("events", { title: "..." }, function (event) { ... }, function (message) { ... });
 *
 * - sends and receives JSON
 * - adds "Authorization: Bearer <token>" to every request
 * - 401 -> token is invalid/expired -> back to the login page
 * - 403 -> friendly "no permission" message
 */
const RestClient = {
    request: function (method, url, data, onSuccess, onError) {
        $.ajax({
            url: Constants.API_BASE_URL + url,
            type: method,
            contentType: "application/json",
            dataType: "json",
            data: data ? JSON.stringify(data) : undefined,
            beforeSend: function (xhr) {
                const token = Auth.getToken();
                if (token) {
                    xhr.setRequestHeader("Authorization", "Bearer " + token);
                }
            },
            success: function (response) {
                if (onSuccess) onSuccess(response);
            },
            error: function (xhr) {
                RestClient.handleError(xhr, url, onError);
            }
        });
    },

    get: function (url, onSuccess, onError) {
        RestClient.request("GET", url, null, onSuccess, onError);
    },
    post: function (url, data, onSuccess, onError) {
        RestClient.request("POST", url, data, onSuccess, onError);
    },
    put: function (url, data, onSuccess, onError) {
        RestClient.request("PUT", url, data, onSuccess, onError);
    },
    delete: function (url, onSuccess, onError) {
        RestClient.request("DELETE", url, null, onSuccess, onError);
    },

    handleError: function (xhr, url, onError) {
        const message = (xhr.responseJSON && xhr.responseJSON.error)
            || (xhr.status === 0 ? "Cannot reach the server. Is the backend running?" : "Unexpected error (" + xhr.status + ").");

        // 401 on a normal request = session is over. (On the login form itself it means "wrong password".)
        if (xhr.status === 401 && url !== "auth/login") {
            Auth.clear();
            toastr.warning("Your session has expired. Please log in again.");
            window.location.hash = "#login";
            return;
        }
        if (xhr.status === 403) {
            toastr.error("You do not have permission to do this.");
            return;
        }
        if (onError) {
            onError(message, xhr);
        } else {
            toastr.error(message);
        }
    }
};
