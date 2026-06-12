@extends('layout.main')
@section('content')
    <section>
        <h2>Category Dashboard</h2>

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Categorys</h5>
                <button class=" btn-custom btn-primary-gradient" id="addCategoryBtn">Add Category</button>
            </div>

            <table id="categoryTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name (EN)</th>
                        <th>Slug</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="modal fade" id="categoryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h5 id="categoryModalTitle">Add Category</h5>
                        <button type="button" class="btn-close btn-custom btn-danger-gradient"
                            data-mdb-dismiss="modal">X</button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">
                        <form id="categoryForm">
                            @csrf

                            <input type="hidden" id="category_id">

                            <!-- PARENT -->
                            <!-- LANGUAGE -->
                            <div class="mb-3">
                                <label>Select Language</label>
                                <select name="language_id" id="language_id" class="form-control">
                                    @foreach ($languages as $lang)
                                        <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 d-none" id="categorySelectWrapper">
                                <label>Select Category (English)</label>
                                <select id="base_category_id" class="form-control">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <!-- NAME -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Category Name" required>
                                <label class="form-label" for="name">Category Name</label>
                            </div>
                            <div class="form-outline mb-4" id="slugWrapper" data-mdb-input-init>
                                <input type="text" name="slug" id="slug" class="form-control"
                                    placeholder="Category slug">
                                <label class="form-label" for="slug">Slug</label>
                            </div>
                            <!-- META TITLE -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <input type="text" name="meta_title" id="meta_title" class="form-control"
                                    placeholder="Meta Title" required>
                                <label class="form-label" for="meta_title">Meta Title</label>
                            </div>

                            <!-- META DESCRIPTION -->
                            <div class="form-outline mb-4" data-mdb-input-init>
                                <textarea name="meta_description" id="meta_description" class="form-control" placeholder="Meta Description" required></textarea>
                                <label class="form-label" for="meta_description">Meta Description</label>
                            </div>

                            <!-- DESCRIPTION -->
                            <div class="mb-3">
                                <textarea name="description" id="description" class="form-control editor"></textarea>
                            </div>

                            <!-- FOOTER -->
                            <div class="modal-footer">
                                <button type="submit" class="btn-custom btn-secondary-gradient">
                                    Save
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            function generateSlug(text) {
                return text.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }

            let slugEdited = false;
            let isEditMode = false;
            let isEnglish = true;
            // detect manual slug edit
            $('#slug').on('input', function() {
                slugEdited = true;
            });

            // auto slug ONLY in ADD mode
            $('#name').on('keyup', function() {

                if (!isEditMode && isEnglish && !slugEdited) {
                    $('#slug').val(generateSlug($(this).val()));
                }
            });

            // ENTER → focus slug (ONLY ADD MODE)
            $('#name').on('keydown', function(e) {
                if (e.key === 'Enter' && !isEditMode && isEnglish) {
                    e.preventDefault();
                    $('#slug').focus();
                }
            });

            let table = $('#categoryTable').DataTable({
                processing: true,
                serverSide: false, // important
                ajax: '/category/list',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'slug'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            $(document).on('click', '.expandLang', function() {

                let btn = $(this);
                let id = btn.data('id');
                let row = btn.closest('tr');

                // 🔁 toggle close
                if (btn.hasClass('opened')) {
                    row.next('.child-row').remove();
                    btn.removeClass('opened')
                        .html('<i class="fa fa-plus"></i>');
                    return;
                }

                $.get('/category/all-translations/' + id, function(res) {

                    let html = `
                            <tr class="child-row">
                                <td colspan="4">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Language</th>
                                                <th>Name</th>
                                                <th width="120">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;

                                    if (res.length === 0) {
                                        html += `
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        No translations found
                                    </td>
                                </tr>
                            `;
                                    }

                                    res.forEach(item => {
                                        html += `
                                <tr>
                                    <td>${item.language.name}</td>
                                    <td>${item.name}</td>
                                    <td>
                                        <div class="d-flex gap-2">

                                            <button class="btn-outline-custom btn-outline-primary-gradient btn-floating-sm editTranslation"
                                                data-id="${item.category_id}"
                                                data-lang="${item.language_id}"
                                                title="Edit">
                                                <i class="fa fa-pen"></i>
                                            </button>

                                            <button class="btn-outline-custom btn-outline-warning-gradient btn-floating-sm deleteTranslation"
                                                data-id="${item.id}"
                                                title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            `;
                                    });

                                    html += `
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        `;

                    row.after(html);

                    // 🔥 change icon to minus
                    btn.addClass('opened')
                        .html('<i class="fa fa-minus"></i>');
                });
            });

            $("#addCategoryBtn").click(function() {

                isEditMode = false; // ✅ ADD MODE
                isEnglish = true;
                slugEdited = false;

                $("#categoryModalTitle").text("Add Category");
                $("#categoryForm")[0].reset();

                $('#slugWrapper').show();
                $('#language_id').closest('.mb-3').show();
                $('#categorySelectWrapper').addClass('d-none');

                $("#category_id").val('');
                tinymce.get('description')?.setContent('');

                $("#categoryModal").modal('show');
            });

            $(document).on('click', '.editCategory', function() {

                isEditMode = true; // ✅ EDIT MODE
                isEnglish = true;
                slugEdited = true; // ❌ disable auto slug

                let id = $(this).data('id');

                $('#language_id').closest('.mb-3').hide();
                $('#slugWrapper').show();

                showLoader();

                $.get('/category/translations/' + id, function(res) {

                    let item = res[0];

                    $("#category_id").val(id);
                    $("#name").val(item.name);
                    $("#slug").val(item.category?.slug ?? '');

                    $("#meta_title").val(item.meta_title);
                    $("#meta_description").val(item.meta_description);
                    $("#language_id").val(item.language_id);

                    tinymce.remove();
                    initEditor();
                    tinymce.get('description')?.setContent(item.description);

                    $("#categoryModal").modal('show');
                    hideLoader();
                });
            });

            $("#categoryForm").submit(function(e) {

                e.preventDefault();
                tinymce.triggerSave();

                let id = $("#category_id").val();
                let url = id ? '/category/update/' + id : '/category/store';
                let formData = new FormData(this);
                formData.append('base_category_id', $('#base_category_id').val());

                showLoader();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(res) {

                        hideLoader();

                        if (res.status) {
                            $("#categoryModal").modal('hide');
                            $('#categoryTable').DataTable().ajax.reload();
                            showToast(res.message, 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    },

                    error: function(xhr) {
                        hideLoader();
                        // ✅ Validation error (Laravel)
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(e => e[0]).join('\n');
                        }

                        // ✅ Server error
                        else if (xhr.status === 500) {
                            message = xhr.responseJSON?.message || 'Server error';
                        }

                        // ✅ Unauthorized / forbidden
                        else if (xhr.status === 401 || xhr.status === 403) {
                            message = 'Unauthorized access';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            });

            $(document).on('click', '.deleteCategory', function() {

                let id = $(this).data('id');
                Swal.fire({
                    title: "Delete this category?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e3342f",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {

                    if (result.isConfirmed) {

                        showLoader();
                        $.post('/category/delete', {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id
                        }, function(res) {
                            hideLoader();
                            if (res.status) {
                                table.ajax.reload();
                            }
                        });
                        hideLoader();
                    }
                });
            });

            $('#categoryModal').on('shown.bs.modal', function() {
                initEditor();
            });

            $('#language_id').on('change', function() {

                let langId = $(this).val();
                let defaultLang = 1;

                isEnglish = (langId == defaultLang);
                slugEdited = false;

                if (isEnglish) {
                    $('#slugWrapper').show();
                    $('#categorySelectWrapper').addClass('d-none');
                } else {
                    $('#slugWrapper').hide();
                    $('#slug').val('');
                    $('#categorySelectWrapper').removeClass('d-none');

                    $.get('/category/english-list', function(res) {

                        let html = '<option value="">Select Category</option>';

                        res.forEach(cat => {
                            html += `<option value="${cat.id}">${cat.name}</option>`;
                        });

                        $('#base_category_id').html(html);
                    });
                }
            });

            $(document).on('click', '.editTranslation', function() {

                isEditMode = true;
                isEnglish = false; // ❌ NOT ENGLISH
                slugEdited = true;

                $('#slugWrapper').hide();

                let categoryId = $(this).data('id');
                let langId = $(this).data('lang');

                showLoader();

                $.get('/category/translations/' + categoryId, function(res) {

                    let item = res.find(r => parseInt(r.language_id) === parseInt(langId));

                    $("#category_id").val(categoryId);
                    $("#name").val(item.name);
                    $("#meta_title").val(item.meta_title);
                    $("#meta_description").val(item.meta_description);

                    $('#language_id').val(item.language_id);
                    $('#language_id').closest('.mb-3').hide();

                    tinymce.remove();
                    initEditor();
                    tinymce.get('description')?.setContent(item.description);

                    $("#categoryModal").modal('show');
                    hideLoader();
                });
            });

            $(document).on('click', '.deleteTranslation', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: "Delete this translation?",
                    icon: "warning",
                    showCancelButton: true
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.post('/category/delete-translation', {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: id
                        }, function(res) {
                            if (res.status) {
                                location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
