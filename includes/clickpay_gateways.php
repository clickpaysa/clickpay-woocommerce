<?php

defined('CLICKPAY_PAYPAGE_VERSION') or die;

class WC_Gateway_Clickpay_All extends WC_Gateway_Clickpay
{
    protected $_code = 'all';
    protected $_title = 'Online payments powered by Clickpay';
    protected $_description = 'Clickpay - All supported payment methods';

    protected $_icon = "clickpay.png";
}

class WC_Gateway_Clickpay_Creditcard extends WC_Gateway_Clickpay
{
    protected $_code = 'creditcard';
    protected $_title = 'Clickpay - CreditCard';
    protected $_description = 'Clickpay - CreditCard payment method';

    protected $_icon = "creditcard.svg";
}

class WC_Gateway_Clickpay_Mada extends WC_Gateway_Clickpay
{
    protected $_code = 'mada';
    protected $_title = 'Clickpay - mada';
    protected $_description = 'Clickpay - mada payment method';

    protected $_icon = "mada.svg";
}


class WC_Gateway_Clickpay_Applepay extends WC_Gateway_Clickpay
{
    protected $_code = 'applepay';
    protected $_title = 'Clickpay - ApplePay';
    protected $_description = 'Clickpay - ApplePay payment method';

    protected $_icon = "applepay.svg";
}

class WC_Gateway_Clickpay_Amex extends WC_Gateway_Clickpay
{
    protected $_code = 'amex';
    protected $_title = 'Clickpay - Amex';
    protected $_description = 'Clickpay - Amex payment method';
}

class WC_Gateway_Clickpay_Tabby extends WC_Gateway_Clickpay
{
    protected $_code = 'tabby';
    protected $_title = 'Clickpay - Tabby';
    protected $_description = 'Clickpay - Tabby payment method';

    protected $_icon = "tabby.svg";
}

