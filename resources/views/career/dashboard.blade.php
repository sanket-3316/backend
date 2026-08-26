@extends('layout.main')
@section('content')
    <section>
        <h2>Career Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Career Openings</h5>
                <button class="btn-custom btn-primary-gradient" id="addCareerBtn">Add Opening</button>
            </div>

            <table id="careerTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role</th>
                        <th>Practice Area</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="modal fade" id="careerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h5 id="careerModalTitle">Add Opening</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient"
                            data-mdb-dismiss="modal">X</button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">
                        <form id="careerForm">
                            @csrf

                            <input type="hidden" id="career_id">

                            <!-- ROLE -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="text" name="role" id="role" class="form-control"
                                    placeholder="Role" required>
                                <label class="form-label" for="role">Role</label>
                            </div>

                            <!-- PRACTICE AREA -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="text" name="practice_area" id="practice_area" class="form-control"
                                    placeholder="Practice Area" required>
                                <label class="form-label" for="practice_area">Practice Area</label>
                            </div>

                            <!-- LOCATION -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="text" name="location" id="location" class="form-control"
                                    placeholder="Location" required>
                                <label class="form-label" for="location">Location (e.g. Remote / UAE)</label>
                            </div>

                            <!-- DESCRIPTION -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <textarea name="description" id="description" class="form-control" rows="4"
                                    placeholder="Description (optional)"></textarea>
                                <label class="form-label" for="description">Description (optional)</label>
                            </div>

                            <!-- SORT ORDER -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="number" name="sort_order" id="sort_order" class="form-control"
                                    value="0">
                                <label class="form-label" for="sort_order">Sort Order</label>
                            </div>

                            <!-- ACTIVE -->
                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                    value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Visible on website
                                </label>
                            </div>

                            <!-- FOOTER -->
                            <div class="modal-footer">
                                <button type="submit" class="btn-custom btn-secondary-gradient">
                                    Save
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {

            let table = $('#careerTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/career/list',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'role'
                    },
                    {
                        data: 'practice_area'
                    },
                    {
                        data: 'location'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $("#addCareerBtn").click(function() {
                $("#careerModalTitle").text("Add Opening");
                $("#careerForm")[0].reset();
                $("#career_id").val('');
                $('#is_active').prop('checked', true);
                $("#careerModal").modal('show');
            });

            $(document).on('click', '.editCareer', function() {

                let id = $(this).data('id');

                showLoader();

                $.get('/career/show/' + id, function(item) {

                    $("#career_id").val(item.id);
                    $("#role").val(item.role);
                    $("#practice_area").val(item.practice_area);
                    $("#location").val(item.location);
                    $("#description").val(item.description);
                    $("#sort_order").val(item.sort_order);
                    $('#is_active').prop('checked', !!item.is_active);

                    $("#careerModalTitle").text("Edit Opening");
                    $("#careerModal").modal('show');
                    hideLoader();
                });
            });

            $("#careerForm").submit(function(e) {

                e.preventDefault();

                let id = $("#career_id").val();
                let url = id ? '/career/update/' + id : '/career/store';
                let formData = new FormData(this);
                formData.set('is_active', $('#is_active').is(':checked') ? 1 : 0);

                showLoader();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(res) {

                        hideLoader();

                        if (res.status) {
                            $("#careerModal").modal('hide');
                            table.ajax.reload();
                            showToast(res.message, 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    },

                    error: function(xhr) {
                        hideLoader();

                        let message = 'Something went wrong';

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(e => e[0]).join('\n');
                        } else if (xhr.status === 500) {
                            message = xhr.responseJSON?.message || 'Server error';
                        } else if (xhr.status === 401 || xhr.status === 403) {
                            message = 'Unauthorized access';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            });

            $(document).on('click', '.deleteCareer', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: "Delete this opening?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e3342f",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {

                    if (result.isConfirmed) {

                        showLoader();
                        $.post('/career/delete', {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id
                        }, function(res) {
                            hideLoader();
                            if (res.status) {
                                table.ajax.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
