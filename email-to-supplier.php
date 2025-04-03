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
                <option value="controlnumber">🔢 Control Number</option>
                <option value="status">⏳ Status</option>
                <option value="section">🏢 Section</option>
            </select>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Email to Supplier CheckList</h4>
                        </div>
                    </div>
                    <div class="clear:both"></div>
                </div>
                <div class="card-body">
                <input type="hidden" name="section" id="section" value="<?php echo  $section; ?>">
					<div class="row">
						<div class="col-sm-12 table-responsive">
							<table id="emailtable" class="table table-hover">
								<thead>
									<tr>		
										<th>Control Number</th>						
										<th>Attachment</th>
                                        <th>Section</th>
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
<div class="modal fade" id="requestdetails" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewdetails" aria-hidden="true">
     <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Item Request Information</h5>
            </div>
            <div class="modal-body " id="requestContent">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
     </div>
 </div>

 <!-- Send Email to Supplier Modal-->
<div class="modal fade" id="emailsupplier" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewdetails" aria-hidden="true">
     <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Supplier</h5>
            </div>
            <div class="modal-body " id="supplierList">
            <form id="emailForm">
                <div class="mb-3">
                    <label class="form-label">Recipient Emails</label>
                    <div id="recipientContainer">
                        <input type="email" class="form-control mb-2 recipientEmail" placeholder="Enter recipient email">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addEmailField('recipientContainer', 'recipientEmail')">+ Add More</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">CC Emails</label>
                    <div id="ccContainer">
                        <input type="email" class="form-control mb-2 ccEmail" placeholder="Enter CC email">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addEmailField('ccContainer', 'ccEmail')">+ Add More</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">BCC Emails</label>
                    <div id="bccContainer">
                        <input type="email" class="form-control mb-2 bccEmail" placeholder="Enter BCC email">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addEmailField('bccContainer', 'bccEmail')">+ Add More</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="sendemail">Send Email</button>
            </div>
            </form>
        </div>
     </div>
 </div>

 <script src="./js/email-to-supplier.js"></script>

 <script>
    function addEmailField(containerId, className) {
        let container = document.getElementById(containerId);
        let input = document.createElement("input");
        input.type = "email";
        input.className = `form-control mb-2 ${className}`;
        input.placeholder = "Enter email";
        
        let removeButton = document.createElement("button");
        removeButton.className = "btn btn-danger btn-sm mb-2";
        removeButton.textContent = "Remove";
        removeButton.onclick = function () {
            container.removeChild(input);
            container.removeChild(removeButton);
        };
    
        container.appendChild(input);
        container.appendChild(removeButton);
    }
 </script>