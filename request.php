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
<div class="row">
    <div class="col-lg-6 mb-3">
            <div class="input-group">
                <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchinput" class="form-control me-3 rounded-3" placeholder="Search..." aria-label="Search">
                <select class="form-select rounded-3" name="searchby" id="searchby" required>
                    <option selected disabled>Filter by</option>
                    <option value="date">📅 Date</option>
                    <option value="itemname">📝 Item Name</option>
                    <option value="controlnumber">🔢 Control Number</option>
                    <option value="status">⏳ Status</option>
                </select>
            </div>
    </div>
</div>
        <div class="col-lg-12">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Request</h4>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6" align="right">
                            <button type="button" name="add" id="addRequest" data-bs-toggle="modal" data-bs-target="#addrequestModal" 
                            class="btn btn-primary bg-gradient btn-sm rounded-0"><i class="far fa-plus-square"></i> New Request</button>
                        </div>   
                    </div>
                    <div class="clear:both"></div>
                </div>
                <div class="card-body">
                <input type="hidden" name="section" id="section" value="<?php echo  $section; ?>">
					<div class="row">
						<div class="col-sm-12 table-responsive">
							<table id="requestTable" class="table table-hover">
								<thead>
									<tr>		
										<th>Control Number</th>		
										<th>Item Name</th>					
										<th>Attachment</th>
                                        <th>Date Requested</th>
                                        <th>Status</th>
                                        <th>Action</th>
									</tr>
								</thead>
                                <tbody>
                                    
                                </tbody>
							</table>
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

</div>

<!--Add Item Modal -->
<div class="modal fade" id="addrequestModal" tabindex="-1" aria-labelledby="addrequestModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form action="./action.php" method="post" id="insertdataform" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create New Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-danger" id="error_message" style="display:none;"></div>
                        <div class="alert alert-success" id="success_message" style="display:none;"></div>
                        <button class="btn btn-primary  btn-sm mb-3" id="addItem"><i class="fa-solid fa-plus"></i> Add Item</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 ">
                        <div class="border-bottom">
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_name_1">Item Name 1</span>
                                <input type="text" name="item_name[]" id="item_name_1" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_desctiption_1">Item Description</span>
                                <input type="text" name="item_description[]" id="item_description_1" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_purpose_1">Item Purpose</span>
                                <input type="text" name="item_purpose[]" id="item_purpose_1" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_quantity_1">Quantity</span>
                                <input type="text" name="item_quantity[]" id="item_quantity_1" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="item_uom_1">UOM</label>
                                <select class="form-select" name="item_uom[]" id="item_uom_1" required>
                                    <option selected>Select unit of measurement</option>
                                    <option value="Bag">Bag</option>
                                    <option value="Bundle">Bundle</option>
                                    <option value="Box">Box</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Dozen">Dozen</option>
                                    <option value="Gallon">Gallon</option>
                                    <option value="Meter">Meter</option>
                                    <option value="Piece">Piece</option>
                                    <option value="Pack">Pack</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <input type="file" name="item_file[]" id="item_file_1" class="form-control" required accept=".jpg, .jpeg, .png, .pdf" multiple>
                            </div>
                        </div>
                        <div class="input-container-1 border-bottom"></div>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="submitrequest">Submit Request</button>
            </div>
        </form>
    </div>
  </div>
</div>

<!--Edit Request-->
<div class="modal fade" id="editrequestModal" tabindex="-1" aria-labelledby="addrequestModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form action="./action.php" method="post" id="editdataform" enctype="multipart/form-data">
        <input type="hidden" name="id" id="id" class="form-control" readonly> 
        <input type="hidden" name="controlnumber" id="controlnumber" class="form-control" readonly>                            
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-danger" id="error_message" style="display:none;"></div>
                        <div class="alert alert-success" id="success_message" style="display:none;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 ">
                        <div class="border-bottom">
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_name_1">Item Name 1</span>
                                <input type="text" name="item_name" id="item_name" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_desctiption_1">Item Description</span>
                                <input type="text" name="item_description" id="item_description" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_purpose_1">Item Purpose</span>
                                <input type="text" name="item_purpose" id="item_purpose" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" for="item_quantity_1">Quantity</span>
                                <input type="text" name="item_quantity" id="item_quantity" class="form-control" required>
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="item_uom_1">UOM</label>
                                <select class="form-select" name="item_uom" id="item_uom" required>
                                    <option selected>Select unit of measurement</option>
                                    <option value="Bag">Bag</option>
                                    <option value="Bundle">Bundle</option>
                                    <option value="Box">Box</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Dozen">Dozen</option>
                                    <option value="Gallon">Gallon</option>
                                    <option value="Meter">Meter</option>
                                    <option value="Piece">Piece</option>
                                    <option value="Pack">Pack</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <input type="file" name="item_file[]" id="item_file" class="form-control" required accept=".jpg, .jpeg, .png, .pdf" multiple>
                            </div>
                        </div>
                        <div class="input-container-1 border-bottom"></div>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="savechanges">Save Changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<!-- View attachment -->
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-custom" id="modalContent">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div> 

<!-- View Item Info Modal-->
 <div class="modal fade" id="viewdetails" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewdetails" aria-hidden="true">
     <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Request Information</h5>
            </div>
            <div class="modal-body " id="viewContent">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
     </div>
 </div>

 
<script src="./js/request.js"></script>

<script>
 var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
        })
</script>