(function (Drupal, once, $) {

  'use strict';

  /**
   * Register Role Selector
   */
  Drupal.behaviors.mangalpathRegister = {
    attach: function (context) {

      once('role-selector', '.role-selector', context).forEach(function () {

        const hiddenField = document.getElementById('custom-hidden-field');

        document.querySelectorAll('input[name="reg-role"]').forEach(function (radio) {

          radio.addEventListener('change', function () {

            if (hiddenField) {
              hiddenField.value = this.value;
            }

            document.querySelectorAll('.role-option').forEach(function (label) {
              label.classList.remove('active');
            });

            const activeLabel = document.querySelector('label[for="' + this.id + '"]');

            if (activeLabel) {
              activeLabel.classList.add('active');
            }

          });

        });

        const checked = document.querySelector('input[name="reg-role"]:checked');

        if (checked && hiddenField) {
          hiddenField.value = checked.value;
        }

      });

    }
  };

  /**
   * EMI Calculator
   */
  Drupal.behaviors.emiCalculatorBehavior = {
    attach: function (context) {

      /**
       * Calculate EMI
       */
      function calculateEMI() {

        const amountField = document.getElementById('loan-amount');
        const rateField = document.getElementById('interest-rate');
        const tenureField = document.getElementById('loan-tenure');

        if (!amountField || !rateField || !tenureField) {
          return;
        }

        const amount = parseFloat(amountField.value) || 0;
        const rate = parseFloat(rateField.value) || 0;
        const years = parseFloat(tenureField.value) || 0;

        const amountDisp = document.getElementById('amount-disp');
        const rateDisp = document.getElementById('rate-disp');
        const yearDisp = document.getElementById('year-disp');

        if (amountDisp) amountDisp.innerText = amount.toLocaleString('en-IN');
        if (rateDisp) rateDisp.innerText = rate;
        if (yearDisp) yearDisp.innerText = years;

        if (amount <= 0 || years <= 0) {
          return;
        }

        const principal = amount;
        const interest = rate / 100 / 12;
        const payments = years * 12;

        let emi;

        if (rate === 0) {
          emi = principal / payments;
        }
        else {
          const x = Math.pow(1 + interest, payments);
          emi = (principal * x * interest) / (x - 1);
        }

        const totalPayable = emi * payments;
        const totalInterest = totalPayable - principal;

        const emiVal = document.getElementById('emi-val');
        const emiBig = document.getElementById('emi-big');
        const totalInterestEl = document.getElementById('total-interest');
        const totalAmountEl = document.getElementById('total-amount');

        if (emiVal) {
          emiVal.innerText = Math.round(emi).toLocaleString('en-IN');
        }

        if (emiBig) {
          emiBig.innerText = Math.round(emi).toLocaleString('en-IN');
        }

        if (totalInterestEl) {
          totalInterestEl.innerText = Math.round(totalInterest).toLocaleString('en-IN');
        }

        if (totalAmountEl) {
          totalAmountEl.innerText = Math.round(totalPayable).toLocaleString('en-IN');
        }

      }

      /**
       * Loan Amount
       */
      once('loan-amount', '#loan-amount', context).forEach(function (element) {
        element.addEventListener('input', calculateEMI);
      });

      /**
       * Interest
       */
      once('interest-rate', '#interest-rate', context).forEach(function (element) {
        element.addEventListener('input', calculateEMI);
      });

      /**
       * Tenure
       */
      once('loan-tenure', '#loan-tenure', context).forEach(function (element) {
        element.addEventListener('input', calculateEMI);
      });

      calculateEMI();

      /**
       * Toggle Existing Loan
       */
      function toggleRunningLoan(isRunning) {

        const field = document.getElementById('existingLoanAmount');

        if (!field) {
          return;
        }

        field.style.display = isRunning ? 'block' : 'none';

      }

      /**
       * Existing Loan Radio
       *
       * name="runningLoan"
       * value="yes"
       * value="no"
       */

      once('running-loan', 'input[name="runningLoan"]', context).forEach(function (radio) {

        radio.addEventListener('change', function () {
          toggleRunningLoan(this.value === 'yes');
        });

      });

      /**
       * Form Submit
       */
      once('loan-submit', '#loanForm', context).forEach(function (form) {

        form.addEventListener('submit', function (e) {

          e.preventDefault();

          const container = document.getElementById('applicationFormContainer');
          const success = document.getElementById('successMessage');

          if (container) {
            container.style.display = 'none';
          }

          if (success) {
            success.style.display = 'block';
          }

        });

      });

      /**
       * Global Reset
       */
      window.resetForm = function () {

        const form = document.getElementById('loanForm');
        const container = document.getElementById('applicationFormContainer');
        const success = document.getElementById('successMessage');

        if (form) {
          form.reset();
        }

        if (container) {
          container.style.display = 'block';
        }

        if (success) {
          success.style.display = 'none';
        }

        toggleRunningLoan(false);

        calculateEMI();

      };

    }
  };

  /**
   * Property Category Step Toggle
   * Shows only category-specific form steps based on propCategory selection.
   */
  Drupal.behaviors.propertyCategoryStepToggle = {
    attach: function (context) {

      function getSelectedCategoryValue() {
        const select = document.getElementById('edit-propcategory') ||
          document.querySelector('select[name="propCategory"], select#edit-propcategory');

        if (!select) {
          return null;
        }

        const value = select.value;
        return value === '' ? null : value;
      }

      function applyVisibility() {
        // 1 = Residential Properties
        // 44 = Agricultural Properties
        // 20 = Commercial Properties
        const selected = getSelectedCategoryValue();

        const residentialStep = context.querySelector('[data-prop-category-step="residential"]');
        const agriculturalStep = context.querySelector('[data-prop-category-step="agricultural"]');
        const commercialStep = context.querySelector('[data-prop-category-step="commercial"]');

        function hide(el) {
          if (!el) return;
          el.style.display = 'none';

          // Prevent HTML required validation errors:
          // - disable required fields
          // - remove required attribute
          el.querySelectorAll('[required]').forEach(function (field) {
            field.dataset.prevRequired = '1';
            field.disabled = true;
            field.removeAttribute('required');
          });
        }

        function show(el) {
          if (!el) return;
          el.style.display = '';

          // Re-enable required inputs and restore required attribute.
          el.querySelectorAll('[data-prev-required="1"], [required]').forEach(function (field) {
            if (field.dataset.prevRequired === '1') {
              field.disabled = false;
              field.setAttribute('required', 'required');
              delete field.dataset.prevRequired;
            }
            else if (field.hasAttribute('required')) {
              field.disabled = false;
            }
          });

          // Also restore required attribute for the step itself inputs that were marked as prevRequired.
          // (No-op for elements that were never disabled.)
        }

        // Default: hide all until a valid selection is made
        hide(residentialStep);
        hide(agriculturalStep);
        hide(commercialStep);

        if (!selected) {
          return;
        }

        if (selected === '1') {
          show(residentialStep);
        }
        else if (selected === '44') {
          show(agriculturalStep);
        }
        else if (selected === '20') {
          show(commercialStep);
        }
      }

      once('property-category-step-toggle', '#edit-propcategory, select[name="propCategory"]', context).forEach(function () {
        const select = document.getElementById('edit-propcategory') ||
          document.querySelector('select[name="propCategory"], select#edit-propcategory');

        if (!select) {
          return;
        }

        select.addEventListener('change', function () {
          // Defer to allow any rebuilds to happen
          window.setTimeout(applyVisibility, 0);
        });

        applyVisibility();
      });

      // Also apply visibility on initial attach even if once selector didn't match
      applyVisibility();
    }
  };


Drupal.behaviors.partnerStatus = {
    attach: function (context) {

      once('partner-status', '.js-change-status', context).forEach(function (element) {

        $(element).on('click', function (e) {

          e.preventDefault();

          $('#node-id').val($(this).data('node'));
          $('#status-select').val($(this).data('status'));

          $('#statusModalChange').modal('show');
        });

      });

      once('save-status', '#save-status', context).forEach(function (element) {

        $(element).on('click', function () {

          $.ajax({
            url: '/partner/update-status',
            type: 'POST',
            data: {
              nid: $('#node-id').val(),
              status: $('#status-select').val()
            },
            success: function () {
              location.reload();
            }
          });

        });

      });

    }
  };


})(Drupal, once, jQuery);
