/*
 * Event details + weather + reviews. The id of the event comes from the events page (localStorage).
 * Students write one review per event; if they already did, the form edits it (PUT).
 */
const EventDetailsPage = {
    eventId: null,

    init: function () {
        EventDetailsPage.eventId = localStorage.getItem("selected_event_id");
        if (!EventDetailsPage.eventId) {
            window.location.hash = "#events";
            return;
        }
        EventDetailsPage.loadEvent();
        EventDetailsPage.loadWeather();
        EventDetailsPage.loadReviews();

        $("#details-register").off("click").on("click", EventDetailsPage.register);
        $("#review-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            EventDetailsPage.saveReview(Utils.formData("#review-form"));
        });
        $("#review-list").off("click").on("click", ".btn-delete-review", function () {
            EventDetailsPage.deleteReview($(this).data("id"));
        });
    },

    loadEvent: function () {
        RestClient.get("events/" + EventDetailsPage.eventId, function (event) {
            const style = Utils.categoryStyle(event.category_name);
            const upcoming = Number(event.is_upcoming) === 1;
            $("#details-banner").attr("class", "event-banner event-banner-lg " + style.css).html('<i class="bi ' + style.icon + '"></i>');
            $("#details-category").text(event.category_name);
            $("#details-title").text(event.title);
            $("#details-club").text(event.club_name);
            $("#details-description").text(event.description || "No description.");
            $("#details-when").html(Utils.formatDateTime(event.starts_at) + " " + Utils.startsBadge(event.starts_at));
            $("#details-where").text(event.location + ", " + event.city);
            $("#details-rating").html(event.review_count > 0
                ? Utils.stars(event.average_rating) + ' <span class="text-muted ms-1">' + event.average_rating + " · " + event.review_count + " review(s)</span>"
                : '<span class="text-muted">No reviews yet</span>');
            $("#details-seats").html(!upcoming
                ? '<span class="text-muted fw-semibold">The event is over</span>'
                : Number(event.available_seats) > 0
                    ? '<span class="text-success fw-semibold">' + Utils.seatsText(event) + "</span>"
                    : '<span class="text-danger fw-semibold">Sold out – all ' + event.capacity + " seats are taken</span>");
            $("#details-register").prop("disabled", !upcoming || Number(event.available_seats) < 1);
        }, function (message) {
            toastr.error(message);
            window.location.hash = "#events";
        });
    },

    /** External API (Open-Meteo) through OUR backend – if it fails, only this card shows a message. */
    loadWeather: function () {
        $("#details-weather").html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        RestClient.get("events/" + EventDetailsPage.eventId + "/weather", function (weather) {
            if (!weather.available) {
                $("#details-weather").html('<div class="empty-state py-3"><i class="bi bi-cloud"></i>' + weather.message + "</div>");
                return;
            }
            const day = weather.forecast;
            $("#details-weather").html(`
                <div class="weather-card">
                    <i class="bi bi-${day.icon}"></i>
                    <div class="h3 fw-bold mt-2 mb-0">${day.temp_max}° / ${day.temp_min}°</div>
                    <div class="text-muted">${day.description} · ${day.precipitation_probability ?? 0}% rain</div>
                    <div class="small text-muted mt-2">${weather.city} · forecast by Open-Meteo</div>
                </div>`);
        }, function (message) {
            // e.g. 503 "The weather service is not available..." – the rest of the page keeps working
            $("#details-weather").html('<div class="alert alert-warning mb-0"><i class="bi bi-cloud-slash me-2"></i>' + message + "</div>");
        });
    },

    loadReviews: function () {
        const user = Auth.getUser();
        RestClient.get("events/" + EventDetailsPage.eventId + "/reviews", function (reviews) {
            // Reset the form, then switch to "edit" mode if this user already wrote a review
            $("#review-form")[0].reset();
            $("#review-form [name=id]").val("");
            $("#review-form-title").text("Write a review");
            $("#review-submit").text("Submit review");

            const mine = reviews.find(review => review.user_id === user.id);
            if (mine) {
                Utils.fillForm("#review-form", { id: mine.id, rating: mine.rating, comment: mine.comment });
                $("#review-form-title").text("Your review");
                $("#review-submit").text("Update review");
            }

            if (reviews.length === 0) {
                $("#review-list").html('<li class="list-group-item text-muted py-4 text-center">No reviews yet – be the first after the event.</li>');
                return;
            }
            $("#review-list").html(reviews.map(review => `
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${review.user_name}</strong>
                            <small class="text-muted ms-2">${Utils.formatDate(review.created_at.substring(0, 10))}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${Utils.stars(review.rating)}
                            ${review.user_id === user.id || Auth.isAdmin()
                                ? `<button class="btn btn-sm btn-light text-danger btn-delete-review" data-id="${review.id}" title="Delete"><i class="bi bi-trash"></i></button>`
                                : ""}
                        </div>
                    </div>
                    <p class="mb-0 mt-1">${review.comment || ""}</p>
                </li>`).join(""));
        });
    },

    saveReview: function (review) {
        review.event_id = EventDetailsPage.eventId;
        const done = function () {
            toastr.success("Thank you for your review!");
            EventDetailsPage.loadEvent();
            EventDetailsPage.loadReviews();
        };
        if (review.id) {
            RestClient.put("reviews/" + review.id, review, done);
        } else {
            RestClient.post("reviews", review, done);
        }
    },

    deleteReview: function (id) {
        RestClient.delete("reviews/" + id, function () {
            toastr.success("Review deleted.");
            EventDetailsPage.loadEvent();
            EventDetailsPage.loadReviews();
        });
    },

    register: function () {
        RestClient.post("registrations", { event_id: EventDetailsPage.eventId }, function (registration) {
            toastr.success("You are registered for '" + registration.event_title + "'. See you there!");
            EventDetailsPage.loadEvent();
        });
    }
};
