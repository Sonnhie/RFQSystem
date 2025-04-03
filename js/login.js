$(document).ready(function() {
        $('#loginForm').submit(function(e) {
             e.preventDefault();
             var form = $(this).form();
             var url = form.attr('action');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: form.serialize(),
                    success: function(data)
                    {
                        if(data == 'true') {
                            window.location.href = 'index.php';
                        } else {
                            $('#loginError').html(data);
                        }
                    }
                });

        });
});