<?php 
    ob_start();
    session_start();
    include('./utility.php');
    $utility = new request();
    $utility->checkLogin();
    if (empty($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: ./index.php");
    }
?>
<nav class="navbar navbar-expand px-4 py-3">
        <span class="fw-bold fs-4">
            Welcome <?php echo $username;?>
        </span>
        <form action="#" class="d-none dsm-inline-block">
            <div class="input-group input-group-navbar">
                <input type="text" class="form-control border-0 rounded-0 pe-0 " 
                 placeholder ="Search" aria-label="Search">
                 <button class="btn border-0 rounded-0" type="button">
                 <i class="material-symbols-outlined">search</i>
                 </button>
            </div>
        </form>
        <div class="navbar-collapse collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                        <img src="profile1.png" data-role = "<?php echo $accessLevel; ?>" class="avatar img-fluid" alt="" srcset="">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end rounded-0 border-0 shadow mt-3">
                        <a href="#" class="dropdown-item">
                        <i class="material-symbols-outlined">settings</i>
                        <span>Settings</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
<ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="userdashboard-tab" data-bs-toggle="tab" data-bs-target="#userdashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">Dashboard</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#usersettings" type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
    </li>
</ul>
<div class="tab-content" id="userDashboardTabsContent" data-section="<?php echo $_SESSION['department']?>">
    <div class="tab-pane fade show active" id="userdashboard" role="tabpanel" aria-labelledby="userdashboard-tab">
        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Dashboard Summary</h5>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="bg-success bg-opacity-10 p-4 rounded">
                                    <h6 class="text-white">Completed Rate</h6>
                                    <h3 class="text-white fw-bold" id="successRate">65%</h3>
                                    <p class="text-white small mb-0">Overall request completed rate</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-primary bg-opacity-10 p-4 rounded">
                                    <h6 class="text-white">Total Requests</h6>
                                    <h3 class="text-white fw-bold" id="summaryTotalRequests">102</h3>
                                    <p class="text-white small mb-0">Total Request of section</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-purple bg-opacity-10 p-4 rounded">
                                    <h6 class="text-white">Not Completed Reqeust</h6>
                                    <h3 class="text-white fw-bold"></h3>
                                    <p class="text-white small mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card shadow-sm h-100" id="statusChartCard"">
                    <div class="card-header bg-white d-flex align-items-center py-3">
                        <i class="fa-solid fa-table-list text-muted me-2"></i>
                        <span class="text-muted me-2">Request Status</span>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2 id="totalRequests"></h2>
                            <p class="text-muted">Total Requests</p>
                        </div>
                        <div class="status-bars">

                            <!--Approved request bar-->
                            <div class="status-items mb-3" data-status="approved">
                                <div class="d-flex justify-content-between mb-1">
                                    <div>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <span>Approved Request</span>
                                    </div>
                                    <span id="approvedCount">67</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="67" aria-valuemin="0" aria-valuemax="102"></div>
                                </div>
                            </div>

                             <!--On-going request bar-->
                             <div class="status-items mb-3" data-status="ongoing">
                                <div class="d-flex justify-content-between mb-1">
                                    <div>
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        <span>On-going Request</span>
                                    </div>
                                    <span id="ongoingCount">23</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="23" aria-valuemin="0" aria-valuemax="102"></div>
                                </div>
                            </div>

                              <!--Hold request bar-->
                              <div class="status-items mb-3" data-status="hold">
                                <div class="d-flex justify-content-between mb-1">
                                    <div>
                                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                        <span>Hold Request</span>
                                    </div>
                                    <span id="holdCount">10</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="102"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        </div>
    </div> 
    <div class="tab-pane fade" id="usersettings" role="tabpanel" aria-labelledby="settings-tab">
        <div class="card shadow-sm mb-2">
            <div class="card-body">
                <h5 class="card-title mb-4">Dashboard Settings</h5>
                <p class="text-muted mb-4">Configure your dashboard preferences and data sources here.</p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border rounded p-4">
                            <h6 class="mb-2">Status Chart Settings</h6>
                            <p class="text-muted small">Configure data sources and display options for the request status chart.</p>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div> 
</div>


<script src="./js/userdashboard.js"></script>