function initSavedCreditCardsSelect(inputId) {
    var selectElement = $("#mundipaggSavedCreditCard-" + inputId);
    var parentForm = $(selectElement.parents("form").get(0));

    //init form attribute
    var ignoreArray = parentForm.attr('disabled-forms');
    try {
        ignoreArray = JSON.parse(ignoreArray);
    } catch (e) {
        ignoreArray = [];
    }

    ignoreArray.push(inputId);

    parentForm.attr('disabled-forms',JSON.stringify(ignoreArray));

    //set change event handler.
    $(selectElement).change(function(){
        var ignoreArray = parentForm.attr('disabled-forms');

        ignoreArray = JSON.parse(ignoreArray);

        var ignoreFormValidation = selectElement.val() !== "new";

        var indexOfInputId = ignoreArray.indexOf(inputId);
        if (indexOfInputId > -1) {
            ignoreArray.splice(indexOfInputId,1);
        }

        if (ignoreFormValidation) {
            ignoreArray.push(inputId);
        }
        ignoreArray = JSON.stringify(ignoreArray);
        parentForm.attr('disabled-forms',ignoreArray);
    });
}