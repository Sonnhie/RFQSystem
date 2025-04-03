$(document).ready(function() {
    //Global variable
    let currentPage = 1; // current page
    let count = 1; // initial count for add item
    const limit = 5; // page limit for table
    var rowCounters = {}; 
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
       let role = $("#comparisontable").data('role');
       console.log(role);
        $.ajax({
            url: "action.php",
            type: "POST",
            data: { 
                action: 'comparisontable',
                page: page,
                limit: limit,
                input: input,
                filterby: filterby,
                role: role
             },
            dataType: "json",
            success: function(data) {
                if (data.status == 'success') {
                    populateTable(data.data);
                    pagination(data.total_pages, data.current_page);
                  //console.log(data.total_pages, data.current_page);
                }
                else {
                    //console.log(data.message); // Show error message
                    $("#comparisontable tbody").html("<tr><td colspan='8' class = 'text-center'>No records found.</td></tr>");
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
            var tableBody = $('#comparisontable tbody');
            let role = $("#comparisontable").data('role');
            console.log(role);
            tableBody.empty();
        
            $.each(data, function(index, value){

                var actionButtons = `
                <button class='btn btn-light btn-gradient btn-sm me-2' id='view-comparisonlist' data-fileid='${value.controlnumber}' data-bs-toggle='modal' data-bs-target='#comparisondetails' title='See Comparison Data'>
                    <i class="fa-solid fa-eye"></i>
                </button>
                `;

                // Add extra button if role is "section approval"
                if (role === "Section Approval") {
                    actionButtons += `
                        <button class='btn btn-success btn-gradient btn-sm me-2' id='approve-comparison' data-section='${value.section}' data-fileid='${value.controlnumber}' title='Approve Data'>
                            <i class="fa-solid fa-check"></i>
                        </button>
           
                    `;

                   // <button class='btn btn-danger btn-gradient btn-sm me-2' id='disapprove-comparison' data-section='${value.section}' data-fileid='${value.controlnumber}' title='Disapprove Data'>
                   // <i class="fa-solid fa-thumbs-down"></i>
                   // </button>   
                }else if (role === "Verifier") {
                    actionButtons += `
                        <button class='btn btn-danger btn-gradient btn-sm me-2' id='delete-comparison' data-section='${value.section}' data-fileid='${value.id}' data-controlid='${value.controlnumber}' title='Delete Data'>
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }else if (role === "Requestor") {
                    actionButtons += `
                        <button class='btn btn-primary btn-gradient btn-sm me-2' id='requestorapprove-comparison' data-section='${value.section}' data-fileid='${value.controlnumber}' title='Approve Data'>
                            <i class="fa-solid fa-check"></i>
                        </button>
                         <button class='btn btn-danger btn-gradient btn-sm me-2' id='requestordisapprove-comparison' data-section='${value.section}' data-fileid='${value.controlnumber}' title='Approve Data'>
                            <i class="fa-solid fa-thumbs-down"></i>
                        </button>
                    `;
                }


                var row = 
                    `<tr>
                        <td>${value.controlnumber}</td>
                        <td>${value.datecreate}</td>
                        <td>${value.section}</td>
                        <td>${value.status}</td>
                        <td>${value.remarks}</td>   
                        <td>${actionButtons}</td>
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

    $('#createBtn').click(function(){
        var controlnumber = $('#controlNumber').val();
        console.log(controlnumber);
        if (controlnumber === "") {
            Swal.fire({
                title: 'Invalid Control Number',
                text: 'Invalid or empty control number',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        $("#controlnumber").text(controlnumber);
            $.ajax({
                url: "action.php",
                type: "POST",
                data: { 
                    action: 'create',
                    controlnumber: controlnumber
                 },
                dataType: "json",
                success: function(data) {
                   if (data.length === 0) {
                        Swal.fire({
                            title: 'Error',
                            text: 'No Item found on this control number',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                   }
                   $("#section").text(data[0].section);
                   $("#itemDiv").empty();
                   data.forEach((item, index) => {
                       var itemID = item.id;
                       var section = item.section;
                       rowCounters[itemID] = 1;
                       console.log(itemID, section);
                       var itemBlock = `
                        <div class="item-block mb-4 border p-3" id="item_${itemID}" ">
                            <h5 data-itemname="${item.itemname}" >${item.itemname}</h5>
                            <p data-quantity=${item.quantity}>Quantity: ${item.quantity}</p>
                            <p>UOM: ${item.unitofquantity}</p>
                            <div class="table-responsive">
                                <table class="table table-bordered supplierTable">
                                    <thead>
                                        <tr>
                                            <th>Supplier Name</th>
                                            <th>Item Price</th>
                                            <th>Discount (%)</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="supplierTableBody_${itemID}">
                                        ${generateSupplierRow(itemID, 1)} <!-- Start with 1 supplier row -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <button type="button" class="btn btn-success addSupplierRow" data-item-id="${itemID}">
                                                    <i class="fa fa-plus"></i> Add Supplier
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>`;

                    $("#itemDiv").append(itemBlock);
                    //console.log(itemID);
                   });

                   $("#comparisonModal").modal("show");
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error in creating comparison',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }); // Show error message
                }
            });
    });

     //Action to change page when click
    $(document).on('click', '.page-link', function(e){
        e.preventDefault();
        var page = $(this).data('page');
        if (page >= 1) {
            loadTable(page);
        }
    });

    // Function to generate a supplier row for an item
    function generateSupplierRow(itemId, rowId) {
        return `
            <tr id="supplierRow_${itemId}_${rowId}">
                <td><input type="text" class="form-control supplierName" name="suppliername[]" placeholder="Supplier Name" /></td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control itemPrice" name="itemPrice[]" placeholder="Price" />
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control discountPercentage" name="discount[]" placeholder="Discount" />
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger removeSupplierRow" data-row-id="supplierRow_${itemId}_${rowId}">
                        <i class="fa fa-minus"></i>
                    </button>
                </td>
            </tr>`;
    }

    $(document).on("input", ".itemPrice, .discountPercentage", function() {
        this.value = this.value.replace(/[^0-9.]/g, ''); // Allow only numbers and decimals
    });
    
     // Add Supplier Row
     $(document).on("click", ".addSupplierRow", function () {
        var itemId = $(this).data("item-id");
        rowCounters[itemId]++;
        $("#supplierTableBody_" + itemId).append(generateSupplierRow(itemId, rowCounters[itemId]));
    });

    // Remove Supplier Row
    $(document).on("click", ".removeSupplierRow", function () {
        var rowId = $(this).data("row-id");
        $("#" + rowId).remove();
    });

    $('#comparisonForm').submit(function(e) {
        e.preventDefault();
        let supplierData = [];
        console.log("submit");
        $(".supplierTable").each(function() {
            //let itemId = $(this).data('id');
            let section = $("#section").text();
            let itemname = $(this).closest(".item-block").find("h5").data("itemname");
            var controlnumber = $('#controlNumber').val();
            let quantity = $(this).closest(".item-block").find("p").data("quantity");
            let status = "For Verification";
          


            console.log(section,itemname, controlnumber);

            $(this).find("tbody tr").each(function() {
                let supplierName = $(this).find(".supplierName").val();
                let itemPrice = $(this).find(".itemPrice").val();
                let discountPercentage = $(this).find(".discountPercentage").val();

                if (supplierName !== "" && itemPrice !== "" && discountPercentage !== "") {
                    supplierData.push({
                        controlnumber: controlnumber,
                        itemname: itemname,
                        suppliername: supplierName,
                        itemprice: itemPrice,
                        discount: discountPercentage,
                        section: section,
                        quantity: quantity,
                        status: status
                    });
                }
            });
        });
        console.log(supplierData);
        $.ajax({
            url: "action.php",
            type: "POST",
            data: { 
                action: 'addcomparison',
                supplierData: supplierData
             },
            dataType: "json",
            success: function(data) {
                if (data.status == "success") {
                    Swal.fire({
                        title: 'Success',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        loadTable(1);
                        $("#comparisonModal").modal("hide");
                        $("#comparisonForm")[0].reset();
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Error in adding comparison',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }); // Show error message
            }
        });
    });

    $(document).on('click', '#view-comparisonlist', function () {
        const fileId = $(this).data('fileid');
        const modalContent = $('#comparisonTable');
        let action = "btn_comparisondetails";
    
        if (fileId) {
            modalContent.html('<p class="text-center">Loading...</p>');
            $.ajax({
                url: "./action.php",
                type: "POST",
                data: { id: fileId, action: action },
                success: function (response) {
                    let suppliers = response.supplierData;
                    let comparisondata = response.comparisonData;
                    let supplierOptions = '<option value="">All Suppliers</option>';
                        suppliers.forEach(supplier => {
                            supplierOptions += `<option value="${supplier}">${supplier}</option>`;
                        });
                        $("#supplierFilter").html(supplierOptions);

    
                    // Fetch and apply currency rates
                    fetchCurrencyRates();
    
                    function renderTable(filterItem = "", filterSupplier = "", currencyRate = 1) {
                        let tableHeader = `<thead><tr><th>Item Name</th><th>Quantity</th>`;
    
                        let filteredSuppliers = suppliers.filter(supplier => !filterSupplier || filterSupplier === supplier);
    
                        filteredSuppliers.forEach(supplier => {
                            tableHeader += `<th>${supplier}<br>(Quotation Info)</th>`;
                        });
                        tableHeader += `</tr></thead><tbody>`;
    
                        let tableBody = "";
    
                        comparisondata.forEach(item => {
                            if (filterItem && !item.itemname.toLowerCase().includes(filterItem.toLowerCase())) return;
    
                            let row = `<tr><td><strong>${item.itemname}</strong></td><td>${item.quantity}</td>`;
    
                            filteredSuppliers.forEach(supplier => {
                                let supplierInfo = item.suppliers.find(s => s.suppliername === supplier);
    
                                if (supplierInfo) {
                                    let unitPrice = parseFloat(supplierInfo.unitprice);
                                    let discount = parseFloat(supplierInfo.discount);
                                    let totalAmount = parseFloat(supplierInfo.totalamount);
    
                                    row += `<td data-unitprice="${unitPrice}" data-discount="${discount}" data-totalamount="${totalAmount}">
                                                <p>Price: <strong class="unitprice">${(unitPrice * currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></p>
                                                <p>Discount: <strong class="discount">${(discount * currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></p>
                                                <p>Total amount: <strong class="totalamount">${(totalAmount * currencyRate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></p>
                                            </td>`;
                                } else {
                                    row += `<td>-</td>`;
                                }
                            });
    
                            tableBody += row + `</tr>`;
                        });
    
                        $("#comparisonTable").html(tableHeader + tableBody + `</tbody>`);
                    }
    
                    renderTable();
    
                    // Search by Item Name
                    $("#searchItem").on("input", function () {
                        renderTable($(this).val(), $("#supplierFilter").val(), $("#currencyFilter").val());
                    });
    
                    // Filter by Supplier
                    $("#supplierFilter").on("change", function () {
                        renderTable($("#searchItem").val(), $(this).val(), $("#currencyFilter").val());
                    });
    
                    // Currency Change Event
                    $("#currencyFilter").on("change", function () {
                        let currencyRate = parseFloat($(this).val());
                        updateCurrencyConversion(currencyRate);
                    });
                },
                error: function () {
                    modalContent.html('<p class="text-danger text-center">Error loading attachment</p>');
                }
            });
        }
    });
    
    // Function to fetch real-time USD to PHP exchange rate
    function fetchCurrencyRates() {
        const API_URL = "get_exchange_rates.php"; // Replace with your API
    
        $.ajax({
            url: API_URL,
            method: "GET",
            success: function (data) {
                console.log("Raw Response:", data);
            
                if (typeof data === "string") {
                    data = JSON.parse(data);
                }
            
                console.log("Parsed Response:", data);
            
                let rates = data.conversion_rates;
                console.log("Rates Object:", rates); // Check if PHP exists
            
                let currencyOptions = ``;
            
                for (let currency in rates) {
                    console.log(`Adding ${currency}: ${rates[currency]}`);
                    currencyOptions += `<option value="${rates[currency]}">${currency}</option>`;
                }
            
                $("#currencyFilter").html(currencyOptions);
            }
            ,
            error: function () {
                console.error("Failed to fetch currency rates.");
            }
        });
    }
    
    // Function to update currency conversion dynamically
    function updateCurrencyConversion(currencyRate) {
        $(".unitprice, .discount, .totalamount").each(function () {
            let parentTd = $(this).closest("td");
            let baseUnitPrice = parseFloat(parentTd.data("unitprice"));
            let baseDiscount = parseFloat(parentTd.data("discount"));
            let baseTotalAmount = parseFloat(parentTd.data("totalamount"));
    
            $(parentTd).find(".unitprice").text((baseUnitPrice * currencyRate).toFixed(2));
            $(parentTd).find(".discount").text((baseDiscount * currencyRate).toFixed(2));
            $(parentTd).find(".totalamount").text((baseTotalAmount * currencyRate).toFixed(2));
        });
    }

    // Event listener for currency change
    $("#currencyFilter").on("change", function () {
        let currencyRate = parseFloat($(this).val());
        updateCurrencyConversion(currencyRate);
    });

    $(document).on('click','#delete-comparison', function(){
        var fileId = $(this).data('fileid');
        var controlId = $(this).data('controlid');
        console.log(fileId, controlId);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete this comparison data. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "action.php",
                    type: "POST",
                    data: { 
                        action: 'delete',
                        id: fileId,
                        controlnumber: controlId
                     },
                    dataType: "json",
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                loadTable(1);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error in deleting comparison',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }); // Show error message
                    }
                });
            }
        });
    });

    $('#sectionDropdown').change(function(){
        let section = $(this).val();
        console.log(section);

        if (section) {
            $.ajax({
                url: "action.php",
                type: "POST",
                data: { 
                    action: 'getcontrolnumber',
                    section: section
                 },
                 success: function(data) {
                    let controlnumbers = data;
                    let controlnumberOptions = '<option value="">Select Control Number</option>';
                    controlnumbers.forEach(controlnumber => {
                        controlnumberOptions += `<option value="${controlnumber.control_number}">${controlnumber.control_number}</option>`;
                    });
                    $("#controlNumber").html(controlnumberOptions);
                 },
                 error: function(xhr, status, error) {
                    console.log("Error: ", error);
                }
            });
        }
        else {
            $("#controlNumber").html('<option value="">Select Control Number</option>');
        }
    });

    $(document).on('click', '#approve-comparison', function(){
        var fileId = $(this).data('fileid');
        let status = "Approved Comparison Data by Procurement Section";
        let section = $(this).data('section');
        console.log("Recipient: ", recipient);
        console.log(recipient);
        console.log(fileId);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to approve this comparison data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while processing the request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Show loading spinner
                }
            });
            if (result.isConfirmed) {
                $.ajax({
                    url: "action.php",
                    type: "POST",
                    data: { 
                        action: 'updatestatus',
                        id: fileId,
                        status: status,
                        section: section
                     },
                    dataType: "json",
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: 'Comparison approved successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                loadTable(1);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error in approving comparison',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }); // Show error message
                    }
                });
            }
        });
    });

    $(document).on('click', '#disapprove-comparison', function(){
        var fileId = $(this).data('fileid');
        let status = "Disapproved Comparison Data by Procurement Section";
        let section = $(this).data('section');
        console.log(fileId);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to disapprove this comparison data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, disapprove it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while processing the request.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading(); // Show loading spinner
                    }
                });
                $.ajax({
                    url: "action.php",
                    type: "POST",
                    data: { 
                        action: 'updatestatus',
                        id: fileId,
                        status: status,
                        section: section
                     },
                    dataType: "json",
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: 'Comparison disapproved successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                loadTable(1);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error in disapproving comparison',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }); // Show error message
                    }
                });
            }
        });
    });

    $(document).on('click', '#requestorapprove-comparison', function(){
        var fileId = $(this).data('fileid');
        let status = "Approved Comparison Data by Requestor";
        let section = $(this).data('section');
        console.log(fileId);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to approve this comparison data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while processing the request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Show loading spinner
                }
            });
            if (result.isConfirmed) {
                $.ajax({
                    url: "action.php",
                    type: "POST",
                    data: { 
                        action: 'updatestatus',
                        id: fileId,
                        status: status,
                        section: section
                     },
                    dataType: "json",
                    success: function(data) {
                        console.log(data)
                        if (data.status == 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: 'Comparison approved successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                loadTable(1);
                            });
                        }
                        else{

                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error in approving comparison',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }); // Show error message
                    }
                });
            }
        });
    });

    $(document).on('click', '#requestordisapprove-comparison', function(){
        var fileId = $(this).data('fileid');
        let status = "Disapproved Comparison Data by Requestor";
        let section = $(this).data('section');
        console.log(fileId);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to disapprove this comparison data. Please provide remarks.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter your remarks here...',
            inputAttributes: {
            'aria-label': 'Enter your remarks here'
            },
            showCancelButton: true,
            confirmButtonText: 'Yes, disapprove it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            preConfirm: (remarks) => {
            if (!remarks) {
                Swal.showValidationMessage('Remarks are required to proceed');
            }
            return remarks;
            }
        }).then((result) => {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while processing the request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Show loading spinner
                }
            });
            if (result.isConfirmed) {
            const remarks = result.value;
            $.ajax({
                url: "action.php",
                type: "POST",
                data: { 
                action: 'updatestatus',
                id: fileId,
                status: status,
                remarks: remarks,
                section: section
                 },
                dataType: "json",
                success: function(data) {
                if (data.status == 'success') {
                    Swal.fire({
                    title: 'Success',
                    text: 'Comparison disapproved successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                    }).then((result) => {
                    loadTable(1);
                    });
                }
                },
                error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Error in disapproving comparison',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }); // Show error message
                }
            });
            }
        });
    });

});