<?php

namespace Wasksofts\Mpesa;

use Wasksofts\Mpesa\Config;

date_default_timezone_set("Africa/Nairobi");

/**----------------------------------------------------------------------------------------
| Mpesa Api library
|------------------------------------------------------------------------------------------
| *
| * @package     mpesa class
| * @author      steven kamanu
| * @email       mukamanusteven at gmail dot com
| * @website     htps://wasksofts.com
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
  private  $pass_key;
  private  $initiator_name;
  private  $initiator_pass;
  private  $security_credential;
  private  $live_endpoint;
  private  $sandbox_endpoint;
  private  $confirmation_url;
  private  $timeout_url;
  private  $validation_url;
  private  $callback_url;
  private  $result_url;
  private  $env;

  function __construct()
  {
    $this->config = Config::getInstance();
    $this->SecurityCredential = $this->security_credential();
    $this->live_endpoint      = 'https://api.safaricom.co.ke/';
    $this->sandbox_endpoint   = 'https://sandbox.safaricom.co.ke/';
  }

  /**
   * Mpesa configuration function
   * 
   * @param $key
   * @param $value
   * 
   * @return object
   */
  public function config($key, $value)
  {
    switch ($key) {
      case 'consumer_key':
        $this->consumer_key = $value;
        break;
      case 'consumer_secret':
        $this->consumer_secret = $value;
        break;
      case 'shortcode':
        $this->shortcode = $value;
        break;
      case 'shortcode1':
        $this->shortcode1 = $value;
        break;
      case 'shortcode2':
        $this->shortcode2 = $value;
        break;
      case 'initiator_name':
        $this->initiator_name = $value;
        break;
      case 'initiator_pass':
        $this->initiator_pass = $value;
        break;
      case 'security_credential':
        $this->security_credential = $value;
        break;
      case 'pass_key':
        $this->pass_key = $value;
        break;
      case 'env':
        $this->env = $value;
        break;
      case 'callback_url':
        $this->callback_url = $value;
        break;
      case 'confirmation_url':
        $this->confirmation_url = $value;
        break;
      case 'validation_url':
        $this->validation_url = $value;
        break;
      case 'result_url':
        $this->result_url = $value;
        break;
      case 'timeout_url':
        $this->timeout_url = $value;
        break;
      default:
        echo 'Invalid config key :' . $key;
        die;
    }
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
    $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

    //setting a custom header      
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    if ($curl_response == true) {
      return json_decode($curl_response)->access_token;
    } else {
      return curl_error($curl);
    }
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

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'ShortCode' => $this->shortcode1,
      'ResponseType' => 'Completed',
      'ConfirmationURL' => $this->confirmation_url,
      'ValidationURL' => $this->validation_url
    );

    $this->query($url, $curl_post_data);
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

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'BusinessShortCode' => $this->shortcode,
      'Password'  => $this->password(),
      'Timestamp' => $this->timestamp(),
      'CheckoutRequestID' => $checkoutRequestID
    );

    $this->query($url, $curl_post_data);
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

    //Fill in the request parameters with valid values     
    $curl_post_data = array(
      'BusinessShortCode' => $this->shortcode,
      'Password' => $this->password(),
      'Timestamp' => $this->timestamp(),
      'TransactionType' => 'CustomerPayBillOnline',
      'Amount' => $Amount,
      'PartyA' => $PartyA,
      'PartyB' => $this->shortcode,
      'PhoneNumber' => $PartyA,
      'CallBackURL' => $this->callback_url,
      'AccountReference' => $AccountReference,
      'TransactionDesc' => $TransactionDesc
    );

    $this->query($url, $curl_post_data);
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

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'ShortCode'  => $this->shortcode1,
      'CommandID'  => 'CustomerPayBillOnline',
      'Amount'     => $Amount,
      'Msisdn'     => $Msisdn,
      'BillRefNumber' => $BillRefNumber  // '00000' //optional
    );

    $this->query($url, $curl_post_data);
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
  public function b2c($amount, $commandId, $receiver, $remark, $occassion = null, $timeout_url, $result_url)
  {
    $url = $this->env('mpesa/b2c/v1/paymentrequest');

    //Fill in the request parameters with valid values           
    $curl_post_data = array(
      'InitiatorName'      => $this->initiator_name,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => $commandId,
      'Amount' => $amount,
      'PartyA' => $this->shortcode1,
      'PartyB' => $receiver,
      'Remarks' => $remark,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url,
      'Occasion' => $occassion
    );

    $this->query($url, $curl_post_data);
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
  public function b2b($Amount, $commandId, $PartyB, $RecieverIdentifierType, $SenderIdentifierType, $AccountReference, $Remarks, $timeout_url, $result_url)
  {
    $url =  $this->env('/mpesa/b2b/v1/paymentrequest');

    $curl_post_data = array(
      //Fill in the request parameters with valid values
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => $commandId,
      'SenderIdentifierType' => $SenderIdentifierType,
      'RecieverIdentifierType' => $RecieverIdentifierType,
      'Amount' => $Amount,
      'PartyA' => $this->shortcode1,
      'PartyB' => $PartyB,
      'AccountReference' => $AccountReference,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url
    );

    $this->query($url, $curl_post_data);
  }



  /** Account Balance API request for account balance of a shortcode
   * 
   * @access  public
   * @param   int     $PartyA | Type of organization receiving the transaction
   * @param   int     $IdentifierType |Type of organization receiving the transaction
   * @param   string  $Remarks | Comments that are sent along with the transaction.
   * @return  array object
   */
  public function accountbalance($PartyA, $IdentifierType, $Remarks, $timeout_url, $result_url)
  {
    $url =  $this->env('mpesa/accountbalance/v1/query');

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'AccountBalance',
      'PartyA' => $PartyA,
      'IdentifierType' => $IdentifierType,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url
    );

    $this->query($url, $curl_post_data);
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
  public function reversal($Amount, $ReceiverParty, $RecieverIdentifierType, $TransactionID, $Remarks, $Occasion = NULL, $timeout_url, $result_url)
  {
    $url =  $this->env('mpesa/reversal/v1/request');

    //Fill in the request parameters with valid values      
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'TransactionReversal',
      'TransactionID' => $TransactionID,
      'Amount' => $Amount,
      'ReceiverParty' => $ReceiverParty,
      'RecieverIdentifierType' => $RecieverIdentifierType, //4
      'ResultURL' => $this->result_url . $result_url,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occasion
    );

    $this->query($url, $curl_post_data);
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
  public function transaction_status($TransactionID, $PartyA, $IdentifierType, $Remarks, $Occassion = NULL, $timeout_url, $result_url)
  {
    $url =  $this->env('mpesa/transactionstatus/v1/query');

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->SecurityCredential,
      'CommandID' => 'TransactionStatusQuery',
      'TransactionID' => $TransactionID,
      'PartyA' => $PartyA,
      'IdentifierType' => $IdentifierType,
      'ResultURL' => $this->result_url . $result_url,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occassion
    );

    $this->query($url, $curl_post_data);
  }

  /** query function
   * 
   * @param  $url
   * @param  $curl_post_data
   * @return json
   */
  public function query($url, $curl_post_data)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //setting custom header
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->oauth_token()));

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $curl_response = curl_exec($curl);
    if ($curl_response == true) {
      $this->msg = $curl_response;
    } else {
      $this->msg = curl_error($curl);
    }
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
      if ($this->env === "sandbox") {
        return $this->sandbox_endpoint . $request_url;
      } elseif ($this->env === "live") {
        return $this->live_endpoint . $request_url;
      }
    }
  }

  /** Password for encrypting the request.
   *  This is generated by base64 encoding Bussiness shorgcode passkey and timestamp
   *
   * @access  private
   * @return  string
   */
  public function password()
  {
    $Merchant_id =  trim($this->shortcode);
    $passkey     =  trim($this->pass_key);
    $password    =  base64_encode($Merchant_id . $passkey . $this->timestamp());

    return $password;
  }

  /**
   * timestamp for the time of transaction
   */
  public function timestamp()
  {
    return date('YmdHis');
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
    $publicKey = file_get_contents(__DIR__ . '\cert.cert');
    openssl_public_encrypt($this->initiator_pass, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

    return if(!is_null($this->security_credential))? $this->security_credential : base64_encode($encrypted);
  }


  /**
   *  response on api call
   * 
   *  @return data array or json
   */
  public function getResponseData($array = NULL)
  {
    if ($array == TRUE) {
      return json_decode($this->msg);
    }
    return json_decode($this->msg);
  }
}
