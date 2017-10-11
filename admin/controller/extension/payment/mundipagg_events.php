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
  $(this).find('td:last').css({display:'flex'}).prepend(
    '<div class="btn-group" style="margin-left:10px;">'+
    '<a href="?route=extension/payment/mundipagg/previewCancelOrder&amp;user_token='+
        getURLVar('user_token')+'&amp;order_id='+
        $(this).find('[name="selected[]"]').val()+
        '" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Cancel"><i class="fa fa-trash"></i></a>'+
    '</div>'
  );
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
