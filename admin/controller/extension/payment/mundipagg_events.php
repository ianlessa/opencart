<?php

use Mundipagg\Model\Order;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

/**
 * ControllerExtensionPaymentMundipaggEvents deal with module events
 *
 * The purpose of this class is to centralize methods related to important
 * events to the module
 *
 * @package Mundipagg
 *
 */
class ControllerExtensionPaymentMundipaggEvents extends Controller
{
    public function onOrderList(string $route, $data = array(), $template = null)
    {
        $cancel = array();
        $cancelCapture = array();

        $ids = array_map(function ($row) {
            return (int) $row['order_id'];
        }, $data['orders']);

        $Order = new Order($this);
        $orders = $Order->getOrders(
            array(
                'order_id' => $ids,
                'order_status_id' => [1,15,2]
            ),
            array(
                'order_status_id',
                'order_id'
            )
        );

        foreach ($orders->rows as $order) {
            switch ($order['order_status_id']) {
                case 1: // I can capture, cancel
                    $cancelCapture[] = '#form-order table tbody tr ' .
                        'input[name="selected[]"][value=' . $order['order_id'] . ']';
                    break;
                case 15: // I can cancel
                case 2:
                    $cancel[] = '#form-order table tbody tr ' .
                        'input[name="selected[]"][value=' . $order['order_id'] . ']';
            }
        }
        $cancelCapture = implode(',', $cancelCapture);
        $cancel = implode(',', $cancel);
        $httpServer = HTTPS_SERVER;
        $footer = <<<FOOTER
<script>
MundiMenu = function(element, html) {
element.prepend(
  '<div class="btn-group" style="display: block; margin-left: 50px;">'+
    '<a href="?route=extension/payment/mundipagg/previewChangeCharge'+
        '&amp;user_token='+
        getURLVar('user_token')+'&amp;order_id='+
        $(this).find('[name="selected[]"]').val()+
        '" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="MundiPagg">'+
        '<img src="{$httpServer}view/image/mundipagg/mundipagg-mini.png" alt="MundiPagg" style="width: 15px;" />'+
    '</a>'+
    '<button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle" aria-expanded="false"><span class="caret"></span></button>'+
    '<ul class="dropdown-menu dropdown-menu-right" style="margin-top: 39px; margin-right: 91px;">'+
        html+
    '</ul>'+
  '</div>'
);
}
template = function(status, order_id) {
    return '<li><a href="?route=extension/payment/mundipagg/previewChangeCharge'+
        '&amp;status='+status+
        '&amp;user_token='+getURLVar('user_token')+
        '&amp;order_id='+order_id+
        '"><i class="fa fa-'+(status == 'cancel'?'trash':'thumbs-up')+'"></i> '+
            (status == 'cancel'?'Cancel':'Capture')+
        '</a></li>';
}
jQuery('$cancelCapture').each(function(){
    html =template('cancel',  jQuery(this).val());
    html+=template('capture', jQuery(this).val());
    MundiMenu(jQuery(this).parents('tr').find('td:last'), html)
});
jQuery('$cancel').each(function(){
    html =template('cancel', jQuery(this).val());
    MundiMenu(jQuery(this).parents('tr').find('td:last'), html)
});
</script>
FOOTER;
        $data['footer'] = $footer . $data['footer'];
        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }
        $template = new Template($this->registry->get('config')->get('template_engine'));

        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }

        return $template->render($this->registry->get('config')->get('template_directory') . $route, $this->registry->get('config')->get('template_cache'));
    }
}
