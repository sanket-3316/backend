'use strict';
function initEditor(selector = '.editor') {

    // remove old instances
    tinymce.remove(selector);

    tinymce.init({
        selector: selector,
        height: 300,
        license_key: 'gpl',

        plugins: 'image code link lists table fullscreen',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code fullscreen',

        menubar: false,
        readonly: false,
        branding: false,
        promotion: false,
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',
        verify_html: false,
        cleanup: false,
        entity_encoding: "raw",

        forced_root_block: 'div',

        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,

        automatic_uploads: true,

        images_upload_handler: function (blobInfo) {
            return new Promise((resolve) => {

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '/upload-image');

                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                );

                xhr.onload = function () {
                    let json = JSON.parse(xhr.responseText);
                    resolve(json.location);
                };

                let formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                xhr.send(formData);
            });
        }
    });
}

// 🔥 AUTO INIT (GLOBAL)
document.addEventListener('DOMContentLoaded', function () {
    initEditor();
});

// 🔥 FIX FOR MODAL + POPUP ISSUE
document.addEventListener('focusin', function (e) {
    if (
        e.target.closest('.tox-tinymce-aux') ||
        e.target.closest('.tox-dialog') ||
        e.target.closest('.tox-textfield') ||
        e.target.closest('.tox-toolbar') ||
        e.target.closest('.tox-dialog-wrap')
    ) {
        e.stopImmediatePropagation();
    }
});