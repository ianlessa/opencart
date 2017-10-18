<?php

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Controller\Settings;
use Mundipagg\Controller\CreditCardSettings;
use Mundipagg\Controller\BoletoSettings;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Order;

/**
 * ControllerExtensionPaymentMundipagg
 *
 * @package Mundipagg
 */
class ControllerExtensionPaymentMundipagg extends Controller
{
    /**
     * @var array
     */
    private $error = array();

    /**
     * @var array
     */
    private $data = array();

    /**
     * Load basic data and call postRequest or getRequest methods accordingly
     *
     * @return void
     */
    public function index()
    {
        $this->load->language('extension/payment/mundipagg');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->postRequest();
        } else {
            $this->getRequest();
        }
    }

    /**
     * This method is called when the module is being installed
     *
     * First, it save an event and then call the install method from
     * module model
     *
     * @return void
     */
    public function install()
    {
        $this->load->model('extension/payment/mundipagg');
        $this->model_extension_payment_mundipagg->install();
    }

    /**
     * This method is called when the module is being removed
     *
     * It calls the methods from model responsible for delete all
     * data created and used by module
     *
     * @return void
     */
    public function uninstall()
    {
        $this->load->model('extension/payment/mundipagg');
        $this->model_extension_payment_mundipagg->uninstall();
    }

    public function previewCancelOrder()
    {
        $this->load->model('sale/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
            $order_info = $this->model_sale_order->getOrder($order_id);
        }

        if (isset($order_info)) {
            $result = $this->db->query(
                'SELECT *, CASE WHEN status != "canceled" AND status != "failed" THEN 1 ELSE 0 END AS cancel FROM `' . DB_PREFIX . 'mundipagg_charge`'.
                ' WHERE opencart_id = ' . $order_id
            );
            foreach ($result->rows as $key => $row) {
                $data['charges'][$key] = $row;
                if ($row['cancel']) {
                    $data['charges'][$key]['cancel'] = $this->url->link(
                        'extension/payment/mundipagg/cancelOrder',
                        'user_token=' . $this->session->data['user_token'].
                        '&order_id='.$order_id.
                        '&charge=' . $row['charge_id'],
                        true
                    );
                }
            }
            $data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true);

            $data['text_order'] = sprintf('Order (#%s)', $order_id);
            $data['column_product'] = 'Product';
            $data['column_model'] = 'Model';
            $data['column_quantity'] = 'Quantity';
            $data['column_price'] = 'Unit Price';
            $data['column_total'] = 'Total';
            $data['products'] = array();

            $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

            foreach ($products as $product) {
                $option_data = array();

                $options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

                foreach ($options as $option) {
                    if ($option['type'] != 'file') {
                        $option_data[] = array(
                            'name'  => $option['name'],
                            'value' => $option['value'],
                            'type'  => $option['type']
                        );
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $option_data[] = array(
                                'name'  => $option['name'],
                                'value' => $upload_info['name'],
                                'type'  => $option['type'],
                                'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], true)
                            );
                        }
                    }
                }

                $data['products'][] = array(
                    'order_product_id' => $product['order_product_id'],
                    'product_id'       => $product['product_id'],
                    'name'             => $product['name'],
                    'model'            => $product['model'],
                    'option'           => $option_data,
                    'quantity'         => $product['quantity'],
                    'price'            => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'total'            => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'href'             => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], true)
                );
            }

            $data['vouchers'] = array();

            $vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

            foreach ($vouchers as $voucher) {
                $data['vouchers'][] = array(
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
                    'href'        => $this->url->link('sale/voucher/edit', 'user_token=' . $this->session->data['user_token'] . '&voucher_id=' . $voucher['voucher_id'], true)
                );
            }

            $data['totals'] = array();

            $totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

            foreach ($totals as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view(
                'extension/payment/mundipagg_previewCancelOrder',
                $data
            ));
        } else {
            return new Action('error/not_found');
        }
    }

    public function cancelOrder()
    {
        $this->load->model('sale/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
            $order_info = $this->model_sale_order->getOrder($order_id);
            if (isset($order_info)) {
                try {
                    $order = new Order($this);
                    $result = $this->db->query(
                        'SELECT charge_id, '.
                        '       CASE WHEN status != "canceled" AND status != "failed" THEN 1 ELSE 0 END AS cancel'.
                        '  FROM `' . DB_PREFIX . 'mundipagg_charge`'.
                        ' WHERE opencart_id = ' . $order_id
                    );
                    $charge_id = $this->request->get['charge'];
                    foreach ($result->rows as $charge) {
                        if ($charge['charge_id'] == $charge_id) {
                            if ($charge['cancel']) {
                                $charge = $order->cancelCharge($charge_id);
                                $this->session->data['success'] = 'Charge canceled with sucess!';
                            } else {
                                $this->session->data['error_warning'] = 'Charge don\'t available to cancel';
                            }
                            break;
                        }
                    }
                    if (empty($this->session->data['success']) && !$this->session->data['error_warning']) {
                        $this->session->data['error_warning'] = 'Fail on cancel charge';
                    } else {
                        $order->createOrUpdateCharge($order_info, $charge);
                    }
                } catch (\Exception $e) {
                    $this->session->data['error_warning'] = $e->getMessage();
                }
                return new Action('sale/order');
            }
        }
        return new Action('error/not_found');
    }

    /**
     * Deal with post requests, in other words, settings being changed
     *
     * @return void
     */
    private function postRequest()
    {
        // validate data here
        if (!$this->validate()) {
            return;
        }

        $this->saveSettings($this->request->post);

        $this->session->data['success'] = $this->language->get('misc')['success'];
        http_response_code(302);
        $this->response->addHeader('Location: '.
            str_replace(
                ['&amp;', "\n", "\r"],
                ['&', '', ''],
                $this->url->link(
                    'marketplace/extension',
                    'user_token=' . $this->session->data['user_token'] . '&type=payment',
                    true
                )
            ));
        $this->response->setOutput(true);
    }

    /**
     * Deal with get requests
     *
     * Get layout components, set language, get bread crumbs
     * and show the form with the previously saved settings
     *
     * @return void
     */
    private function getRequest()
    {
        $this->setLayoutComponents();
        $this->setLanguageData();
        $this->setBreadCrumbs();
        $this->setFormControlData();
        $this->getSavedSettings();
        $this->getCustomFields();

        $this->response->setOutput(
            $this->load->view(
                'extension/payment/mundipagg',
                $this->data
            )
        );
    }

    /**
     * This method saves module settings
     *
     * @param array $postRequest Settings posted from admin panel
     * @return void
     */
    private function saveSettings($postRequest)
    {
        // save credit card payment information
        $this->load->model('extension/payment/mundipagg');
        $this->model_extension_payment_mundipagg->savePaymentInformation(
            $postRequest['creditCard']
        );

        unset($postRequest['creditCard']);

        // save module settings
        $modules = array(
            'payment_mundipagg' => '/^payment_mundipagg/'
        );

        // use array_walk
        foreach ($modules as $module => $pattern) {
            $this->model_setting_setting->editSetting(
                $module,
                $this->getSettingsFor($pattern, $postRequest)
            );
        }
    }

    /**
     * It gets the request and return an array with the correct information to be stored
     *
     * @param string $pattern Contain the pattern to be searched for
     * @param array $request Post request from admin panel
     * @return array
     */
    private function getSettingsFor($pattern, $request)
    {
        return array_filter(
            $request,
            function ($key) use ($pattern) {
                return preg_match($pattern, $key);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Sets opencart dashboard layout components
     *
     * It puts opencart header, left column and footer
     *
     * @return void
     */
    private function setLayoutComponents()
    {
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');
    }

    /**
     * Get language data from language file
     *
     * @return void
     */
    private function setLanguageData()
    {
        $this->data['general'] = $this->language->get('general');
        $this->data['credit_card'] = $this->language->get('credit_card');
        $this->data['boleto'] = $this->language->get('boleto');
        $this->data['misc'] = $this->language->get('misc');
    }

    /**
     * Set bread crumbs, which will be shown in the dashboard panel
     *
     * @return void
     */
    private function setBreadCrumbs()
    {
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token'],
                true
            ));

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=payment',
                true
            ));

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'extension/payment/mundipagg',
                'user_token=' . $this->session->data['user_token'],
                true
            ));
    }

    /**
     * Set the action to the save/cancel buttons from dashboard
     *
     * @return void
     */
    private function setFormControlData()
    {
        $this->data['action'] = $this->url->link(
            'extension/payment/mundipagg',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $this->data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment',
            true
        );
    }


    /**
     * Get the previously saved module settings
     *
     * @return void
     */
    private function getSavedSettings()
    {
        $mundiPaggSettings = new Settings($this);
        $creditCardSettings = new CreditCardSettings($this);
        $boletoSettings = new BoletoSettings($this);

        $this->data['settings'] = array(
            'general_status'             => $this->config->get('payment_mundipagg_status'),
            'general_mapping_number'     => $this->config->get('payment_mundipagg_mapping_number'),
            'general_mapping_complement' => $this->config->get('payment_mundipagg_mapping_complement'),
            'general_prod_secret_key'    => $this->config->get('payment_mundipagg_prod_secret_key'),
            'general_test_secret_key'    => $this->config->get('payment_mundipagg_test_secret_key'),
            'general_prod_pub_key'       => $this->config->get('payment_mundipagg_prod_public_key'),
            'general_test_pub_key'       => $this->config->get('payment_mundipagg_test_public_key'),
            'general_test_mode'          => $this->config->get('payment_mundipagg_test_mode'),
            'general_log_enabled'        => $this->config->get('payment_mundipagg_log_enabled'),
            'general_payment_title'      => $this->config->get('payment_mundipagg_title'),
        );

        $this->data['settings'] = array_merge(
            $this->data['settings'],
            $creditCardSettings->getAllSettings(),
            $boletoSettings->getAllSettings()
        );

        $this->load->model('extension/payment/mundipagg');
        $this->data['creditCard'] = $this->model_extension_payment_mundipagg->getCreditCardInformation();
        $this->data['boletoInfo'] = $this->model_extension_payment_mundipagg->getBoletoInformation();
    }

    /**
     * Set error messages
     *
     * TODO: make this method cover more possible errors
     *
     * @return void
     */
    private function setError()
    {
        if (isset($this->error['error_service_key'])) {
            $data['error_service_key'] = $this->error['error_service_key'];
        } else {
            $data['error_service_key'] = '';
        }
    }

    /**
     * Simple validation
     *
     * Validates user permission
     * TODO: think about (and implement) other validation necessities
     *
     * @return Boolean
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/mundipagg')) {
            // put error message inside language file
            $this->error['warning'] = 'Permission error';
        }

        return !$this->error;
    }

    /**
     * Return an array with custom fields
     */
    private function getCustomFields()
    {
        $this->load->model('customer/custom_field');
        $customFields = $this->model_customer_custom_field->getCustomFields();
        $this->data['general_custom_fields'] = $customFields;
    }
}
