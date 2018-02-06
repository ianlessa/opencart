<?php

require_once DIR_SYSTEM."library/mundipagg/vendor/autoload.php";

use Mundipagg\Settings\General as GeneralSettings;

class ModelExtensionPaymentMundipagg extends Model
{
    /**
     * This method is called by opencart when showing the payment methods available
     *
     * @return array
     */
    public function getMethod()
    {
        $settings = new GeneralSettings($this);

        $method_data = array(
                'code'       => $settings->getCode(),
                'title'      => $settings->getPaymentTitle(),
                'terms'      => $settings->getTerms(),
                'sort_order' => $settings->getSortOrder()
        );

        return $method_data;
    }
}
