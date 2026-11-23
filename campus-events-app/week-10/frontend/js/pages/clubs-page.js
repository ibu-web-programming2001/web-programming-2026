/*
 * Clubs page: DataTable + add/edit/delete modals (admin).
 */
const ClubsPage = {
    clubs: [],

    init: function () {
        ClubsPage.load();

        $("#add-club-button").off("click").on("click", function () {
            $("#club-form")[0].reset();
            $("#club-form [name=id]").val("");
            $("#club-modal-title").text("Add club");
            Utils.showModal("#club-modal");
        });
        $("#club-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            ClubsPage.save(Utils.formData("#club-form"));
        });
        $("#confirm-delete-club").off("click").on("click", ClubsPage.remove);

        $("#clubs-table").off("click")
            .on("click", ".btn-edit", function () { ClubsPage.openEditModal($(this).data("id")); })
            .on("click", ".btn-delete", function () { ClubsPage.openDeleteModal($(this).data("id")); });
    },

    load: function () {
        RestClient.get("clubs", function (clubs) {
            ClubsPage.clubs = clubs;
            Utils.datatable("clubs-table", [
                { data: "name", title: "Name", render: name => "<strong>" + name + "</strong>" },
                { data: "faculty", title: "Faculty", defaultContent: "–" },
                { data: "founded_year", title: "Founded", defaultContent: "–" },
                { data: "event_count", title: "Events", render: count => '<span class="badge text-bg-light">' + count + "</span>" },
                {
                    data: "id", title: "", render: id => Auth.isAdmin() ? `
                        <button class="btn btn-sm btn-light btn-edit" data-id="${id}" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light text-danger btn-delete" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>` : ""
                }
            ], clubs);
        });
    },

    openEditModal: function (id) {
        const club = ClubsPage.clubs.find(a => a.id === id);
        Utils.fillForm("#club-form", {
            id: club.id, name: club.name, faculty: club.faculty, founded_year: club.founded_year, description: club.description
        });
        $("#club-modal-title").text("Edit club");
        Utils.showModal("#club-modal");
    },

    save: function (club) {
        const done = function () {
            Utils.hideModal("#club-modal");
            toastr.success("Club saved.");
            ClubsPage.load();
        };
        if (club.id) {
            RestClient.put("clubs/" + club.id, club, done);
        } else {
            RestClient.post("clubs", club, done);
        }
    },

    openDeleteModal: function (id) {
        const club = ClubsPage.clubs.find(a => a.id === id);
        $("#delete-club-name").text(club.name);
        $("#confirm-delete-club").data("id", id);
        Utils.showModal("#delete-club-modal");
    },

    remove: function () {
        RestClient.delete("clubs/" + $("#confirm-delete-club").data("id"), function () {
            Utils.hideModal("#delete-club-modal");
            toastr.success("Club deleted.");
            ClubsPage.load();
        }, function (message) {
            Utils.hideModal("#delete-club-modal");
            toastr.error(message);              // "This club still organises 2 event(s)..."
        });
    }
};
