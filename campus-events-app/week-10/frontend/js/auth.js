/*
 * Everything about the logged-in user on the frontend.
 * The JWT is stored in localStorage and sent with every request (see rest-client.js).
 */
const Auth = {
    saveToken: function (token) {
        localStorage.setItem(Constants.TOKEN_KEY, token);
    },

    getToken: function () {
        return localStorage.getItem(Constants.TOKEN_KEY);
    },

    /**
     * Read the user from the token payload (the middle part of the JWT).
     * We can READ it without the secret – only the backend can VERIFY it.
     */
    getUser: function () {
        const token = Auth.getToken();
        if (!token) return null;
        try {
            const base64 = token.split(".")[1].replace(/-/g, "+").replace(/_/g, "/");
            const bytes = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
            const payload = JSON.parse(new TextDecoder().decode(bytes));   // UTF-8 safe (č, ć, ž...)
            if (payload.exp * 1000 < Date.now()) {                          // expired
                Auth.clear();
                return null;
            }
            return payload.user;
        } catch (e) {
            Auth.clear();
            return null;
        }
    },

    isLoggedIn: function () {
        return Auth.getUser() !== null;
    },

    isAdmin: function () {
        const user = Auth.getUser();
        return user !== null && user.role === Constants.ADMIN_ROLE;
    },

    clear: function () {
        localStorage.removeItem(Constants.TOKEN_KEY);
    },

    logout: function () {
        Auth.clear();
        toastr.info("You have been logged out.");
        window.location.hash = "#login";
    }
};
