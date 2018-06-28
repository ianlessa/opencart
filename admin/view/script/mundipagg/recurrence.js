function removeRow(e) {
    $(e).parent().parent().remove();
    return false;
}

function makeInput(inputName, type, value){
    return '<input type="hidden" name="' + inputName + "[" + type + ']" value="' + value + '"/>'
}

function makeSpan(text) {
    return '<span class="label label-default">' + text + '</span>';
}

function formatValueDiscount(value, type, symbol) {
    if (value.length  <= 0 ) {
        return "Não";
    }

    if (type == 'percent') {
        return [value, symbol].join("");
    }
    return [symbol, value].join(" ");
}

function makeFrequencyColumn(inputName, frequency)
{
    var newCol = $("<td>");

    var name = ' Repetição';
    if (frequency > 1) {
        name = ' Repetições';
    }

    newCol.append(makeSpan(frequency + name));
    newCol.append(makeInput(inputName, "frequency", frequency));
    return newCol;
}

function makeIntervalColumn(inputName, interval)
{
    var newCol = $("<td>");
    newCol.append(makeSpan(interval));
    newCol.append(makeInput(inputName, "interval", interval));

    return newCol;
}

function makeDiscountColumn(inputName, discount, type_discount, type_discount_symbol)
{
   var newCol = $("<td>");
   value_type_discount = formatValueDiscount(discount, type_discount, type_discount_symbol);

   newCol.append(makeSpan(value_type_discount));
   newCol.append(makeInput(inputName, "discount", discount));
   newCol.append(makeInput(inputName, "type_discount", type_discount));

    return newCol;
}

function makeButtonColumn()
{
    var newCol = $("<td>");
    newCol.append('<button type="button" onCLick="removeRow(this)" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>');

    return newCol;
}

$(document).ready(function(e){

    $(document).on('click', '.btn-add-frequency', function(e)
    {
        e.preventDefault();

        var table = $('.table-interval');
        var currentNumber = table.data('number') + 1;
        var newRow = $("<tr>");
        var currentFrequency = $(this).parents('.frequency:first');

        var frequency = currentFrequency.find('#frequency').val();
        var interval = currentFrequency.find('#interval').val();
        var discount = currentFrequency.find('#discount').val();
        var type_discount_symbol = currentFrequency.find('#type_discount').attr('data-symbol');
        var type_discount = currentFrequency.find('#type_discount').val();

        var inputName = "intervals[" + currentNumber + "]";

        newRow.append(makeFrequencyColumn(inputName, frequency));
        newRow.append(makeIntervalColumn(inputName, interval));
        newRow.append(makeDiscountColumn(
            inputName,
            discount,
            type_discount,
            type_discount_symbol
        ));
        newRow.append(makeButtonColumn());

        table.append(newRow);
        table.data('number', currentNumber);

    });

    $( document ).on( 'click', '.bs-dropdown-to-select-group .dropdown-menu li a', function( event ) {
        event.preventDefault();
        var $target = $( event.currentTarget );
        var symbol = $target.text();
        $target.closest('.bs-dropdown-to-select-group')
            .find('[data-bind="bs-drp-sel-value"]').val($target.attr('data-value'))
            .attr('data-symbol', symbol)
            .end()
            .children('.dropdown-toggle').dropdown('toggle');

        $target.closest('.bs-dropdown-to-select-group')
            .find('[data-bind="bs-drp-sel-label"]').text(symbol);



        $target.closest('.bs-dropdown-to-select-group')
            .find('[data-bind="bs-drp-sel-value"]')
            .attr('data-symbol', symbol);

        return false;
    });

});
