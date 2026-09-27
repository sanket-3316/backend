@extends('layout.main')
@section('content')
    <section>
        <h2>Login Logs</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">All Login Details</h5>

                <div class="d-flex align-items-center gap-2">
                    <select id="filterUser" class="form-control form-select" style="width: 220px;">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn-custom btn-secondary-gradient" id="clearLogFilterBtn">Clear</button>
                    <button class="btn-custom btn-warning-gradient" id="bulkDeleteLogsBtn">Delete Selected</button>
                </div>
            </div>

            <table id="logsTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllLogs"></th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Login At</th>
                        <th>Logout At</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
            </table>

        </div>
    </section>

    <script>
        $(document).ready(function() {

            let logsTable = $('#logsTable').DataTable({
                ajax: {
                    url: '/logs/list',
                    data: function(d) {
                        d.user_id = $('#filterUser').val();
                    }
                },
                order: [
                    [3, 'desc']
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
                        data: 'user_name'
                    },
                    {
                        data: 'user_email'
                    },
                    {
                        data: 'login_at',
                        render: function(data) {
                            if (!data) return '—';
                            let d = new Date(data.replace(' ', 'T'));
                            return isNaN(d) ? data : d.toLocaleString();
                        }
                    },
                    {
                        data: 'logout_at',
                        render: function(data) {
                            if (!data) return '<span class="badge bg-success">Active</span>';
                            let d = new Date(data.replace(' ', 'T'));
                            return isNaN(d) ? data : d.toLocaleString();
                        }
                    },
                    {
                        data: 'ip_address',
                        render: function(data) {
                            return data || '—';
                        }
                    }
                ]
            });

            $('#filterUser').on('change', function() {
                logsTable.ajax.reload();
            });

            $('#clearLogFilterBtn').on('click', function() {
                $('#filterUser').val('');
                logsTable.ajax.reload();
            });

            $(document).on('change', '#selectAllLogs', function() {
                $('.rowCheckbox').prop('checked', $(this).is(':checked'));
            });

            // Hard delete — login logs are an audit trail, not soft-deletable.
            $('#bulkDeleteLogsBtn').on('click', function() {
                let ids = $('.rowCheckbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!ids.length) {
                    showToast('Select at least one log entry first', 'danger');
                    return;
                }

                Swal.fire({
                    title: `Permanently delete ${ids.length} log entr${ids.length === 1 ? 'y' : 'ies'}?`,
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete permanently'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/logs/bulk-delete',
                        method: 'POST',
                        data: {
                            ids: ids
                        },
                        success: function(res) {
                            showToast(`${res.deleted} log entr${res.deleted === 1 ? 'y' : 'ies'} deleted`, 'success');
                            logsTable.ajax.reload();
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
