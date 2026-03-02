$(document).ready(function() {
    // Toggle S3 fields visibility
    $('#storage_driver').on('change', function() {
        if ($(this).val() === 's3') {
            $('.s3-fields').addClass('active');
        } else {
            $('.s3-fields').removeClass('active');
        }
    });

    // Test Connection
    $('#testConnectionBtn').on('click', function() {
        var btn = $(this);
        var resultDiv = $('#testResult');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Testing...');
        resultDiv.hide().removeClass('success error');

        $.ajax({
            url: '/external-apps/external-storage/test-connection',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                storage_driver: 's3',
                s3_access_key_id: $('#s3_access_key_id').val(),
                s3_secret_access_key: $('#s3_secret_access_key').val(),
                s3_default_region: $('#s3_default_region').val(),
                s3_bucket: $('#s3_bucket').val(),
                s3_endpoint: $('#s3_endpoint').val(),
                s3_url: $('#s3_url').val(),
                s3_root: $('#s3_root').val()
            },
            success: function(response) {
                resultDiv
                    .addClass(response.success ? 'success' : 'error')
                    .text(response.message)
                    .show();
            },
            error: function(xhr) {
                var message = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                resultDiv.addClass('error').text(message).show();
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
