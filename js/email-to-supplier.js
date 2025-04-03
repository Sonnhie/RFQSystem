$(document).ready(function(){
    //Global variable
    let currentPage = 1; // current page
    let count = 1; // initial count for add item
    const limit = 10; // page limit for table

    //--Function management
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    function loadTable(page = 1){
       // var section = $('#section').val();
       // console.log(section);
       const input = $('#searchinput').val().toLowerCase();
       const filterby = $('#searchby').val();

        $.ajax({
            url: "action.php",
            type: "POST",
            data: { 
                action: 'emailtable',
                page: page,
                limit: limit,
                input: input,
                filterby: filterby
             },
            dataType: "json",
            success: function(data) {
                if (data.status == 'success') {
                    populateTable(data.data);
                    pagination(data.total_pages, data.current_page);
                    console.log(data.total_pages, data.current_page);
                }
                else {
                    //console.log(data.message); // Show error message
                    $("#emailtable tbody").html("<tr><td colspan='8' class = 'text-center'>No records found.</td></tr>");
                    $('#pagination').html('');
                }
            },
            error: function(xhr, status, error) {
                console.log("Error: ", error);
            }
        });
    }

    $("#searchinput, #searchby").on("input change", function() {
        loadTable(1); // Reload table on search input change
    });

    //function to populate table
    function populateTable(data){
        var tableBody = $('#emailtable tbody');
        tableBody.empty();
    
        $.each(data, function(index, value){
            var row = `<tr>
            <td>${value.controlnumber}</td>
            <td>
                <a href='#' class='view-attachment' data-bs-toggle='modal' 
                data-bs-target='#attachmentModal' data-fileid='${value.controlnumber}'>
                    View Attachment
                </a>
            </td>
            <td>${value.section}</td>
            <td>${value.date_requested}</td>
            <td>${value.status}</td>
            <td>   
                <button class = 'btn btn-light btn-gradient btn-sm me-3' id='view-itemslist' data-fileid='${value.controlnumber}' data-bs-toggle='modal' data-bs-target='#requestdetails' title='See Details'><i class="fa-solid fa-eye"></i></button>
                <button class = 'btn btn-success btn-gradient btn-sm me-3' id='send-email'  data-fileid='${value.id}' data-controlid = '${value.controlnumber}' data-bs-toggle='modal' data-bs-target='#emailsupplier' title='Send email to supplier'><i class="fa-solid fa-envelopes-bulk"></i></button>
            </td>
        </tr>`;
        tableBody.append(row);
        });
    }

     //function for pagination
     function pagination(total_pages, currentPage){
        let paginationHtml = "";

        paginationHtml += `<ul class="pagination">`
        if (total_pages > 1) {
           
            // Page Numbers
            for (let i = 1; i <= total_pages; i++) {
                const activeclass = (i == currentPage) ? 'active' : '';
                paginationHtml += `<li class='page-item ${activeclass}'>`
                paginationHtml += `<a class='page-link' href='#' data-page="${i}">${i}</button>`;
            }
 
        }
        else{
            paginationHtml += `<p class='text-center col-12'>No more pages available.</p>`;
        }

        $("#pagination").html(paginationHtml);
    }


    loadTable();

     //Action to change page when click
     $(document).on('click', '.page-link', function(e){
        e.preventDefault();
        var page = $(this).data('page');
        if (page >= 1) {
            loadTable(page);
        }
    });

     //Action to view attachment on modal
    $(document).on('click', '.view-attachment' ,function(){
        const fileId = $(this).data('fileid');
        const modalContent = $('#modalContent');
        let action = "btn_viewmultiple";
        //console.log(action);
        if (fileId) {
            modalContent.html('<p class="text-center">Loading...</p>');
            //$('#attachmentModal').modal('show');
            $.ajax({
                url: "./action.php",
                type: "POST",
                data: { 
                    id: fileId,
                    action: action
                 },
                success: function (response) {
                   // console.log(response);
                    modalContent.html(response);
                },
                error: function () {
                    modalContent.html('<p class="text-danger text-center">Error loading attachment</p>');
                }
            });
        }
    });

    //View Request details
    $(document).on('click','#view-itemslist', function(){
                const fileId = $(this).data('fileid');
                const modalContent = $('#requestContent');
                let action = "btn_requestdetail";
            // console.log(action)
                if (fileId) {
                    modalContent.html('<p class="text-center">Loading...</p>');
                    $.ajax({
                        url: "./action.php",
                        type: "POST",
                        data: {
                            id: fileId,
                            action: action
                        },
                        success: function(response){
                        //console.log(response);
                            modalContent.html(response);
                        },
                        error: function(){
                            modalContent.html('<p class="text-danger text-center">Error loading attachment</p>');
                        }
                    })
                }
    });

    $(document).on('click', '#send-email', function(){
        const id = $(this).data('fileid');
        const controlid = $(this).data('controlid');
        $('#sendemail').data('fileid', id);
        $('#sendemail').data('controlid', controlid);
    });

    //Send email
    $('#sendemail').click(function(){
        //e.preventDefault();
        let action = "btn_sendemail";
        let recipients = $(".recipientEmail").map(function() { return $(this).val().trim(); }).get().filter(Boolean);
        let cc = $(".ccEmail").map(function() { return $(this).val().trim(); }).get().filter(Boolean);
        let bcc = $(".bccEmail").map(function() { return $(this).val().trim(); }).get().filter(Boolean);
        const fileId = $(this).data('fileid');
        const controlid = $(this).data('controlid');
        let status = 'Request Quotation sent to supplier';
        console.log('Recipients: ', recipients, 'CC: ', cc, 'BCC: ', bcc, 'File ID: ', fileId);

        Swal.fire({ 
            title: 'Are you sure?',
            text: "Send email to this recipients?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Send'
        }).then((result) => {
            if(result.isConfirmed){

                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we send the email.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading(); // Show loading spinner
                    }
                });
                $.ajax({
                    url: './action.php',
                    type: 'POST',
                    data: JSON.stringify({action, recipients, bcc, cc, controlid, fileId, status}),
                    contentType: "application/json",
                    success: function(response){
                        console.log(response);
                        try{
                            if (response.status == 'success') {
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                }).then((result) => {
                                    loadTable(1);
                                });
                            }
                            else{
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'Try again'
                                });
                            }
                        }
                        catch(e){
                            Swal.fire({
                                title: 'Error!',
                                text: `Invalid server response: ${e}`,
                                icon: 'error',
                                confirmButtonText: 'Try again' 
                            });
                        }
                    },
                    error: function(xhr, status, error){
                        Swal.close();
                        Swal.fire({
                            title: 'Error!',
                            text: `AJAX request failed: ${xhr.response || error}`,
                            icon: 'error',
                            confirmButtonText: 'Try again'
                        });
                    }
                })
            }
        })
        
    });

});