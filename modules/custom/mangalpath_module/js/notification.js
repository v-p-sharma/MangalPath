(function ($, Drupal) {
  Drupal.behaviors.notificationBell = {
    attach: function (context, settings) {
      // Run only once per page load
      if (context !== document) {
        return;
      }

      function loadNotificationCount() {
        $.ajax({
          url: Drupal.url('admin/notifications/count'),
          type: 'GET',
          dataType: 'json',
          success: function (response) {
            if (response.count > 0) {
              $('.notification-dot', context).text(response.count).show();
            } else {
              $('.notification-dot', context).hide();
            }
          }
        });
      }

      // Call immediately on page load
      loadNotificationCount();

      // Refresh every 10 seconds
      setInterval(loadNotificationCount, 10000);
    }
  };


  Drupal.behaviors.notificationRead = {
    attach: function (context) {

      $(once('notification-read', '.notification-view-link', context))
        .on('click', function (e) {

          e.preventDefault();

          const link = $(this);
          const url = link.attr('href');
          const id = link.data('id');

          $.ajax({
            url: Drupal.url('admin/notifications/read/' + id),
            type: 'POST',
            complete: function () {
              window.location.href = url;
            
            }
          });

        });

    }
  };

  
})(jQuery, Drupal);
