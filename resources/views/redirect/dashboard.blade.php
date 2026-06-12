@extends('layout.main')
@section('content')
    <section>
        <h2>Redirects</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>All Redirects</h5>
                <button class="btn-custom btn-primary-gradient" id="addRedirectBtn">
                    Add Redirect
                </button>
            </div>

            <table id="redirectTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Redirect From</th>
                        <th>Redirect To</th>
                        <th>Created By</th>
                        <th>Updated By</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>

        {{-- Add / Edit Modal --}}
        <div class="modal fade" id="redirectModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modalTitle">Add Redirect</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient"
                            data-mdb-dismiss="modal">X</button>
                    </div>

                    <form id="redirectForm">
                        @csrf
                        <input type="hidden" id="redirect_id">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Redirect From</label>
                                <input type="text" id="redirect_from" name="redirect_from"
                                    class="form-control" placeholder="/old-url">
                                <small class="text-danger error-redirect_from"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Redirect To</label>
                                <input type="text" id="redirect_to" name="redirect_to"
                                    class="form-control" placeholder="/new-url">
                                <small class="text-danger error-redirect_to"></small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-custom btn-secondary-gradient">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        let table;
        let modal;

        $(document).ready(function () {

            function resetForm() {
                $('#redirectForm')[0].reset();
                $('#redirect_id').val('');
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');
                $('#redirectForm').attr('action', '/redirect/store');
            }

            $('#redirectModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            table = $('#redirectTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/redirect/list',
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5 },
                    { data: 6 },
                    { data: 7, orderable: false, searchable: false },
                ]
            });

            modal = createModal('redirectModal');

            // ADD
            $('#addRedirectBtn').click(function () {
                $('#modalTitle').text('Add Redirect');
                resetForm();
                modal.show();
            });

            // EDIT
            $(document).on('click', '.editRedirect', function () {
                showLoader();

                setTimeout(() => {
                    hideLoader();

                    $('#modalTitle').text('Edit Redirect');
                    $('#redirect_from').val($(this).data('from'));
                    $('#redirect_to').val($(this).data('to'));
                    $('#redirect_id').val($(this).data('id'));
                    $('#redirectForm').attr('action', '/redirect/update/' + $(this).data('id'));

                    modal.show();
                }, 200);
            });

            // SUBMIT
            $('#redirectForm').submit(function (e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');

                $('.text-danger').text('');
                showLoader();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),

                    success: function () {
                        hideLoader();
                        modal.hide();
                        table.ajax.reload();

                        let msg = url.includes('store')
                            ? 'Redirect added successfully'
                            : 'Redirect updated successfully';

                        showToast(msg, 'success');
                    },

                    error: function (err) {
                        hideLoader();

                        if (err.status === 422) {
                            let errors = err.responseJSON.errors;
                            $.each(errors, function (key, val) {
                                $('.error-' + key).text(val[0]);
                            });
                        } else {
                            showToast('Something went wrong!', 'error');
                        }
                    }
                });
            });

            // DELETE
            $(document).on('click', '.deleteRedirect', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Delete this redirect?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoader();
                        $.post('/redirect/delete', {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id
                        }, function (res) {
                            hideLoader();
                            if (res.status) {
                                table.ajax.reload();
                                showToast('Redirect deleted successfully', 'success');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
