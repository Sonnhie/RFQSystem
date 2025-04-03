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
            Welcome <?php echo $_SESSION['name']?>
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
                        <img src="img/profile.png" data-role = "<?php echo $_SESSION['role'] ?>" class="avatar img-fluid" alt="" srcset="">
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

   <!-- Tabs Navigation -->
   <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">Dashboard</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
            </li>
    </ul>
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="row g-4">
                    <!-- Status Chart Section -->
                    <div class="col-md-6">
                        <!-- Time Range Filter -->
                        <div class="bg-white p-3 rounded shadow-sm mb-3 d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            <span class="text-muted me-2">Request Status Time Range:</span>
                            <select class="form-select form-select-sm w-auto" id="statusTimeRange">
                                <option value="1h">Last hour</option>
                                <option value="24h" selected>Last 24 hours</option>
                                <option value="7d">Last 7 days</option>
                                <option value="30d">Last 30 days</option>
                                <option value="90d">Last 90 days</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary ms-2" id="applyStatusRange">Apply</button>
                        </div>                    
                        <!-- Status Chart Card -->
                        <div class="card shadow-sm h-100" id="statusChartCard">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chart-bar text-muted me-2"></i>
                                    <h5 class="card-title mb-0">Request Status</h5>
                                </div>
                                <select class="form-select form-select-sm w-auto" id="statusChartRange">
                                    <option value="1h">Last Hour</option>
                                    <option value="24h" >Last 24 Hours</option>
                                    <option value="7d">Last 7 Days</option>
                                    <option value="30d" selected>Last 30 Days</option>
                                </select>
                            </div>
                            <div class="card-body">
                                <!-- Status Chart Content will be loaded via jQuery -->
                                <div class="text-center mb-4">
                                    <h2 id="totalRequests"></h2>
                                    <p class="text-muted">Total Requests</p>
                                </div>
                                
                                <div class="status-bars">
                                    <!-- Success Bar -->
                                    <div class="status-item mb-3" data-status="approved">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                <span>Approved Request</span>
                                            </div>
                                            <span id="approvedCount">67</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="67" aria-valuemin="0" aria-valuemax="102"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Error Bar -->
                                    <div class="status-item mb-3" data-status="hold">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                                <span>Hold Request</span>
                                            </div>
                                            <span id="holdCount">12</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="102"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Pending Bar -->
                                    <div class="status-item mb-3" data-status="ongoing">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                <i class="fas fa-clock text-warning me-1"></i>
                                                <span>On-going Request</span>
                                            </div>
                                            <span id="ongoingCount">23</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="23" aria-valuemin="0" aria-valuemax="102"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center text-muted small mt-4">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span>Click on a status to filter the view</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Exchange Rate Section -->
                    <div class="col-md-6">
                        <!-- Time Range Filter -->
                        <div class="bg-white p-3 rounded shadow-sm mb-3 d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            <span class="text-muted me-2">Exchange Rate Time Range:</span>
                            <select class="form-select form-select-sm w-auto" id="exchangeTimeRange">
                                <option value="1h">Last hour</option>
                                <option value="24h" selected>Last 24 hours</option>
                                <option value="7d">Last 7 days</option>
                                <option value="30d">Last 30 days</option>
                                <option value="90d">Last 90 days</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary ms-2" id="applyExchangeRange">Apply</button>
                        </div>
                        
                        <!-- Exchange Rate Card -->
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="card-title mb-0">Exchange Rate Chart</h5>
                                    <p class="text-muted small mb-0">Real-time exchange rates for major currency pairs</p>
                                </div>
                                <div class="d-flex align-items-center">
                                    <select class="form-select form-select-sm me-2" id="baseCurrency">
                                        <option value="USD" selected>USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="JPY">JPY</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary" id="refreshRates" title="Refresh data">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Exchange Rate Content -->
                                <ul class="nav nav-tabs mb-3" id="exchangeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab">Table View</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-view" type="button" role="tab">Chart View</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="exchangeTabsContent">
                                    <!-- Table View -->
                                    <div class="tab-pane fade show active" id="table-view" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Currency</th>
                                                        <th class="text-end">Rate</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="exchangeRatesTable">
                                                    <!-- Exchange rates will be loaded via jQuery -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-end text-muted small" id="lastUpdated">Last updated: 12:00:00</div>
                                    </div>
                                    
                                    <!-- Chart View -->
                                    <div class="tab-pane fade" id="chart-view" role="tabpanel">
                                        <div class="border rounded p-4 d-flex justify-content-center align-items-center" style="height: 300px;">
                                            <div class="text-center text-muted">
                                                <p>Chart visualization would appear here</p>
                                                <p class="small">Using Chart.js in the actual implementation</p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-3">
                                            <div class="btn-group" role="group" aria-label="Time range">
                                                <button type="button" class="btn btn-sm btn-outline-secondary">1d</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary">1w</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary">1m</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary">3m</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary">1y</button>
                                            </div>
                                            <div class="text-muted small" id="chartLastUpdated">Last updated: 12:00:00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dashboard Summary -->
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
                                            <p class="text-white small mb-0">Requests in selected time period</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-purple bg-opacity-10 p-4 rounded">
                                            <h6 class="text-white">Exchange Rate Volatility</h6>
                                            <h3 class="text-white fw-bold">Low</h3>
                                            <p class="text-white small mb-0">Based on recent fluctuations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
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
                            <div class="col-md-6">
                                <div class="border rounded p-4">
                                    <h6 class="mb-2">Exchange Rate Settings</h6>
                                    <p class="text-muted small">Configure currency pairs and update frequency for exchange rate data.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- General Settings -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <form id="generalSettingsForm">
                                    <div class="mb-3">
                                        <label for="dashboardTitle" class="form-label">Dashboard Title</label>
                                        <input type="text" class="form-control" id="dashboardTitle" value="Status Dashboard">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dashboardSubtitle" class="form-label">Dashboard Subtitle</label>
                                        <input type="text" class="form-control" id="dashboardSubtitle" value="Request Status & Exchange Rate Tracker">
                                    </div>
                                    <div class="mb-3">
                                        <label for="refreshInterval" class="form-label">Data Refresh Interval (seconds)</label>
                                        <input type="number" class="form-control" id="refreshInterval" value="60" min="10" max="3600">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="autoRefresh" checked>
                                        <label class="form-check-label" for="autoRefresh">Enable Auto Refresh</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Chart Settings -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Status Chart Settings</h5>
                            </div>
                            <div class="card-body">
                                <form id="statusChartSettingsForm">
                                    <div class="mb-3">
                                        <label for="defaultStatusTimeRange" class="form-label">Default Time Range</label>
                                        <select class="form-select" id="defaultStatusTimeRange">
                                            <option value="1h">Last hour</option>
                                            <option value="24h" selected>Last 24 hours</option>
                                            <option value="7d">Last 7 days</option>
                                            <option value="30d">Last 30 days</option>
                                            <option value="90d">Last 90 days</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status Colors</label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">Success</span>
                                            <input type="color" class="form-control form-control-color" id="successColor" value="#198754">
                                        </div>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">Error</span>
                                            <input type="color" class="form-control form-control-color" id="errorColor" value="#dc3545">
                                        </div>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">Pending</span>
                                            <input type="color" class="form-control form-control-color" id="pendingColor" value="#ffc107">
                                        </div>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="showPercentages" checked>
                                        <label class="form-check-label" for="showPercentages">Show Percentages</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Exchange Rate Settings -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Exchange Rate Settings</h5>
                            </div>
                            <div class="card-body">
                                <form id="exchangeRateSettingsForm">
                                    <div class="mb-3">
                                        <label for="defaultBaseCurrency" class="form-label">Default Base Currency</label>
                                        <select class="form-select" id="defaultBaseCurrency">
                                            <option value="USD" selected>USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="JPY">JPY - Japanese Yen</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Displayed Currencies</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyUSD" checked disabled>
                                            <label class="form-check-label" for="currencyUSD">USD - US Dollar</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyEUR" checked>
                                            <label class="form-check-label" for="currencyEUR">EUR - Euro</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyGBP" checked>
                                            <label class="form-check-label" for="currencyGBP">GBP - British Pound</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyJPY" checked>
                                            <label class="form-check-label" for="currencyJPY">JPY - Japanese Yen</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyCAD" checked>
                                            <label class="form-check-label" for="currencyCAD">CAD - Canadian Dollar</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="currencyAUD" checked>
                                            <label class="form-check-label" for="currencyAUD">AUD - Australian Dollar</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Source Settings -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Data Source Settings</h5>
                            </div>
                            <div class="card-body">
                                <form id="dataSourceSettingsForm">
                                    <div class="mb-3">
                                        <label for="statusDataSource" class="form-label">Status Data Source</label>
                                        <select class="form-select" id="statusDataSource">
                                            <option value="api" selected>Live API</option>
                                            <option value="mock">Mock Data</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="exchangeRateDataSource" class="form-label">Exchange Rate Data Source</label>
                                        <select class="form-select" id="exchangeRateDataSource">
                                            <option value="api" selected>Live API</option>
                                            <option value="mock">Mock Data</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="apiEndpoint" class="form-label">API Endpoint URL</label>
                                        <input type="url" class="form-control" id="apiEndpoint" value="https://api.example.com/data">
                                        <div class="form-text">Enter the base URL for your API endpoints</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="apiKey" class="form-label">API Key</label>
                                        <input type="password" class="form-control" id="apiKey" placeholder="Enter your API key">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
<script src="./js/dashboard.js"></script>
<script src="./js/settings.js"></script>