@extends('layout.main')
@section('content')
    <section>
        <h2>Reports Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Reports</h5>
                <button class=" btn-custom btn-primary-gradient" id="addReportBtn">Add Report</button>
            </div>

            <table id="reportTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Report Title</th>
                        <th>Category</th>
                        <th>Thumbnail</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="modal fade" id="reportModal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <form id="reportForm" enctype="multipart/form-data">
                        @csrf
                        <ul class="nav nav-tabs" id="reportTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-mdb-tab-init href="#report">Report</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-mdb-tab-init href="#segmentation">Segmentation</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-mdb-tab-init href="#description">Description</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-mdb-tab-init href="#price">Price</a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">

                            <!-- 🔹 TAB 1: REPORT -->
                            <div class="tab-pane fade show active" id="report">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" name="keyword" id="keyword" class="form-control">
                                            <label class="form-label" for="keyword">Keyword</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" name="slug" id="slug" class="form-control">
                                            <label class="form-label" for="slug">Slug</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4 ">
                                        <select id="category_id" name="category_id" class="form-control">
                                            <option value="">Select category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="number" name="base_year" value="{{ report_years()['base_year'] }}"
                                                class="form-control">
                                            <label class="form-label" for="base_year">Base year</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="number" name="forecast_year"
                                                value="{{ report_years()['forecast_start_year'] }}" class="form-control">
                                            <label class="form-label" for="forecast_year">Forecast year</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="number" name="historic_year"
                                                value="{{ report_years()['historic_start_year'] }}" class="form-control">
                                            <label class="form-label" for="historic_year">Historic year</label>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" name="base_year_market_size" class="form-control">
                                            <label class="form-label" for="base_year_market_size">Base year market
                                                size</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" name="forecast_year_market_size" class="form-control">
                                            <label class="form-label" for="forecast_year_market_size">Forecast year market
                                                size</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" name="forecast_cagr" class="form-control">
                                            <label class="form-label" for="forecast_cagr"> Forecast CAGR</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class=" col-md-3">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="number" name="pages"
                                                value="{{ report_random_stats()['pages'] }}" class="form-control">
                                            <label class="form-label" for="pages">Pages</label>
                                        </div>
                                    </div>
                                    <div class=" col-md-3">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="number" name="views"
                                                value="{{ report_random_stats()['views'] }}" class="form-control">
                                            <label class="form-label" for="views">Views</label>
                                        </div>
                                    </div>
                                    <div class=" col-md-3">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <input type="text" value="{{ report_random_stats()['rating'] }}"
                                                name="rating" class="form-control">
                                            <label class="form-label" for="rating">Rating</label>
                                        </div>
                                    </div>
                                    <div class=" col-md-3">
                                        <select id="author" name="author" class="form-control">
                                            <option value="">Select author</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label" for="author">Format</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input"name="format" type="checkbox"
                                                    id="excel" value="Excel" />
                                                <label class="form-check-label" for="excel">Excel</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" name="format" type="checkbox"
                                                    id="pdf" value="PDF" />
                                                <label class="form-check-label" for="pdf">PDF</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" name="format" type="checkbox"
                                                    id="ppt" value="PPT" />
                                                <label class="form-check-label" for="ppt">PPT</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="file" name="thumbnail" class="form-control mb-3">

                                    </div>
                                </div>


                            </div>

                            <!-- 🔹 TAB 2: segmentation -->
                            <div class="tab-pane fade" id="segmentation">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-outline mb-4" data-mdb-input-init>
                                            <textarea type="text" name="key_companys" class="form-control"> </textarea>
                                            <label class="form-label" for="key_companys">Key companys</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="segmentation">Segments</label>
                                    <div id="segmentContainer"></div>

                                    <div class="col-auto">
                                        <button type="button" id="addSegmentBtn" class="btn btn-success btn-sm mt-3">
                                            + Add Segment
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!-- 🔹 TAB 3: DESCRIPTION -->
                            <div class="tab-pane fade px-4" id="description">
                                <div class="row">
                                    <div class="form-outline mb-4" data-mdb-input-init>
                                        <input type="text" name="report_title" class="form-control">
                                        <label class="form-label" for="meta_title">Report title</label>
                                    </div>
                                    <div class="form-outline mb-4" data-mdb-input-init>
                                        <input type="text" name="meta_desc" class="form-control ">
                                        <label class="form-label" for="meta_desc">Meta description</label>
                                    </div>
                                    <div class="form-outline mb-4" data-mdb-input-init>
                                        <textarea type="text" name="h1_long_title" class="form-control"> </textarea>
                                        <label class="form-label" for="h1_long_title">H1 long title</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="description">Report description</label>
                                    <textarea name="description" id="description" class="editor"></textarea>
                                </div>
                            </div>

                            <!-- 🔹 TAB 5: PRICE -->
                            <div class="tab-pane fade" id="price">

                                <div class="row">
                                    <div class="col-md-3"><input type="number" name="single" class="form-control mb-3"
                                            placeholder="Single"></div>
                                    <div class="col-md-3"><input type="number" name="multiuser"
                                            class="form-control mb-3" placeholder="Multiuser"></div>
                                    <div class="col-md-3"><input type="number" name="corporate"
                                            class="form-control mb-3" placeholder="Corporate"></div>
                                    <div class="col-md-3"><input type="number" name="excel" class="form-control mb-3"
                                            placeholder="Excel"></div>
                                </div>

                            </div>

                        </div>

                        <div class="p-3 text-end">
                            <button type="submit" class="btn btn-primary">Save Report</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(document).ready(function() {
            $('#addReportBtn').click(function() {

                $('#reportForm')[0].reset();

                tinymce.get('description')?.setContent('');
                tinymce.get('segmentation')?.setContent('');
                tinymce.get('swot_analysis')?.setContent('');
                tinymce.get('primary_interview_insights')?.setContent('');

                $('#reportModal').modal('show');
                // create default one segment
                $('#addSegmentBtn').click();
                // 🔥 INIT MDB TABS
                setTimeout(() => {
                    document.querySelectorAll('[data-mdb-tab-init]').forEach((el) => {
                        new mdb.Tab(el);
                    });
                }, 300);
            });
            $('input[name="report_title"]').on('keyup', function() {
                let slug = $(this).val().toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');

                $('#slug').val(slug);
            });

            let segmentIndex = 0;

            // ADD NEW PARENT SEGMENT
            $('#addSegmentBtn').click(function() {

                let html = `
                <div class="card p-3 mb-3 segment-block" data-index="${segmentIndex}">
                    
                    <div class="d-flex justify-content-between mb-2">
                        <input type="text" class="form-control segment-name me-2" placeholder="Segment Name (e.g. Component)">
                        <button type="button" class="btn btn-danger btn-sm remove-segment">X</button>
                    </div>

                    <div class="subsegment-wrapper mb-2"></div>

                    <div class="d-flex">
                        <input type="text" class="form-control subsegment-input me-2" placeholder="Add Sub Segment">
                    </div>

                </div>
                `;

                $('#segmentContainer').append(html);
                segmentIndex++;
            });
        })
        $(document).on('keypress', '.subsegment-input', function(e) {

            if (e.which === 13) {
                e.preventDefault();

                let block = $(this).closest('.segment-block');
                let parent = block.find('.segment-name').val().trim();

                if (!parent) {
                    alert('Please enter segment name first');
                    return;
                }

                let value = $(this).val().trim();
                if (!value) return;

                let pill = `
                <span class="badge bg-primary me-2 mb-2 subsegment-pill">
                    ${value}
                    <span class="ms-2 remove-pill">×</span>
                </span>
                `;

                block.find('.subsegment-wrapper').append(pill);
                $(this).val('');

                // 🔥 auto create next segment if last
                if (block.is(':last-child')) {
                    $('#addSegmentBtn').click();
                }
            }
        });
        // remove parent
        $(document).on('click', '.remove-segment', function() {
            $(this).closest('.segment-block').remove();
        });

        // remove subsegment
        $(document).on('click', '.remove-pill', function() {
            $(this).parent().remove();
        });

        $(document).on('click', '#reportTabs .nav-link', function(e) {
            e.preventDefault();

            let target = $(this).attr('href');

            // remove active from all tabs
            $('#reportTabs .nav-link').removeClass('active');
            $(this).addClass('active');

            // hide all tab content
            $('.tab-pane').removeClass('show active');

            // show selected tab
            $(target).addClass('show active');
        });

        function getSegmentsJSON() {

            let data = {};

            $('.segment-block').each(function() {

                let parent = $(this).find('.segment-name').val().trim();

                if (!parent) return;

                let subs = [];

                $(this).find('.subsegment-pill').each(function() {
                    let text = $(this).clone().children().remove().end().text().trim();
                    subs.push(text);
                });

                data[parent] = subs;
            });

            return JSON.stringify(data);
        }

        $('#reportForm').on('submit', function(e) {
            e.preventDefault();

            // 🔥 Sync TinyMCE
            tinymce.triggerSave();

            // ===== SEGMENT JSON =====
            let segmentJSON = getSegmentsJSON();

            if ($('#segmentation_json').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'segmentation_json',
                    name: 'segmentation_json',
                    value: segmentJSON
                }).appendTo('#reportForm');
            } else {
                $('#segmentation_json').val(segmentJSON);
            }

            let valid = true;

            function markTab(index, status) {
                $('#reportTabs li:eq(' + index + ') a')
                    .toggleClass('text-danger', !status);
            }

            // ===== TAB 1 =====
            let tab1 = $('[name="slug"]').val() &&
                $('[name="base_year"]').val();

            markTab(0, tab1);
            if (!tab1) valid = false;

            // ===== TAB 2 (SEGMENTATION) =====
            let validSegments = true;
            let hasAtLeastOne = false;

            $('.segment-block').each(function() {
                let parent = $(this).find('.segment-name').val().trim();
                let subCount = $(this).find('.subsegment-pill').length;

                if (!parent && subCount === 0) return;

                hasAtLeastOne = true;

                if (!parent || subCount === 0) {
                    validSegments = false;
                }
            });

            if (!hasAtLeastOne) validSegments = false;

            markTab(1, validSegments);
            if (!validSegments) valid = false;

            // ===== TAB 3 (DESCRIPTION) =====
            let descEditor = tinymce.get('description');
            let tab3 = descEditor && descEditor.getContent().trim() !== '';

            markTab(2, tab3);
            if (!tab3) valid = false;

            // ===== TAB 4 (PRICE) =====
            let tab4 = $('[name="single"]').val() &&
                $('[name="multiuser"]').val() &&
                $('[name="corporate"]').val() &&
                $('[name="excel"]').val();

            markTab(3, tab4);
            if (!tab4) valid = false;

            if (!valid) {
                alert('Fill all required fields');
                return;
            }

            // ===== FORM DATA =====
            let formData = new FormData(this);

            // 🔥 FORMAT CHECKBOX FIX
            let formats = [];
            $('input[name="format"]:checked').each(function() {
                formats.push($(this).val());
            });
            formData.set('format', formats.join(',')); // overwrite if exists

            // ===== ADD OR UPDATE =====
            let id = $('#reportForm').attr('data-id');

            let url = id ?
                `/report/update/${id}` :
                `/report/store`;

            // ===== AJAX =====
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status) {
                        showToast(res.message || 'Saved Successfully', 'success');

                        // reset edit mode
                        $('#reportForm').removeAttr('data-id');

                        setTimeout(() => {
                            location.reload();
                        }, 800);
                    } else {
                        showToast(res.message || 'Something went wrong', 'danger');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    showToast('Server Error', 'danger');
                }
            });
        });

        function loadSegments(jsonData) {

            let data = JSON.parse(jsonData);

            $('#segmentContainer').html('');

            Object.keys(data).forEach(function(parent) {

                $('#addSegmentBtn').click();

                let block = $('.segment-block').last();

                block.find('.segment-name').val(parent);

                data[parent].forEach(function(sub) {
                    let pill = `
            <span class="badge bg-primary me-2 mb-2 subsegment-pill">
                ${sub}
                <span class="ms-2 remove-pill">×</span>
            </span>`;
                    block.find('.subsegment-wrapper').append(pill);
                });

            });
        }

        $(document).ready(function(e) {
            $('#reportTable').DataTable({
                ajax: '/report/list',
                columns: [{
                        data: 'report_id',
                        render: function(data, type, row) {

                            // show + only if multiple languages exist
                            if (row.lang_count > 1) {
                                return `<button class="btn btn-sm btn-primary showLang" data-id="${data}">+</button>`;
                            }

                            return '';
                        }
                    },
                    {
                        data: 'report_title'
                    },
                    {
                        data: 'category_name'
                    },

                    {
                        data: 'thumbnail',
                        render: function(data) {
                            return `<img src="/assets/reports/images/${data}" width="50">`;
                        }
                    },
                    {
                        data: 'report_id',
                        render: function(data) {
                            return `
                <button class=" btn-custom btn-primary-gradient editReport" data-id="${data}">Edit</button>
                <button class="btn-custom btn-warning-gradient deleteReport" data-id="${data}">Delete</button>
                `;
                        }
                    }
                ]
            });

            $(document).on('click', '.showLang', function() {

                let reportId = $(this).data('id');

                $.ajax({
                    url: '/report/languages/' + reportId,
                    method: 'GET',
                    success: function(res) {

                        let html = '';

                        res.forEach(lang => {
                            html += `
                <tr class="bg-light">
                    <td></td>
                    <td>${lang.report_title} (${lang.language_id})</td>
                    <td colspan="4">Language Version</td>
                </tr>
                `;
                        });

                        $('#reportTable tbody').append(html);
                    }
                });
            });

            $(document).on('click', '.editReport', function() {

                let id = $(this).data('id');

                // reset form
                $('#reportForm')[0].reset();
                $('#segmentContainer').html('');

                $.get('/report/edit/' + id, function(res) {

                    // ===== TAB 1 (REPORT) =====
                    $('[name="keyword"]').val(res.keyword);
                    $('[name="slug"]').val(res.report_url);
                    $('[name="category_id"]').val(res.category_id).trigger('change');

                    $('[name="base_year"]').val(res.base_year);
                    $('[name="forecast_year"]').val(res.forecast_year);
                    $('[name="historic_year"]').val(res.historic_year);

                    $('[name="base_year_market_size"]').val(res.base_year_market_size);
                    $('[name="forecast_year_market_size"]').val(res.forecast_year_market_size);
                    $('[name="forecast_cagr"]').val(res.forecast_cagr);

                    $('[name="pages"]').val(res.pages);
                    $('[name="views"]').val(res.views);
                    $('[name="rating"]').val(res.rating);

                    $('[name="author"]').val(res.author);

                    // ✅ format checkbox
                    $('input[name="format"]').prop('checked', false);
                    if (res.format) {
                        let formats = res.format.split(',');
                        formats.forEach(f => {
                            $(`input[name="format"][value="${f}"]`).prop('checked', true);
                        });
                    }

                    // ===== TAB 2 (SEGMENTATION) =====
                    $('[name="key_companys"]').val(res.key_companys);

                    if (res.segmentation) {
                        loadSegments(res.segmentation); // already defined in your code ✅
                    } else {
                        $('#addSegmentBtn').click();
                    }

                    // ===== TAB 3 (DESCRIPTION) =====
                    $('[name="report_title"]').val(res.report_title);
                    $('[name="meta_desc"]').val(res.meta_desc);
                    $('[name="h1_long_title"]').val(res.h1_long_title);

                    tinymce.get('description')?.setContent(res.description || '');

                    // ===== TAB 4 (PRICE) =====
                    $('[name="single"]').val(res.single);
                    $('[name="multiuser"]').val(res.multiuser);
                    $('[name="corporate"]').val(res.corporate);
                    $('[name="excel"]').val(res.excel);

                    // open modal
                    $('#reportModal').modal('show');

                    // set edit mode
                    $('#reportForm').attr('data-id', id);
                });
            });

            $(document).on('click', '.deleteReport', function() {

                let id = $(this).data('id');

                if (confirm('Delete report?')) {

                    $.ajax({
                        url: '/report/delete/' + id,
                        method: 'DELETE',
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        })
    </script>
@endsection
