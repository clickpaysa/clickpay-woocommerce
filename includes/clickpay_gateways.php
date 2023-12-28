<?php

defined('CLICKPAY_PAYPAGE_VERSION') or die;

class WC_Gateway_Clickpay_All extends WC_Gateway_Clickpay
{
    protected $_code = 'all';
    protected $_title = 'Online payments powered by ClickPay';
    protected $_description = 'ClickPay - All supported payment methods';

    protected $_icon = "clickpay.png";
}

class WC_Gateway_Clickpay_Creditcard extends WC_Gateway_Clickpay
{
    protected $_code = 'creditcard';
    protected $_title = 'ClickPay - CreditCard';
    protected $_description = 'ClickPay - CreditCard payment method';

    protected $_icon = "creditcard.svg";
}

class WC_Gateway_Clickpay_Mada extends WC_Gateway_Clickpay
{
    protected $_code = 'mada';
    protected $_title = 'ClickPay - mada';
    protected $_description = 'ClickPay - mada payment method';

    protected $_icon = "mada.svg";
}

class WC_Gateway_Clickpay_Applepay extends WC_Gateway_Clickpay
{
    protected $_code = 'applepay';
    protected $_title = 'ClickPay - ApplePay';
    protected $_description = 'ClickPay - ApplePay payment method';

    protected $_icon = "applepay.svg";
}

class WC_Gateway_Clickpay_Amex extends WC_Gateway_Clickpay
{
    protected $_code = 'amex';
    protected $_title = 'ClickPay - Amex';
    protected $_description = 'ClickPay - Amex payment method';
}

class WC_Gateway_Clickpay_Tabby extends WC_Gateway_Clickpay
{
    protected $_code = 'tabby';
    protected $_title = 'ClickPay - Tabby';
    protected $_description = 'ClickPay - Tabby payment method';

    protected $_icon = "tabby.svg";
}

