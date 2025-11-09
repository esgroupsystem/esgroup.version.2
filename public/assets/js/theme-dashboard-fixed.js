(function () {
  'use strict';

  /* -------------------------------------------------------------------------- */
  /*                              Utility: docReady                             */
  /* -------------------------------------------------------------------------- */
  if (typeof window.docReady !== 'function') {
    window.docReady = function (fn) {
      if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(fn, 1);
      } else {
        document.addEventListener('DOMContentLoaded', fn);
      }
    };
  }

  /* -------------------------------------------------------------------------- */
  /*                            Falcon Dashboard Init                           */
  /* -------------------------------------------------------------------------- */
  window.docReady(function () {
    try {
      // --- Core UI Helpers ---
      if (typeof detectorInit === 'function') detectorInit();
      if (typeof handleNavbarVerticalCollapsed === 'function') handleNavbarVerticalCollapsed();
      if (typeof navbarTopDropShadow === 'function') navbarTopDropShadow();
      if (typeof tooltipInit === 'function') tooltipInit();
      if (typeof popoverInit === 'function') popoverInit();
      if (typeof toastInit === 'function') toastInit();
      if (typeof progressAnimationToggle === 'function') progressAnimationToggle();

      // --- Visuals, Media, Widgets ---
      if (typeof glightboxInit === 'function') glightboxInit();
      if (typeof plyrInit === 'function') plyrInit();
      if (typeof initMap === 'function') initMap();
      if (typeof dropzoneInit === 'function') dropzoneInit();
      if (typeof choicesInit === 'function') choicesInit();
      if (typeof formValidationInit === 'function') formValidationInit();
      if (typeof countupInit === 'function') countupInit();
      if (typeof copyLink === 'function') copyLink();
      if (typeof typedTextInit === 'function') typedTextInit();
      if (typeof navbarDarkenOnScroll === 'function') navbarDarkenOnScroll();
      if (typeof tinymceInit === 'function') tinymceInit();
      if (typeof bulkSelectInit === 'function') bulkSelectInit();
      if (typeof quantityInit === 'function') quantityInit();
      if (typeof navbarComboInit === 'function') navbarComboInit();
      if (typeof listInit === 'function') listInit();
      if (typeof chatInit === 'function') chatInit();
      if (typeof kanbanInit === 'function') kanbanInit();
      if (typeof swiperInit === 'function') swiperInit();
      if (typeof ratingInit === 'function') ratingInit();
      if (typeof wizardInit === 'function') wizardInit();
      if (typeof lottieInit === 'function') lottieInit();

      // --- Charts (Chart.js / ECharts / D3) ---
      if (typeof barChartInit === 'function') barChartInit();
      if (typeof productShareDoughnutInit === 'function') productShareDoughnutInit();
      if (typeof chartHalfDoughnutInit === 'function') chartHalfDoughnutInit();
      if (typeof chartScatter === 'function') chartScatter();
      if (typeof chartDoughnut === 'function') chartDoughnut();
      if (typeof chartPie === 'function') chartPie();
      if (typeof chartPolar === 'function') chartPolar();
      if (typeof chartRadar === 'function') chartRadar();
      if (typeof chartCombo === 'function') chartCombo();
      if (typeof chartBubble === 'function') chartBubble();
      if (typeof chartLine === 'function') chartLine();

      if (typeof totalSalesInit === 'function') totalSalesInit();
      if (typeof weeklySalesInit === 'function') weeklySalesInit();
      if (typeof totalOrderInit === 'function') totalOrderInit();
      if (typeof marketShareInit === 'function') marketShareInit();
      if (typeof topProductsInit === 'function') topProductsInit();
      if (typeof marketShareEcommerceInit === 'function') marketShareEcommerceInit();
      if (typeof totalSalesEcommerce === 'function') totalSalesEcommerce();
      if (typeof grossRevenueChartInit === 'function') grossRevenueChartInit();
      if (typeof candleChartInit === 'function') candleChartInit();
      if (typeof returningCustomerRateInit === 'function') returningCustomerRateInit();
      if (typeof salesByPosLocationInit === 'function') salesByPosLocationInit();
      if (typeof audienceChartInit === 'function') audienceChartInit();
      if (typeof activeUsersChartReportInit === 'function') activeUsersChartReportInit();
      if (typeof trafficChannelChartInit === 'function') trafficChannelChartInit();
      if (typeof bounceRateChartInit === 'function') bounceRateChartInit();
      if (typeof usersByTimeChartInit === 'function') usersByTimeChartInit();

      // --- Additional Modules ---
      if (typeof themeControl === 'function') themeControl();
      if (typeof dropdownOnHover === 'function') dropdownOnHover();
      if (typeof cookieNoticeInit === 'function') cookieNoticeInit();
      if (typeof scrollbarInit === 'function') scrollbarInit();
      if (typeof iconCopiedInit === 'function') iconCopiedInit();
      if (typeof treeviewInit === 'function') treeviewInit();
      if (typeof bottomBarInit === 'function') bottomBarInit();
      if (typeof scrollInit === 'function') scrollInit();
      if (typeof dataTablesInit === 'function') dataTablesInit();
      if (typeof select2Init === 'function') select2Init();
      if (typeof inputmaskInit === 'function') inputmaskInit();
      if (typeof emojiMartInit === 'function') emojiMartInit();
      if (typeof nouisliderInit === 'function') nouisliderInit();
      if (typeof advanceAjaxTableInit === 'function') advanceAjaxTableInit();
      if (typeof sortableInit === 'function') sortableInit();
      if (typeof flatpickrIntit === 'function') flatpickrIntit();

      // --- E-learning, CRM, and Support Widgets ---
      if (typeof courseEnrollmentsInit === 'function') courseEnrollmentsInit();
      if (typeof weeklyGoalsInit === 'function') weeklyGoalsInit();
      if (typeof assignmentScoresInit === 'function') assignmentScoresInit();
      if (typeof browsedCoursesInit === 'function') browsedCoursesInit();
      if (typeof courseStatusInit === 'function') courseStatusInit();
      if (typeof marketingExpensesInit === 'function') marketingExpensesInit();
      if (typeof echartTicketPriority === 'function') echartTicketPriority();
      if (typeof ticketVolumeChartInit === 'function') ticketVolumeChartInit();
      if (typeof echartsUnresolvedTicketsInit === 'function') echartsUnresolvedTicketsInit();
      if (typeof echartsCustomerSatisfactionInit === 'function') echartsCustomerSatisfactionInit();
      if (typeof echartsNumberOfTicketsInit === 'function') echartsNumberOfTicketsInit();
      if (typeof echartsDistributionOfPerformanceInit === 'function') echartsDistributionOfPerformanceInit();
      if (typeof echartsReceivedTicketsInit === 'function') echartsReceivedTicketsInit();
      if (typeof echartsSatisfactionSurveyInit === 'function') echartsSatisfactionSurveyInit();
      if (typeof unresolvedTicketsTabInit === 'function') unresolvedTicketsTabInit();
      if (typeof userByLocationInit === 'function') userByLocationInit();

    } catch (error) {
      console.error('⚠️ Error initializing Falcon dashboard:', error);
    }
  });
})();
