@extends('layout.main')
@section('content')
    <section>
        <h2>All Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Keywords</h5>
                <div class="d-flex gap-2">
                    <a href="{{ url('/keywords/download-template') }}" class="btn-outline-custom btn-outline-primary-gradient">
                        Download CSV Format
                    </a>
                    <button class="btn-custom btn-secondary-gradient" id="uploadCsvBtn">
                        Upload CSV
                    </button>
                    <button class="btn-custom btn-primary-gradient" id="addKeywordBtn">
                        Add Keyword
                    </button>
                </div>
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

                            <div class="mt-3" id="statusWrapper" style="display:none;">
                                <label class="form-label" for="report_status">Status</label>
                                <select id="report_status" name="report_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="hold">Hold</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <small class="text-danger error-report_status"></small>
                            </div>
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

        {{-- CSV upload --}}
        <div class="modal fade" id="csvModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Upload Keywords CSV</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient"
                            data-mdb-dismiss="modal">X</button>
                    </div>

                    <form id="csvForm">
                        @csrf

                        <div class="modal-body">
                            <p class="text-muted small">
                                CSV with the market name in the first column.
                                <a href="{{ url('/keywords/download-template') }}">Download the format</a> if you need it.
                            </p>

                            <input type="file" id="csv_file" name="file" class="form-control" accept=".csv,text/csv" required>
                            <small class="text-danger error-file"></small>

                            <div class="form-check mt-3">
                                <input type="checkbox" class="form-check-input" id="skip_existing" name="skip_existing"
                                    value="1" checked>
                                <label class="form-check-label" for="skip_existing">
                                    Skip existing keywords
                                    <small class="d-block text-muted">
                                        Checked: keywords already in the table are left untouched, only new ones are added.
                                        Unchecked: existing keywords are replaced (status resets to pending).
                                    </small>
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient">
                                Upload
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

                // New keywords always start as pending — no need to choose.
                $("#statusWrapper").hide();

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
                    $("#report_status").val($(this).data("status") || 'pending');

                    $("#keywordForm").attr("action", "/keywords/update/" + $(this).data("id"));

                    // Editing is also how a keyword stuck on "failed" (or
                    // "processing") after a generation error gets moved back
                    // to pending, or held, without status silently resetting.
                    $("#statusWrapper").show();

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

            // CSV UPLOAD
            let csvModal = createModal('csvModal');

            $("#uploadCsvBtn").click(function() {
                $("#csvForm")[0].reset();
                $("#skip_existing").prop('checked', true);
                $(".error-file").text('');
                csvModal.show();
            });

            $("#csvForm").submit(function(e) {
                e.preventDefault();

                $(".error-file").text('');

                let formData = new FormData(this);
                formData.set('skip_existing', $("#skip_existing").is(':checked') ? 1 : 0);

                showLoader();

                $.ajax({
                    url: '/keywords/import-csv',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(res) {
                        hideLoader();
                        csvModal.hide();
                        table.ajax.reload();
                        showToast(res.message, 'success');
                    },

                    error: function(err) {
                        hideLoader();

                        if (err.status === 422) {
                            let message = err.responseJSON?.message;
                            let errors = err.responseJSON?.errors;

                            if (errors) {
                                $.each(errors, function(key, val) {
                                    $(".error-" + key).text(val[0]);
                                });
                            } else if (message) {
                                $(".error-file").text(message);
                            }
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
