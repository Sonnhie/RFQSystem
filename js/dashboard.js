/**
 * Dashboard JavaScript
 * Handles all the interactive functionality for the dashboard
 */

$(document).ready(function () {
    // Initialize variables
    let statusTimeRange = "24h";
    let exchangeTimeRange = "24h";
    let baseCurrency = "USD";
    let selectedStatus = null;
    let lastUpdated = new Date();
  
    // Initialize the dashboard
    initializeDashboard();
    updateStatusChart();
    // Event handlers for time range filters
    $("#statusTimeRange, #exchangeTimeRange").on("change", function () {
      const id = $(this).attr("id");
      if (id === "statusTimeRange") {
        statusTimeRange = $(this).val();
        updateStatusChart(statusTimeRange);
      } else {
        exchangeTimeRange = $(this).val();
      }
    });
  
    $("#applyStatusRange").on("click", function () {
      updateStatusChart(statusTimeRange);
    });
  
    $("#applyExchangeRange").on("click", function () {
      updateExchangeRates(exchangeTimeRange);
    });
  
    // Status chart range selector
    $("#statusChartRange").on("change", function () {
      statusTimeRange = $(this).val();
      updateStatusChart(statusTimeRange);
    });
  
    // Base currency selector
    $("#baseCurrency").on("change", function () {
      baseCurrency = $(this).val();
      updateExchangeRates(exchangeTimeRange);
    });
  
    // Refresh exchange rates
    $("#refreshRates").on("click", function () {
      const $button = $(this);
      $button.find("i").addClass("refreshing");
  
      // Simulate API call delay
      setTimeout(function () {
        updateExchangeRates(exchangeTimeRange);
        $button.find("i").removeClass("refreshing");
        lastUpdated = new Date();
        updateLastUpdatedTime();
      }, 1000);
    });
  
    // Status item click handler
    $(".status-item").on("click", function () {
      const status = $(this).data("status");
  
      if (selectedStatus === status) {
        // Deselect if already selected
        selectedStatus = null;
        $(".status-item .progress-bar").css("opacity", "1");
      } else {
        // Select new status
        selectedStatus = status;
        $(".status-item .progress-bar").css("opacity", "0.5");
        $(this).find(".progress-bar").css("opacity", "1");
      }
    });
  
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
  
    // Functions
    function initializeDashboard() {
      updateStatusChart(statusTimeRange);
      updateExchangeRates(exchangeTimeRange);
      updateLastUpdatedTime();
      updateSummary();
  
      // Set up auto-refresh
      setInterval(function () {
        updateExchangeRates(exchangeTimeRange);
        updateLastUpdatedTime();
      }, 60000); // Refresh every minute
    }
  
    function updateStatusChart(timeRange = null) {
      $.ajax({
          url: 'action.php',
          method: 'POST',
          data: {
              action: 'getstatuscount',
              timeRange: timeRange
          },
          dataType: 'json', // Ensure JSON response
          success: function (data) {
              console.log("Fetched Data:", data);
  
              if (data.error) {
                  console.error('Error fetching status data:', data.error);
                  return;
              }
  
              // Ensure we don't divide by zero
              const total = data.total_requests > 0 ? data.total_requests : 1;
              console.log("Total Requests:", total);
  
              // Update counts
              $("#totalRequests").text(data.total_requests);
              $("#approvedCount").text(data.completed);
              $("#holdCount").text(data.hold);
              $("#ongoingCount").text(data.on_going);
  
              // Calculate percentages safely
              let approvedPercentage = (data.completed / total) * 100;
              let holdPercentage = (data.hold / total) * 100;
              let ongoingPercentage = (data.on_going / total) * 100;
  
              console.log("Progress:", {
                  approved: approvedPercentage,
                  hold: holdPercentage,
                  ongoing: ongoingPercentage
              });
  
              // Update progress bars
              $('.status-item[data-status="approved"] .progress-bar').css({
                  "width": `${approvedPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });
  
              $('.status-item[data-status="hold"] .progress-bar').css({
                  "width": `${holdPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });
  
              $('.status-item[data-status="ongoing"] .progress-bar').css({
                  "width": `${ongoingPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });
  
              // Update summary
              updateSummary(data.total_requests, data.completed);
          },
          error: function (error) {
              console.error('Error fetching status data:', error);
          }
      });
    }
  
    // Store previous exchange rates
    let previousRates = {};

    function updateExchangeRates(timeRange) {
        $("#exchangeRatesTable").empty();

        $.ajax({
            url: `https://v6.exchangerate-api.com/v6/6305bface2cd44436516f0f2/latest/${baseCurrency}`,
            method: 'GET',
            success: function (data) {
                if (data.result === 'success') {
                    const exchangeRatesFromApi = data.conversion_rates;
                    const requiredCurrencies = ['USD', 'PHP'];

                    requiredCurrencies.forEach(function (currencyCode) {
                        const currentRate = exchangeRatesFromApi[currencyCode];
                        let previousRate = previousRates[currencyCode] || currentRate; // Default to current if no previous rate

                        // Calculate rate change
                        let rateChange = currentRate - previousRate;
                        let changePercentage = ((rateChange / previousRate) * 100).toFixed(2);

                        // Determine change class (increase, decrease, no change)
                        let changeClass = rateChange > 0 ? "text-success" : rateChange < 0 ? "text-danger" : "text-muted";
                        let changeIcon = rateChange > 0 ? "fa-arrow-up" : rateChange < 0 ? "fa-arrow-down" : "fa-minus";

                        // Update previous rate
                        previousRates[currencyCode] = currentRate;

                        // Create row
                        const row = $("<tr></tr>");

                        // Currency cell
                        const currencyCell = $('<td class="p-2"></td>');
                        currencyCell.html(`
                            <div class="d-flex align-items-center">
                                <div class="currency-code">${currencyCode.substring(0, 1)}</div>
                                <span>${currencyCode} (${currencyCode})</span>
                            </div>
                        `);

                        // Rate cell
                        const rateCell = $('<td class="p-2 text-end"></td>');
                        rateCell.html(`
                            <div class="d-flex align-items-center justify-content-end">
                                <i class="fas fa-dollar-sign me-1 text-muted small"></i>
                                ${currentRate.toFixed(4)}
                            </div>
                        `);

                        // Change cell
                        const changeCell = $('<td class="p-2 text-end"></td>');
                        changeCell.html(`
                            <div class="d-flex align-items-center justify-content-end ${changeClass}">
                                <i class="fas ${changeIcon} me-1 small"></i>
                                ${rateChange.toFixed(4)} (${changePercentage}%)
                            </div>
                        `);

                        // Append cells to row
                        row.append(currencyCell, rateCell, changeCell);

                        // Append row to table
                        $("#exchangeRatesTable").append(row);
                    });
                } else {
                    console.error('API call failed:', data.error);
                }
            },
            error: function (error) {
                console.error('Error fetching exchange rates:', error);
            }
        });
    }

    function updateLastUpdatedTime() {
      const timeString = lastUpdated.toLocaleTimeString();
      $("#lastUpdated, #chartLastUpdated").text(`Last updated: ${timeString}`);
    }

    function updateSummary(totalrequest, totalcompleted) {
      // Prevent division by zero
      const total = totalrequest > 0 ? totalrequest : 1;
      
      // Calculate success rate safely
      const successRate = totalrequest > 0 ? Math.round((totalcompleted / total) * 100) : 0;
  
      // Update summary cards
      $("#successRate").text(`${successRate}%`);
      $("#summaryTotalRequests").text(totalrequest);
    }
  
  });
  