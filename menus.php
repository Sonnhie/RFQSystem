
    <aside id="sidebar">
        <div class="d-flex justify-content-between p-4">
            <div class="sidebar-logo">
                <a href="#">RFQ System</a>
            </div>
                <button class="toggle-btn border-0">
                <i id="icon" class='bx bx-chevrons-right'></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <?php 
                    if($_SESSION['role'] == 'Requestor')
                    {
                 ?>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="dashboard_menu" data-page="userdashboard.php">
                        <i class='material-symbols-outlined'>dashboard</i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li class = "sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" 
                        data-bs-target="#requestmanagement" aria-expanded="false" aria-controls="requestmanagement">
                        <i class='material-symbols-outlined'>request_quote</i>
                        <span>Request Quote</span>
                        </a>
                        <ul id="requestmanagement" class="sidebar-dropdown list-unstyled collapse " data-bs-parent = "#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="request.php" id="createRequest_menu">
                                    Create New Request
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="comparison_menu" data-page="comparison.php">
                        <i class='material-symbols-outlined'>difference</i>
                        <span>Comparison</span>
                        </a>
                    </li>
                <?php
                    } 
                    else if($_SESSION['role'] == 'Verifier')
                    {
                ?>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="dashboard_menu" data-page="dashboard.php">
                        <i class='material-symbols-outlined'>dashboard</i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li class = "sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" 
                        data-bs-target="#requestmanagement" aria-expanded="false" aria-controls="requestmanagement">
                        <i class='material-symbols-outlined'>request_quote</i>
                        <span>Request Quote</span>
                        </a>
                        <ul id="requestmanagement" class="sidebar-dropdown list-unstyled collapse " data-bs-parent = "#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="request.php" id="createRequest_menu">
                                    Create New Request
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="verificationlist.php" id="requestchecklist_menu">
                                    Request Checklist
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="hold-request.php" id="requestchecklist_menu">
                                    Hold Request Checklist
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="email-to-supplier.php" id="requestchecklist_menu">
                                    Email to Supplier
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="comparison_menu" data-page="comparison.php">
                        <i class='material-symbols-outlined'>difference</i>
                        <span>Comparison</span>
                        </a>
                    </li>
                <?php
                    }
                    else if($_SESSION['role'] == 'Section Approval' && $_SESSION['department'] <> 'Procurement')
                    {
                ?>
                     <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="dashboard_menu" data-page="userdashboard.php">
                        <i class='material-symbols-outlined'>dashboard</i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li class = "sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" 
                        data-bs-target="#requestmanagement" aria-expanded="false" aria-controls="requestmanagement">
                        <i class='material-symbols-outlined'>request_quote</i>
                        <span>Request Quote</span>
                        </a>
                        <ul id="requestmanagement" class="sidebar-dropdown list-unstyled collapse " data-bs-parent = "#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="request.php" id="createRequest_menu">
                                    Create New Request
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="status.php" id="requestchecklist_menu">
                                    Approval Checklist
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php
                    }
                    else if($_SESSION['role'] == 'Section Approval' && $_SESSION['department'] == 'Procurement')
                    {
                ?>
                     <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="dashboard_menu" data-page="dashboard.php">
                        <i class='material-symbols-outlined'>dashboard</i>
                        <span>Dashboard</span>
                        </a>
                    </li>
                    <li class = "sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" 
                        data-bs-target="#requestmanagement" aria-expanded="false" aria-controls="requestmanagement">
                        <i class='material-symbols-outlined'>request_quote</i>
                        <span>Request Quote</span>
                        </a>
                        <ul id="requestmanagement" class="sidebar-dropdown list-unstyled collapse " data-bs-parent = "#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="request.php" id="createRequest_menu">
                                    Create New Request
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link load-content" data-page="status.php" id="requestchecklist_menu">
                                    Approval Checklist
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link load-content" id="comparison_menu" data-page="comparison.php">
                        <i class='material-symbols-outlined'>difference</i>
                        <span>Comparison</span>
                        </a>
                    </li>
                <?php
                    }
                ?>
                
                
            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link" id="logout">
                <i class='material-symbols-outlined'>logout</i>
                <span>Logout</span>
                </a>
            </div>
    </aside>

    

<script>
    const hamburger = document.querySelector(".toggle-btn");
    const toggler = document.querySelector("#icon");

    hamburger.addEventListener("click", function(){
        document.querySelector("#sidebar").classList.toggle("expand");
        toggler.classList.toggle("bxs-chevrons-right");

    });
</script>