var MundiPagg = {};

MundiPagg.Validator = function() {
    return {
        validateForm: function () {
            var errors = [];

            var creditCardNumber = this.validateCardNumber($("#cardNumber").val());
            var expiration = this.validateExpiration($("#cardExpMonth").val(), $("#cardExpYear").val());
            var holderName = this.validateHolderName($("#cardName").val());
            var cvv = this.validateCVV($("#cardCVV").val());

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

        validateExpiration: function (month, year) {
            var date = new Date();

            var expDate = new Date(year, month - 1, 1);
            var today = new Date(date.getFullYear(), date.getMonth(), 1);

            if (expDate < today) {
                return 'Cartão expirado';
            }

            return undefined;
        },

        validateHolderName: function (name) {
            if (!/^[a-zA-Z ]+$/.test(name)) {
                return 'Nome no cartão inválido';
            }

            return undefined;
        },

        validateCVV: function (cvv) {
            if (cvv.length > 4 || cvv.length < 3 || !/^[0-9]+$/.test(cvv)) {
                return 'CVV inválido';
            }

            return undefined;
        },

        validateCardNumber: function (number) {
            if (!this.isValidCreditCardNumber(number)) {
                return 'Cartão de crédito inválido';
            }

            if (!/^[0-9]+/.test(number)) {
                return 'Por favor, digite somente números.';
            }

            return undefined;
        },

        isValidCreditCardNumber: function (value) {
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
        }
    };
};

MundiPagg.Form = function() {
    return {
        setup: function(validator) {
            this.initializeVariables();
            this.hideAll();
            this.addListeners();
            this.validator = validator;
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
                var brandUrl = this.cardBrand.getElementsByTagName('img')[0];

                this.hideAll();

                if (brandUrl) {
                    brandUrl = brandUrl.getAttribute('src').split('/').pop().split('.')[0].toLowerCase();
                    this.showSpecific(brandUrl);
                }
            }.bind(this), false);

            // listener to handle form validation
            this.submitForm.addEventListener('submit', function(event) {
                this.clearErrorMessages();
                var result = this.validator.validateForm();

                if (Object.keys(result).length > 0) {
                    this.showErrorMessages(result);
                    event.stopImmediatePropagation();
                    event.preventDefault();
                }
            }.bind(this), false);

            // add listener to clean up card number field
            this.cardNumberField.addEventListener("keyup", function() {
                this.cleanUpField(this.cardNumberField, /[^[0-9]/gi);
            }.bind(this), false);

            // add listener to clean up card number field
            this.cardNameField.addEventListener("keyup", function() {
                this.cleanUpField(this.cardNameField, /[^[a-zA-Z ]/gi);
            }.bind(this), false);

            // add listener to clean up cvv field
            this.cardCVVField.addEventListener("keyup", function() {
                this.cleanUpField(this.cardCVVField, /[^[0-9]/gi);
            }.bind(this), false);
        },

        cleanUpField: function(field, regex) {
            field.value = field.value.replace(regex, '');
        },

        initializeVariables: function() {
            this.cardBrand = document.querySelector('[data-mundicheckout-brand]');
            this.submitForm = $('[data-mundicheckout-form]')[0];

            this.creditCardNumberMessageField = $('#credit-card-number-message').text('');
            this.expirationDateMessageField = $('#expiration-date-message').text('');
            this.holderNameMessageField = $('#holder-name-message').text('');
            this.cvvMessageField = $('#cvv-message').text('');
            this.tokenErrorMessage = $('#token-error-message').text('');

            this.cardNumberField = document.getElementById('cardNumber');
            this.cardNameField = document.getElementById('cardName');
            this.cardCVVField = document.getElementById('cardCVV');
        },

        hideAll: function() {
            hideElements();
        },

        showSpecific: function(brand) {
            if (brand) {
                showSpecific(brand);
            } else {
                this.hideAll();
            }
        }
    };
};

function hideElements() {
    $(".installments").each(function () {
        $(this).val("");
        $(this).children().each(function() {
            $(this).addClass("hidden");
        });
    })
}

function showSpecific(brand) {
    if (brand != "" && brand != undefined) {
        brand = brand.toLowerCase();
        var brandSelector = '[data-card-brand="' + brand + '"]';
        var installments = document.querySelectorAll(brandSelector);

        installments.forEach(function(element) {
            element.classList.remove('hidden');
        });
    }
}

(function () {
    var mundiValidator = MundiPagg.Validator();
    var mundiForm = MundiPagg.Form();

    mundiForm.setup(mundiValidator);

    console.log('starting');

    MundiCheckout.init(
        function(data) {
            console.log('success');
            console.log(data);
            return true;
        },
        function(error) {
            console.log('error');
            console.log(error);
            $('#token-error-message').text('Ocorreu um erro, verifique as informações fornecidas');
        }
    );
})();

function switchNewSaved(value, formId) {
    console.log(formId);
    if(value == "new") {
        $(".creditCard-" + formId).show();
        $(".savedCreditcard").hide();
    } else {
        $(".creditCard-" + formId).hide();
        $(".savedCreditcard").show();
    }
}

function changeInstallments() {
    showSpecific(
        $( "#mundipaggSavedCreditCard option:selected" ).attr("brand")
    );
}

$("#savedCreditcardInstallments").ready(function () {
    changeInstallments();
});