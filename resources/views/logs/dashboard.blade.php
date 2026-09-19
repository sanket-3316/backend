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
                </div>
            </div>

            <table id="logsTable" class="table table-striped align-middle">
                <thead>
                    <tr>
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
                    [2, 'desc']
                ],
                columns: [{
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
        });
    </script>
@endsection
