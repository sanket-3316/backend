@extends('layout.main')
@section('content')
    <section>
        <h2>Prompts Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>GPT Prompts</h5>
                <button class="btn-custom btn-primary-gradient" id="addPromptBtn">Add Prompt</button>
            </div>

            <p class="text-muted small">
                The report-generation cron always picks the active prompt where <code>type = report</code>.
                <code>blog</code> / <code>press_release</code> are reserved for future crons that will reuse this
                same table.
            </p>

            <table id="promptsTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Temp</th>
                        <th>Max Tokens</th>
                        <th>Format</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prompts as $prompt)
                        <tr>
                            <td>{{ $prompt->name }}</td>
                            <td><span class="badge bg-secondary">{{ $prompt->type }}</span></td>
                            <td>{{ $prompt->temperature }}</td>
                            <td>{{ $prompt->max_tokens }}</td>
                            <td>{{ $prompt->response_format }}</td>
                            <td>
                                <span class="badge {{ $prompt->is_active ? 'bg-success' : 'bg-secondary' }} toggleStatusBadge"
                                    data-id="{{ $prompt->id }}" style="cursor:pointer;">
                                    {{ $prompt->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-custom btn-primary-gradient editPromptBtn"
                                    data-id="{{ $prompt->id }}"
                                    data-name="{{ $prompt->name }}"
                                    data-type="{{ $prompt->type }}"
                                    data-system_prompt="{{ $prompt->system_prompt }}"
                                    data-user_prompt="{{ $prompt->user_prompt }}"
                                    data-temperature="{{ $prompt->temperature }}"
                                    data-max_tokens="{{ $prompt->max_tokens }}"
                                    data-response_format="{{ $prompt->response_format }}"
                                    data-is_active="{{ $prompt->is_active }}">
                                    Edit
                                </button>
                                <button class="btn-custom btn-warning-gradient deletePromptBtn" data-id="{{ $prompt->id }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <!-- ADD / EDIT MODAL -->
        <div class="modal fade" id="promptModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="promptModalTitle">Add Prompt</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient" data-mdb-dismiss="modal"> X
                        </button>
                    </div>
                    <form id="promptForm">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" id="prompt_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Name, e.g. Default Report Prompt">
                                    <small class="text-danger error-name"></small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <select name="type" id="type" class="form-control">
                                        <option value="report">report</option>
                                        <option value="blog">blog</option>
                                        <option value="press_release">press_release</option>
                                    </select>
                                    <small class="text-danger error-type"></small>
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Temperature</label>
                                    <input type="number" step="0.01" min="0" max="2" name="temperature" id="temperature" class="form-control" value="0.4">
                                    <small class="text-danger error-temperature"></small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Tokens</label>
                                    <input type="number" min="100" max="32000" name="max_tokens" id="max_tokens" class="form-control" value="14000">
                                    <small class="text-danger error-max_tokens"></small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Response Format</label>
                                    <select name="response_format" id="response_format" class="form-control">
                                        <option value="json_object">json_object</option>
                                        <option value="text">text</option>
                                    </select>
                                    <small class="text-danger error-response_format"></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">System Prompt</label>
                                <textarea name="system_prompt" id="system_prompt" class="form-control" rows="6"></textarea>
                                <small class="text-danger error-system_prompt"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">User Prompt</label>
                                <textarea name="user_prompt" id="user_prompt" class="form-control" rows="18" style="font-family: monospace; font-size: 13px;"></textarea>
                                <small class="text-danger error-user_prompt"></small>
                                <small class="text-muted d-block mt-1">
                                    Placeholders available: [[keyword]], [[base_year]], [[forecast_year]], [[historic_period]],
                                    [[forecast_period]], [[base_year_market_size]], [[forecast_year_market_size]], [[forecast_cagr]],
                                    [[segments]], [[companies]], [[category_instruction]], [[category_json_key]]
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient">Save Prompt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        let promptModal;

        $(document).ready(function() {

            promptModal = createModal('promptModal');

            $('#promptsTable').DataTable({
                order: [],
                columnDefs: [{
                    targets: -1,
                    orderable: false
                }]
            });

            $('#addPromptBtn').on('click', function() {
                clearFormErrors();
                $('#promptModalTitle').text('Add Prompt');
                $('#promptForm')[0].reset();
                $('#prompt_id').val('');
                $('#is_active').prop('checked', true);
                promptModal.show();
            });

            $(document).on('click', '.editPromptBtn', function() {
                clearFormErrors();
                $('#promptModalTitle').text('Edit Prompt');
                $('#prompt_id').val($(this).data('id'));
                $('#name').val($(this).data('name'));
                $('#type').val($(this).data('type'));
                $('#system_prompt').val($(this).data('system_prompt'));
                $('#user_prompt').val($(this).data('user_prompt'));
                $('#temperature').val($(this).data('temperature'));
                $('#max_tokens').val($(this).data('max_tokens'));
                $('#response_format').val($(this).data('response_format'));
                $('#is_active').prop('checked', $(this).data('is_active') == 1);
                promptModal.show();
            });

            $('#promptForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#prompt_id').val();
                let url = id ? `/prompts/update/${id}` : '/prompts/store';

                let formData = $(this).serializeArray();
                if (!$('#is_active').is(':checked')) {
                    formData.push({
                        name: 'is_active',
                        value: '0'
                    });
                }

                showLoader();
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $.param(formData),
                    success: function(res) {
                        hideLoader();
                        promptModal.hide();
                        showToast(res.message || 'Saved successfully', 'success');
                        setTimeout(() => location.reload(), 700);
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

            $(document).on('click', '.toggleStatusBadge', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: '/prompts/toggle-active/' + id,
                    method: 'POST',
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });

            $(document).on('click', '.deletePromptBtn', function() {
                let id = $(this).data('id');

                if (!confirm('Delete this prompt?')) return;

                $.ajax({
                    url: '/prompts/delete',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function() {
                        showToast('Prompt deleted', 'success');
                        location.reload();
                    },
                    error: function() {
                        showToast('Something went wrong', 'danger');
                    }
                });
            });
        });
    </script>
@endsection
