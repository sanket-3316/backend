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

            <div class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterStatus">Report Status</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="hold">Hold</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" id="clearKeywordFiltersBtn" class="btn btn-outline-secondary btn-sm">Clear filter</button>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <select id="bulkStatusSelect" class="form-control form-control-sm" style="width:auto;">
                        <option value="pending">Pending</option>
                        <option value="hold">Hold</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button type="button" id="bulkUpdateStatusBtn" class="btn-custom btn-secondary-gradient btn-sm">
                        Set Status (Selected)
                    </button>
                    <button type="button" id="bulkDeleteBtn" class="btn-custom btn-warning-gradient btn-sm">
                        Delete Selected
                    </button>
                </div>
            </div>

            <table id="keywords" class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllKeywords"></th>
                        <th>ID</th>
                        <th>Keyword</th>
                        <th>Category</th>
                        <th>Report Status</th>
                        <th>Created At</th>
                        <th>Error</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>
        <div class="modal fade" id="keywordModal">
            <div class="modal-dialog modal-lg">
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
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label" for="keyword">Keyword / Market Name</label>
                                    <input type="text" id="keyword" name="keyword" class="form-control"
                                        placeholder="e.g. Biogas">
                                    <small class="text-danger error-keyword"></small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="category_id">Category (optional)</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">— GPT will choose —</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger error-category_id"></small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="base_year_market_size">Base Year Market Size</label>
                                    <input type="text" id="base_year_market_size" name="base_year_market_size" class="form-control"
                                        placeholder='e.g. $1.5 Billion'>
                                    <small class="text-danger error-base_year_market_size"></small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="forecast_year_market_size">Forecast Year Market Size</label>
                                    <input type="text" id="forecast_year_market_size" name="forecast_year_market_size" class="form-control"
                                        placeholder='e.g. $3.2 Billion'>
                                    <small class="text-danger error-forecast_year_market_size"></small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="forecast_cagr">Forecast CAGR (%)</label>
                                    <input type="text" id="forecast_cagr" name="forecast_cagr" class="form-control"
                                        placeholder="e.g. 8.5">
                                    <small class="text-danger error-forecast_cagr"></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="segments">Segments (JSON)</label>
                                <textarea id="segments" name="segments" class="form-control" rows="4"
                                    style="font-family: monospace; font-size: 13px;"
                                    placeholder='{"By Type": ["Agricultural", "Industrial"], "By Application": ["Power Generation", "Heat"]}'></textarea>
                                <small class="text-danger error-segments"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="companies">Key Companies (comma separated)</label>
                                <input type="text" id="companies" name="companies" class="form-control"
                                    placeholder="Company A, Company B, Company C">
                                <small class="text-danger error-companies"></small>
                            </div>

                            <div class="mt-3" id="statusWrapper" style="display:none;">
                                <label class="form-label" for="report_status">Status</label>
                                <select id="report_status" name="report_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="hold">Hold</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <small class="text-danger error-report_status"></small>
                            </div>

                            <div class="form-check form-switch mt-3" id="quickGenerateWrapper">
                                <input class="form-check-input" type="checkbox" id="quick_generate" name="quick_generate" value="1">
                                <label class="form-check-label" for="quick_generate">
                                    Quick Generate — generate the report immediately and save it, instead of waiting for the next cron run
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient" id="keywordSubmitBtn">
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
                                CSV columns: Market Name, Base Year Market Size, Forecast Year Market Size, Forecast CAGR,
                                Segments (JSON), Key Companies (comma separated).
                                <a href="{{ url('/keywords/download-template') }}">Download the format</a> if you need it.
                            </p>

                            <input type="file" id="csv_file" name="file" class="form-control" accept=".csv,text/csv" required>
                            <small class="text-danger error-file"></small>

                            <div class="mt-3">
                                <label class="form-label" for="csv_category_id">Category (optional — applies to every row in this file)</label>
                                <select id="csv_category_id" name="category_id" class="form-control">
                                    <option value="">— GPT will choose per report —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

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
                ajax: {
                    url: '/keywords/list',
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                    }
                },
                order: [
                    [5, 'desc']
                ],
                columns: [{
                        data: 0,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 1
                    },
                    {
                        data: 2
                    },
                    {
                        data: 3,
                        orderable: false
                    },
                    {
                        data: 4
                    },
                    {
                        data: 5
                    },
                    {
                        data: 6,
                        orderable: false
                    },
                    {
                        data: 7,
                        orderable: false
                    }
                ]
            });

            modal = createModal('keywordModal');

            $('#filterStatus').on('change', function() {
                table.ajax.reload();
            });

            $('#clearKeywordFiltersBtn').on('click', function() {
                $('#filterStatus').val('');
                table.ajax.reload();
            });

            $(document).on('change', '#selectAllKeywords', function() {
                $('.rowCheckbox').prop('checked', $(this).is(':checked'));
            });

            function getSelectedKeywordIds() {
                return $('.rowCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            $('#bulkDeleteBtn').on('click', function() {
                let ids = getSelectedKeywordIds();

                if (!ids.length) {
                    showToast('Select at least one keyword first', 'danger');
                    return;
                }

                if (!confirm(`Delete ${ids.length} selected keyword(s)? This cannot be undone.`)) {
                    return;
                }

                $.ajax({
                    url: '/keywords/bulk-delete',
                    method: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function(res) {
                        showToast(`${res.deleted} keyword(s) deleted`, 'success');
                        table.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            $('#bulkUpdateStatusBtn').on('click', function() {
                let ids = getSelectedKeywordIds();

                if (!ids.length) {
                    showToast('Select at least one keyword first', 'danger');
                    return;
                }

                $.ajax({
                    url: '/keywords/bulk-update-status',
                    method: 'POST',
                    data: {
                        ids: ids,
                        report_status: $('#bulkStatusSelect').val()
                    },
                    success: function(res) {
                        showToast(`${res.updated} keyword(s) updated`, 'success');
                        table.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            // ADD
            $("#addKeywordBtn").click(function() {
                $("#modalTitle").text("Add Keyword");
                $("#keywordForm")[0].reset();
                $("#keyword_id").val('');
                $("#keywordForm").attr("action", "/keywords/store");

                // New keywords always start as pending — no need to choose,
                // and Quick Generate only makes sense for a brand-new keyword.
                $("#statusWrapper").hide();
                $("#quickGenerateWrapper").show();

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
                    $("#base_year_market_size").val($(this).data("base_year_market_size"));
                    $("#forecast_year_market_size").val($(this).data("forecast_year_market_size"));
                    $("#forecast_cagr").val($(this).data("forecast_cagr"));
                    $("#segments").val($(this).data("segments"));
                    $("#companies").val($(this).data("companies"));
                    $("#category_id").val($(this).data("category_id") || '');

                    $("#keywordForm").attr("action", "/keywords/update/" + $(this).data("id"));

                    // Editing is also how a keyword stuck on "failed" (or
                    // "processing") after a generation error gets moved back
                    // to pending, or held, without status silently resetting.
                    $("#statusWrapper").show();
                    // Quick Generate is an add-time-only convenience — editing
                    // an existing keyword still goes through the normal cron.
                    $("#quickGenerateWrapper").hide();
                    $("#quick_generate").prop('checked', false);

                    modal.show();
                }, 200);
            });

            // SUBMIT
            $("#keywordForm").submit(function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr("action");
                let isQuickGenerate = $("#quick_generate").is(":checked") && url.includes('store');

                $(".text-danger").text('');
                $(".form-control").removeClass("is-invalid");
                showLoader();

                if (isQuickGenerate) {
                    $("#keywordSubmitBtn").prop('disabled', true).text('Generating… this can take a minute');
                }

                $.ajax({
                    url: url,
                    type: "POST",
                    data: form.serialize(),
                    timeout: 180000, // Quick Generate runs a real GPT call synchronously

                    success: function(res) {
                        hideLoader();
                        $("#keywordSubmitBtn").prop('disabled', false).text('Save');
                        modal.hide();
                        table.ajax.reload();

                        showToast(res.message || (url.includes('store') ? 'Keyword added successfully' : 'Keyword updated successfully'), 'success');
                    },

                    error: function(err) {
                        hideLoader();
                        $("#keywordSubmitBtn").prop('disabled', false).text('Save');

                        if (err.status === 422) {
                            let errors = err.responseJSON.errors;

                            if (errors) {
                                $.each(errors, function(key, val) {
                                    $(".error-" + key).text(Array.isArray(val) ? val[0] : val);
                                    $("#" + key).addClass("is-invalid");
                                });
                            } else {
                                showToast(err.responseJSON?.message || 'Something went wrong', 'danger');
                            }
                        } else {
                            showToast("Something went wrong!", 'danger');
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
