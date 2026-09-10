@extends('layout.main')
@section('content')
    <section>
        <h2>Report Price</h2>

        <div class="card p-3" style="max-width: 540px;">

            <h5 class="mb-2">Bulk update report pricing</h5>
            <p class="text-muted mb-4">
                Pick one price type, enter a value, and apply it to
                <strong>all {{ $reportCount }} report(s)</strong> in one go.
            </p>

            <form id="reportPriceForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="column">Price Type</label>
                    <select name="column" id="column" class="form-control">
                        @foreach ($columns as $col)
                            <option value="{{ $col }}">{{ ucfirst($col) }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger error-column"></small>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="price">Price</label>
                    <input type="number" name="price" id="price" class="form-control"
                        min="0" step="1" placeholder="e.g. 2999">
                    <small class="text-danger error-price"></small>
                </div>

                <button type="submit" class="btn-custom btn-primary-gradient">
                    Apply to All Reports
                </button>
            </form>
        </div>
    </section>

    <script>
        $(document).ready(function () {

            $('#reportPriceForm').on('submit', function (e) {
                e.preventDefault();

                $('.text-danger').text('');

                let column = $('#column').val();
                let price = $('#price').val();

                if (price === '' || Number(price) < 0 || !Number.isInteger(Number(price))) {
                    $('.error-price').text('Enter a valid whole number');
                    return;
                }

                Swal.fire({
                    title: 'Apply to all reports?',
                    text: 'This will set the "' + column + '" price to ' + price + ' for every report.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, apply it'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    showLoader();

                    $.ajax({
                        url: '/report-price/apply',
                        type: 'POST',
                        data: $('#reportPriceForm').serialize(),
                        success: function (res) {
                            hideLoader();
                            showToast(res.message || 'Prices updated', 'success');
                            $('#price').val('');
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
            });
        });
    </script>
@endsection
