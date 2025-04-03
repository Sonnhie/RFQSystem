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
        console.log("Ajax running");
        $.ajax({
            url: "action.php",
            type: "POST",
            data: { 
                action: 'holdrequestTable',
                page: page,
                limit: limit,
                input: input,
                filterby: filterby
             },
            dataType: "json",
            success: function(data) {
                //const JsonParsed = JSON.parse(data);
                //console.log(JsonParsed);
                console.log("Ajax successfully compiled");
                if (data.status == 'success') {
                    populateTable(data.data);
                    pagination(data.total_pages, data.current_page);
                  //console.log(data.total_pages, data.current_page);
                }
                else {
                    console.log(data.message); // Show error message
                    $("#holdrequestTable tbody").html("<tr><td colspan='8' class = 'text-center'>No records found.</td></tr>");
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
        var tableBody = $('#holdrequestTable tbody');
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
                <button class = 'btn btn-primary btn-gradient btn-sm me-3' id='verify-items' data-fileid='${value.controlnumber}' title='Verified'><i class="fa-solid fa-thumbs-up"></i></button>
                <button class = 'btn btn-danger btn-gradient btn-sm' id='hold-items' data-fileid='${value.controlnumber}' title='Hold'><i class="fa-solid fa-ban"></i></button>
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

    loadTable(1);

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

    //function for action request
    function actionRequest(url, tablecallback, action, id, status){
        //debugger
        //console.log("Url: " + url, "Form: " + formselector, "Modal: " + modalselector , "Action: " + action);
        //action

        if (status == "Hold request by Procurement") {
            Swal.fire({
                title: 'Please provide remarks',
                input: 'textarea',
                inputPlaceholder: 'Enter your remarks here...',
                inputAttributes: {
                    'aria-label': 'Enter your remarks'
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Submit'
            }).then((result) => {
                if (result.isConfirmed) {
                    const remarks = result.value; // Get the inputted remarks
                    if (!remarks) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Remarks are required.',
                            icon: 'error',
                            confirmButtonText: 'Try again'
                        });
                        return;
                    }
    
                    // Proceed with the AJAX request after the user inputs the remarks
                    PerformActionRequest(url, tablecallback, action, id, status, remarks);
                }
            });
        }else{
            Swal.fire({
                title: 'Are you sure?',
                text: "Approved this request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if(result.isConfirmed){
                    PerformActionRequest(url, tablecallback, action, id, status);
                }
            })
        }    
    }

    function PerformActionRequest(url, tablecallback, action, id, status, remarks = null){
        $.ajax({
            url: url,
            method: 'POST',
            data:{
                action: action,
                id: id,
                status: status,
                remarks: remarks
            },
            beforeSend: function(){
                Swal.fire({
                    title: 'Please wait...',
                    text: 'Processing your request',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response){
                Swal.close();
                //debugger
               console.log(response);
                try{
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                        }).then(()=>{
                            tablecallback();
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
        });
    }

    //verify request
    $(document).on('click', '#verify-items', function(){
        const id = $(this).data('fileid');
        let action = "btn_verify";
        let status = "Verified by Procurement";
        console.log('Control number: ', id, 'Action: ',action , 'Status: ', status);

        if (id) {
            actionRequest(
                './action.php',
                () => loadTable(1),
                action,
                id,
                status
            );
        }else{
            Swal.fire({
                title: 'Invalid Control Number',
                text: 'Invalid or empty control number',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });

    //Hold request
    $(document).on('click', '#hold-items', function(){
        const id = $(this).data('fileid');
        let action = "btn_verify";
        let status = "Hold request by Procurement";
        console.log('Control number: ', id, 'Action: ',action , 'Status: ', status);

        if (id) {
            actionRequest(
                './action.php',
                () => loadTable(1),
                action,
                id,
                status
            );
        }else{
            Swal.fire({
                title: 'Invalid Control Number',
                text: 'Invalid or empty control number',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });
});