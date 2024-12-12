$(document).ready(function() {
    $('#displayname').on('focus', function() {
        if ($(this).val() === '') {
            var firstName = $('#firstname').val();
            var lastName = $('#lastname').val();
            $(this).val(firstName + ' ' + lastName);
        }
    });
});