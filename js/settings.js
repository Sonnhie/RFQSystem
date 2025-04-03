/**
 * Settings JavaScript
 * Handles all the interactive functionality for the settings page
 */

$(document).ready(function () {
    // Load saved settings from localStorage
    loadSettings();
  
    // Form submission handlers
    $("#generalSettingsForm").on("submit", function (e) {
      e.preventDefault();
      saveGeneralSettings();
    });
  
    $("#statusChartSettingsForm").on("submit", function (e) {
      e.preventDefault();
      saveStatusChartSettings();
    });
  
    $("#exchangeRateSettingsForm").on("submit", function (e) {
      e.preventDefault();
      saveExchangeRateSettings();
    });
  
    $("#dataSourceSettingsForm").on("submit", function (e) {
      e.preventDefault();
      saveDataSourceSettings();
    });
  
    // Functions
    function loadSettings() {
      // General settings
      if (localStorage.getItem("dashboardTitle")) {
        $("#dashboardTitle").val(localStorage.getItem("dashboardTitle"));
      }
  
      if (localStorage.getItem("dashboardSubtitle")) {
        $("#dashboardSubtitle").val(localStorage.getItem("dashboardSubtitle"));
      }
  
      if (localStorage.getItem("refreshInterval")) {
        $("#refreshInterval").val(localStorage.getItem("refreshInterval"));
      }
  
      if (localStorage.getItem("autoRefresh") === "false") {
        $("#autoRefresh").prop("checked", false);
      }
  
      // Status chart settings
      if (localStorage.getItem("defaultStatusTimeRange")) {
        $("#defaultStatusTimeRange").val(
          localStorage.getItem("defaultStatusTimeRange"),
        );
      }
  
      if (localStorage.getItem("successColor")) {
        $("#successColor").val(localStorage.getItem("successColor"));
      }
  
      if (localStorage.getItem("errorColor")) {
        $("#errorColor").val(localStorage.getItem("errorColor"));
      }
  
      if (localStorage.getItem("pendingColor")) {
        $("#pendingColor").val(localStorage.getItem("pendingColor"));
      }
  
      if (localStorage.getItem("showPercentages") === "false") {
        $("#showPercentages").prop("checked", false);
      }
  
      // Exchange rate settings
      if (localStorage.getItem("defaultBaseCurrency")) {
        $("#defaultBaseCurrency").val(
          localStorage.getItem("defaultBaseCurrency"),
        );
      }
  
      // Currency checkboxes
      ["EUR", "GBP", "JPY", "CAD", "AUD"].forEach(function (currency) {
        if (localStorage.getItem(`currency${currency}`) === "false") {
          $(`#currency${currency}`).prop("checked", false);
        }
      });
  
      // Data source settings
      if (localStorage.getItem("statusDataSource")) {
        $("#statusDataSource").val(localStorage.getItem("statusDataSource"));
      }
  
      if (localStorage.getItem("exchangeRateDataSource")) {
        $("#exchangeRateDataSource").val(
          localStorage.getItem("exchangeRateDataSource"),
        );
      }
  
      if (localStorage.getItem("apiEndpoint")) {
        $("#apiEndpoint").val(localStorage.getItem("apiEndpoint"));
      }
  
      if (localStorage.getItem("apiKey")) {
        $("#apiKey").val(localStorage.getItem("apiKey"));
      }
    }
  
    function saveGeneralSettings() {
      localStorage.setItem("dashboardTitle", $("#dashboardTitle").val());
      localStorage.setItem("dashboardSubtitle", $("#dashboardSubtitle").val());
      localStorage.setItem("refreshInterval", $("#refreshInterval").val());
      localStorage.setItem("autoRefresh", $("#autoRefresh").prop("checked"));
  
      // Save to server (in a real app)
      saveSettingsToServer("general");
    }
  
    function saveStatusChartSettings() {
      localStorage.setItem(
        "defaultStatusTimeRange",
        $("#defaultStatusTimeRange").val(),
      );
      localStorage.setItem("successColor", $("#successColor").val());
      localStorage.setItem("errorColor", $("#errorColor").val());
      localStorage.setItem("pendingColor", $("#pendingColor").val());
      localStorage.setItem(
        "showPercentages",
        $("#showPercentages").prop("checked"),
      );
  
      // Save to server (in a real app)
      saveSettingsToServer("statusChart");
    }
  
    function saveExchangeRateSettings() {
      localStorage.setItem(
        "defaultBaseCurrency",
        $("#defaultBaseCurrency").val(),
      );
  
      // Currency checkboxes
      ["EUR", "GBP", "JPY", "CAD", "AUD"].forEach(function (currency) {
        localStorage.setItem(
          `currency${currency}`,
          $(`#currency${currency}`).prop("checked"),
        );
      });
  
      // Save to server (in a real app)
      saveSettingsToServer("exchangeRate");
    }
  
    function saveDataSourceSettings() {
      localStorage.setItem("statusDataSource", $("#statusDataSource").val());
      localStorage.setItem(
        "exchangeRateDataSource",
        $("#exchangeRateDataSource").val(),
      );
      localStorage.setItem("apiEndpoint", $("#apiEndpoint").val());
      localStorage.setItem("apiKey", $("#apiKey").val());
  
      // Save to server (in a real app)
      saveSettingsToServer("dataSource");
    }
  
    function saveSettingsToServer(settingsType) {
      // Show saving indicator
      const $form = $(`#${settingsType}SettingsForm`);
      const $button = $form.find('button[type="submit"]');
      const originalText = $button.text();
  
      $button.html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');
      $button.prop("disabled", true);
  
      // In a real app, this would be an AJAX call to save settings to the server
      // For now, we'll just simulate a delay
      setTimeout(function () {
        // Show success message
        $button.html('<i class="fas fa-check me-2"></i> Saved!');
        $button.removeClass("btn-primary").addClass("btn-success");
  
        // Reset button after a delay
        setTimeout(function () {
          $button.html(originalText);
          $button.removeClass("btn-success").addClass("btn-primary");
          $button.prop("disabled", false);
        }, 1500);
      }, 800);
    }
  });
  