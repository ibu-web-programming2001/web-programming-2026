/*
 * Small helpers used by many pages.
 */
const Utils = {
    /** (Re)create a DataTable with new data. */
    datatable: function (tableId, columns, data, pageLength = 10) {
        if ($.fn.dataTable.isDataTable("#" + tableId)) {
            $("#" + tableId).DataTable().destroy();
            $("#" + tableId).empty();
        }
        $("#" + tableId).DataTable({
            data: data,
            columns: columns,
            pageLength: pageLength,
            order: [],
            columnDefs: [{ targets: -1, orderable: false, className: "text-end text-nowrap" }]
        });
    },

    /** Read all fields of a <form> into an object: { title: "...", location: "..." } */
    formData: function (formSelector) {
        return Object.fromEntries(new FormData($(formSelector)[0]).entries());
    },

    /** Put the values of an object into the fields of a form (by name). */
    fillForm: function (formSelector, data) {
        const form = $(formSelector);
        Object.keys(data).forEach(function (key) {
            form.find('[name="' + key + '"]').val(data[key] === null ? "" : data[key]).trigger("change");
        });
    },

    showModal: function (selector) {
        bootstrap.Modal.getOrCreateInstance(document.querySelector(selector)).show();
    },

    hideModal: function (selector) {
        bootstrap.Modal.getOrCreateInstance(document.querySelector(selector)).hide();
    },

    /** Grey overlay while a request is running. */
    block: function () {
        $.blockUI({
            message: '<div class="spinner-border text-light" role="status"></div>',
            css: { border: "none", background: "transparent" }
        });
    },

    unblock: function () {
        $.unblockUI();
    },

    /** MySQL "2026-10-09 19:00:00" -> JavaScript Date (local time). */
    toDate: function (value) {
        return new Date(String(value).replace(" ", "T"));
    },

    formatDate: function (date) {
        return date ? Utils.toDate(String(date).substring(0, 10) + "T00:00:00").toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" }) : "–";
    },

    /** "Fri, 9 Oct 2026 · 19:00" */
    formatDateTime: function (value) {
        if (!value) return "–";
        const date = Utils.toDate(value);
        return date.toLocaleDateString("en-GB", { weekday: "short", day: "numeric", month: "short", year: "numeric" })
            + " · " + date.toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" });
    },

    /** "19:00" */
    formatTime: function (value) {
        return Utils.toDate(value).toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" });
    },

    /** "2026-10-09 19:00:00" -> "2026-10-09T19:00" for <input type="datetime-local"> */
    toInputDateTime: function (value) {
        return value ? String(value).substring(0, 16).replace(" ", "T") : "";
    },

    /** Whole calendar days from today until the given date (negative = in the past). */
    daysUntil: function (value) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const day = Utils.toDate(String(value).substring(0, 10) + "T00:00:00");
        return Math.round((day - today) / 86400000);
    },

    /** Coloured banner + icon per category (no images needed). */
    categoryStyle: function (categoryName) {
        const styles = {
            "Music": { css: "cat-music", icon: "bi-music-note-beamed" },
            "Tech": { css: "cat-tech", icon: "bi-cpu" },
            "Sports": { css: "cat-sports", icon: "bi-trophy" },
            "Culture": { css: "cat-culture", icon: "bi-palette" },
            "Career": { css: "cat-career", icon: "bi-briefcase" }
        };
        return styles[categoryName] || { css: "cat-other", icon: "bi-calendar-event" };
    },

    /** Small white date badge on the event banner: 9 / OCT */
    dateBadge: function (value) {
        const date = Utils.toDate(value);
        return '<span class="date-badge"><span class="day">' + date.getDate() + '</span><span class="month">'
            + date.toLocaleDateString("en-GB", { month: "short" }) + "</span></span>";
    },

    stars: function (rating) {
        let html = "";
        for (let i = 1; i <= 5; i++) {
            if (rating >= i) html += '<i class="bi bi-star-fill"></i>';
            else if (rating >= i - 0.5) html += '<i class="bi bi-star-half"></i>';
            else html += '<i class="bi bi-star"></i>';
        }
        return '<span class="rating">' + html + "</span>";
    },

    initials: function (user) {
        return ((user.first_name || "?")[0] + (user.last_name || "")[0]).toUpperCase();
    },

    /** "18 of 20 seats left" / "Sold out" */
    seatsText: function (event) {
        return Number(event.available_seats) > 0
            ? event.available_seats + " of " + event.capacity + " seats left"
            : "Sold out";
    },

    registrationStatusBadge: function (registration) {
        if (registration.status === "cancelled") return '<span class="badge text-bg-secondary">cancelled</span>';
        if (registration.status === "attended") return '<span class="badge text-bg-success">attended</span>';
        if (Number(registration.is_upcoming) === 1) return '<span class="badge text-bg-primary">registered</span>';
        return '<span class="badge text-bg-warning">not checked in</span>';      // event is over, still "registered"
    },

    /** "today", "tomorrow", "in 3 days", "3 days ago" */
    startsBadge: function (value) {
        const days = Utils.daysUntil(value);
        if (days < 0) return '<span class="badge text-bg-light">' + -days + " day" + (days === -1 ? "" : "s") + " ago</span>";
        if (days === 0) return '<span class="badge text-bg-danger">today</span>';
        if (days === 1) return '<span class="badge text-bg-warning">tomorrow</span>';
        if (days <= 3) return '<span class="badge text-bg-warning">in ' + days + " days</span>";
        return '<span class="badge text-bg-light">in ' + days + " days</span>";
    },

    /** One forecast day (from our backend, which calls Open-Meteo) as a small card. */
    weatherCard: function (day) {
        const date = Utils.toDate(day.date + "T00:00:00");
        return `
            <div class="card weather-card h-100"><div class="card-body">
                <div class="fw-semibold">${date.toLocaleDateString("en-GB", { weekday: "short", day: "2-digit", month: "2-digit" })}</div>
                <i class="bi bi-${day.icon}" title="${day.description}"></i>
                <div class="h5 fw-bold mb-0">${day.temp_max}° / ${day.temp_min}°</div>
                <small class="text-muted">${day.description}<br>${day.precipitation_probability ?? 0}% rain</small>
            </div></div>`;
    }
};
