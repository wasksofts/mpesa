# Introduction

This package seeks to help php developers implement the various Mpesa APIs without much hustle. It is based on the REST API whose documentation is available on https://developer.safaricom.co.ke.

#  Installation using composer
``` bash
$ composer require wasksofts/mpesa
```

#  Usage example

     require_once('vendor/autoload.php')
     use Wasksofts\Mpesa\Mpesa;

    $mpesa  = new Mpesa();
    $mpesa->config('consumer_key', '');
    $mpesa->config('consumer_secret', '');
    $mpesa->config('pass_key', '');
    $mpesa->config('initiator_name', '');
    $mpesa->config('initiator_pass', '');
    $mpesa->config('shortcode', '174379');
    $mpesa->config('shortcode1', '');
    $mpesa->config('shortcode2', '');
    $mpesa->config('callback_url', 'https://example.com/callback_url.php');
    $mpesa->config('confirmation_url', 'https://example.com/confirmation_url.php');
    $mpesa->config('validation_url', 'https://example.com/validation_url.php');
    $mpesa->config('result_url', 'https://example.com/result_url.php');
    $mpesa->config('timeout_url', 'https://example.com/timeout_url.php');
    $mpesa->config('env', 'sandbox');
    
    echo " Token : " . $mpesa->oauth_token();
    $mpesa->STKPushQuery('ws_CO_DMZ_297481201_09042019174418021');
    $mpesa->STKPushSimulation('1','254708374149','pay now','test');
    $mpesa->register_url(); 
    $mpesa->c2b('10', '254708374149', 'account');
    $mpesa->b2c('2', 'BusinessPayment', '254708374149', 'payment'); //refund
    $mpesa->b2b('10000','BusinessPayBill','60000','4','4','paytest','cool');
    $mpesa->accountbalance('600443','4','remarks');
    $mpesa->reversal('2','254708374149','1','NCR7S1UXBT','PAY NOW VIA WASKSOFT');
    $mpesa->transaction_status('NCR7S1UXBT','254708374149','4','apitest');
    
    //get responses
    echo $mpesa->getResponseData();
    
    require_once('vendor/autoload.php');
    
# Callback json data received from safaricom
    use Wasksofts\Mpesa\Callback;

    $callback = new Callback;
    $callback::processSTKPushRequestCallback();
    $callback::processC2BRequestConfirmation();
    $callback::processC2BRequestValidation();
    $callback::processB2CRequestCallback();
    $callback::processB2BRequestCallback();
    $callback::processAccountBalanceRequestCallback();
    $callback::processReversalRequestCallBack();
    $callback::processTransactionStatusRequestCallback();

    
    
