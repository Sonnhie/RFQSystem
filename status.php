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
        <div class="col-lg-12">
            <div class="card card-default rounded-0 shadow">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                            <h4 class="card-title">Section Request List</h4>
                        </div>
                    </div>
                    <div class="clear:both"></div>
                </div>
                <div class="card-body">
                <input type="hidden" name="section" id="section" value="<?php echo  $section; ?>">
					<div class="row">
						<div class="col-sm-12 table-responsive">
							<table id="forApprovedTable" class="table table-hover">
								<thead>
									<tr>		
										<th>Control Number</th>						
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


<script src="./js/status.js"></script>