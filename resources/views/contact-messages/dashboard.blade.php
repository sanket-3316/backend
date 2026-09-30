@extends('layout.main')
@section('content')
    <section>
        <h2>Contact Us Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Contact Us Submissions</h5>
            </div>

            <div class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-1" for="filterActiveStatus">Show</label>
                    <select id="filterActiveStatus" class="form-control form-control-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive (Deleted)</option>
                    </select>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <button type="button" id="bulkDeleteBtn" class="btn-custom btn-warning-gradient btn-sm">
                        Delete Selected
                    </button>
                    <button type="button" id="bulkRestoreBtn" class="btn-custom btn-secondary-gradient btn-sm" style="display:none;">
                        Restore Selected
                    </button>
                </div>
            </div>

            <table id="contactTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllContacts"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>

        <!-- VIEW MODAL -->
        <div class="modal fade" id="viewContactModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Contact Message Details</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient" data-mdb-dismiss="modal"> X
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless">
                            <tr><th style="width:180px;">Name</th><td id="view_name"></td></tr>
                            <tr><th>Email</th><td id="view_email"></td></tr>
                            <tr><th>Phone</th><td id="view_phone"></td></tr>
                            <tr><th>Job Title</th><td id="view_job_title"></td></tr>
                            <tr><th>Company</th><td id="view_company"></td></tr>
                            <tr><th>Submitted At</th><td id="view_created_at"></td></tr>
                            <tr><th>Message</th><td id="view_message" style="white-space: pre-wrap;"></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        let viewContactModal;
        let contactTable;

        $(document).ready(function() {

            viewContactModal = createModal('viewContactModal');

            contactTable = $('#contactTable').DataTable({
                ajax: {
                    url: '/contact-messages/list',
                    data: function(d) {
                        d.status = $('#filterActiveStatus').val();
                    }
                },
                order: [
                    [6, 'desc']
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
                        data: 'company',
                        render: function(data) {
                            return data || '—';
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: function(data) {
                            let isInactive = $('#filterActiveStatus').val() === 'inactive';
                            let btns = `<button class="btn-custom btn-secondary-gradient viewContact" data-id="${data}">View</button> `;

                            if (isInactive) {
                                btns += `<button class="btn-custom btn-primary-gradient restoreContact" data-id="${data}">Restore</button>`;
                            } else {
                                btns += `<button class="btn-custom btn-warning-gradient deleteContact" data-id="${data}">Delete</button>`;
                            }

                            return btns;
                        }
                    }
                ]
            });

            $('#filterActiveStatus').on('change', function() {
                let isInactive = $(this).val() === 'inactive';
                $('#bulkDeleteBtn').toggle(!isInactive);
                $('#bulkRestoreBtn').toggle(isInactive);
                contactTable.ajax.reload();
            });

            $(document).on('change', '#selectAllContacts', function() {
                $('.rowCheckbox').prop('checked', $(this).is(':checked'));
            });

            function getSelectedContactIds() {
                return $('.rowCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            // ===== VIEW =====
            $(document).on('click', '.viewContact', function() {
                let id = $(this).data('id');

                $.get('/contact-messages/show/' + id, function(res) {
                    let d = res.data;
                    $('#view_name').text(d.name || '—');
                    $('#view_email').text(d.email || '—');
                    $('#view_phone').text(d.phone || '—');
                    $('#view_job_title').text(d.job_title || '—');
                    $('#view_company').text(d.company || '—');
                    $('#view_created_at').text(d.created_at || '—');
                    $('#view_message').text(d.message || '—');
                    viewContactModal.show();
                }).fail(function() {
                    showToast('Could not load message details', 'danger');
                });
            });

            // ===== SINGLE DELETE (soft) =====
            $(document).on('click', '.deleteContact', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Delete this message?',
                    text: 'This can be restored from the Inactive filter.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/contact-messages/delete',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            showToast(res.message || 'Message deleted', 'success');
                            contactTable.ajax.reload();
                        },
                        error: function() {
                            showToast('Something went wrong', 'danger');
                        }
                    });
                });
            });

            $(document).on('click', '.restoreContact', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: '/contact-messages/restore',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function(res) {
                        showToast(res.message || 'Message restored', 'success');
                        contactTable.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            // ===== BULK DELETE (soft) =====
            $('#bulkDeleteBtn').on('click', function() {
                let ids = getSelectedContactIds();

                if (!ids.length) {
                    showToast('Select at least one message first', 'danger');
                    return;
                }

                if (!confirm(`Delete ${ids.length} selected message(s)? They can be restored from the Inactive filter.`)) {
                    return;
                }

                $.ajax({
                    url: '/contact-messages/bulk-delete',
                    method: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function(res) {
                        showToast(`${res.deleted} message(s) deleted`, 'success');
                        contactTable.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            // ===== BULK RESTORE =====
            $('#bulkRestoreBtn').on('click', function() {
                let ids = getSelectedContactIds();

                if (!ids.length) {
                    showToast('Select at least one message first', 'danger');
                    return;
                }

                $.ajax({
                    url: '/contact-messages/bulk-restore',
                    method: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function(res) {
                        showToast(`${res.restored} message(s) restored`, 'success');
                        contactTable.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });
        });
    </script>
@endsection
