@extends('layout.main')
@section('content')
    <section>
        <h2>API Key</h2>

        <div class="card p-3" style="max-width: 600px;">
            <h5 class="mb-3">OpenAI API Key</h5>
            <p class="text-muted small">
                Used by the report-generation cron (<code>report:generate</code>) and the dashboard's
                "Generate" button to call GPT. Updating it here takes effect immediately — no server
                restart or deployment needed.
            </p>

            @if ($masked)
                <div class="alert alert-info py-2">
                    Current key: <code>{{ $masked }}</code>
                </div>
            @else
                <div class="alert alert-warning py-2">
                    No API key is set yet — report generation will fail until one is added.
                </div>
            @endif

            <form id="apiKeyForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">New API Key</label>
                    <div class="input-group">
                        <input type="password" name="openai_api_key" id="openai_api_key" class="form-control" placeholder="sk-...">
                        <button type="button" class="btn btn-outline-secondary" id="toggleApiKey">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-danger error-openai_api_key"></small>
                </div>
                <button type="submit" class="btn-custom btn-primary-gradient">Save API Key</button>
            </form>
        </div>
    </section>

    <script>
        $(document).ready(function() {

            $('#toggleApiKey').on('click', function() {
                let input = $('#openai_api_key');
                let icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#apiKeyForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                showLoader();

                $.ajax({
                    url: '/settings/api-key/update',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        hideLoader();
                        showToast(res.message || 'Saved successfully', 'success');
                        setTimeout(() => location.reload(), 900);
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
        });
    </script>
@endsection
