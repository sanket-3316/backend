@extends('layout.main')
@section('content')
    <section>
        <h2>Recycle Bin — Reports</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Deleted Reports</h5>
                <div class="d-flex gap-2">
                    <a href="{{ url('/report') }}" class="btn-outline-custom btn-outline-primary-gradient">
                        Back to Reports
                    </a>
                    <button type="button" id="restoreSelectedBtn" class="btn-custom btn-secondary-gradient">
                        Restore Selected
                    </button>
                    <button type="button" id="permanentDeleteSelectedBtn" class="btn-custom btn-warning-gradient">
                        Delete Permanently
                    </button>
                </div>
            </div>

            <p class="text-muted small">
                Reports here are hidden from the site and the main dashboard, but are not gone yet.
                Restore a report to bring it back, or delete it permanently — that cannot be undone.
            </p>

            <table id="recycleBinTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllDeleted"></th>
                        <th>#</th>
                        <th>Report Title</th>
                        <th>Category</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>
    </section>

    <script>
        let recycleBinTable;

        $(document).ready(function() {

            recycleBinTable = $('#recycleBinTable').DataTable({
                ajax: '/report/recycle-bin/list',
                order: [
                    [4, 'desc']
                ],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="rowCheckbox" value="${row.report_id}">`;
                        }
                    },
                    {
                        data: 'report_id',
                        orderable: false
                    },
                    {
                        data: 'report_title'
                    },
                    {
                        data: 'category_name',
                        render: function(data) {
                            return data || '—';
                        }
                    },
                    {
                        data: 'deleted_date',
                        render: function(data) {
                            if (!data) return '—';
                            let d = new Date(data.replace(' ', 'T'));
                            return isNaN(d) ? data : d.toLocaleString();
                        }
                    },
                    {
                        data: 'report_id',
                        orderable: false,
                        render: function(data) {
                            return `
                <button class="btn-custom btn-primary-gradient restoreReport" data-id="${data}">Restore</button>
                <button class="btn-custom btn-warning-gradient permanentDeleteReport" data-id="${data}">Delete Permanently</button>
                `;
                        }
                    }
                ]
            });

            $(document).on('change', '#selectAllDeleted', function() {
                $('.rowCheckbox').prop('checked', $(this).is(':checked'));
            });

            function getSelectedIds() {
                return $('.rowCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            // ===== SINGLE RESTORE =====
            $(document).on('click', '.restoreReport', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: '/report/restore/' + id,
                    method: 'POST',
                    success: function(res) {
                        showToast(res.message || 'Report restored', 'success');
                        recycleBinTable.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            // ===== SINGLE PERMANENT DELETE =====
            $(document).on('click', '.permanentDeleteReport', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Permanently delete this report?',
                    text: 'This cannot be undone — the report and every language translation of it will be removed for good.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete permanently'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/report/permanent-delete/' + id,
                        method: 'DELETE',
                        success: function(res) {
                            showToast(res.message || 'Report permanently deleted', 'success');
                            recycleBinTable.ajax.reload();
                        },
                        error: function() {
                            showToast('Something went wrong', 'danger');
                        }
                    });
                });
            });

            // ===== BULK RESTORE =====
            $('#restoreSelectedBtn').on('click', function() {
                let ids = getSelectedIds();

                if (!ids.length) {
                    showToast('Select at least one report first', 'danger');
                    return;
                }

                $.ajax({
                    url: '/report/bulk-restore',
                    method: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function(res) {
                        showToast(`${res.restored} report(s) restored`, 'success');
                        recycleBinTable.ajax.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            // ===== BULK PERMANENT DELETE =====
            $('#permanentDeleteSelectedBtn').on('click', function() {
                let ids = getSelectedIds();

                if (!ids.length) {
                    showToast('Select at least one report first', 'danger');
                    return;
                }

                Swal.fire({
                    title: `Permanently delete ${ids.length} report(s)?`,
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete permanently'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/report/bulk-permanent-delete',
                        method: 'POST',
                        data: {
                            ids: ids
                        },
                        success: function(res) {
                            showToast(`${res.deleted} report(s) permanently deleted`, 'success');
                            recycleBinTable.ajax.reload();
                        },
                        error: function() {
                            showToast('Something went wrong', 'danger');
                        }
                    });
                });
            });
        });
    </script>
@endsection
