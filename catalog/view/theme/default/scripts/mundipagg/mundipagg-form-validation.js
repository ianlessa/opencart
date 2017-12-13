var MundiPagg = {};
MundiPagg.Form = function() {
    return {
        setup: function() {
            this.initializeVariables();
            this.hideAll();
            this.addListeners();
        },

        validateForm: function() {
            var formData = $('#mundi-credit-card-form').serializeArray();
            var errors = [];

            var creditCardNumber = this.validateCardNumber(formData[0]['value']);
            var expiration = this.validateExpiration(formData[2]['value'], formData[3]['value']);
            var holderName = this.validateHolderName(formData[4]['value']);
            var cvv = this.validateCVV(formData[5]['value']);

            if (creditCardNumber !== undefined) {
                errors['credit-card-number'] = creditCardNumber;
            }

            if (expiration !== undefined) {
                errors['expiration'] = expiration;
            }

            if (holderName !== undefined) {
                errors['holder-name'] = holderName;
            }

            if (cvv !== undefined) {
                errors['cvv'] = cvv;
            }

            return errors;
        },

        validateExpiration: function(month, year) {
            var date = new Date();

            var expDate = new Date(year, month - 1, 1);
            var today = new Date(date.getFullYear(), date.getMonth(), 1);

            if (expDate < today) {
                return 'Cartão expirado';
            }

            return undefined;
        },

        validateHolderName: function(name) {
            if (!/^[a-zA-Z ]+$/.test(name)) {
                return 'Nome no cartão inválido';
            }

            return undefined;
        },

        validateCVV: function(cvv) {
            if (cvv.length > 4 || cvv.length < 3 || !/^[0-9]+$/.test(cvv)) {
                return 'CVV inválido';
            }

            return undefined;
        },

        validateCardNumber: function(number) {
            if (!this.isValidCreditCardNumber(number)) {
                return 'Cartão de crédito inválido';
            }

            return undefined;
        },

        isValidCreditCardNumber: function(value) {
            // accept only digits, dashes or spaces
            if (/[^0-9-\s]+/.test(value)) {
                return false;
            }

            var nCheck = 0, nDigit = 0, bEven = false;
            value = value.replace(/\D/g, "");

            for (var n = value.length - 1; n >= 0; n--) {
                var cDigit = value.charAt(n),
                    nDigit = parseInt(cDigit, 10);

                if (bEven) {
                    if ((nDigit *= 2) > 9) nDigit -= 9;
                }

                nCheck += nDigit;
                bEven = !bEven;
            }

            return (nCheck % 10) == 0;
        },

        showErrorMessages: function(errors) {
            if (errors['credit-card-number']) {
                $('#credit-card-number-message').text(errors['credit-card-number']);
            }

            if (errors['expiration']) {
                $('#expiration-date-message').text(errors['expiration']);
            }

            if (errors['holder-name']) {
                $('#holder-name-message').text(errors['holder-name']);
            }

            if (errors['cvv']) {
                $('#cvv-message').text(errors['cvv']);
            }
        },

        clearErrorMessages: function() {
            this.creditCardNumberMessageField.text('');
            this.expirationDateMessageField.text('');
            this.holderNameMessageField.text('');
            this.cvvMessageField.text('');
        },

        addListeners: function() {
            // listener to show/hide installments
            this.cardBrand.addEventListener('DOMSubtreeModified', function(event) {
                this.hideAll();
                this.showSpecific(this.cardBrand.getAttribute('data-mundicheckout-brand'));
            }.bind(this), false);

            // listener to handle form validation
            this.submitForm.addEventListener('submit', function(event) {
                this.clearErrorMessages();
                var result = this.validateForm();

                if (Object.keys(result).length > 0) {
                    this.showErrorMessages(result);
                    event.stopImmediatePropagation();
                    event.preventDefault();
                }
            }.bind(this), false);
        },

        initializeVariables: function() {
            this.cardBrand = document.querySelector('[data-mundicheckout-brand]');
            this.installmentsSelector = document.querySelectorAll('[data-card-brand]');
            this.submitForm = $('[data-mundicheckout-form]')[0];

            this.creditCardNumberMessageField = $('#credit-card-number-message').text('');
            this.expirationDateMessageField = $('#expiration-date-message').text('');
            this.holderNameMessageField = $('#holder-name-message').text('');
            this.cvvMessageField = $('#cvv-message').text('');
        },

        hideAll: function() {
            this.installmentsSelector.forEach(function (element) {
                element.classList.add('hidden');
            });
        },

        showSpecific: function(brand) {
            var brandSelector = '[data-card-brand="' + brand + '"]';
            var installments = document.querySelectorAll(brandSelector);

            if (brand) {
                installments.forEach(function(element) {
                    element.classList.remove('hidden');
                });
            }
            else {
                this.hideAll();
            }
        }
    };
};

(function () {
    var mundiForm = MundiPagg.Form();
    mundiForm.setup();

    MundiCheckout.init(
        function (data) {
            return true;
        },
        function(data) {
        }
    );
})();