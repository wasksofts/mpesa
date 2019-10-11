<?php

namespace Wasksofts\Mpesa;

use Wasksofts\Mpesa\Config;
use Jenssegers\Date\Date;

/**----------------------------------------------------------------------------------------
| Mpesa Api library
|------------------------------------------------------------------------------------------
| *
| * @package     mpesa class
| * @author      steven kamanu
| * @email       mukamanusteven at gmail dot com
| * @website     htps://kenyadevlopers.co.ke
| * @version     1.0
| * @license     MIT License Copyright (c) 2017 Wasksofts technology
| *--------------------------------------------------------------------------------------- 
| *---------------------------------------------------------------------------------------
 */

class Mpesa
{
  private  $msg = [];
  private  $SecurityCredential;
  private  $consumer_key;
  private  $consumer_secret;
  private  $shortcode;
  private  $confirmation_url;
  private  $timeout_url;
  private  $validation_url;
  private  $callback_url;
  private  $result_url;
  private  $passkey;
  private  $password;
  private  $initiatorName;
  private  $initiatorPass;
  private  $live_endpoint;
  private  $sandbox_endpoint;
  private  $env;

  function __construct(array $data = [])
  {

    $this->config = Config::getInstance();
    $this->consumer_key       = $this->config->get('consumer_key');
    $this->consumer_secret    = $this->config->get('consumer_secret');
    $this->shortcode          = $this->config->get('shortcode');
    $this->shortcode1         = $this->config->get('shortcode1');
    $this->shortcode2         = $this->config->get('shortcode2');
    $this->initiatorName      = $this->config->get('initiator_name');
    $this->initiatorPass      = $this->config->get('initiator_pass');
    $this->passkey            = $this->config->get('pass_key');
    $this->env                = $this->config->get('env');
    $this->confirmation_url   = 'https://example.com/confirmation_url.php';
    $this->validation_url     = 'https://example.com/validation_url.php';
    $this->callback_url       = 'https://example.com/callback_url.php';
    $this->result_url         = 'https://example.com/result_url.php';
    $this->timeout_url        = 'https://example.com/timeout_url.php';
    $this->SecurityCredential = $this->security_credential();
    $this->timestamp          = $this->timestamp();
    $this->password           = $this->password();
    $this->live_endpoint      = 'https://api.safaricom.co.ke/';
    $this->sandbox_endpoint   = 'https://sandbox.safaricom.co.ke/';
  }

  public function config($key, $value)
  {
    $this->config->set($key, $value);
  }

  /** To authenticate your app and get an Oauth access token
   * An access token expires in 3600 seconds or 1 hour
   *
   * @access   private
   * @return   array object
   */
  public function oauth_token()
  {
    $url = $this->env('oauth/v1/generate?grant_type=client_credentials');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    $credentials = base64_encode($this->config->get('consumer_key') . ':' . $this->config->get('consumer_secret'));

    //setting a custom header      
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $curl_response = curl_exec($curl);

    return json_decode($curl_response)->access_token;
  }

  /** C2B enable Paybill and buy goods merchants to integrate to mpesa and receive real time payment notification
   *  C2B register URL API register the 3rd party's confirmation and validation url to mpesa
   *  which then maps these URLs to the 3rd party shortcode whenever mpesa receives atransaction on the shortcode
   *  Mpesa triggers avalidation request against the validation URL and the 3rd party system responds to mpesa 
   *  with a validation response (eithera success or an error code)
   *
   *  @return json
   */
  public function register_url()
  {
    $url =  $this->env('mpesa/c2b/v1/registerurl');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header
    //Fill in the request parameters with valid values

    $curl_post_data = array(
      'ShortCode' => $this->config->get('shortcode'),
      'ResponseType' => 'Timeout',
      'ConfirmationURL' => $this->confirmation_url,
      'ValidationURL' => $this->validation_url
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);

    $curl_response = curl_exec($curl);
    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }

  /** STK Push Simulation lipa na M-pesa Online payment API is used to initiate a M-pesa transaction on behalf of a customer using STK push
   * This is the same technique mySafaricom app uses whenever the app is used to make payments
   *  
   * @param  int     $amount
   * @param  int     $PartyA | The MSISDN sending the funds.
   * @param  int     $AccountReference  | (order id) Used with M-Pesa PayBills
   * @param  string  $TransactionDesc | A description of the transaction.
   * @return array object
   */
  public function STKPushSimulation($Amount, $PartyA, $AccountReference, $TransactionDesc)
  {
    $url =  $this->env('mpesa/stkpush/v1/processrequest');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header
    //Fill in the request parameters with valid values     
    $curl_post_data = array(
      'BusinessShortCode' => $this->config->get('shortcode'),
      'Password' => $this->password,
      'Timestamp' => $this->timestamp,
      'TransactionType' => 'CustomerPayBillOnline',
      'Amount' => $Amount,
      'PartyA' => $PartyA,
      'PartyB' => $this->config->get('shortcode'),
      'PhoneNumber' => $PartyA,
      'CallBackURL' => $this->callback_url,
      'AccountReference' => $AccountReference,
      'TransactionDesc' => $TransactionDesc
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    echo   $curl_response;
    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }

  /** C2B simulate transaction
   *
   * @param  int   $Amount | The amount been transacted.
   * @param  int   $Msisdn | MSISDN (phone number) sending the transaction, start with country code without the plus(+) sign.
   * @param  int   $BillRefNumber | Bill Reference Number (Optional).
   * @return array object
   */
  public function c2b($Amount, $Msisdn, $BillRefNumber = NULL)
  {
    $url =  $this->env('mpesa/c2b/v1/simulate');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'ShortCode'  => $this->shortcode1,
      'CommandID'  => 'CustomerPayBillOnline',
      'Amount'     => $Amount,
      'Msisdn'     => $Msisdn,
      'BillRefNumber' => $BillRefNumber  // '00000' //optional
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);

    $curl_response = curl_exec($curl);

    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }


  /** STK Push Status Query
   * This is used to check the status of a Lipa Na M-Pesa Online Payment.
   *
   * @param   string  $checkoutRequestID | Checkout RequestID
   * @return  array object
   */
  public function STKPushQuery($checkoutRequestID)
  {
    $url =  $this->env('mpesa/stkpushquery/v1/query');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'BusinessShortCode' => $this->shortcode,
      'Password'  => $this->password,
      'Timestamp' => $this->timestamp,
      'CheckoutRequestID' => $checkoutRequestID
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }


  /**  
   * B2C Payment Request transactions betwwen a company and customers 
   * who are the enduser of its products ir services
   * command id SalaryPayment,BussinessPayment ,PromotionPayment
   *
   * @param   int       $amount
   * @param   string    $commandId | Unique command for each transaction type e.g. SalaryPayment, BusinessPayment, PromotionPayment
   * @param   string    $receiver  | Phone number receiving the transaction
   * @param   string    $remark    | Comments that are sent along with the transaction.
   * @param   string    $ocassion  | optional
   * @return  array object
   */
  public function b2c($amount, $commandId, $receiver, $remark, $occassion = null)
  {
    $url = $this->env('mpesa/b2c/v1/paymentrequest');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //setting custom header
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token()));
    //Fill in the request parameters with valid values           
    $curl_post_data = array(
      'InitiatorName'      => $this->initiatorName,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => $commandId,
      'Amount' => $amount,
      'PartyA' => $this->shortcode1,
      'PartyB' => $receiver,
      'Remarks' => $remark,
      'QueueTimeOutURL' => $this->timeout_url,
      'ResultURL' => $this->result_url,
      'Occasion' => $occassion
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }

  /** B2B Payment Request transactions between a business and another business
   * Api requires a valid and verifiedB2B Mpesa shortcode for the business initiating the transaction 
   * andthe bothbusiness involved in the transaction
   * Command ID : BussinessPayBill ,MerchantToMerchantTransfer,MerchantTransferFromMerchantToWorking,MerchantServucesMMFAccountTransfer,AgencyFloatAdvance
   *
   * @param  int      $Amount
   * @param  string   $commandId
   * @param  int      $PartyB | Organization’s short code receiving the funds being transacted.
   * @param  int      $SenderIdentifierType | Type of organization sending the transaction. 1,2,4
   * @param  int      $RecieverIdentifierType | Type of organization receiving the funds being transacted. 1,2,4
   * @param  string   $AccountReference | Account Reference mandatory for “BusinessPaybill” CommandID.
   * @param  string   $remarks
   * @return array    object 
   */
  public function b2b($Amount, $commandId, $PartyB, $RecieverIdentifierType, $SenderIdentifierType, $AccountReference, $Remarks)
  {
    $url =  $this->env('/mpesa/b2b/v1/paymentrequest');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header


    $curl_post_data = array(
      //Fill in the request parameters with valid values
      'Initiator' => $this->initiatorName,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => $commandId,
      'SenderIdentifierType' => $SenderIdentifierType,
      'RecieverIdentifierType' => $RecieverIdentifierType,
      'Amount' => $Amount,
      'PartyA' => $this->shortcode1,
      'PartyB' => $PartyB,
      'AccountReference' => $AccountReference,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url,
      'ResultURL' => $this->result_url
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);


    $curl_response = curl_exec($curl);
    if ($curl_response === true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
  }



  /** Account Balance API request for account balance of a shortcode
   * 
   * @access  public
   * @param   int     $PartyA | Type of organization receiving the transaction
   * @param   int     $IdentifierType |Type of organization receiving the transaction
   * @param   string  $Remarks | Comments that are sent along with the transaction.
   * @return  array object
   */
  public function accountbalance($PartyA, $IdentifierType, $Remarks)
  {
    $url =  $this->env('mpesa/accountbalance/v1/query');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header
    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiatorName,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'AccountBalance',
      'PartyA' => $PartyA,
      'IdentifierType' => $IdentifierType,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url,
      'ResultURL' => $this->result_url
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    return $curl_response;

    $this->msg = json_decode($curl_response);
  }


  /** reverses a B2B ,B2C ir C2B Mpesa,transaction
   *
   * @access  public
   * @param   int      $amount
   * @param   int      $ReceiverParty
   * @param   int      $TransactionID
   * @param   int      $RecieverIdentifierType
   * @param   string   $Remarks
   * @param   string   $Ocassion
   * @return  string
   */
  public function reversal($Amount, $ReceiverParty, $RecieverIdentifierType, $TransactionID, $Remarks, $Occasion = NULL)
  {
    $url =  $this->env('mpesa/reversal/v1/request');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header
    //Fill in the request parameters with valid values      
    $curl_post_data = array(
      'Initiator' => $this->initiatorName,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'TransactionReversal',
      'TransactionID' => $TransactionID,
      'Amount' => $Amount,
      'ReceiverParty' => $ReceiverParty,
      'RecieverIdentifierType' => $RecieverIdentifierType, //4
      'ResultURL' => $this->result_url,
      'QueueTimeOutURL' => $this->timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occasion
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    return $curl_response;

    $this->msg = $curl_response;
  }



  /** Transaction Status Request API checks the status of B2B ,B2C and C2B APIs transactions
   *
   * @access  public
   * @param   string  $TransactionID | Organization Receiving the funds.
   * @param   int     $PartyA | Organization/MSISDN sending the transaction
   * @param   int     $IdentifierType | Type of organization receiving the transaction
   * @param   string  $Remarks
   * @param   string  $Ocassion
   * @return array object
   */
  public function transaction_status($TransactionID, $PartyA, $IdentifierType, $Remarks, $Occassion = NULL)
  {
    $url =  $this->env('mpesa/transactionstatus/v1/query');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token())); //setting custom header

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiatorName,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'TransactionStatusQuery',
      'TransactionID' => $TransactionID,
      'PartyA' => $PartyA,
      'IdentifierType' => $IdentifierType,
      'ResultURL' => $this->result_url,
      'QueueTimeOutURL' => $this->timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occassion
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    return $curl_response;

    $this->msg = $curl_response;
  }



  /** get environment url
   *
   * @access public
   * @param  string $request_url
   * @return string
   */
  public function env($request_url = null)
  {
    if (!is_null($request_url)) {
      if ($this->config->get('env') == "sandbox") {
        return $this->sandbox_endpoint . $request_url;
      } elseif ($this->config->get('env') == "live") {
        return $this->live_endpoint . $request_url;
      }
    }
  }

  /** function validate kenya phone number
   * 
   * @param  int  $phone
   * @return int
   */
  public function validate_phone($phone)
  {
    $phone = str_replace("+", "", $phone);
    $phone = str_replace(" ", "", $phone);
    $phone = str_replace("(", "", $phone);
    $phone = str_replace(")", "", $phone);
    if (is_numeric($phone)) {
      $length = strlen($phone);
      if ($length == 12) {
        if (substr($phone, 0, 3) == '254') {
          return $phone;
        }
      } elseif ($length == 10) {
        if (substr($phone, 0, 1) == '0') {
          return "254" . substr($phone, 1, 9);
        }
      } elseif ($length == 9) {
        return "254" . $phone;
      }
    }
    return false;
  }

  /**
   * Mpesa authenticate a transaction by decrypting the security credential 
   * Security credentials are generated by encrypting the Base64 encoded string of the M-Pesa short code 
   * and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
   * 
   * @access  private
   * @return  String
   */
  public function security_credential()
  {
    //$publicKey = file_get_contents('myapi/cert/cert.cer');
    $publicKey = $this->cert();
    $plaintext = $this->initiatorPass;

    openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

    return base64_encode($encrypted);
  }

  /**
   * Generate rsa key using phpseclib a pure rsa implementation
   * @deprecated
   * @return string,
   */
  public function generate_key()
  {
    $key = $this->cert();
    $rsa = new Crypt_RSA();
    $rsa->loadKey($key);
    $rsa->setPublicKey($key);

    return $rsa->getPublicKey();
  }

  /** Password for encrypting the request.
   *  This is generated by base64 encoding Bussiness shorgcode passkey and timestamp
   *
   * @access  private
   * @return  string
   */
  public function password()
  {
    $Merchant_id =  trim($this->config->get('shortcode'));
    $passkey     =  trim($this->config->get('passkey'));
    $password    =  base64_encode($Merchant_id . $passkey . $this->timestamp());

    return $password;
  }

  public function timestamp()
  {
    $datetime = Date::now()->format('YmdHis');

    return $datetime;
    //return date('YmdHis');
  }

  /**
   *Use this function to confirm all transactions in callback routes
   */
  public function finishTransaction($status = true)
  {
    if ($status === true) {
      $resultArray = [
        "ResultDesc" => "Confirmation Service request accepted successfully",
        "ResultCode" => "0"
      ];
    } else {
      $resultArray = [
        "ResultDesc" => "Confirmation Service not accepted",
        "ResultCode" => "1"
      ];
    }

    header('Content-Type: application/json');
    echo json_encode($resultArray);
  }

  /**
   *Use this function to get callback data posted in callback routes
   */
  public function getDataFromCallback()
  {
    $callbackJSONData = file_get_contents('php://input');
    return $callbackJSONData;
  }

  public function jsonEncode($responseData)
  {

    header('Content-Type: application/json');
    echo json_encode($responseData, JSON_PRETTY_PRINT);
  }




  public function getResponseData()
  {
    return $this->msg;
  }

  public function cert()
  {

    return "-----BEGIN CERTIFICATE-----
MIIGkzCCBXugAwIBAgIKXfBp5gAAAD+hNjANBgkqhkiG9w0BAQsFADBbMRMwEQYK
CZImiZPyLGQBGRYDbmV0MRkwFwYKCZImiZPyLGQBGRYJc2FmYXJpY29tMSkwJwYD
VQQDEyBTYWZhcmljb20gSW50ZXJuYWwgSXNzdWluZyBDQSAwMjAeFw0xNzA0MjUx
NjA3MjRaFw0xODAzMjExMzIwMTNaMIGNMQswCQYDVQQGEwJLRTEQMA4GA1UECBMH
TmFpcm9iaTEQMA4GA1UEBxMHTmFpcm9iaTEaMBgGA1UEChMRU2FmYXJpY29tIExp
bWl0ZWQxEzARBgNVBAsTClRlY2hub2xvZ3kxKTAnBgNVBAMTIGFwaWdlZS5hcGlj
YWxsZXIuc2FmYXJpY29tLmNvLmtlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB
CgKCAQEAoknIb5Tm1hxOVdFsOejAs6veAai32Zv442BLuOGkFKUeCUM2s0K8XEsU
t6BP25rQGNlTCTEqfdtRrym6bt5k0fTDscf0yMCoYzaxTh1mejg8rPO6bD8MJB0c
FWRUeLEyWjMeEPsYVSJFv7T58IdAn7/RhkrpBl1dT7SmIZfNVkIlD35+Cxgab+u7
+c7dHh6mWguEEoE3NbV7Xjl60zbD/Buvmu6i9EYz+27jNVPI6pRXHvp+ajIzTSsi
eD8Ztz1eoC9mphErasAGpMbR1sba9bM6hjw4tyTWnJDz7RdQQmnsW1NfFdYdK0qD
RKUX7SG6rQkBqVhndFve4SDFRq6wvQIDAQABo4IDJDCCAyAwHQYDVR0OBBYEFG2w
ycrgEBPFzPUZVjh8KoJ3EpuyMB8GA1UdIwQYMBaAFOsy1E9+YJo6mCBjug1evuh5
TtUkMIIBOwYDVR0fBIIBMjCCAS4wggEqoIIBJqCCASKGgdZsZGFwOi8vL0NOPVNh
ZmFyaWNvbSUyMEludGVybmFsJTIwSXNzdWluZyUyMENBJTIwMDIsQ049U1ZEVDNJ
U1NDQTAxLENOPUNEUCxDTj1QdWJsaWMlMjBLZXklMjBTZXJ2aWNlcyxDTj1TZXJ2
aWNlcyxDTj1Db25maWd1cmF0aW9uLERDPXNhZmFyaWNvbSxEQz1uZXQ/Y2VydGlm
aWNhdGVSZXZvY2F0aW9uTGlzdD9iYXNlP29iamVjdENsYXNzPWNSTERpc3RyaWJ1
dGlvblBvaW50hkdodHRwOi8vY3JsLnNhZmFyaWNvbS5jby5rZS9TYWZhcmljb20l
MjBJbnRlcm5hbCUyMElzc3VpbmclMjBDQSUyMDAyLmNybDCCAQkGCCsGAQUFBwEB
BIH8MIH5MIHJBggrBgEFBQcwAoaBvGxkYXA6Ly8vQ049U2FmYXJpY29tJTIwSW50
ZXJuYWwlMjBJc3N1aW5nJTIwQ0ElMjAwMixDTj1BSUEsQ049UHVibGljJTIwS2V5
JTIwU2VydmljZXMsQ049U2VydmljZXMsQ049Q29uZmlndXJhdGlvbixEQz1zYWZh
cmljb20sREM9bmV0P2NBQ2VydGlmaWNhdGU/YmFzZT9vYmplY3RDbGFzcz1jZXJ0
aWZpY2F0aW9uQXV0aG9yaXR5MCsGCCsGAQUFBzABhh9odHRwOi8vY3JsLnNhZmFy
aWNvbS5jby5rZS9vY3NwMAsGA1UdDwQEAwIFoDA9BgkrBgEEAYI3FQcEMDAuBiYr
BgEEAYI3FQiHz4xWhMLEA4XphTaE3tENhqCICGeGwcdsg7m5awIBZAIBDDAdBgNV
HSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwEwJwYJKwYBBAGCNxUKBBowGDAKBggr
BgEFBQcDAjAKBggrBgEFBQcDATANBgkqhkiG9w0BAQsFAAOCAQEAC/hWx7KTwSYr
x2SOyyHNLTRmCnCJmqxA/Q+IzpW1mGtw4Sb/8jdsoWrDiYLxoKGkgkvmQmB2J3zU
ngzJIM2EeU921vbjLqX9sLWStZbNC2Udk5HEecdpe1AN/ltIoE09ntglUNINyCmf
zChs2maF0Rd/y5hGnMM9bX9ub0sqrkzL3ihfmv4vkXNxYR8k246ZZ8tjQEVsKehE
dqAmj8WYkYdWIHQlkKFP9ba0RJv7aBKb8/KP+qZ5hJip0I5Ey6JJ3wlEWRWUYUKh
gYoPHrJ92ToadnFCCpOlLKWc0xVxANofy6fqreOVboPO0qTAYpoXakmgeRNLUiar
0ah6M/q/KA==
-----END CERTIFICATE-----";
  }
}
