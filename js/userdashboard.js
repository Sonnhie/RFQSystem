$(document).ready(function(){
    let selectedStatus = null;
    initializeDahboard();

    // Status item click handler
    $(".status-items").on("click", function () {
        const status = $(this).data("status");
    
        if (selectedStatus === status) {
          // Deselect if already selected
          selectedStatus = null;
          $(".status-items .progress-bar").css("opacity", "1");
        } else {
          // Select new status
          selectedStatus = status;
          $(".status-items .progress-bar").css("opacity", "0.5");
          $(this).find(".progress-bar").css("opacity", "1");
        }
    });

    $('[data-bs-toggle="tooltip"]').tooltip();

    function initializeDahboard(){
        updateStatusChart();
    }

    function updateStatusChart(){
        let section = $("#userDashboardTabsContent").data('section');
        console.log(section);
        $.ajax({
            url: "action.php",
            method: "POST",
            data: {
                action: 'updateStatusChart',
                section: section
            },
            dataType: "json",
            success: function(response){
                console.log(response);

                const total = response.total_requests > 0 ? response.total_requests : 1;
                $("#totalRequests").text(response.total_requests);
                $("#approvedCount").text(response.completed);
                $("#holdCount").text(response.hold);
                $("#ongoingCount").text(response.on_going);

                 // Calculate percentages safely
              let approvedPercentage = (response.completed / total) * 100;
              let holdPercentage = (response.hold / total) * 100;
              let ongoingPercentage = (response.on_going / total) * 100;
  
              console.log("Progress:", {
                  approved: approvedPercentage,
                  hold: holdPercentage,
                  ongoing: ongoingPercentage
              });
  
              // Update progress bars
              $('.status-items[data-status="approved"] .progress-bar').css({
                  "width": `${approvedPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });
  
              $('.status-items[data-status="hold"] .progress-bar').css({
                  "width": `${holdPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });
  
              $('.status-items[data-status="ongoing"] .progress-bar').css({
                  "width": `${ongoingPercentage}%`,
                  "transition": "width 0.5s ease-in-out"
              });

              updateStatusSummary(response.total_requests, response.completed);
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function updateStatusSummary(totalrequest, completedrequest){
         // Prevent division by zero
      const total = totalrequest > 0 ? totalrequest : 1;
      
      // Calculate success rate safely
      const successRate = totalrequest > 0 ? Math.round((completedrequest/ total) * 100) : 0;
  
      // Update summary cards
      $("#successRate").text(`${successRate}%`);
      $("#summaryTotalRequests").text(totalrequest);
    }
});