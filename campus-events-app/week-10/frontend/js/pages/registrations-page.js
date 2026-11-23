/*
 * Registrations page.
 *  - students: their own registrations, can cancel upcoming ones
 *  - admins: all registrations, register any student, check in (attended), cancel, delete
 */
const RegistrationsPage = {
    registrations: [],

    init: function () {
        $("#registrations-subtitle").text(Auth.isAdmin() ? "Who is coming to which event." : "Events you registered for. Find new ones on the Events page.");
        RegistrationsPage.load();

        $("#add-registration-button").off("click").on("click", RegistrationsPage.openAddModal);
        $("#registration-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            RegistrationsPage.create(Utils.formData("#registration-form"));
        });
        $("#confirm-delete-registration").off("click").on("click", RegistrationsPage.remove);

        $("#registrations-table").off("click")
            .on("click", ".btn-cancel", function () { RegistrationsPage.cancel($(this).data("id")); })
            .on("click", ".btn-attend", function () { RegistrationsPage.markAttended($(this).data("id")); })
            .on("click", ".btn-delete", function () { RegistrationsPage.openDeleteModal($(this).data("id")); });
    },

    load: function () {
        const isAdmin = Auth.isAdmin();
        RestClient.get("registrations", function (registrations) {
            RegistrationsPage.registrations = registrations;
            Utils.datatable("registrations-table", [
                { data: "event_title", title: "Event", render: title => "<strong>" + title + "</strong>" },
                { data: "user_name", title: "Student", visible: isAdmin },
                // "display" shows a nice date, sorting uses the raw YYYY-MM-DD HH:MM:SS value
                { data: "event_starts_at", title: "Event date", render: (date, type) => type === "display" ? Utils.formatDateTime(date) : date },
                { data: "registered_at", title: "Registered", render: (date, type) => type === "display" ? Utils.formatDate(date) : date },
                { data: null, title: "Status", render: (data, type, registration) => Utils.registrationStatusBadge(registration) },
                {
                    data: null, title: "", render: (data, type, registration) => {
                        let buttons = "";
                        if (Number(registration.is_upcoming) === 1) {
                            buttons += `<button class="btn btn-sm btn-outline-primary btn-cancel" data-id="${registration.id}">Cancel</button> `;
                        }
                        if (isAdmin) {
                            if (registration.status === "registered") {
                                buttons += `<button class="btn btn-sm btn-light text-success btn-attend" data-id="${registration.id}" title="Check in (attended)"><i class="bi bi-person-check"></i></button> `;
                            }
                            buttons += `<button class="btn btn-sm btn-light text-danger btn-delete" data-id="${registration.id}" title="Delete"><i class="bi bi-trash"></i></button>`;
                        }
                        return buttons;
                    }
                }
            ], registrations);
        });
    },

    /** Admin: register any student. Students and upcoming events with free seats come from the API. */
    openAddModal: function () {
        $("#registration-form")[0].reset();
        RestClient.get("users", function (users) {
            const students = users.filter(u => u.role === Constants.STUDENT_ROLE);
            $("#registration-user").html(students.map(u => `<option value="${u.id}">${u.first_name} ${u.last_name}</option>`).join(""));
        });
        RestClient.get("events", function (events) {
            const open = events.filter(e => Number(e.is_upcoming) === 1 && Number(e.available_seats) > 0);
            $("#registration-event").html(open.map(e => `<option value="${e.id}">${e.title} (${e.available_seats} seats left)</option>`).join(""));
        });
        Utils.showModal("#registration-modal");
    },

    create: function (registration) {
        RestClient.post("registrations", registration, function () {
            Utils.hideModal("#registration-modal");
            toastr.success("Registration created.");
            RegistrationsPage.load();
        });
    },

    cancel: function (id) {
        RestClient.put("registrations/" + id + "/cancel", null, function (registration) {
            toastr.success("Registration for '" + registration.event_title + "' cancelled – the seat is free again.");
            RegistrationsPage.load();
        });
    },

    markAttended: function (id) {
        RestClient.put("registrations/" + id + "/attend", null, function (registration) {
            toastr.success(registration.user_name + " checked in.");
            RegistrationsPage.load();
        });
    },

    openDeleteModal: function (id) {
        const registration = RegistrationsPage.registrations.find(r => r.id === id);
        $("#delete-registration-name").text(registration.event_title + " – " + registration.user_name);
        $("#confirm-delete-registration").data("id", id);
        Utils.showModal("#delete-registration-modal");
    },

    remove: function () {
        RestClient.delete("registrations/" + $("#confirm-delete-registration").data("id"), function () {
            Utils.hideModal("#delete-registration-modal");
            toastr.success("Registration deleted.");
            RegistrationsPage.load();
        });
    }
};
