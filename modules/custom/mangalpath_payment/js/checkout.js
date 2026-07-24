const AjaxCommands = Drupal.AjaxCommands || {};
(function ($, Drupal, drupalSettings, once) {

  'use strict';

  Drupal.behaviors.mangalpathCheckout = {

    attach: function (context) {

      $(once('mangalpath-payment', '#rzp-pay-btn', context)).each(function () {

        const settings = drupalSettings.mangalpathPayment;

        if (!settings) {
          console.error('MangalPath Payment settings not found.');
          return;
        }

        $(this).on('click', function (e) {

          e.preventDefault();

          const button = $(this);

          button.prop('disabled', true);

          button.text('Please wait...');

          const options = {

            key: settings.key,

            amount: settings.amount,

            currency: settings.currency,

            name: 'MangalPath',

            description: 'Listing Payment',

            order_id: settings.orderId,

            handler: function (response) {

              paymentSuccess(response);

            },

            modal: {

              ondismiss: function () {

                button.prop('disabled', false);

                button.text('Pay Now');

              }

            },

            theme: {

              color: '#0d6efd'

            }

          };

          const rzp = new Razorpay(options);

          rzp.on('payment.failed', function (response) {

            paymentFailed(response);

          });

          rzp.open();

        });

      });

    }

  };
  /**
   * Payment Success.
   */
  function paymentSuccess(response) {

  $.ajax({

    url: Drupal.url('payment/complete'),

    type: 'POST',

    dataType: 'json',

    data: {

      razorpay_order_id: response.razorpay_order_id,

      razorpay_payment_id: response.razorpay_payment_id,

      razorpay_signature: response.razorpay_signature,

      node_id: drupalSettings.mangalpathPayment.nodeId,

      transaction_id: drupalSettings.mangalpathPayment.transactionId

    },

    success: function (result) {

      if (result.status) {

        window.location.href = result.redirect;

      }
      else {

        alert(result.message);

        location.reload();

      }

    },

    error: function (xhr) {

      console.error(xhr);

      alert('Payment verification failed.');

      location.reload();

    }

  });

}

  /**
   * Payment Failed.
   */
  function paymentFailed(response) {

  let message = 'Payment Failed';

  if (
    response.error &&
    response.error.description
  ) {

    message = response.error.description;

  }

  $.ajax({

    url: Drupal.url('payment/fail'),

    type: 'POST',

    dataType: 'json',

    data: {

      razorpay_order_id: drupalSettings.mangalpathPayment.orderId,

      node_id: drupalSettings.mangalpathPayment.nodeId,

      message: message

    },

    complete: function () {

      window.location.href =
        drupalSettings.mangalpathPayment.failedUrl;

    }

  });

}

})(jQuery, Drupal, drupalSettings, once);