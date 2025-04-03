$(document).ready(function() {

    // Active Navigation
    var url = window.location.pathname.split("/").pop();
    var page = url.substr(0, url.lastIndexOf('.'));
    $("a#" + page + "_menu").css({ 'color': '#FFF' });
    var role = $("#content").data('role');
    var url;
    console.log(role);
    // Logout
    $('#logout').click(function(e)
    {
        e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to logout?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: './logout.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Logged Out',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                setTimeout(() => {
                                    window.location.href = './login.php';
                                }, 1000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to log out. Please try again.',
                                });
                            }
                        }
                    })
                }
            }); 
    });
    
    if (role == "Procurement") {
       url = "dashboard.php";
       loadPage(url);
    }else{
        url = "userdashboard.php";
        loadPage(url);
    }

    function loadPage(page){
        $.ajax({
            url: page,
            type: 'GET',
            success: function(response){
                //console.log(response);
                $('#content').html(response);
            },
            error: function(){
                $('#content').html("<h3 class='text-danger'>Error loading page</h3>");
            }
        });
    }


    $('.load-content').click(function(e){
        e.preventDefault();

        var page = $(this).data('page');

        $(".sidebar-link").removeClass("active");
        $(this).addClass("active");
        loadPage(page);
    });

});