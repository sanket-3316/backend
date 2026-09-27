@extends('layout.main')
@section('content')
    <section>
        <h2>My Profile</h2>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card p-3">
                    <h5 class="mb-3">Account Details</h5>

                    <form id="profileForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="p_name" class="form-control" value="{{ $user->name }}">
                            <small class="text-danger error-name"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="p_email" class="form-control" value="{{ $user->email }}">
                            <small class="text-danger error-email"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $user->role_name ?? '—' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}" disabled>
                        </div>
                        <button type="submit" class="btn-custom btn-primary-gradient">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3">
                    <h5 class="mb-3">Change Password</h5>

                    <form id="passwordForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password" class="form-control">
                                <button type="button" class="btn btn-outline-secondary togglePassword" data-target="current_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-danger error-current_password"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control">
                                <button type="button" class="btn btn-outline-secondary togglePassword" data-target="new_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-danger error-new_password"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control">
                                <button type="button" class="btn btn-outline-secondary togglePassword" data-target="new_password_confirmation">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-custom btn-secondary-gradient">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {

            $(document).on('click', '.togglePassword', function() {
                let target = $('#' + $(this).data('target'));
                let icon = $(this).find('i');

                if (target.attr('type') === 'password') {
                    target.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    target.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                showLoader();

                $.ajax({
                    url: '/profile/update',
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
                                $('#p_' + key).addClass('is-invalid');
                            });
                        } else {
                            showToast('Something went wrong', 'danger');
                        }
                    }
                });
            });

            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();
                showLoader();

                $.ajax({
                    url: '/profile/change-password',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        hideLoader();
                        $('#passwordForm')[0].reset();
                        showToast(res.message || 'Password changed successfully', 'success');
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
