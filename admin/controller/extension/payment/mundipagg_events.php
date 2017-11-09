<?php

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
        $footer = <<<FOOTER
<script>
jQuery('#form-order table tbody tr').each(function(){
    template = function(status, tr) {
        return '<li><a href="?route=extension/payment/mundipagg/previewChangeCharge'+
            '&amp;status='+status+
            '&amp;user_token='+
            getURLVar('user_token')+'&amp;order_id='+
            $(tr).find('[name="selected[]"]').val()+
            '"><i class="fa fa-'+(status == 'cancel'?'trash':'thumbs-up')+'"></i> '+
                (status == 'cancel'?'Cancel':'Capture')+
            '</a></li>';
    }
    var status = $(this).find("td:eq('3')").text();
    var html = '';
    if (status == 'Pending') {
        html+=template('cancel', this);
        html+=template('capture', this);
    } else if (status == 'Processed' || status == 'Processing') {
        html+=template('cancel', this)
    }
    if (html) {
        $(this).find('td:last').prepend(
          '<div class="btn-group" style="display: block; margin-left: 50px;">'+
            '<a href="?route=extension/payment/mundipagg/previewChangeCharge'+
                '&amp;user_token='+
                getURLVar('user_token')+'&amp;order_id='+
                $(this).find('[name="selected[]"]').val()+
                '" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="MundiPagg">'+
                '<img src="/admin/view/image/mundipagg/mundipagg-mini.png" alt="MundiPagg" style="width: 15px;" />'+
            '</a>'+
            '<button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle" aria-expanded="false"><span class="caret"></span></button>'+
            '<ul class="dropdown-menu dropdown-menu-right" style="margin-top: 39px; margin-right: 91px;">'+
                html+
            '</ul>'+
          '</div>'
        );
    }
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
