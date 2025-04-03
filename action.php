<?php 
    ob_start();
    session_start();
    include ('./utility.php');
    require_once './model/requestmodel.php';
    $utility = new request();
    
    //load the request table
    if (!empty($_POST['action']) && $_POST['action'] == 'requestTable' && !empty($_POST['section'])) {
        header('Content-Type: application/json');
        $section = $_POST['section'];
        $searchInput = $_POST['input'] ?? ''; 
        $searchBy = $_POST['filterby'] ?? '';

        $utility->getRequestList($section, $searchInput, $searchBy);
    }

    //load the hold request table
    if (!empty($_POST['action']) && $_POST['action'] == 'holdrequestTable' && !empty($_POST['section'])) {
        header('Content-Type: application/json');
        //$section = $_POST['section'];
        $searchInput = $_POST['input'] ?? ''; 
        $searchBy = $_POST['filterby'] ?? '';
        $utility->holdrequestList($searchInput, $searchBy);
    }

    //Load for approved request table
    if (!empty($_POST['action']) && $_POST['action'] == 'forApprovedTable' && !empty($_POST['section'])) {
        header('Content-Type: application/json');
        $section = $_POST['section'];
        $utility->forApprovedList($section);
    }

    //Add new request
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_add' && !empty($_SESSION['department'])) {
        header('Content-Type: application/json');
        try{
            $section = $_SESSION['department'];
            $itemname = $_POST['item_name'];
            $description = $_POST['item_description'];
            $purpose = $_POST['item_purpose'];
            $quantity = $_POST['item_quantity'];
            $uom = $_POST['item_uom'];
            $requestor = $_SESSION['name'];
            $status = "Pending Supervisor Approval";
            $controlnumber = $utility->generateControlNumber();
    
            $seperator = '---END---';
            $combinedFileData = '';
            if (!empty($_FILES['item_file']['tmp_name']) && count($_FILES['item_file']['tmp_name']) > 0) {
                foreach ($_FILES['item_file']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['item_file']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileContent = file_get_contents($tmpName);
                        $combinedFileData .= $fileContent . $seperator;
                    }
                }
            } else {
                throw new Exception("File upload is required.");
            } if (empty($itemname) || empty($description) || empty($purpose) ||
                empty($quantity) || empty($uom)) {
                throw new Exception("All fields required");
            }

            $successcount = 0;
            $errorcount = 0;
            foreach($itemname as $key => $itemnames){
                $itemdescription = $description[$key] ?? '';
                $itemquantity = $quantity[$key] ?? 0;
                $uom = $uom[$key] ?? '';
                $purpose = $purpose[$key] ?? '';
                
                $newrequest = new Quotation();
                $newrequest->request_id = $controlnumber;
                $newrequest->request_item = $itemnames;
                $newrequest->request_description = $itemdescription;
                $newrequest->request_purpose = $purpose;
                $newrequest->request_quantity = $itemquantity;
                $newrequest->quotation_file = $combinedFileData;
                $newrequest->uom = $uom;
                $newrequest->request_section = $section;
                $newrequest->requestor = $requestor;
                $newrequest->quotation_status = $status;
    
                $result = $utility->addRequest($newrequest);
    
                if ($result) {
                    $successcount++;
                }
                else{
                    $errorcount++;
                }
            }
           
            if ($successcount > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $successcount . ' ' . 'Items' . ' ' . 'with' . ' ' . $controlnumber . 'request # added successfully.'
                ]);
            }
            if ($errorcount > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $errorcount . $controlNumber . ' request(s) failed to add.',
                ]);
            }
        }
        catch(Exception $e){
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . htmlspecialchars($e->getMessage())
            ]);
        }
    }

    //View attachment
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_view' && !empty($_POST['id'])) {

        $id = isset($_POST['id']) ? $_POST['id'] : null;

        if ($id) {
            $request = $utility->GetAttachment($id); // Ensure GetAttachment returns an array

            echo '<div class="container-modal">';

            if (!empty($request)) {
                foreach ($request as $fileData) {
                    if (empty($fileData)) continue;

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($fileData);
                    $encodedFile = base64_encode($fileData);

                    if ($mimeType == 'application/pdf') {
                        echo "<iframe src='data:application/pdf;base64,$encodedFile' 
                                width='100%' height='100%' frameborder='0' 
                                style='border: 1px solid #ccc; margin-bottom: 10px;'></iframe>";
                    } elseif (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                        echo "<img src='data:$mimeType;base64,$encodedFile' style='max-width:100%; height:auto; margin-bottom:10px;'/>";
                    } else {
                        echo "<p class='text-danger text-center'>Unsupported attachment type</p>";
                    }
                }
            } else {
                echo "<p class='text-danger text-center'>No attachments found</p>";
            }

            echo '</div>';
        } else {
            echo "<p class='text-danger text-center'>Invalid request</p>";
        }

    }

    //View multiple attachment
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_viewmultiple' && !empty($_POST['id'])) {

        $id = isset($_POST['id']) ? $_POST['id'] : null;

        if ($id) {
            $request = $utility->GetMultipleAttachment($id); // Ensure GetAttachment returns an array

            echo '<div class="container-modal">';

            if (!empty($request)) {
                foreach ($request as $fileData) {
                    if (empty($fileData)) continue;

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($fileData);
                    $encodedFile = base64_encode($fileData);

                    if ($mimeType == 'application/pdf') {
                        echo "<iframe src='data:application/pdf;base64,$encodedFile' 
                                width='100%' height='100%' frameborder='0' 
                                style='border: 1px solid #ccc; margin-bottom: 10px;'></iframe>";
                    } elseif (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                        echo "<img src='data:$mimeType;base64,$encodedFile' style='max-width:100%; height:auto; margin-bottom:10px;'/>";
                    } else {
                        echo "<p class='text-danger text-center'>Unsupported attachment type</p>";
                    }
                }
            } else {
                echo "<p class='text-danger text-center'>No attachments found</p>";
            }

            echo '</div>';
        } else {
            echo "<p class='text-danger text-center'>Invalid request</p>";
        }

    }

    //View request information
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_detail' && !empty($_POST['id'])) {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if ($id) {
            $itemdetails = $utility->GetItemDetails($id);
            
            echo '<div class="card">';
            if (!empty($itemdetails)) {
                foreach($itemdetails as $item){
                    echo '<div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($item->request_item) . '</h5>
                    <p class="card-text"><strong>Description:</strong> ' . htmlspecialchars($item->request_description) . '</p>
                    <p class="card-text"><strong>Purpose:</strong> ' . htmlspecialchars($item->request_purpose) . '</p>
                    <p class="card-text"><strong>Quantity:</strong> ' . htmlspecialchars($item->request_quantity) . ' ' . htmlspecialchars($item->uom) . '</p>
                    <p class="card-text"><strong>Remarks:</strong> ' . htmlspecialchars($item->quotation_remarks) . '</p>
                    <p class="card-text"><strong>Requestor:</strong> ' . htmlspecialchars($item->requestor) . '</p>
                  </div>';

                }
            }
            else{
                echo '<div class="card-body"><p class="text-danger">No item details found.</p></div>';
            }
            echo '</div>';
            echo '</div>';
        } 
    }

    //Edit Item Information
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_edit' && !empty($_POST['id'])) {
        header('Content-Type: application/json');

        try{
            $itemname = $_POST['item_name'];
            $description = $_POST['item_description'];
            $purpose = $_POST['item_purpose'];
            $quantity = $_POST['item_quantity'];
            $uom = $_POST['item_uom'];
            $id = $_POST['id'];
            
            $seperator = '---END---';
                $combinedFileData = '';
                if (!empty($_FILES['item_file']['tmp_name']) && count($_FILES['item_file']['tmp_name']) > 0) {
                    foreach ($_FILES['item_file']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['item_file']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileContent = file_get_contents($tmpName);
                            $combinedFileData .= $fileContent . $seperator;
                        }
                    }
                } else {
                    throw new Exception("File upload is required.");
                }
                if (empty($itemname) || empty($description) || empty($purpose) ||
                    empty($quantity) || empty($uom)) {
                    throw new Exception("All fields required");
                }
    
                $request = new Quotation();
                $request->request_item = $itemname;
                $request->request_description = $description;
                $request->request_purpose = $purpose;
                $request->request_quantity = $quantity;
                $request->uom = $uom;
                $request->quotation_file = $combinedFileData;
    
                $result = $utility->EditRequestItem($request, $id);
    
                if ($result) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Request successfully updated.'
                    ]);
                } else{
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Request change failed.'
                    ]);
                }
        }
        catch(Exception $e){
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . htmlspecialchars($e->getMessage())
            ]);
        }
        
    }

    //Delete Item Request
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_delete' && !empty($_POST['id'])) {
        header('Content-Type: application/json');
        $id = $_POST['id'];
        try{
            $result = $utility->DeleteRequestItem($id);
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Item successfully deleted.'
                ]);
            } else{
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Deleting item failed.'
                ]);
            }
        }
        catch(Exception $e){
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . htmlspecialchars($e->getMessage())
            ]);
        }
    }

    //View request information
    if (!empty($_POST['action']) && $_POST['action'] == 'btn_requestdetail' && !empty($_POST['id'])) {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if ($id) {
            $itemdetails = $utility->Getrequestdetatils($id);
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered" id ="item-details">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th>Item Name</th>';
            echo '<th>Description</th>';
            echo '<th>Purpose</th>';
            echo '<th>Quantity</th>';
            echo '<th>UOM</th>';
            echo '<th>Remarks</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
    
            if (!empty($itemdetails['items'])) {
                foreach ($itemdetails['items'] as $item) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['description']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['purpose']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['uom']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['remarks']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="12" class="text-center text-danger">No item details found.</td></tr>';
            }
    
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } 
    }

    //Update status section request by section head
    if (!empty($_POST['action']) && $_POST['action'] == "btn_approved" && !empty($_POST['id']) && !empty($_POST['status'])) {
        header('Content-Type: application/json');
        $controlNumber = isset($_POST['id']) ? ($_POST['id']) : null;
        $status = isset($_POST['status']) ? ($_POST['status']) : null;

        if (empty($controlNumber)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or empty control number.'
            ]);
        }

        $result = $utility->UpdateStatusSectionRequest($controlNumber, $status);
        if ($result) {

            if ($status == "Disapproved Request") {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Request: ' . $controlNumber . ' successfully disapproved by the section head.'
                ]);
            }
            else{
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Request: ' . $controlNumber . ' successfully approved by the section head.'
                ]);
            }
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Request update failed. No changes made.'
            ]);
        }
    }

     //load the request table
    if (!empty($_POST['action']) && $_POST['action'] == 'forverifyTable') {
        header('Content-Type: application/json');
        //$section = $_POST['section'];
        $searchInput = $_POST['input'] ?? ''; 
        $searchBy = $_POST['filterby'] ?? '';

        $utility->getverifyList($searchInput, $searchBy);
    }



   //load the request table
   if (!empty($_POST['action']) && $_POST['action'] == 'emailtable') {
        header('Content-Type: application/json');
        //$section = $_POST['section'];
        $searchInput = $_POST['input'] ?? ''; 
        $searchBy = $_POST['filterby'] ?? '';

        $utility->getemailList($searchInput, $searchBy);
    }
    

        //Send email to supplier
        $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data['action']) && $data['action'] == 'btn_sendemail') {
        header('Content-Type: application/json');
        $recipient = isset($data['recipients']) ? $data['recipients'] : [];
        $cc = isset($data['cc']) ? $data['cc'] : [];
        $bcc = isset($data['bcc']) ? $data['bcc'] : [];
       // $tableData = isset($data['tableData']) ? $data['tableData'] : [];
        $to = implode("," , $recipient);
        $ccEmails = implode("," , $cc);
        $bccEmails = implode("," , $bcc);
        $id = isset($data['fileId']) ? $data['fileId'] : null;
        $controlNumber = isset($data['controlid']) ? $data['controlid'] : null;
        $status = isset($data['status']) ? $data['status'] : null;
        $itemdetails = $utility->Getrequestdetatils($controlNumber);

        $tableHtml = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                    <thead style='background-color: #333; color: #fff;'>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>UOM</th>
                    </tr>
                    </thead>
                    <tbody>";

        foreach($itemdetails['items'] as $row){
            $tableHtml .= "
                <tr>
                    <td>{$row['item_name']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['uom']}</td>
                </tr>
            ";
        }
        $tableHtml .= "</tbody>";
        $tableHtml .= "</table>";

        $subject = "Request for Quotation";
        $body = "<p>Dear Supplier,</p>
             <p>I hope this email finds you well. We would like to request a quotation for the following items:</p>" 
            . $tableHtml . 
            "<p>Please provide us with the following details in your quotation:</p>
             <ul>
                 <li>Unit price and total price</li>
                 <li>Payment terms</li>
                 <li>Lead time and delivery schedule</li>
                 <li>Availability of stock</li>
                 <li>Warranty (if applicable)</li>
             </ul>
             <p>Should you require any further information, please feel free to reach out.</p>
             
             <p>Looking forward to your prompt response.<br>
                Kindly see the attached file for reference.</p>

             <p>Best regards,</p>
             <p>Nidec Instruments Philippines Corporation</p>

             <p><strong>Note:</strong> This is an auto-generated email. Please do not reply directly to this email. Kindly send your response to <a href='mailto:regine.guellena@nidec.com'>regine.guellena@nidec.com</a>.</p>";



        $result = $utility->sendEmailtoSupplier($recipient, $cc, $bcc, $subject, $body, $id);
       //$result = $utility->UpdateStatusSectionRequest($controlNumber, $status);
        if ($result) {
            $result = $utility->UpdateStatusSectionRequest($controlNumber, $status);
            echo json_encode([
                'status' => 'success',
                'message' => 'Email sent to supplier.'
            ]);
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Email failed to send.'
            ]);
        }
    }

    //Auto suggest control number
    if (!empty($_POST['action']) && $_POST['action'] == 'getcontrolnumber') {
        header('Content-Type: application/json');
        $section = $_POST['section'];
        $result = $utility->GetControlNumberList($section);
        echo $result;
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'create') {
        header('Content-Type: application/json');
        $controlNumber = isset($_POST['controlnumber']) ? $_POST['controlnumber'] : null;
        $result = $utility->GetItemList($controlNumber);
        echo json_encode($result);
    }
    
    if (!empty($_POST['action']) && $_POST['action'] == 'addcomparison') {
        $suppliers = isset($_POST['supplierData'] ) ? $_POST['supplierData'] : [];
        $receipient = isset($_POST['section']) ? $_POST['section'] : null;
        $cc = isset($_SESSION['section']) ? $_SESSION['section'] : null;
        $controlNumber = isset($_POST['controlnumber']) ? $_POST['controlnumber'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $emailrecipient[] = $utility->FindEmailAddress($section);
        $emailcc[] = $utility->FindEmailAddress($cc);

        $subject = "Request for Quotation Status Update";
        $body = "
                <p><strong>Control Number: </strong>$controlNumber</p>
                <p><strong>Section: </strong>$section</p>
                <p><strong>Status: </strong>$status</p>
                ";
        $result = $utility->SaveSupplierData($suppliers);

        if ($result) {

            $emailresult = $utility->sendEmailNotification($emailrecipient, $emailcc, $subject, $body);
            echo json_encode([
                'status' => 'success',
                'message' => 'Comparison successfully added.'
            ]);
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Comparison failed to add.'
            ]);
        }
    }

     //load the request table
    if (!empty($_POST['action']) && $_POST['action'] == 'comparisontable') {
        header('Content-Type: application/json');
       // $section = $_POST['section'];
        $searchInput = $_POST['input'] ?? ''; 
        $searchBy = $_POST['filterby'] ?? '';
        $role = $_POST['role'] ?? '';   

        $utility->GetComparisonList($searchInput, $searchBy, $role);
    }

     //View request information
     if (!empty($_POST['action']) && $_POST['action'] == 'btn_comparisondetails' && !empty($_POST['id'])) {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $currency = isset($_POST['currency']) ? $_POST['currency'] : null;
        $utility->GetComparisonData($id, $currency);
    } 
    
    if (!empty($_POST['action']) && $_POST['action'] == 'delete' && !empty($_POST['controlnumber'])) {
        header('Content-Type: application/json');
        $controlNumber = isset($_POST['controlnumber']) ? $_POST['controlnumber'] : null;
        $result = $utility->DeleteComparisonItem($controlNumber);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Comparison successfully deleted.'
            ]);
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Comparison failed to delete.'
            ]);
        }
    }
    
    if (!empty($_POST['action'] && $_POST['action'] == 'btn_verify')) {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        $emailrecipient = $utility->FindEmailAddress($section);
        $emailcc = [
            "regine.guellena@nidec.com"
        ];

        $subject = "Request for Quotation Status Update";
        $body = "
                <p><strong>Control Number: </strong>$id</p>
                <p><strong>Section: </strong>$section</p>
                <p><strong>Status: </strong>$status</p>
                ";

        if (empty($id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or empty control number.'
            ]);
        }

        $result =$utility->UpdateStatusSectionRequest($id, $status);
        if ($result) {
            $emailresult = $utility->sendEmailNotification($emailrecipient, $emailcc, $subject, $body);
            echo json_encode([
                'status' => 'success',
                'message' => 'Successfuly updated.'
            ]);
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Update failed.'
            ]);
        }

    }

    //Approved Comparison data by section head
    if (!empty($_POST['action']) && $_POST['action'] == 'updatestatus' && !empty($_POST['id']) && !empty($_POST['status'])) {
        header('Content-Type: application/json');
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $controlNumber = isset($_POST['id']) ? $_POST['id'] : null;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        $cc = isset($_SESSION['department']) ? $_SESSION['department'] : null;

      
        $emailrecipient = $utility->FindEmailAddress($section);
        $emailcc = [
            "regine.guellena@nidec.com"
        ];

        $subject = "Request for Quotation Status Update";
        $body = "
                <p><strong>Control Number: </strong>$controlNumber</p>
                <p><strong>Section: </strong>$section</p>
                <p><strong>Status: </strong>$status</p>
                ";
        $result = $utility->UpdateComparisonStatus($controlNumber, $status, $remarks);
        
       
        if ($result) {
                $update = $utility->UpdateStatusSectionRequest($controlNumber, $status, $remarks);
                $emailresult = $utility->sendEmailNotification($emailrecipient, $emailcc, $subject, $body);

                echo json_encode([
                    'status' => 'success'
                ]);
        }
        else{
            echo json_encode([
                'status' => 'error',
                'message' => 'Comparison status update failed.',
            ]);
        }
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'getstatuscount') {
        header('Content-Type: application/json');
        
        // Check if timeRange is set, otherwise set it to 'all' (new default case)
        $timeRange = isset($_POST['timeRange']) ? $_POST['timeRange'] : 'all';
    
        $currentTime = date('Y-m-d H:i:s');
        $startTime = null; // Default to NULL to retrieve all data
    
        // Determine the start time based on the selected time range
        switch ($timeRange) {
            case '24h':
                $startTime = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($currentTime)));
                break;
            case '7d':
                $startTime = date('Y-m-d H:i:s', strtotime('-7 days', strtotime($currentTime)));
                break;
            case '30d':
                $startTime = date('Y-m-d H:i:s', strtotime('-30 days', strtotime($currentTime)));
                break;
            case 'all': // New case for retrieving all data
            default:
                $startTime = null; // Pass NULL to function to retrieve all records
                break;
        }
    
        echo $utility->getRequestCount($startTime);
    }

    if (!empty($_POST['action']) && $_POST['action'] == 'updateStatusChart') {
        header('Content-Type: application/json');
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        echo $utility->getRequestsectionCount($section);
    }
    
?>