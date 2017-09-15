<?php

/**
 * ModelExtensionPaymentMundipagg is responsible for module basic routines
 *
 * The purpose of this class is create and destroy data used by module. It
 * creates (and destroy) two tables: mundipagg_payments and mundipagg_customer,
 * which are used, respectively, to store user's credit card payments settings
 * and the relation between opencart and mundipagg customer ids.
 *
 * @package Mundipagg
 */
class ModelExtensionPaymentMundipagg extends Model
{
    /**
     * Install module
     *
     * @return void
     */
    public function install()
    {
        $this->createPaymentTable();
        $this->createCustomerTable();
        $this->createOrderTable();

        $this->populatePaymentTable();
    }

    /**
     * Uninstall module
     *
     * @return void
     */
    public function uninstall()
    {
        $this->dropPaymentTable();
        $this->dropCustomerTable();
        $this->dropOrderTable();
    }

    /**
     * Install opencart event handlers
     *
     * @return void
     */
    private function installEvents()
    {
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent(
            'payment_mundipagg',
            'catalog/model/account/customer/editCustomer/after',
            'extension/payment/mundipagg_events/onCustomerEdit'
        );
                                                                                 
        $this->model_setting_event->addEvent(
            'payment_mundipagg',
            'catalog/model/account/address/addAddress/after',
            'extension/payment/mundipagg_events/onAddressAdd'
        );
    }

    /***
     * Uninstall opencart event handlers
     *
     * @return void
     */
    private function uninstallEvents()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEvent('payment_mundipagg');
    }

    /**
     * Create Payment table (mundipagg_payments)
     *
     * This table is used to store user settings on credit card transactions
     *
     * @return void
     */
    private function createPaymentTable()
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mundipagg_payments` (
                `brand_name` VARCHAR(20),
                `is_enabled` TINYINT(1),
                `installments_up_to` TINYINT,
                `installments_without_interest` TINYINT,
                `interest` DOUBLE,
                `incremental_interest` DOUBLE
            );"
        );
    }

    /**
     * Drop mundipagg_payments table
     *
     * @return void
     */
    private function dropPaymentTable()
    {
        $this->db->query(
            "DROP TABLE IF EXISTS `" . DB_PREFIX . "mundipagg_payments`;"
        );
    }

    /**
     * Populate mundipagg_payments with preset info from remote json
     *
     * @return void
     */
    private function populatePaymentTable()
    {
        $preset = $this->getPaymentInfo()->brands;

        foreach ($preset as $brand => $value) {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "mundipagg_payments`
                (brand_name, is_enabled, installments_up_to, installments_without_interest, interest, incremental_interest)
                VALUES ('" .
                    $brand . "', " .
                    $value->enabled . ", " .
                    $value->installmentsUpTo . ", " .
                    $value->installmentsWithoutInterest . ", " .
                    $value->interest . ", " .
                    $value->incrementalInterest . "
                );"
            );
        }
    }

    /**
     * Save payment information from module admin panel
     *
     * @param array $payments Payment information from admin panel
     * @return void
     */
    public function savePaymentInformation($payments)
    {
        foreach ($payments as $brand => $info) {
            $sql = "UPDATE `" . DB_PREFIX . "mundipagg_payments` SET " .
            "is_enabled='" . $info['is_enabled'] . "', " .
            "installments_up_to='" . $info['installments_up_to'] . "', " .
            "installments_without_interest='" . $info['installments_without_interest'] . "', " .
            "interest='" . $info['interest'] . "', " .
            "incremental_interest='" . $info['incremental_interest'] . "' " .
            "WHERE brand_name='" . $brand . "'";

            $this->db->query($sql);
        }
    }

    /**
     * Get credit card information from database
     *
     * @return array
     */
    public function getCreditCardInformation()
    {
        $sql = "SELECT * from `". DB_PREFIX ."mundipagg_payments`";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get bank information used to generate boleto
     *
     * @return array
     */
    public function getBoletoInformation()
    {
        return array(
            '341' => 'Itau',
            '033' => 'Santander',
            '237' => 'Bradesco',
            '001' => 'Banco do Brasil',
            '399' => 'HSBC ',
            '104' => 'Caixa',
            '745' => 'CitiBank'
        );
    }

    /**
     * Create customer table, called mundipagg_customer
     *
     * This table is used to create a relation between opencart customers
     * and its respective profile in mundipagg api
     *
     * @return void
     */
    private function createCustomerTable()
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `". DB_PREFIX ."mundipagg_customer` (
                `customer_id` INT(11) NOT NULL,
                `mundipagg_customer_id` VARCHAR(30) NOT NULL,
                UNIQUE INDEX `customer_id` (`customer_id`),
                UNIQUE INDEX `mundipagg_customer_id` (`mundipagg_customer_id`)
            );"
        );
    }

    /**
     * Drop mundipagg_customer table
     *
     * @return void
     */
    private function dropCustomerTable()
    {
        $this->db->query(
            "DROP TABLE IF EXISTS `" . DB_PREFIX . "mundipagg_customer`;"
        );
    }

    /**
     * Create order table, called mundipagg_order
     *
     * This table is used to create a relation between opencart orders
     * and its respective orders in mundipagg api
     *
     * @return void
     */
    private function createOrderTable()
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `". DB_PREFIX ."mundipagg_order` (
                `opencart_id` INT(11) NOT NULL,
                `mundipagg_id` VARCHAR(30) NOT NULL,
                UNIQUE INDEX `opencart_id` (`opencart_id`),
                UNIQUE INDEX `mundipagg_id` (`mundipagg_id`)
            );"
        );
    }

    /**
     * Drop mundipagg_order table
     *
     * @return void
     */
    private function dropOrderTable()
    {
        $this->db->query(
            "DROP TABLE IF EXISTS `" . DB_PREFIX . "mundipagg_order`;"
        );
    }

    /**
     * Get preset payment information from json
     *
     * @return array
     */
    private function getPaymentInfo()
    {
        $baseUrl = 'http://embeddables.eastus2.cloudapp.azure.com/payment/';

        return json_decode(
            file_get_contents(
                $baseUrl . 'payment.json'
            )
        );
    }
}
