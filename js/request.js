//const { time } = require("console");
//const { title } = require("process");
//const { text } = require("stream/consumers");
//const { FormData } = require("undici-types");

//const { error } = require("console");
//const { title } = require("process");
//const { text } = require("stream/consumers");
//const { resourceLimits } = require("worker_threads");

//const { error } = require("console");
//const { url } = require("inspector");

$(document).ready(function() {
        //console.log("this document is load!")
 
        //Global variable
        let currentPage = 1; // current page
        let count = 1; // initial count for add item
        const limit = 10; // page limit for table

        //--Function management
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        //function to load table
        function loadTable(page = 1){
            var section = $('#section').val();
            const input = $('#searchinput').val().toLowerCase();
            const filterby = $('#searchby').val();

           // console.log(section);
           //console.log(input, filterby);
            $.ajax({
                url: "action.php",
                type: "POST",
                data: { action: 'requestTable',
                        section: section,
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
                     //   console.log(data.total_pages, data.current_page);
                    }
                    else {
                        console.log(data.message); // Show error message
                        $("#requestTable tbody").html("<tr><td colspan='8' class = 'text-center'>No records found.</td></tr>");
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
            var tableBody = $('#requestTable tbody');
            tableBody.empty();
        
            $.each(data, function(index, value){
               // var isDisabled = (value.status === "Sent to Supplier") ? "disabled" : "";

                var editButton = (value.status === "Verified by Procurement") ? "" : 
                `<button class = 'btn btn-primary btn-gradient btn-sm me-3' id='edit-items' 
                                    data-bs-toggle='modal' 
                                    data-bs-target='#editrequestModal'
                                    data-id='${value.id}'
                                    data-controlnumber='${value.controlnumber}'
                                    data-itemname='${value.itemname}'
                                    data-description='${value.description}'
                                    data-purpose='${value.purpose}' 
                                    data-quantity='${value.quantity}' 
                                    title='Edit Item'>
                                    <i class="fa-solid fa-pen-to-square" title="Edit"></i>
                    </button>`;

                var deleteButton = (value.status === "Verified by Procurement") ? "" : 
                `<button class = 'btn btn-danger btn-gradient btn-sm' id='delete-items' data-id='${value.id}' title='Delete Item'><i class="fa-solid fa-trash" title="Delete"></i></button>`;

                var row = `<tr>
                <td>${value.controlnumber}</td>
                <td>${value.itemname}</td>
                <td>
                    <a href='#' class='view-attachment' data-bs-toggle='modal' 
                    data-bs-target='#attachmentModal' data-fileid='${value.id}'>
                        View Attachment
                    </a>
                </td>
                <td>${value.date_requested}</td>
                <td>${value.status}</td>
                <td>
                    <button class = 'btn btn-light btn-gradient btn-sm me-3' id='view-items' data-fileid='${value.id}' data-bs-toggle='modal' data-bs-target='#viewdetails' title='See Details'><i class="fa-solid fa-eye" title="View Info"></i></button>
                    ${editButton}
                    ${deleteButton}
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

        //function for customized input (only accepting numerical input)
        function validateQuantity(input) {
            $(input).on('input', function () {
                const value = $(this).val();
                if (!/^\d*\.?\d*$/.test(value)) {
                    $(this).val(value.slice(0, -1)); // Remove last invalid character
                }
            });
        }

        //autorun function
        loadTable(); // loadtable when document is ready
        validateQuantity('#item_quantity_1'); // only accepting numbers as inputs

        //function for action request
        function actionRequest(url, formselector, modalselector, tablecallback, action){
            //debugger
            //console.log("Url: " + url, "Form: " + formselector, "Modal: " + modalselector , "Action: " + action);

            //action
            $.ajax({
                url: url,
                method: 'POST',
                data: (function(){
                    let formData = new FormData($(formselector)[0]);
                    formData.append("action", action)
                    return formData;
                })(),
                contentType: false,
                processData: false,
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
                                $(modalselector).modal('hide');
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

        //function for validating the form fields
        function validateForm(selector){
            let isValid = true;
            $(selector + '.required').each(function(){
                if ($(this).val().trim() === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
                else{
                    $(this).removeClass('is-invalid');
                }
            });
            return isValid;
        }

        //--Action and buttons

        //Action to view item details
        $(document).on('click', '#view-items',function(){
            const fileId = $(this).data('fileid');
            const modalContent = $('#viewContent');
            let action = "btn_detail";
           //console.log(action)
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
            let action = "btn_view";
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

        //Edit request
        $(document).on('click', '#edit-items', function(){
            const itemname = $(this).data('itemname');
            const description = $(this).data('description');
            const purpose = $(this).data('purpose');
            const quantity = $(this).data('quantity');
            const controlnumber = $(this).data('controlnumber');
            const id = $(this).data('id');

            $('#controlnumber').val(controlnumber);
            $('#id').val(id);
            $('#item_name').val(itemname);
            $('#item_description').val(description);
            $('#item_purpose').val(purpose);
            $('#item_quantity').val(quantity);
        });

        //Action to add another input for item
        $('#addItem').on('click', function(){
            count++;
            const newInput = `
            <div class="input-group mb-3">
                <span class="input-group-text" for="item_name_${count}">Item Name ${count}</span>
                <input type="text" name="item_name[]" id="item_name_${count}" class="form-control" required></input>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" for="item_desctiption_${count}">Item Description</span>
                <input type="text" name="item_description[]" id="item_description_${count}" class="form-control" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" for="item_purpose_${count}">Item Purpose</span>
                <input type="text" name="item_purpose[]" id="item_purpose_${count}" class="form-control" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" for="item_quantity_${count}">Quantity</span>
                <input type="text" name="item_quantity[]" id="item_quantity_${count}" class="form-control" required>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text" for="item_uom_${count}">UOM</label>
                <select class="form-select" name="item_uom[]" id="item_uom_${count}">
                        <option selected>Select unit of measurement</option>
                        <option value="BAG">Bag</option>
                        <option value="BND">Bundle</option>
                        <option value="BOX">Box</option>
                        <option value="CTN">Carton</option>
                        <option value="DZN">Dozen</option>
                        <option value="GAL">Gallon</option>
                        <option value="M">Meter</option>
                        <option value="PC">Piece</option>
                        <option value="PK">Pack</option>
                </select>
            </div>
            <div class="input-group mb-3">
                <input type="file" name="item_file[]" id="item_file_${count}" class="form-control" required accept=".jpg, .jpeg, .png, .pdf" multiple>
            </div>
            `;
           // console.log(newInput); //debugger
           
            $('.input-container-1').append(newInput);
            validateQuantity(`#item_quantity_${count}`);
        });

        //Action to reset the content of modal to initial content
        $('#addrequestModal').on('hidden.bs.modal', function(){
            $('#addItemModal').trigger('reset');
            $('.input-container-1').empty();
        });

        //Action to insert data to the database
        $('#insertdataform').submit(function (e){
            e.preventDefault();
           // console.log('this is submit');
            var action = "btn_add";
            ///var insertdataRoute = 'action.php'
            if (validateForm('#insertdataform')) {
              //  console.log('This form is validated');
                actionRequest(
                    './action.php',
                    '#insertdataform',
                    '#addrequestModal',
                    () => loadTable(1),
                    action
                );
              //console.log('Function successfully work');
                $("#insertdataform")[0].reset();
            }
            else{
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });

        //Edit Item Request
        $('#editdataform').submit(function (e){
            e.preventDefault();
           // console.log('This is submit');
            var action = 'btn_edit';
            if (validateForm('#editdataform')) {
                actionRequest(
                    './action.php',
                    '#editdataform',
                    '#editrequestModal',
                    ()=>loadTable(1),
                    action
                );
              // console.log('Function successfully work');
            }
            else{
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });

        //Delete Item Request
        $(document).on('click', '#delete-items', function(){
            const id = $(this).data('id');
            let action = 'btn_delete';
           // console.log('this button is clicked');
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result)=>{
                if (result.isConfirmed) {
                    $.ajax({
                        url: './action.php',
                        type: 'POST',
                        data: {
                            id: id,
                            action: action
                        },
                        success: function(response){
                           // console.log(response);
                            try{
                                if (response.status === 'success') {
                                    Swal.fire("Delete Request", response.message, "success");
                                    loadTable(1);
                                }
                                else{
                                    console.error("Error Response: ", response);
                                    Swal.fire("Error!", response.message || "Failed to cancel the request.", "error");
                                }
                            } catch(error){
                                console.error("JSON parsing error: ", error);
                                console.log("Raw Response: ", response);
                                Swal.fire("Error! ", "Invalid response from the server.");
                            }
                        },
                        error: function(xhr, status, error){
                            console.error("AJAX error: ", status, error);
                            Swal.fire("Error!", "An unexpected error occured. Please try again. ", "error");
                        }
                    });
                }
            });
        })
});