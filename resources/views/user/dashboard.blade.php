@extends('layout.main')
@section('content')
    <section>
        <h2>User Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Users</h5>
                <button class=" btn-custom btn-primary-gradient" id="addUserBtn">Add User</button>
            </div>

            <table id="users" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
            </table>

        </div>
        <div class="modal fade" id="userModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modalTitle">Add User</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient" data-mdb-dismiss="modal"> X
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            @csrf

                            <div class="modal-body">

                                <input type="hidden" id="user_id">

                                <!-- NAME -->
                                <div class="mb-3">
                                    <input type="text" id="name" name="name" class="form-control"
                                        placeholder="Enter user name">
                                    <small class="text-danger error-name"></small>
                                </div>

                                <!-- EMAIL -->
                                <div class="mb-3">
                                    <input type="email" id="email" name="email" class="form-control"
                                        placeholder="Enter email address">
                                    <small class="text-danger error-email"></small>
                                </div>

                                <!-- PASSWORD -->
                                <div class="mb-3">
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Enter password">
                                    <small class="text-danger error-password"></small>
                                </div>

                                <!-- ROLE -->
                                <div class="mb-3">
                                    <select id="role" name="role" class="form-control">
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger error-role"></small>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class=" btn-custom  btn-secondary-gradient">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        let table;
        let modal;

        $(document).ready(function() {

            table = $('#users').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/users/list',
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4, orderable: false }
                ]
            });
            // ✅ create modal ONLY ONCE
            modal = createModal('userModal');

            /* =========================
               ADD USER
            ========================= */
            $("#addUserBtn").click(function() {
                clearFormErrors();
                $("#modalTitle").text("Add User");
                $("#userForm").attr("action", "/users/store");

                $("#userForm")[0].reset();
                $("#user_id").val('');

                modal.show();
            });

            /* =========================
               EDIT USER
            ========================= */
            $(document).on("click", ".editUser", function() {
                clearFormErrors();
                $("#modalTitle").text("Edit User");

                $("#name").val($(this).data("name"));
                $("#email").val($(this).data("email"));
                $("#user_id").val($(this).data("id"));
                $("#role").val($(this).data("role"));
                $("#userForm").attr("action", "/users/update/" + $(this).data("id"));

                modal.show();
            });

            /* =========================
               DELETE USER (AJAX)
            ========================= */



            $("#userForm").submit(function(e) {

                e.preventDefault();

                let form = $(this);
                let url = form.attr("action");
                let formData = form.serialize();

                // clear old errors
                $(".text-danger").text('');
                $(".form-control").removeClass("is-invalid");
                showLoader();
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,

                    success: function(res) {
                        hideLoader();
                        // SUCCESS
                        modal.hide();

                        // reset form
                        $("#userForm")[0].reset();

                        // reload table
                        table.ajax.reload();

                    },

                    error: function(err) {
                        hideLoader();
                        if (err.status === 422) {

                            let errors = err.responseJSON.errors;

                            $.each(errors, function(key, value) {

                                // show error text
                                $(".error-" + key).text(value[0]);

                                // red border
                                $("#" + key).addClass("is-invalid");
                            });
                        }
                    }
                });
            });

            $(document).on("click",'.deleteUser', function(e) {
                e.preventDefault()
                let id = $(this).data("id");

                Swal.fire({
                    title: "Delete this user?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e3342f",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {

                    if (result.isConfirmed) {

                        showLoader();

                        $.ajax({
                            url: "/users/delete",
                            type: "POST",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                user_id: id
                            },

                            success: function(res) {

                                hideLoader();

                                if (res.status) {

                                    table.ajax.reload();

                                    showToast(res.message, 'success');

                                } else {
                                    showToast(res.message, 'danger');
                                }
                            },

                            error: function() {
                                hideLoader();
                                showToast("Something went wrong!", 'danger');
                            }
                        });

                    }
                });

            });
        });
    </script>
@endsection
