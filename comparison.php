<?php
     ob_start();
     session_start();
    // include('./inc/header.php');
     include('./utility.php');
     $utility = new request();
     $utility->checkLogin();
     $section = isset($_SESSION['department']) ? $_SESSION['department'] : '';
     if (empty($_SESSION['role'])) {
         session_unset();
         session_destroy();
         header("Location: ./login.php");
     }
?>
<div class="container">

    <?php
        if ($_SESSION['role'] == 'Verifier' && $_SESSION['department'] == 'Procurement') {
    ?>
    <div class="row mb-3">
        <div class="col-lg-12 mb-3">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Create Comparison</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
     
                    <div class="row mb-3">
                        <div class="col-md-5">
                                <div class="input-group">
                                    <select id="sectionDropdown" class="form-select me-2">
                                        <option value="">Select Section</option>
                                        <option value="IT">IT</option>
                                        <option value="HRGA">HRGA</option>
                                        <option value="Injection">Injection</option>
                                        <option value="2nd Process">2nd Process</option>
                                        <option value="PCD">PCD</option>
                                        <option value="Procurement">Procurement</option>
                                        <option value="Accounting">Accounting</option>
                                        <option value="Facility">Facility</option>
                                        <option value="Sales">Sales</option>
                                        <option value="Machine Maintenance">Machine Maintenance</option>
                                        <option value="Safety&Health">Safety&Health</option>
                                        <option value="QA/QC">QA/QC</option>
                                        <option value="Mold Maintenance">Mold Maintenance</option>
                                        <option value="Technical">Technical</option>
                                    </select>
                                    <select id="controlNumber" class="form-select me-2">
                                        <option value="">Select Control Number</option>
                                        
                                    </select>
                                    <button type="button" id="createBtn" data-bs-toggle="modal" data-bs-target="#comparisonModal" class="btn btn-primary rounded-3">Create</button>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-12 mb-3">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Comparison List</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-lg-12 col-md-12 mb-3">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" id="searchinput" class="form-control me-3 rounded-3" placeholder="Search..." aria-label="Search">
                                    <select class="form-select rounded-3" name="searchby" id="searchby" required>
                                        <option selected disabled>Filter by</option>
                                        <option value="date">📅 Date</option>
                                        <option value="controlnumber">🔢 Control Number</option>
                                        <option value="status">⏳ Status</option>
                                        <option value="section">🏢 Section</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 table-responsive">
                                <table id="comparisontable" data-role="<?php echo $_SESSION['role']?>" class="table table-hover">
                                    <thead>
                                        <tr>		
                                            <th>Control Number</th>						
                                            <th>Date Created</th>
                                            <th>Section</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>                     
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-12 col-md-12">
                            <nav id="pagination">

                            </nav>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        }
        else
        {
    ?>
    <div class="row mb-3">
        <div class="col-lg-12 mb-3">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Comparison List</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-lg-12 col-md-12 mb-3">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" id="searchinput" class="form-control me-3 rounded-3" placeholder="Search..." aria-label="Search">
                                    <select class="form-select rounded-3" name="searchby" id="searchby" required>
                                        <option selected disabled>Filter by</option>
                                        <option value="date">📅 Date</option>
                                        <option value="controlnumber">🔢 Control Number</option>
                                        <option value="status">⏳ Status</option>
                                        <option value="section">🏢 Section</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 table-responsive">
                                <table id="comparisontable" data-role="<?php echo $_SESSION['role']?>" class="table table-hover">
                                    <thead>
                                        <tr>		
                                            <th>Control Number</th>						
                                            <th>Date Created</th>
                                            <th>Section</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>                     
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-12 col-md-12">
                            <nav id="pagination">

                            </nav>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        }
    ?>
</div>


<!-- Modal -->
<div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <form id="comparisonForm">
            <div class="modal-header">
                <h5 class="modal-title" id="comparisonModalLabel">Supplier Comparison for Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <h4 id="controlnumber">....</h4>
                    <h5 id="section"></h4>
                </div>
                <div class="row">
                    <div class="mb-3" id="itemDiv"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- View Item Info Modal-->
<div class="modal fade" id="comparisondetails" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewdetails" aria-hidden="true">
     <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Quotation Information</h5>
            </div>
            <div class="modal-body " id="requestContent">
                 <!-- Search and Filter Controls -->
                 <div class="d-flex justify-content-between mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="searchItem" class="form-control me-3 rounded-3" placeholder="Search item name..." aria-label="Search">
                        <span  class="input-group-text bg-primary text-white"><i class="fa-solid fa-boxes-packing"></i></span>
                        <select id="supplierFilter" class="form-control w-10 rounded-3 me-3">
                            <option value="">All Suppliers</option>
                        </select>
                        <span  class="input-group-text bg-primary text-white"><i class="fa-solid fa-dollar-sign"></i></span>
                        <select id="currencyFilter" class="form-control w-10 rounded-3">
                            <option value="">Choose Currency</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover" id="comparisonTable"></table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
     </div>
 </div>
<script src="./js/comparisondetails.js"></script>
<script>

</script>