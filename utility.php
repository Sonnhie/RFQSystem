<?php
    include(__DIR__ . '/./model/requestmodel.php');
    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';


    use Dotenv\Dotenv;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    class request{
        private $host = "localhost";
        private $user = "root";
        private $password = "";
        private $database = "db_rfq";
        private $userTable = "rfq_user";
        private $requestTable = "rfq_request";
        private $supplierTable = "rfq_supplier";
        private $statusTable = "rfq_status";
        private $emailList = "rfq_emailadd";
        private $comparisonTable = "rfq_comparison";
        private $dbconnect;

        public function __construct() {
            $this->connectDB();
        }

        private function connectDB(){
            $this->dbconnect = new mysqli($this->host, $this->user, $this->password, $this->database);
            if($this->dbconnect->connect_error){
                die("❌ Connection failed: " . $this->dbconnect->connect_error);
            }
        }

        public function getConnection(){
            return $this->dbconnect;
        }
        
        public function login($email, $pwd){
            
            $sqlquery = "select * from " . $this->userTable . " where email = ? and password = ?";
            $stmt = $this->getConnection()->prepare($sqlquery);

            if (!$stmt) {
                die("❌ Prepare failed: (" . $this->getConnection()->errno . ") " . $this->getConnection()->error);
            }
            $stmt->bind_param("ss", $email, $pwd);
            $stmt->execute();

            $result = $stmt->get_result();
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                return $row;
            }else{
                return false;
            }
        }

        public function checkLogin(){
            if($_SESSION['email'] == ''){
                header("Location: login.php");
            }
        }

        public function getRequestList($section, $searchinput, $searchby) {
            if (empty($section)) {
                echo json_encode(["status" => "error", "message" => "Section undefined."]);
                return;
            }
        
            $status = "Disapproved Request";
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;
        
            // Base queries
            $countQuery = "SELECT COUNT(*) as total FROM rfq_request WHERE section = ? AND status <> ?";
            $sqlquery = "SELECT id, controlnumber, status, itemname, description, purpose, quantity, unitofquantity, date_requested 
                         FROM rfq_request WHERE section = ? AND status <> ?";
        
            // Append search filter
            if (!empty($searchinput)) {
                if ($searchby == "date") {
                    $countQuery .= " AND date_requested LIKE ?";
                    $sqlquery .= " AND date_requested LIKE ?";
                } elseif ($searchby == "itemname") {
                    $countQuery .= " AND itemname LIKE ?";
                    $sqlquery .= " AND itemname LIKE ?";
                } elseif ($searchby == "controlnumber") {
                    $countQuery .= " AND controlnumber LIKE ?";
                    $sqlquery .= " AND controlnumber LIKE ?";
                }elseif ($searchby == "status") {
                    $countQuery .= " AND status LIKE ?";
                    $sqlquery .= " AND status LIKE ?";
                }
                
            }
        
            $sqlquery .= " LIMIT ? OFFSET ?"; // ✅ Properly structured query
        
            // **1️⃣ Prepare the COUNT Query**
            $stmt = $this->getConnection()->prepare($countQuery);
        
            // **2️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $searchinput = "%$searchinput%"; // Add wildcard for LIKE
                $stmt->bind_param("sss", $section, $status, $searchinput);
            } else {
                $stmt->bind_param("ss", $section, $status);
            }
        
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $limit);
        
            // **3️⃣ Prepare the MAIN Query**
            $stmt = $this->getConnection()->prepare($sqlquery);
        
            // **4️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $stmt->bind_param("sssii", $section, $status, $searchinput, $limit, $offset);
            } else {
                $stmt->bind_param("ssii", $section, $status, $limit, $offset);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            // **5️⃣ Return JSON Response**
            if ($result->num_rows > 0) {
                $requestArray = [];
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success",
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records found."]);
            }
        }
             
        public function addRequest(Quotation $newrequest){
            try{
                $this->getConnection()->begin_transaction();
                $sql = "insert into " . $this->requestTable . "(controlnumber, itemname, description, purpose, attachment, 
                quantity, unitofquantity, section, requestor, status) values (?,?,?,?,?,?,?,?,?,?)";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bind_param(
                    "ssssbissss",
                    $newrequest->request_id,
                    $newrequest->request_item,
                    $newrequest->request_description,
                    $newrequest->request_purpose,
                    $newrequest->quotation_file,
                    $newrequest->request_quantity,
                    $newrequest->uom,
                    $newrequest->request_section,
                    $newrequest->requestor,
                    $newrequest->quotation_status
                );
                $stmt->send_long_data(4, $newrequest->quotation_file);
                if ($stmt->execute()) {
                    $this->getConnection()->commit();
                    return true;
                }
                else{
                    $this->getConnection()->rollback();
                    return false;
                }
            }
            catch(Exception $e){
                $this->getConnection()->rollback();
                error_log($e->getMessage());
                return false;
            }
        }

        public function generateControlNumber(){
            $date = date('Ymd');
            $currentMonth = date('Ym');

            $sql = "select controlnumber, date_requested 
                    from " . $this->requestTable . "
                    where DATE_FORMAT(date_requested, '%Y%m') = '$currentMonth' 
                    order by date_requested desc 
                    limit 1";
            $lastId = 0;
            $result = $this->getConnection()->query($sql);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $lastId = isset($row['controlnumber']) ? 
                intval(substr($row['controlnumber'], -4)) : 0;
            }
            $newID = $lastId + 1;
            $controlID = "RFQ-" . $date . "-" . str_pad($newID, 4, "0", STR_PAD_LEFT);
            return $controlID;
        }

        public function GetAttachment($id){
            
            $attachment = '';
            $sql = "select attachment from " . $this->requestTable . " where id = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($attachment);
            $stmt->fetch();

            $separator = '---END---';
            $files = explode($separator, $attachment);
            
            return $files;
        }

        public function GetallAttachment($id){
            
            $attachment = '';
            $sql = "select attachment from " . $this->requestTable . " where controlnumber = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($attachment);
            $stmt->fetch();

            $separator = '---END---';
            $files = explode($separator, $attachment);
            
            return $files;
        }

        public function GetItemDetails($id){
            $sql = "select * from " . $this->requestTable . " where id = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $itemDetail = [];

            while ($row = $result->fetch_assoc()) {
                $item = new Quotation();
                $item->request_id = $row['controlnumber'];
                $item->request_item = $row['itemname'];
                $item->request_description = $row['description'];
                $item->request_purpose = $row['purpose'];
                $item->request_quantity = $row['quantity'];
                $item->uom = $row['unitofquantity'];
                $item->request_section = $row['section'];
                $item->quotation_date = $row['date_requested'];
                $item->requestor = $row['requestor'];
                $item->quotation_remarks = $row['remarks'];

                $itemDetail[] = $item;
            }
            return $itemDetail;
        }

        public function EditRequestItem(Quotation $request , $id){
        
            try{
                $this->getConnection()->begin_transaction();
                $sql = "update " . $this->requestTable . 
                " set itemname = ?, 
                      description = ?, 
                      purpose = ?, 
                      attachment = ?, 
                      quantity = ?, 
                      unitofquantity = ? where id = ?";
                $stmt = $this->getConnection()->prepare($sql);
                if ($stmt) {
                        $stmt->bind_param("sssbisi",
                        $request->request_item,
                        $request->request_description,
                        $request->request_purpose,
                        $request->quotation_file,
                        $request->request_quantity,
                        $request->uom,
                        $id  
                    );

                    $stmt->send_long_data(3, $request->quotation_file);

                    if ($stmt->execute()==='false') {
                        $this->getConnection()->rollback();
                        die("Failed to execute the sql statement: " . $stmt->error);
                    }

                    if ($stmt->affected_rows > 0) {
                        $this->getConnection()->commit();
                        return true;
                    }
                    else{
                        $this->getConnection()->rollback();
                        return false;
                    }
                }
                else{
                    $this->getConnection()->rollback();
                    return false;
                }
            }
            catch(Exception $e){
                $this->getConnection()->rollback();
                error_log($e->getMessage());
                return false;
            }
        }

        public function DeleteRequestItem($id){
            try{
                $this->getConnection()->begin_transaction();
                $sql = "delete from " 
                       . $this->requestTable . 
                       " where id = ?";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $this->getConnection()->commit();
                    return true;
                }
                else{
                    $this->getConnection()->rollback();
                    return false;
                }
            }
            catch(Exception $e){
                $this->getConnection()->rollback();
                return false;
            }
        }

        public function forApprovedList($section) {

            if (empty($section)) {
                echo json_encode(["status" => "error", "message" => "Section undefined."]);
                return;
            }
            $status = "Pending Supervisor Approval";
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10; // Default: 10 per page
            $offset = ($page - 1) * $limit;

            //Get total count of records
            $countQuery = "SELECT COUNT(distinct controlnumber) as total FROM rfq_request WHERE section = ? AND status = ?";
            $stmt = $this->getConnection()->prepare($countQuery);
            $stmt->bind_param("ss", $section, $status);
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $limit);

            //Get records
            $sqlquery = "SELECT distinct controlnumber, status, date_requested FROM " . $this->requestTable .  " WHERE section = ? AND status = ? Limit ? OFFSET ?";
            $stmt = $this->getConnection()->prepare($sqlquery);
            $stmt->bind_param("ssii", $section, $status, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                $requestArray = array();
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success", 
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No record found."]);
            }
        }

        public function Getrequestdetatils($id){
            $sql = "select * from " . $this->requestTable . " where controlnumber = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $requestDetails = [];
            $items = [];

            while ($row = $result->fetch_assoc()) {
                $items[] = [
                    "item_name" => $row['itemname'],
                    "description" => $row['description'],
                    "purpose" => $row['purpose'],
                    "quantity" => $row['quantity'],
                    "uom" => $row['unitofquantity'],
                    "remarks" => $row['remarks']
                ];

                // Store common request details only once
                if (empty($requestDetails)) {
                    $requestDetails = [
                        "controlnumber" => $row['controlnumber'],
                        "section" => $row['section'],
                        "date_requested" => $row['date_requested'],
                        "requestor" => $row['requestor'],
                        "remarks" => $row['remarks'],
                        "items" => [] // Placeholder for items
                    ];
                }
            }
            // Add items to the main request details
            $requestDetails["items"] = $items;

            return $requestDetails;
        }

        //get multiple attachment
        public function GetMultipleAttachment($id){
            
            $attachment = '';
            $sql = "select attachment from " . $this->requestTable . " where controlnumber = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($attachment);
            $stmt->fetch();

            $separator = '---END---';
            $files = explode($separator, $attachment);
            
            return $files;
        }

        public function UpdateStatusSectionRequest($id, $status, $remarks = null){
            try {
                // Validate input
                if (empty($id)) {
                    return;
                }
        
                // Start transaction
                $this->getConnection()->begin_transaction();
                
                //$status = "For Procurement Verification";
                $sql = "UPDATE " . $this->requestTable . " SET status = ?, remarks = ? WHERE controlnumber = ?";
                $stmt = $this->getConnection()->prepare($sql);
        
                if (!$stmt) {
                    return false;
                }
        
                // Bind parameters and execute
                $stmt->bind_param("sss", $status, $remarks, $id);
                $stmt->execute();
        
                if ($stmt->affected_rows > 0) {
                    // Commit transaction on success
                    $this->getConnection()->commit();
                    return true;
                } else {
                    // No rows were updated
                    $this->getConnection()->rollback();
                    return false;
                }
            } catch (Exception $e) {
                // Rollback on error
                $this->getConnection()->rollback();
                error_log($e->getMessage());
                return false;
            }
        }     
        
        public function getverifyList($searchinput, $searchby) {

            $status1 = "For Procurement Verification";
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;
        
            // Base queries
            $countQuery = "SELECT COUNT(*) as total FROM rfq_request WHERE status = ?";
            $sqlquery = "SELECT id, controlnumber, section, status, itemname, description, purpose, quantity, unitofquantity, date_requested 
                         FROM rfq_request WHERE status = ?";
        
            // Append search filter
            if (!empty($searchinput)) {
                if ($searchby == "date") {
                    $countQuery .= " AND date_requested LIKE ?";
                    $sqlquery .= " AND date_requested LIKE ?";
                } elseif ($searchby == "itemname") {
                    $countQuery .= " AND itemname LIKE ?";
                    $sqlquery .= " AND itemname LIKE ?";
                } elseif ($searchby == "controlnumber") {
                    $countQuery .= " AND controlnumber LIKE ?";
                    $sqlquery .= " AND controlnumber LIKE ?";
                }elseif ($searchby == "status") {
                    $countQuery .= " AND status LIKE ?";
                    $sqlquery .= " AND status LIKE ?";
                }elseif ($searchby == "section") {
                    $countQuery .= " AND section LIKE ?";
                    $sqlquery .= " AND section LIKE ?";
                }
                
                
            }
        
            $sqlquery .= " GROUP BY controlnumber LIMIT ? OFFSET ? "; // ✅ Properly structured query
        
            // **1️⃣ Prepare the COUNT Query**
            $stmt = $this->getConnection()->prepare($countQuery);
        
            // **2️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $searchinput = "%$searchinput%"; // Add wildcard for LIKE
                $stmt->bind_param("ss", $status1, $searchinput);
            } else {
                $stmt->bind_param("s", $status1);
            }
        
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $limit);
        
            // **3️⃣ Prepare the MAIN Query**
            $stmt = $this->getConnection()->prepare($sqlquery);
        
            // **4️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $stmt->bind_param("ssii", $status1, $searchinput, $limit, $offset);
            } else {
                $stmt->bind_param("sii", $status1, $limit, $offset);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            // **5️⃣ Return JSON Response**
            if ($result->num_rows > 0) {
                $requestArray = [];
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success",
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records found."]);
            }
        }

        public function holdrequestList($searchinput, $searchby) {

            $status1 = "Hold request by Procurement";
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;
        
            // Base queries
            $countQuery = "SELECT COUNT(*) as total FROM rfq_request WHERE status = ?";
            $sqlquery = "SELECT id, controlnumber, section, status, itemname, description, purpose, quantity, unitofquantity, date_requested, remarks 
                         FROM rfq_request WHERE status = ?";
        
            // Append search filter
            if (!empty($searchinput)) {
                if ($searchby == "date") {
                    $countQuery .= " AND date_requested LIKE ?";
                    $sqlquery .= " AND date_requested LIKE ?";
                } elseif ($searchby == "itemname") {
                    $countQuery .= " AND itemname LIKE ?";
                    $sqlquery .= " AND itemname LIKE ?";
                } elseif ($searchby == "controlnumber") {
                    $countQuery .= " AND controlnumber LIKE ?";
                    $sqlquery .= " AND controlnumber LIKE ?";
                }elseif ($searchby == "status") {
                    $countQuery .= " AND status LIKE ?";
                    $sqlquery .= " AND status LIKE ?";
                }elseif ($searchby == "section") {
                    $countQuery .= " AND section LIKE ?";
                    $sqlquery .= " AND section LIKE ?";
                }
                
                
            }
        
            $sqlquery .= " LIMIT ? OFFSET ?"; // ✅ Properly structured query
        
            // **1️⃣ Prepare the COUNT Query**
            $stmt = $this->getConnection()->prepare($countQuery);
        
            // **2️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $searchinput = "%$searchinput%"; // Add wildcard for LIKE
                $stmt->bind_param("ss", $status1, $searchinput);
            } else {
                $stmt->bind_param("s", $status1);
            }
        
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $limit);
        
            // **3️⃣ Prepare the MAIN Query**
            $stmt = $this->getConnection()->prepare($sqlquery);
        
            // **4️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $stmt->bind_param("ssii", $status1, $searchinput, $limit, $offset);
            } else {
                $stmt->bind_param("sii", $status1, $limit, $offset);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            // **5️⃣ Return JSON Response**
            if ($result->num_rows > 0) {
                $requestArray = [];
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success",
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records found."]);
            }
        }
        
        public function getemailList($searchinput, $searchby) {

            $status1 = "Verified by Procurement";
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;
        
            // Base queries
            $countQuery = "SELECT COUNT(*) as total FROM rfq_request WHERE status = ?";
            $sqlquery = "SELECT id, controlnumber, section, status, itemname, description, purpose, quantity, unitofquantity, date_requested 
                         FROM rfq_request WHERE status = ?";
        
            // Append search filter
            if (!empty($searchinput)) {
                if ($searchby == "date") {
                    $countQuery .= " AND date_requested LIKE ?";
                    $sqlquery .= " AND date_requested LIKE ?";
                } elseif ($searchby == "itemname") {
                    $countQuery .= " AND itemname LIKE ?";
                    $sqlquery .= " AND itemname LIKE ?";
                } elseif ($searchby == "controlnumber") {
                    $countQuery .= " AND controlnumber LIKE ?";
                    $sqlquery .= " AND controlnumber LIKE ?";
                }elseif ($searchby == "status") {
                    $countQuery .= " AND status LIKE ?";
                    $sqlquery .= " AND status LIKE ?";
                }elseif ($searchby == "section") {
                    $countQuery .= " AND section LIKE ?";
                    $sqlquery .= " AND section LIKE ?";
                }
                
                
            }
        
            $sqlquery .= " GROUP BY controlnumber LIMIT ? OFFSET ? "; // ✅ Properly structured query
        
            // **1️⃣ Prepare the COUNT Query**
            $stmt = $this->getConnection()->prepare($countQuery);
        
            // **2️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $searchinput = "%$searchinput%"; // Add wildcard for LIKE
                $stmt->bind_param("ss", $status1, $searchinput);
            } else {
                $stmt->bind_param("s", $status1);
            }
        
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $limit);
        
            // **3️⃣ Prepare the MAIN Query**
            $stmt = $this->getConnection()->prepare($sqlquery);
        
            // **4️⃣ Bind parameters dynamically**
            if (!empty($searchinput)) {
                $stmt->bind_param("ssii", $status1, $searchinput, $limit, $offset);
            } else {
                $stmt->bind_param("sii", $status1, $limit, $offset);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            // **5️⃣ Return JSON Response**
            if ($result->num_rows > 0) {
                $requestArray = [];
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success",
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records found."]);
            }
        }

        public function supplierEmailList(){
            try{
                $this->getConnection()->begin_transaction();

                $sql = "select email, suppliername from " . $this->supplierTable;
                $stmt = $this->getConnection()->prepare($sql);
                
                if ($stmt) {
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $supplieremail = [];

                    while ($row = $result->fetch_assoc()) {
                        $this->getConnection()->commit();
                        $supplieremail[] = [
                            'suppliername' => $row['suppliername'],
                            'email' => $row['email']
                        ];
                    }
                    return json_encode($supplieremail);
                }
                else{
                    $this->getConnection()->rollback();
                    return json_encode([
                        'message' => 'error'
                    ]);
                }
            }
            catch(Exception $e){
                $this->getConnection()->rollback();
                return json_encode([
                    'message' => 'error message: ' . error_log($e->getMessage()) 
                ]);
            }
        }

        public function sendEmailtoSupplier(array $recipient, array $cc = [], array $bcc = [], $subject, $body, $id){
            if (empty($recipient)) {
                return false;
            }

            $attachments = $this->GetAttachment($id);
            $mail = new PHPMailer(true);
            try{
                //$mail->SMTPDebug = 3;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username = 'nidecrfq.noreply@gmail.com';
                $mail->Password = 'xyprohexdaphtvrj';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = '587';

                $mail->setFrom('nidecrfq.noreply@gmail.com', 'Nidec Instruments Philippines Inc. (RFQ System)');

                foreach($recipient as $emails){
                    $mail->addAddress($emails);                   
                }  

                foreach($cc as $ccrecipient){
                    $mail->addCC($ccrecipient);
                }
                   
                foreach($bcc as $bccrecipient){
                    $mail->addBCC($bccrecipient);
                }

                foreach ($attachments as $index => $fileData) {
                    if (empty($fileData)) {
                        continue;
                    }
        
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($fileData);
        
                    // Assign a default filename since it's not stored in the DB
                    $filename = "attachment_" . ($index + 1) . ".jpeg";  // Change .bin to expected file type if possible
        
                    $mail->addStringAttachment($fileData, $filename, 'base64', $mimeType);
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->send();

                return true;  
            }
            catch(Exception){
                error_log("Mail error: " . $mail->ErrorInfo);
                return false;
            }
        }
        
        public function GetControlNumberList($section){
            try{
                $status = "Request Quotation sent to supplier";
                $sql = "select controlnumber from " . $this->requestTable . " where status = ?";

                if (!empty($section)) {
                    if ($section == "IT") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "HRGA") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Injection") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "2nd Process") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "PCD") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Procurement") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Accounting") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Facility") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Sales") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Machine Maintenance") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Safety&Health") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "QA/QC") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Mold Maintenance") {
                        $sql .= " and section =  ?";
                    }
                    elseif ($section == "Technical") {
                        $sql .= " and section =  ?";
                    }
                }

                $sql .= " group by controlnumber";

                if (!empty($section)) {
                    $stmt = $this->getConnection()->prepare($sql);
                    $stmt->bind_param("ss", $status, $section);
                }
                else{
                    $stmt = $this->getConnection()->prepare($sql);
                    $stmt->bind_param("s", $status);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                $controlnumber = [];

                while ($row = $result->fetch_assoc()) {
                    $controlnumber[] = ['control_number' => $row['controlnumber']];
                }
                return json_encode($controlnumber);
            }
            catch(Exception $e){
                return false;
            }
        }

        public function GetItemList($controlnumber){
            $sql = "select * from " . $this->requestTable . " where controlnumber = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("s", $controlnumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $itemDetail = [];

            while($row= $result->fetch_assoc()){
                $itemDetail[] = [
                    'id' => $row['id'],
                    'itemname' => $row['itemname'],
                    'description' => $row['description'],
                    'purpose' => $row['purpose'],
                    'quantity' => $row['quantity'],
                    'unitofquantity' => $row['unitofquantity'],
                    'section' => $row['section']
                ];
            }
            return $itemDetail;
        }

        public function SaveSupplierData($supplierDataArray){
            try {
                $this->getConnection()->begin_transaction();
                $sql = "INSERT INTO " . $this->comparisonTable . " (controlnumber, itemname, section, suppliername, quantity, unitprice, discount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->getConnection()->prepare($sql);
        
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $this->getConnection()->error);
                }
        
                foreach ($supplierDataArray as $data) {
                    // Ensure correct data binding
                    //$id = $data['id'];
                    $controlnumber = $data['controlnumber'];
                    $itemname = $data['itemname'];
                    $suppliername = $data['suppliername'];
                    $quantity = $data['quantity'];
                    $unitprice = $data['itemprice'];
                    $discount = $data['discount'];
                    $section = $data['section'];
                    $status = "For Verification";
        
                    $stmt->bind_param("ssssddds", $controlnumber, $itemname, $section, $suppliername, $quantity, $unitprice, $discount, $status);
                    $stmt->execute();
        
                    if ($stmt->affected_rows <= 0) {
                        throw new Exception("Insert failed for: " . json_encode($data));
                    }
                }
        
                $this->getConnection()->commit();
                return true;
            } catch (Exception $e) {
                $this->getConnection()->rollback();
                error_log("Error in SaveSupplierData: " . $e->getMessage()); // Log error for debugging
                return false;
            }
        }
        
        public function GetComparisonList($searchinput, $searchby, $role) {
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;
        
            // Base queries
            $countQuery = "SELECT COUNT(*) as total FROM " . $this->comparisonTable;
            $sqlquery = "SELECT id, controlnumber, section, status, remarks, datecreate 
                         FROM " . $this->comparisonTable;
        
            $whereClauses = [];
            $params = [];
            $types = "";
        
            // Role-based filtering
            if (!empty($role)) {
                if ($role == "Requestor") {
                    $whereClauses[] = "status = 'Approved Comparison Data by Procurement Section'";
                } elseif ($role == "Section Approval") {
                    $whereClauses[] = "status = 'For Verification'";
                }
            }
        
            // Search filtering
            if (!empty($searchinput)) {
                $searchinput = "%$searchinput%"; // Add wildcard for LIKE
                if ($searchby == "date") {
                    $whereClauses[] = "datecreate LIKE ?";
                } elseif ($searchby == "controlnumber") {
                    $whereClauses[] = "controlnumber LIKE ?";
                } elseif ($searchby == "status") {
                    $whereClauses[] = "status LIKE ?";
                } elseif ($searchby == "section") {
                    $whereClauses[] = "section LIKE ?";
                }
                $params[] = $searchinput;
                $types .= "s"; // String parameter
            }
        
            // Construct WHERE clause
            if (!empty($whereClauses)) {
                $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
                $sqlquery .= " WHERE " . implode(" AND ", $whereClauses);
            }
        
            // Remove GROUP BY from COUNT query
            $countQuery .= " GROUP BY controlnumber"; // ❌ Incorrect: GROUP BY should not be in COUNT query.
        
            // Fix: Use COUNT DISTINCT controlnumber
            $countQuery = str_replace("COUNT(*)", "COUNT(DISTINCT controlnumber)", $countQuery);
        
            $sqlquery .= " GROUP BY controlnumber LIMIT ? OFFSET ?";
        
            // **1️⃣ Prepare and execute COUNT Query**
            $stmt = $this->getConnection()->prepare($countQuery);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $countResult = $stmt->get_result()->fetch_assoc();
            $totalRecords = $countResult['total'] ?? 0; // Handle null case
            $totalPages = ceil($totalRecords / $limit);
            $stmt->close();
        
            // **2️⃣ Prepare and execute MAIN Query**
            $stmt = $this->getConnection()->prepare($sqlquery);
            
            // Add limit & offset parameters
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii"; // Integer parameters for LIMIT and OFFSET
        
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        
            // **3️⃣ Return JSON Response**
            if ($result->num_rows > 0) {
                $requestArray = [];
                while ($row = $result->fetch_assoc()) {
                    $requestArray[] = $row;
                }
                echo json_encode([
                    "status" => "success",
                    "data" => $requestArray,
                    "total_pages" => $totalPages,
                    "current_page" => $page
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records found."]);
            }
        
            $stmt->close();
        }
        

        public function GetComparisonData($controlnumber) {
            $sql = "SELECT itemname, quantity, suppliername, unitprice, discount 
                    FROM " . $this->comparisonTable . 
                   " WHERE controlnumber = ? ORDER BY itemname, suppliername";
        
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("s", $controlnumber);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $comparisonData = [];
            $supplierData = [];
        
            while ($row = $result->fetch_assoc()) {
                $itemname = $row['itemname'];
                $quantity = $row['quantity'];
                $suppliername = $row['suppliername'];
                $unitprice = $row['unitprice'];
                $discount = $row['discount'];
                $totalamount = ($unitprice * $quantity) - $discount;
                

                // Initialize item if not set
                if (!isset($comparisonData[$itemname])) {
                    $comparisonData[$itemname] = [
                        "itemname" => $itemname,
                        "quantity" => $quantity, // Keep total quantity at the top level
                        "suppliers" => [] // Create a sub-array for suppliers
                    ];
                }
        
                // Add supplier details to the item
                $comparisonData[$itemname]["suppliers"][] = [
                    "suppliername" => $suppliername,
                    "unitprice" => number_format($unitprice, 2, '.', ','),
                    "discount" => number_format($discount, 2, '.', ','),
                    "totalamount" => number_format($totalamount, 2, '.', ',')
                ];
        
                // Add unique suppliers to supplierData array
                if (!in_array($suppliername, $supplierData)) {
                    $supplierData[] = $suppliername;
                }
            }
        
            // Output JSON response
            header('Content-Type: application/json');
            echo json_encode([
                "comparisonData" => array_values($comparisonData), // Ensure correct JSON structure
                "supplierData" => $supplierData
            ], JSON_PRETTY_PRINT);
        }
        
        public function DeleteComparisonItem($id){
            try{
                $this->getConnection()->begin_transaction();
                $sql = "delete from " 
                       . $this->comparisonTable . 
                       " where controlnumber = ?";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bind_param("s", $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $this->getConnection()->commit();
                    return true;
                }
                else{
                    $this->getConnection()->rollback();
                    return false;
                }
            }
            catch(Exception $e){
                $this->getConnection()->rollback();
                return false;
            }
        }

        public function UpdateComparisonStatus($controlnumber, $status, $remarks = null){
            try {
                // Validate input
                if (empty($controlnumber)) {
                    return;
                }
        
                // Start transaction
                $this->getConnection()->begin_transaction();
                
                $sql = "UPDATE " . $this->comparisonTable . " SET status = ?, remarks = ? WHERE controlnumber = ?";
                $stmt = $this->getConnection()->prepare($sql);
        
                if (!$stmt) {
                    return false;
                }
        
                // Bind parameters and execute
                $stmt->bind_param("sss", $status, $remarks, $controlnumber);
                $stmt->execute();
        
                if ($stmt->affected_rows > 0) {
                    // Commit transaction on success
                    $this->getConnection()->commit();
                    return true;
                } else {
                    // No rows were updated
                    $this->getConnection()->rollback();
                    return false;
                }
            } catch (Exception $e) {
                // Rollback on error
                $this->getConnection()->rollback();
                error_log($e->getMessage());
                return false;
            }
        }

        public function sendEmailNotification(array $recipient, array $cc, $subject, $body){
            if (empty($recipient)) {
                return false;
            }

            $mail = new PHPMailer(true);
            
            try{
                //$mail->SMTPDebug = 3;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username = 'nidecrfq.noreply@gmail.com';
                $mail->Password = 'xyprohexdaphtvrj';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = '587';

                $mail->setFrom('nidecrfq.noreply@gmail.com', 'Nidec Instruments Philippines Inc. (RFQ System)');

                foreach($recipient as $emails){
                    $mail->addAddress($emails);                   
                }  

                foreach($cc as $ccrecipient){
                    $mail->addCC($ccrecipient);
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                if ($mail->send()) {
                    return true;
                }      
            }
            catch(Exception){
                error_log("Mail error: " . $mail->ErrorInfo);
                return false;
            }
        }

        public function FindEmailAddress($section){
            $sql = "select email_add from " . $this->emailList . " where section = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("s", $section);
            $stmt->execute();
            $result = $stmt->get_result();
            $emailaddress = [];

            while($row= $result->fetch_assoc()){
                $emailaddress[] =  $row['email_add'];
            }
            return $emailaddress;
        }

        public function getRequestCount($startTime = null){
            // Default SQL (retrieves all data if no time range is set)
            $sql = "SELECT status, COUNT(*) as count FROM " . $this->requestTable;
        
            // Add filtering only if a time range is provided
            if (!empty($startTime)) {
                $sql .= " WHERE date_requested >= ?";
            }
            
            $sql .= " GROUP BY status";
            
            $stmt = $this->getConnection()->prepare($sql);
        
            // Bind parameter only if filtering is applied
            if (!empty($startTime)) {
                $stmt->bind_param("s", $startTime);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            $statusdata = [
                "on_going" => 0,
                "hold" => 0,
                "completed" => 0,
                "total_requests" => 0
            ];
        
            while ($row = $result->fetch_assoc()) {
                $status = $row['status'];
                $count = (int) $row['count'];
        
                // Categorize based on status
                if (in_array($status, ["Approved Comparison Data by Requestor", "For Procurement Verification", "Request Quotation sent to supplier", "Pending Supervisor Approval"])) {
                    $statusdata["on_going"] += $count;
                } elseif ($status == "Hold request by Procurement") {
                    $statusdata["hold"] += $count;
                } elseif ($status == "Verified by Procurement") {
                    $statusdata["completed"] += $count;
                }
        
                // Sum total requests
                $statusdata["total_requests"] += $count;
            }
        
            echo json_encode($statusdata);
        }

        public function getRequestsectionCount($section, $startTime = null){
            // Default SQL (retrieves all data if no time range is set)
            $sql = "SELECT status, COUNT(*) as count FROM " . $this->requestTable . " WHERE section = ?";
        
            // Add filtering only if a time range is provided
            if (!empty($startTime)) {
                $sql .= " and date_requested >= ?";
            }
            
            $sql .= " GROUP BY status";
            
            $stmt = $this->getConnection()->prepare($sql);
        
            // Bind parameter only if filtering is applied
            if (!empty($startTime)) {
                $stmt->bind_param("s", $startTime);
            }
            else{
                $stmt->bind_param("s", $section);
            }
        
            $stmt->execute();
            $result = $stmt->get_result();
        
            $statusdata = [
                "on_going" => 0,
                "hold" => 0,
                "completed" => 0,
                "total_requests" => 0
            ];
        
            while ($row = $result->fetch_assoc()) {
                $status = $row['status'];
                $count = (int) $row['count'];
        
                // Categorize based on status
                if (in_array($status, ["Approved Comparison Data by Requestor", "For Procurement Verification", "Request Quotation sent to supplier", "Pending Supervisor Approval"])) {
                    $statusdata["on_going"] += $count;
                } elseif ($status == "Hold request by Procurement") {
                    $statusdata["hold"] += $count;
                } elseif ($status == "Verified by Procurement") {
                    $statusdata["completed"] += $count;
                }
        
                // Sum total requests
                $statusdata["total_requests"] += $count;
            }
        
            echo json_encode($statusdata);
        }

        
        
              
    }
?>