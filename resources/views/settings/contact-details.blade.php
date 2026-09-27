@extends('layout.main')
@section('content')
    <section>
        <h2>Contact Details</h2>

        <div class="card p-3" style="max-width: 600px;">
            <h5 class="mb-3">Website Contact Information</h5>
            <p class="text-muted small">
                Shown on the public website's Contact page. Changes here are picked up automatically —
                the website always fetches the current values.
            </p>

            <form id="contactForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">USA Headquarters Phone</label>
                    <input type="text" name="phone_usa" id="phone_usa" class="form-control" value="{{ $details['phone_usa'] }}" placeholder="+1-302-846-2799">
                    <small class="text-danger error-phone_usa"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">EMEA Office Phone</label>
                    <input type="text" name="phone_emea" id="phone_emea" class="form-control" value="{{ $details['phone_emea'] }}" placeholder="+49-176-7450-2496">
                    <small class="text-danger error-phone_emea"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sales Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $details['email'] }}" placeholder="sales@bremontstrategy.com">
                    <small class="text-danger error-email"></small>
                </div>
                <button type="submit" class="btn-custom btn-primary-gradient">Save Contact Details</button>
            </form>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                showLoader();

                $.ajax({
                    url: '/settings/contact-details/update',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        hideLoader();
                        showToast(res.message || 'Saved successfully', 'success');
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
