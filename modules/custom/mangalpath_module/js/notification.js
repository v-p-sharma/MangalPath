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

      $('.notification-view-link', context).once('notification-read').on('click', function (e) {

        e.preventDefault();

        var link = $(this);
        var url = link.attr('href');
        var id = link.data('id');

        $.ajax({
          url: '/admin/notifications/read/' + id,
          type: 'POST',
          success: function () {
            window.location.href = url;
          },
          error: function () {
            // Even if update fails, redirect.
            window.location.href = url;
          }
        });

      });

    }
  };

  
})(jQuery, Drupal);
