var MundiPagg = {};

MundiPagg.Validator = function() {
    return {
        validateForm: function (form) {
            var hasErrors = false;
            var errors = {
                'credit-card-number': {},
                'expiration': {},
                'holder-name': {},
                'cvv' : {},
                'new_installments': {},
                'saved_installments': {}
            };
            var inputsToValidate = form.find('[data-mundicheckout-element]');

            var ignoredForms = JSON.parse(form.attr('disabled-forms'));

            inputsToValidate.each(function(index,element){
                var checkoutElement = $(element).attr('data-mundicheckout-element').split("-");;
                var elementIndex = checkoutElement[1];
                var elementType = checkoutElement[0];
                var elementValue = $(element).val();

                //if the form is on the disable list, validate only saved card installments;
                if (
                    ignoredForms.indexOf(parseInt(elementIndex)) > -1 &&
                    elementType !== 'saved_installments'
                ) {
                    return;
                }

                //if the form isn't on the disable list, ignore saved card installments validation;
                if (
                    ignoredForms.indexOf(parseInt(elementIndex)) === -1 &&
                    elementType === 'saved_installments'
                ) {
                    return;
                }

                switch(elementType) {
                    case 'number':
                        error = this.validateCardNumber(elementValue);
                        if (typeof error !== 'undefined') {
                            hasErrors = true;
                            errors['credit-card-number'][elementIndex] = error;
                        }
                        break;
                    case 'holder_name':
                        error = this.validateHolderName(elementValue);
                        if (typeof error !== 'undefined') {
                            hasErrors = true;
                            errors['holder-name'][elementIndex] = error;
                        }
                        break;
                    case 'exp_month':
                    case 'exp_year':
                        var exp_month = $('[data-mundicheckout-element="exp_month-'+elementIndex+'"]');
                        var exp_year = $('[data-mundicheckout-element="exp_year-'+elementIndex+'"]');
                        error = this.validateExpiration($(exp_month).val(), $(exp_year).val());
                        if (typeof error !== 'undefined') {
                            hasErrors = true;
                            errors['expiration'][elementIndex] = error;
                        }
                        break;
                    case 'cvv':
                        error =  this.validateCVV($(element).val());
                        if (typeof error !== 'undefined') {
                            hasErrors = true;
                            errors['cvv'][elementIndex] = error;
                        }
                        break;
                    case 'new_installments':
                    case 'saved_installments':
                        error = this.validateInstallments($(element).val());
                        if (typeof error !== 'undefined') {
                            hasErrors = true;
                            errors[elementType][elementIndex] = error;
                        }
                    break;
                }
            }.bind(this));
            return {
                hasErrors : hasErrors,
                errors : errors
            };
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

        validateInstallments: function (number) {
            var errorMsg = "Por favor, selecione as parcelas.";
            return number === '' ? errorMsg : undefined;
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
            var errorIndexes = {
                'credit-card-number': 'credit-card-number-message',
                'expiration': 'expiration-date-message',
                'holder-name': 'holder-name-message',
                'cvv' : 'cvv-message',
                'new_installments': 'new_installments-message',
                'saved_installments': 'saved_installments-message'
            };

            Object.keys(errorIndexes).forEach(function(property){
                if (errors[property]) {
                    Object.keys(errors[property]).forEach(function(key){
                        $('#' + errorIndexes[property] + '-' + key).text(errors[property][key]);
                    });
                }
            });
        },

        clearErrorMessages: function(form) {
            form.find('.form-validation-error-message').each(function(index,element){
               $(element).text('');
            });
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
            this.submitForm.each(function(index,formElement){
                formElement.addEventListener('submit', function(event) {
                    this.clearErrorMessages($(formElement));
                    var result = this.validator.validateForm($(formElement));

                    if (result.hasErrors) {
                        this.showErrorMessages(result.errors);
                        event.stopImmediatePropagation();
                        event.preventDefault();
                    }
                }.bind(this), false);
            }.bind(this));

            // add listener to clean up card number field
            this.cardNumberFields.each(function(index,input){
                input.addEventListener("keyup", function() {
                    this.cleanUpField(input, /[^[0-9]/gi);
                }.bind(this), false);
            }.bind(this));

            // add listener to clean up card name field
            this.cardNameFields.each(function(index,input){
                input.addEventListener("keyup", function() {
                    this.cleanUpField(input, /[^[a-zA-Z ]/gi);
                }.bind(this), false);
            }.bind(this));

            // add listener to clean up cvv field
            this.cardCVVFields.each(function(index,input){
                input.addEventListener("keyup", function() {
                    this.cleanUpField(input, /[^[0-9]/gi);
                }.bind(this), false);
            }.bind(this));
        },

        cleanUpField: function(field, regex) {
            field.value = field.value.replace(regex, '');
        },

        initializeVariables: function() {

            this.cardBrand = document.querySelector('[data-mundicheckout-brand]');
            this.submitForm = $('[data-mundicheckout-form]');
            this.cardNumberFields = $('.mundipagg-cardNumber');
            this.cardNameFields = $('.mundipagg-cardName');
            this.cardCVVFields = $('.mundipagg-cardCVV');
        },

        hideAll: function() {
            hideElements();
        },

        showSpecific: function(brand, amount, inputId) {
            console.log(brand, amount, inputId);

            if (typeof brand === 'undefined' && amount > 0) {
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

$("#mundipaggCheckout").ready(function () {

    var mundiValidator = MundiPagg.Validator();
    var mundiForm = MundiPagg.Form();

    mundiForm.setup(mundiValidator);

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

    $(".mundipagg-saved-creditCard").on("change", function(){
        switchNewSaved($(this).val(), $(this).attr('inputId'));
        fillSavedCreditCardInstallments($(this));
    })

    $(".mundipagg-saved-creditCard").each(function(){
        fillSavedCreditCardInstallments($(this));
    });

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
        $("#saved-creditcard-installments-" + formId).parent().hide();
    } else {
        $(".creditCard-" + formId).hide();
        $("#saved-creditcard-installments-" + formId).parent().show();
    }
}

function installments(obj) {

    var inputId = obj.attr("inputId");
    clearInstallments(inputId);

    if (
        obj.html() != "" &&
        typeof obj.html() !== 'undefined'
    ) {
        var brandImage = obj.children("img");
        var brand = brandImage.attr("src").split('/').pop().split('.')[0]
        var amount = $("#amount-" + inputId).val();

        if (typeof brand !== "undefined" && amount > 0) {
            getInstallments(brand, amount, inputId, "new");
        }
    }
}

function clearInstallments(inputId) {

}

function getInstallments(brand, amount, inputId, newSaved) {
    if (typeof brand === 'undefined' && amount > 0) {
        //Hide
    } else {

        url = "index.php?route=extension/payment/mundipagg/api/installments&";
        url += "brand=" + brand.toLowerCase();
        url += "&total=" + amount;

        $.get(url)
        .done(function( data ) {
            var html = buildInstallmentsOptions(brand, data);
            $("#" + newSaved + "-creditcard-installments-" + inputId).html(html);
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

function fillSavedCreditCardInstallments(obj) {
    if(obj.val() != "new") {
        var inputId = obj.attr("inputId");
        var amount = $("#amount-" + inputId).val();
        var brand = obj.children("option:selected").attr("brand");

        getInstallments(brand, amount, inputId, "saved");
    }
}
