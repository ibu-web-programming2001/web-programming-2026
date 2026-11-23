/*
 * Categories page: DataTable + add/edit/delete modals (admin).
 */
const CategoriesPage = {
    categories: [],

    init: function () {
        CategoriesPage.load();

        $("#add-category-button").off("click").on("click", function () {
            $("#category-form")[0].reset();
            $("#category-form [name=id]").val("");
            $("#category-modal-title").text("Add category");
            Utils.showModal("#category-modal");
        });
        $("#category-form").off("submit").on("submit", function (event) {
            event.preventDefault();
            CategoriesPage.save(Utils.formData("#category-form"));
        });
        $("#confirm-delete-category").off("click").on("click", CategoriesPage.remove);

        $("#categories-table").off("click")
            .on("click", ".btn-edit", function () { CategoriesPage.openEditModal($(this).data("id")); })
            .on("click", ".btn-delete", function () { CategoriesPage.openDeleteModal($(this).data("id")); });
    },

    load: function () {
        RestClient.get("categories", function (categories) {
            CategoriesPage.categories = categories;
            Utils.datatable("categories-table", [
                { data: "name", title: "Name", render: name => "<strong>" + name + "</strong>" },
                { data: "description", title: "Description", defaultContent: "–" },
                { data: "event_count", title: "Events", render: count => '<span class="badge text-bg-light">' + count + "</span>" },
                {
                    data: "id", title: "", render: id => Auth.isAdmin() ? `
                        <button class="btn btn-sm btn-light btn-edit" data-id="${id}" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light text-danger btn-delete" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>` : ""
                }
            ], categories);
        });
    },

    openEditModal: function (id) {
        const category = CategoriesPage.categories.find(c => c.id === id);
        Utils.fillForm("#category-form", { id: category.id, name: category.name, description: category.description });
        $("#category-modal-title").text("Edit category");
        Utils.showModal("#category-modal");
    },

    save: function (category) {
        const done = function () {
            Utils.hideModal("#category-modal");
            toastr.success("Category saved.");
            CategoriesPage.load();
        };
        if (category.id) {
            RestClient.put("categories/" + category.id, category, done);
        } else {
            RestClient.post("categories", category, done);
        }
    },

    openDeleteModal: function (id) {
        const category = CategoriesPage.categories.find(c => c.id === id);
        $("#delete-category-name").text(category.name);
        $("#confirm-delete-category").data("id", id);
        Utils.showModal("#delete-category-modal");
    },

    remove: function () {
        RestClient.delete("categories/" + $("#confirm-delete-category").data("id"), function () {
            Utils.hideModal("#delete-category-modal");
            toastr.success("Category deleted.");
            CategoriesPage.load();
        }, function (message) {
            Utils.hideModal("#delete-category-modal");
            toastr.error(message);
        });
    }
};
