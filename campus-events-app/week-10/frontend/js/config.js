/*
 * Frontend configuration.
 *
 * The backend folder lives next to the frontend folder:
 *   http://localhost:8000/frontend/            -> http://localhost:8000/backend/
 *   http://localhost/campusevents/frontend/index.html -> http://localhost/campusevents/backend/
 * so the API URL is calculated from the current page – no editing needed after deployment.
 */
const Constants = {
    API_BASE_URL: window.location.origin + window.location.pathname.replace(/frontend\/.*$/, "backend/"),
    ADMIN_ROLE: "admin",
    STUDENT_ROLE: "student",
    TOKEN_KEY: "campusevents_token"
};
