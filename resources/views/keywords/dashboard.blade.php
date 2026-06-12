@extends('layout.main')
@section('content')
    <section>
        <h2>All Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Keywords</h5>
                <button class="btn-custom btn-primary-gradient" id="addKeywordBtn">
                    Add Keyword
                </button>
            </div>

            <table id="keywords" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Keyword</th>
                        <th>Report Status</th>
                        <th>Created At</th>
                        <th>Error</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>
        <div class="modal fade" id="keywordModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modalTitle">Add Keyword</h5>

                        <button type="button" class="btn-close btn-custom btn-danger-gradient"
                            data-mdb-dismiss="modal">X</button>
                    </div>

                    <form id="keywordForm">
                        @csrf
                        <input type="hidden" id="keyword_id">

                        <div class="modal-body">
                            <input type="text" id="keyword" name="keyword" class="form-control"
                                placeholder="Enter keyword">

                            <small class="text-danger error-keyword"></small>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- show errors --}}
    <div class="modal fade" id="errorModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Error Details</h5>
                    <button type="button" class="btn-close" id="closeErrorModal"></button>
                </div>

                <div class="modal-body">
                    <pre id="errorText" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        let table;
        let modal;

        $(document).ready(function() {

            function resetKeywordForm() {
                $("#keywordForm")[0].reset();
                $("#keyword_id").val('');

                // clear validation errors
                $(".text-danger").text('');
                $(".form-control").removeClass("is-invalid");

                // reset action (optional safety)
                $("#keywordForm").attr("action", "/keywords/store");
            }
            $("#closeModalBtn").click(function() {
                resetKeywordForm();
            });
            $('#keywordModal').on('hidden.bs.modal', function() {
                resetKeywordForm();
            });

            table = $('#keywords').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/keywords/list',
                columns: [{
                        data: 0
                    },
                    {
                        data: 1
                    },
                    {
                        data: 2
                    },
                    {
                        data: 3
                    },
                    {
                        data: 4
                    },
                    {
                        data: 5
                    }
                ]
            });

            modal = createModal('keywordModal');

            // ADD
            $("#addKeywordBtn").click(function() {
                $("#modalTitle").text("Add Keyword");
                $("#keywordForm")[0].reset();
                $("#keyword_id").val('');
                $("#keywordForm").attr("action", "/keywords/store");
                modal.show();
            });

            // EDIT
            $(document).on("click", ".editKeyword", function() {

                showLoader();

                setTimeout(() => {
                    hideLoader();

                    $("#modalTitle").text("Edit Keyword");
                    $("#keyword").val($(this).data("keyword"));
                    $("#keyword_id").val($(this).data("id"));

                    $("#keywordForm").attr("action", "/keywords/update/" + $(this).data("id"));

                    modal.show();
                }, 200);
            });

            // SUBMIT
            $("#keywordForm").submit(function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr("action");

                $(".text-danger").text('');
                showLoader();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: form.serialize(),

                    success: function(res) {
                        hideLoader();
                        modal.hide();
                        table.ajax.reload();

                        let msg = url.includes('store') ?
                            'Keyword added successfully' :
                            'Keyword updated successfully';

                        showToast(msg, 'success');
                    },

                    error: function(err) {
                        hideLoader();

                        if (err.status === 422) {
                            let errors = err.responseJSON.errors;

                            $.each(errors, function(key, val) {
                                $(".error-" + key).text(val[0]);
                            });
                        } else {
                            showToast("Something went wrong!", 'error');
                        }
                    }
                });
            });

            // DELETE


            $(document).on('click', '.deleteKeyword', function() {

                let id = $(this).data("id");
                Swal.fire({
                    title: "Delete this keyword?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e3342f",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {

                    if (result.isConfirmed) {

                        showLoader();
                        $.post('/keywords/delete', {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id
                        }, function(res) {
                            hideLoader();
                            if (res.status) {
                                table.ajax.reload();
                            }
                        });
                        hideLoader();
                    }
                });
            });
        });

        // error modal
        let errorModal;

        $(document).ready(function() {

            errorModal = createModal('errorModal');

            // OPEN ERROR MODAL
            $(document).on("click", ".viewError", function() {

                let error = $(this).data("error");

                $("#errorText").text(error);

                errorModal.show();
            });

            // CLOSE ERROR MODAL
            $("#closeErrorModal").click(function() {
                errorModal.hide();
                $("#errorText").text('');
            });

        });
    </script>
@endsection
