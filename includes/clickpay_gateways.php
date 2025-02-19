<?php

defined('CLICKPAY_VERSION') or die;

class WC_Gateway_Clickpay_CC_Hosted extends WC_Gateway_Clickpay
{
    protected $_code = 'cchosted';
    protected $_title = 'Hosted online payments powered by Clickpay';
    protected $_description = 'ClickPay - Credit Card Hosted';

    protected $_icon = "clickpay.png";
}

class WC_Gateway_Clickpay_CC_Managed extends WC_Gateway_Clickpay
{
    protected $_code = 'ccmanaged';
    protected $_title = 'Credit card form based payments powered by Clickpay';
    protected $_description = 'Clickpay - Credit Card Managed';

    protected $_icon = "clickpay.svg";
}


class WC_Gateway_Clickpay_Applepay extends WC_Gateway_Clickpay
{
    protected $_code = 'ccapplepay';
    protected $_title = 'Clickpay - ApplePay';
    protected $_description = 'Clickpay - ApplePay payment method';

    protected $_icon = "applepay.svg";
}

class WC_Gateway_Clickpay_HostedApplepay extends WC_Gateway_Clickpay
{
    protected $_code = 'cchostedapplepay';
    protected $_title = 'Clickpay - Hosted ApplePay';
    protected $_description = 'Clickpay - Hosted ApplePay payment method';

    protected $_icon = "applepay.svg";
}
