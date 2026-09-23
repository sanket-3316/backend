@extends('layout.main')
@section('content')
    <section>
        <h2>Leads Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Leads</h5>
                <button class="btn-custom btn-primary-gradient" id="addLeadBtn">Add Lead</button>
            </div>

            <div class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterDateFrom">Submitted from</label>
                    <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterDateTo">Submitted to</label>
                    <input type="date" id="filterDateTo" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterReport">Report</label>
                    <input type="text" id="filterReport" class="form-control form-control-sm" placeholder="Search by report name">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterStatus">Status</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary btn-sm">Clear filters</button>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <button type="button" id="exportSelectedBtn" class="btn-custom btn-secondary-gradient btn-sm">
                        Export Selected
                    </button>
                    <button type="button" id="exportFilteredBtn" class="btn-custom btn-primary-gradient btn-sm">
                        Export (Filtered)
                    </button>
                </div>
            </div>

            <table id="leadsTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllLeads"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Report</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>

        <!-- ADD / EDIT MODAL -->
        <div class="modal fade" id="leadModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="leadModalTitle">Add Lead</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient" data-mdb-dismiss="modal"> X
                        </button>
                    </div>
                    <form id="leadForm">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" id="lead_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Name">
                                    <small class="text-danger error-name"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Email">
                                    <small class="text-danger error-email"></small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone">
                                    <small class="text-danger error-phone"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="designation" id="designation" class="form-control" placeholder="Designation">
                                    <small class="text-danger error-designation"></small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <select name="report_id" id="report_id" class="form-control">
                                        <option value="">Select report (optional)</option>
                                        @foreach ($reports as $report)
                                            <option value="{{ $report->report_id }}">{{ $report->report_title }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger error-report_id"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <select name="category_id" id="category_id" class="form-control">
                                        <option value="">Select category (optional)</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger error-category_id"></small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <select name="status_id" id="status_id" class="form-control">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger error-status_id"></small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <textarea name="message" id="message" class="form-control" rows="4" placeholder="Requirements / message"></textarea>
                                    <small class="text-danger error-message"></small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient">Save Lead</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        let leadModal;
        let leadsTable;

        $(document).ready(function() {

            leadModal = createModal('leadModal');

            leadsTable = $('#leadsTable').DataTable({
                ajax: '/leads/list',
                order: [
                    [8, 'desc']
                ],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="rowCheckbox" value="${row.id}">`;
                        }
                    },
                    {
                        data: 'id',
                        orderable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone',
                        render: function(data) {
                            return data || '—';
                        }
                    },
                    {
                        data: 'report_title',
                        render: function(data) {
                            return data || '—';
                        }
                    },
                    {
                        data: 'category_name',
                        render: function(data) {
                            return data || '—';
                        }
                    },
                    {
                        data: 'status_name',
                        render: function(data) {
                            return data ? `<span class="badge bg-info">${data}</span>` : '—';
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (!data) return '—';
                            let d = new Date(data.replace(' ', 'T'));
                            return isNaN(d) ? data : d.toLocaleString();
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                <button class="btn-custom btn-primary-gradient editLead" data-id="${data}">Edit</button>
                <button class="btn-custom btn-warning-gradient deleteLead" data-id="${data}">Delete</button>
                `;
                        }
                    }
                ]
            });

            // ===== FILTERS (client-side, same pattern as the report dashboard) =====
            $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData) {
                if (settings.nTable.id !== 'leadsTable') return true;

                let dateFrom = $('#filterDateFrom').val();
                let dateTo = $('#filterDateTo').val();
                let reportFilter = $('#filterReport').val().trim().toLowerCase();
                let statusFilter = $('#filterStatus').val();

                if (dateFrom || dateTo) {
                    let rowDate = rowData.created_at ? String(rowData.created_at).substring(0, 10) : null;
                    if (!rowDate) return false;
                    if (dateFrom && rowDate < dateFrom) return false;
                    if (dateTo && rowDate > dateTo) return false;
                }

                if (reportFilter) {
                    let reportName = (rowData.report_title || '').toLowerCase();
                    if (!reportName.includes(reportFilter)) return false;
                }

                if (statusFilter && rowData.status_name !== statusFilter) {
                    return false;
                }

                return true;
            });

            $('#filterDateFrom, #filterDateTo, #filterStatus').on('change', function() {
                leadsTable.draw();
            });

            $('#filterReport').on('keyup', function() {
                leadsTable.draw();
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#filterReport').val('');
                $('#filterStatus').val('');
                leadsTable.draw();
            });

            // "select all" only affects checkboxes currently rendered on this page
            $(document).on('change', '#selectAllLeads', function() {
                $('.rowCheckbox').prop('checked', $(this).is(':checked'));
            });

            // ===== EXPORT SELECTED (checked rows only, ignores date filter) =====
            $('#exportSelectedBtn').on('click', function() {
                let ids = $('.rowCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!ids.length) {
                    showToast('Select at least one lead first', 'danger');
                    return;
                }

                window.location.href = '/leads/export?ids=' + ids.join(',');
            });

            // ===== EXPORT (FILTERED) — respects the active date filter =====
            $('#exportFilteredBtn').on('click', function() {
                let params = new URLSearchParams();
                let dateFrom = $('#filterDateFrom').val();
                let dateTo = $('#filterDateTo').val();

                if (dateFrom) params.set('date_from', dateFrom);
                if (dateTo) params.set('date_to', dateTo);

                window.location.href = '/leads/export?' + params.toString();
            });

            // ===== ADD LEAD =====
            $('#addLeadBtn').on('click', function() {
                clearFormErrors();
                $('#leadModalTitle').text('Add Lead');
                $('#leadForm')[0].reset();
                $('#lead_id').val('');
                $('#status_id').val('{{ $statuses->first()->id ?? '' }}');
                leadModal.show();
            });

            // ===== EDIT LEAD =====
            $(document).on('click', '.editLead', function() {
                let id = $(this).data('id');
                let row = leadsTable.rows().data().toArray().find(r => r.id == id);

                if (!row) return;

                clearFormErrors();
                $('#leadModalTitle').text('Edit Lead');
                $('#lead_id').val(row.id);
                $('#name').val(row.name);
                $('#email').val(row.email);
                $('#phone').val(row.phone);
                $('#designation').val(row.designation);
                $('#report_id').val(row.report_id);
                $('#category_id').val(row.category_id);
                $('#status_id').val(row.status_id);
                $('#message').val(row.message);

                leadModal.show();
            });

            // ===== SAVE (ADD OR UPDATE) =====
            $('#leadForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#lead_id').val();
                let url = id ? `/leads/update/${id}` : '/leads/store';

                showLoader();
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        hideLoader();
                        leadModal.hide();
                        showToast(res.message || 'Saved successfully', 'success');
                        leadsTable.ajax.reload(null, false);
                    },
                    error: function(err) {
                        hideLoader();
                        if (err.status === 422) {
                            let errors = err.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.error-' + key).text(value[0]);
                                $('#' + key).addClass('is-invalid');
                            });
                        } else {
                            showToast('Something went wrong', 'danger');
                        }
                    }
                });
            });

            // ===== DELETE (soft) =====
            $(document).on('click', '.deleteLead', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Delete this lead?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoader();
                    $.ajax({
                        url: '/leads/delete',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            hideLoader();
                            showToast(res.message || 'Lead deleted', 'success');
                            leadsTable.ajax.reload(null, false);
                        },
                        error: function() {
                            hideLoader();
                            showToast('Something went wrong', 'danger');
                        }
                    });
                });
            });
        });
    </script>
@endsection
