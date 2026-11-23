/*
 * Users page (admin only): DataTable + add/edit/delete modals.
 */
const UsersPage = {
    users: [],

    init: function () {
        UsersPage.load();

        $("#add-user-button").off("click").on("click", function () {
            $("#user-form")[0].reset();
            $("#user-form [name=id]").val("");
            $("#user-modal-title").text("Add user");
            $("#user-password-help").text("At least 8 characters, with a letter and a number.");
            Utils.showModal("#user-modal");
        });
        $("#user-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            UsersPage.save(Utils.formData("#user-form"));
        });
        $("#confirm-delete-user").off("click").on("click", UsersPage.remove);

        $("#users-table").off("click")
            .on("click", ".btn-edit", function () { UsersPage.openEditModal($(this).data("id")); })
            .on("click", ".btn-delete", function () { UsersPage.openDeleteModal($(this).data("id")); });
    },

    load: function () {
        const me = Auth.getUser();
        RestClient.get("users", function (users) {
            UsersPage.users = users;
            Utils.datatable("users-table", [
                { data: null, title: "Name", render: (data, type, user) => "<strong>" + user.first_name + " " + user.last_name + "</strong>" },
                { data: "email", title: "Email" },
                {
                    data: "role", title: "Role",
                    render: role => role === "admin" ? '<span class="badge text-bg-primary">admin</span>' : '<span class="badge text-bg-light">student</span>'
                },
                { data: "faculty", title: "Faculty", defaultContent: "–" },
                { data: "created_at", title: "Joined", render: date => Utils.formatDate(date.substring(0, 10)) },
                {
                    data: "id", title: "", render: id => `
                        <button class="btn btn-sm btn-light btn-edit" data-id="${id}" title="Edit"><i class="bi bi-pencil"></i></button>
                        ${id === me.id ? "" : `<button class="btn btn-sm btn-light text-danger btn-delete" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>`}`
                }
            ], users);
        });
    },

    openEditModal: function (id) {
        const user = UsersPage.users.find(u => u.id === id);
        $("#user-form")[0].reset();
        Utils.fillForm("#user-form", { id: user.id, first_name: user.first_name, last_name: user.last_name, email: user.email, role: user.role, faculty: user.faculty });
        $("#user-modal-title").text("Edit user");
        $("#user-password-help").text("Leave empty to keep the current password.");
        Utils.showModal("#user-modal");
    },

    save: function (user) {
        const done = function () {
            Utils.hideModal("#user-modal");
            toastr.success("User saved.");
            UsersPage.load();
        };
        if (user.id) {
            RestClient.put("users/" + user.id, user, done);
        } else {
            RestClient.post("users", user, done);
        }
    },

    openDeleteModal: function (id) {
        const user = UsersPage.users.find(u => u.id === id);
        $("#delete-user-name").text(user.first_name + " " + user.last_name);
        $("#confirm-delete-user").data("id", id);
        Utils.showModal("#delete-user-modal");
    },

    remove: function () {
        RestClient.delete("users/" + $("#confirm-delete-user").data("id"), function () {
            Utils.hideModal("#delete-user-modal");
            toastr.success("User deleted.");
            UsersPage.load();
        }, function (message) {
            Utils.hideModal("#delete-user-modal");
            toastr.error(message);
        });
    }
};
