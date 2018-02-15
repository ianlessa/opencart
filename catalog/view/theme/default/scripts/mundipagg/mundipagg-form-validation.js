/* var MundiPagg = {};

MundiPagg.Validator = function(cardNumber) {
    var cardNumber = cardNumber;

    return {
        validateForm: function () {
            var errors = [];

            var creditCardNumber = this.validateCardNumber($(this.cardNumber).val());
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
                    console.log(brandUrl);
                this.hideAll();

                if (brandUrl) {
                    brand = brandUrl.getAttribute('src').split('/').pop().split('.')[0];
                    inputId = this.cardBrand.getAttribute('inputId');
                    amount = $("#amount-" + inputId).val();

                    this.showSpecific(brand, amount, inputId);
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

        showSpecific: function(brand, amount, inputId) {
            console.log(brand, amount, inputId);

            if (typeof brand === 'undefined' && amount > 0) {
                this.hideAll();
            } else {
                showSpecific(brand, amount, inputId);
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

(function () {
    var mundiValidator = MundiPagg.Validator();
    var mundiForm = MundiPagg.Form();

    mundiForm.setup(mundiValidator);

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
})();*/



$("#mundipaggCheckout").ready(function () {

    //Call checkout.js methods
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

    //Brand listener
    $('.input-group-addon').bind("DOMSubtreeModified",function(){
        installments($(this));
    });
});


function changeInstallments() {
    return;
}

function switchNewSaved(value, formId) {
    if(value == "new") {
        $(".creditCard-" + formId).show();
        $(".savedCreditcard").hide();
    } else {
        $(".creditCard-" + formId).hide();
        $(".savedCreditcard").show();
    }
}

function installments(obj) {

    var inputId = obj.attr("inputId");
    clearInstallments(inputId);

    if (
        obj.html() != "" &&
        typeof obj.html()  !== 'undefined'
    ) {
        var brandImage = obj.children("img");
        var brand = brandImage.attr("src").split('/').pop().split('.')[0]
        var amount = $("#amount-" + inputId).val();

        console.log(brand, amount, inputId);

        if (typeof brand !== 'undefined' && amount > 0) {
            getInstallments(brand, amount, inputId);
        }
    }
}

function clearInstallments(inputId) {
    //$("#new-creditcard-installments-" + inputId).html("");
}

function getInstallments(brand, amount, inputId) {

    if (typeof brand === 'undefined' && amount > 0) {
        //Hide
    } else {

        url = "index.php?route=extension/payment/mundipagg/api/installments&";
        url += "brand=" + brand.toLowerCase();
        url += "&total=" + amount;

        $.get(url)
        .done(function( data ) {
            var html = buildInstallmentsOptions(brand, data);
            $("#new-creditcard-installments-" + inputId).html(html);
        }).fail(function () {
            console.log('Get installments fail');
        });
    }
}


function buildInstallmentsOptions(brand, data) {
    var json = $.parseJSON(data);
    var html = "<option value=''> Selecione </option>";

    Object.keys(json).forEach(function(k){

        var amount = json[k].amount.toFixed(2).replace('.', ',');
        if (json[k].interest === 0) {
            var interest = 'Sem juros';
        } else {
            var interest = json[k].interest + '%';
        }

        html += "<option data-card-brand='" + brand.toLowerCase() + "' ";
        html += "value='" + json[k].times + "|" + json[k].amount + "|" + json[k].interest  + "'>";
        html += brand + " - " + json[k].times + " x R$" + amount + " - " + interest;
        html += "</option>";
    });

    return html;
}

