/*
 * Events page: grid of event cards, client-side filter, add/edit/delete (admin), register (student).
 */
const EventsPage = {
    events: [],

    init: function () {
        EventsPage.loadEvents();
        EventsPage.loadSelects();

        $("#event-search, #event-category-filter, #event-when-filter").off("input change").on("input change", EventsPage.render);
        $("#add-event-button").off("click").on("click", EventsPage.openAddModal);
        $("#event-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            EventsPage.save(Utils.formData("#event-form"));
        });
        $("#confirm-delete-event").off("click").on("click", EventsPage.remove);

        // Buttons inside the cards are created later -> "delegated" events on the container
        $("#event-list").off("click")
            .on("click", ".btn-details", function () { EventsPage.openDetails($(this).data("id")); })
            .on("click", ".btn-edit", function () { EventsPage.openEditModal($(this).data("id")); })
            .on("click", ".btn-delete", function () { EventsPage.openDeleteModal($(this).data("id")); })
            .on("click", ".btn-register", function () { EventsPage.register($(this).data("id")); });
    },

    loadEvents: function () {
        RestClient.get("events", function (events) {
            EventsPage.events = events;
            EventsPage.render();
        });
    },

    /** Categories (filter + form) and clubs (form) come from the API too – no hard-coded lists. */
    loadSelects: function () {
        RestClient.get("categories", function (categories) {
            const options = categories.map(c => `<option value="${c.id}">${c.name}</option>`).join("");
            const selected = $("#event-category-filter").val();
            $("#event-category-filter").html('<option value="">All categories</option>' + options).val(selected);
            $("#event-category").html(options);
        });
        if (Auth.isAdmin()) {
            RestClient.get("clubs", function (clubs) {
                const select = $("#event-club");
                if (select.hasClass("select2-hidden-accessible")) select.select2("destroy");
                select.html(clubs.map(c => `<option value="${c.id}">${c.name}</option>`).join(""));
                // Select2 = searchable dropdown (useful with many clubs)
                select.select2({ theme: "bootstrap-5", dropdownParent: $("#event-modal") });
            });
        }
    },

    render: function () {
        const search = $("#event-search").val().toLowerCase();
        const categoryId = $("#event-category-filter").val();
        const when = $("#event-when-filter").val();
        const isAdmin = Auth.isAdmin();

        const events = EventsPage.events.filter(event =>
            (!categoryId || String(event.category_id) === categoryId) &&
            (when === "" || (when === "upcoming") === (Number(event.is_upcoming) === 1)) &&
            (event.title.toLowerCase().includes(search) || event.club_name.toLowerCase().includes(search) ||
             event.location.toLowerCase().includes(search))
        );
        // past events: newest first
        if (when === "past") events.reverse();

        if (events.length === 0) {
            $("#event-list").html('<div class="col-12"><div class="empty-state"><i class="bi bi-search"></i>No events found.</div></div>');
            return;
        }

        $("#event-list").html(events.map(event => {
            const style = Utils.categoryStyle(event.category_name);
            const upcoming = Number(event.is_upcoming) === 1;
            const free = Number(event.available_seats) > 0;
            return `
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
                <div class="card event-card ${upcoming ? "" : "event-past"}">
                    <div class="event-banner ${style.css}">
                        ${Utils.dateBadge(event.starts_at)}
                        <i class="bi ${style.icon}"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge text-bg-light align-self-start mb-2">${event.category_name}</span>
                        <div class="event-title">${event.title}</div>
                        <div class="event-meta mb-1"><i class="bi bi-people me-1"></i>${event.club_name}</div>
                        <div class="event-meta mb-2"><i class="bi bi-geo-alt me-1"></i>${event.location}, ${event.city} · ${Utils.formatTime(event.starts_at)}</div>
                        <div class="small ${!upcoming ? "text-muted" : free ? "text-success" : "text-danger"} mt-auto mb-2 fw-semibold">
                            <i class="bi ${!upcoming ? "bi-clock-history" : free ? "bi-ticket-perforated" : "bi-x-circle"}"></i>
                            ${upcoming ? Utils.seatsText(event) : "Event is over"}
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary flex-grow-1 btn-details" data-id="${event.id}">Details</button>
                            ${isAdmin ? `
                                <button class="btn btn-sm btn-light btn-edit" data-id="${event.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-light text-danger btn-delete" data-id="${event.id}" title="Delete"><i class="bi bi-trash"></i></button>
                            ` : `
                                <button class="btn btn-sm btn-primary btn-register" data-id="${event.id}" ${upcoming && free ? "" : "disabled"} title="Register"><i class="bi bi-ticket-perforated"></i></button>
                            `}
                        </div>
                    </div>
                </div>
            </div>`;
        }).join(""));
    },

    findEvent: function (id) {
        return EventsPage.events.find(event => event.id === id);
    },

    openDetails: function (id) {
        localStorage.setItem("selected_event_id", id);
        window.location.hash = "#event-details";
    },

    openAddModal: function () {
        $("#event-form")[0].reset();
        $("#event-form [name=id]").val("");
        $("#event-capacity").val(50);
        $("#event-club").trigger("change");
        $("#event-modal-title").text("Add event");
        Utils.showModal("#event-modal");
    },

    openEditModal: function (id) {
        const event = EventsPage.findEvent(id);
        Utils.fillForm("#event-form", {
            id: event.id, title: event.title, club_id: event.club_id, category_id: event.category_id, location: event.location,
            city: event.city, starts_at: Utils.toInputDateTime(event.starts_at), capacity: event.capacity, description: event.description
        });
        $("#event-modal-title").text("Edit event");
        Utils.showModal("#event-modal");
    },

    save: function (event) {
        const isNew = !event.id;
        Utils.block();
        const done = function () {
            Utils.unblock();
            Utils.hideModal("#event-modal");
            toastr.success(isNew ? "Event added." : "Event saved.");
            EventsPage.loadEvents();
        };
        const failed = function (message) {
            Utils.unblock();
            toastr.error(message);
        };
        if (isNew) {
            RestClient.post("events", event, done, failed);
        } else {
            RestClient.put("events/" + event.id, event, done, failed);
        }
    },

    openDeleteModal: function (id) {
        const event = EventsPage.findEvent(id);
        $("#delete-event-name").text(event.title);
        $("#confirm-delete-event").data("id", id);
        Utils.showModal("#delete-event-modal");
    },

    remove: function () {
        const id = $("#confirm-delete-event").data("id");
        RestClient.delete("events/" + id, function () {
            Utils.hideModal("#delete-event-modal");
            toastr.success("Event deleted.");
            EventsPage.loadEvents();
        }, function (message) {
            Utils.hideModal("#delete-event-modal");
            toastr.error(message);                // e.g. "2 student(s) are registered for this event..."
        });
    },

    register: function (id) {
        RestClient.post("registrations", { event_id: id }, function (registration) {
            toastr.success("You are registered for '" + registration.event_title + "' – see you on " + Utils.formatDateTime(registration.event_starts_at) + "!");
            EventsPage.loadEvents();
        });
    }
};
