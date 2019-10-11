<?php
namespace Wasksofts\Mpesa;

class Callback
{

    /**
     * Use this function to process the STK push request callback
     * @return string
     */
    public static function processSTKPushRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[
            "resultDesc"=>$callbackData->Body->stkCallback->ResultDesc,
            "resultCode"=>$callbackData->Body->stkCallback->ResultCode,
            "merchantRequestID"=>$callbackData->Body->stkCallback->MerchantRequestID,
            "checkoutRequestID"=>$callbackData->Body->stkCallback->CheckoutRequestID,
            "amount"=>$callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value,
            "mpesaReceiptNumber"=>$callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value,
            "transactionDate"=>$callbackData->Body->stkCallback->CallbackMetadata->Item[3]->Value,
            "phoneNumber"=>$callbackData->Body->stkCallback->CallbackMetadata->Item[4]->Value
        ];

        return json_encode($result);
    }

    /**
     * Use this function to process the STK Push  request callback
     * @return string
     */
    public static function processSTKPushQueryRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[
            "resultCode"=>$callbackData->ResultCode,
            "responseDescription"=>$callbackData->ResponseDescription,
            "responseCode"=>$callbackData->ResponseCode,
            "merchantRequestID"=>$callbackData->MerchantRequestID,
            "checkoutRequestID"=>$callbackData->CheckoutRequestID,
            "resultDesc"=>$callbackData->ResultDesc
        ];

        return json_encode($result);
    }

     /**
     * Use this function to process the C2B Confirmation result callback
     * @return string
     */
    public static function processC2BRequestConfirmation(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[
            "TransactionType" => $callbackData->TransactionType ,
            "TransID" => $callbackData->TransID,
            "TransTime"=> $callbackData->TransTime ,
            "TransAmount"=> $callbackData->TransAmount,
            "BusinessShortCode"=> $callbackData->BusinessShortCode ,
            "BillRefNumber"=> $callbackData->BillRefNumber,
            "InvoiceNumber"=> $callbackData->InvoiceNumber,
            "OrgAccountBalance"=> $callbackData->OrgAccountBalance,
            "ThirdPartyTransID"=> $callbackData->ThirdPartyTransID,
            "MSISDN"=> $callbackData->MSISDN,
            "FirstName"=> $callbackData->FirstName,
            "MiddleName"=> $callbackData->MiddleName,
            "LastName"=> $callbackData->LastName ];

        return json_encode($result);
    }

    /**
     * Use this function to process the B2C request callback
     * @return string
     */
    public static function processB2CRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
    
        $result=[
          "resultCode"=>$callbackData->Result->ResultCode,
          "resultDesc"=>$callbackData->Result->ResultDesc,
          "originatorConversationID"=>$callbackData->Result->OriginatorConversationID,
          "conversationID"=>$callbackData->Result->ConversationID,
          "transactionID"=>$callbackData->Result->TransactionID, 
          "TransactionAmount" => $callbackData->Result->ResultParameters->ResultParameter[0]->Value,
          "TransactionReceipt" => $callbackData->Result->ResultParameters->ResultParameter[1]->Value,
          "B2CRecipientIsRegisteredCustomer" => $callbackData->Result->ResultParameters->ResultParameter[2]->Value,
          "B2CChargesPaidAccountAvailableFunds" => $callbackData->Result->ResultParameters->ResultParameter[3]->Value,
          "ReceiverPartyPublicName" => $callbackData->Result->ResultParameters->ResultParameter[4]->Value,
          "TransactionCompletedDateTime" => $callbackData->Result->ResultParameters->ResultParameter[5]->Value,
          "B2CUtilityAccountAvailableFunds" => $callbackData->Result->ResultParameters->ResultParameter[6]->Value,
          "B2CWorkingAccountAvailableFunds"=> $callbackData->Result->ResultParameters->ResultParameter[7]->Value
         ];
    
            return json_encode($result);
        }
    /**
     * Use this function to process the B2B request callback
     * @return string
     */
    public static function processB2BRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        
        $result=[
          "resultCode" => $callbackData->Result->ResultCode,
          "resultDesc" => $callbackData->Result->ResultDesc,
          "originatorConversationID"=>$callbackData->Result->OriginatorConversationID,
          "conversationID" =>$callbackData->Result->ConversationID,
          "transactionID"=>$callbackData->Result->TransactionID,     
          "InitiatorAccountCurrentBalance" => $callbackData->Result->ResultParameters->ResultParameter[0]->Value,
          "DebitAccountCurrentBalance"=> $callbackData->Result->ResultParameters->ResultParameter[1]->Value,
          "Amount" => $callbackData->Result->ResultParameters->ResultParameter[2]->Value,
          "DebitPartyAffectedAccountBalance"=> $callbackData->Result->ResultParameters->ResultParameter[3]->Value,
          "TransCompletedTime" => $callbackData->Result->ResultParameters->ResultParameter[4]->Value,
          "DebitPartyCharges" =>  $callbackData->Result->ResultParameters->ResultParameter[5]->Value,
          "ReceiverPartyPublicName" =>  $callbackData->Result->ResultParameters->ResultParameter[6]->Value,
          "Currency"  => $callbackData->Result->ResultParameters->ResultParameter[7]->Value
        ];

        return json_encode($result);
    }


    /**
     * Use this function to process the C2B Validation request callback
     * @return string
     */
    public static function processC2BRequestValidation(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[
            "TransactionType" => $callbackData->TransactionType ,
            "TransID" => $callbackData->TransID,
            "TransTime"=> $callbackData->TransTime ,
            "TransAmount"=> $callbackData->TransAmount,
            "BusinessShortCode"=> $callbackData->BusinessShortCode ,
            "BillRefNumber"=> $callbackData->BillRefNumber,
            "InvoiceNumber"=> $callbackData->InvoiceNumber,
            "OrgAccountBalance"=> $callbackData->OrgAccountBalance,
            "ThirdPartyTransID"=> $callbackData->ThirdPartyTransID,
            "MSISDN"=> $callbackData->MSISDN,
            "FirstName"=> $callbackData->FirstName,
            "MiddleName"=> $callbackData->MiddleName,
            "LastName"=> $callbackData->LastName ];

        return json_encode($result);
    }

    /**
     * Use this function to process the Account Balance request callback
     * @return string
     */
    public static function processAccountBalanceRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        
        $result=[
              "ResultType"=> $callbackData->Result->ResultType,
              "ResultCode"=> $callbackData->Result->ResultCode,
              "ResultDesc"=> $callbackData->Result->ResultDesc,
              "OriginatorConversationID"=> $callbackData->Result->OriginatorConversationID,
              "ConversationID" => $callbackData->Result->ConversationID,
              "TransactionID"=> $callbackData->Result->TransactionID,
              "AccountBalance" => $callbackData->Result->ResultParameters->ResultParameter[0]->Value,
              "BOCompletedTime"=> $callbackData->Result->ResultParameters->ResultParameter[1]->Value,
        ];

        return json_encode($result);
    }

    /**
     * Use this function to process the Reversal request callback
     * @return string
     */
    public static function processReversalRequestCallBack(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[
        "ResultType"=> $callbackData->Result->ResultType ,
        "ResultCode"=> $callbackData->Result->ResultCode,
        "ResultDesc"=> $callbackData->Result->ResultDesc,
        "OriginatorConversationID"=>$callbackData->Result->OriginatorConversationID,
        "ConversationID"=>$callbackData->Result->ConversationID,
        "TransactionID"=>$callbackData->Result->TransactionID
        ];
        
        return json_encode($result);
    }

    /**
     * Use this function to process the Transaction status request callback
     * @return string
     */
    public static function processTransactionStatusRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);

        $result=[ 
            "ResultType" => $callbackData->Result->ResultType,
            "ResultCode" => $callbackData->Result->ResultCode ,
            "ResultDesc" => $callbackData->Result->ResultDesc,
            "OriginatorConversationID" => $callbackData->Result->OriginatorConversationID,
            "ConversationID" => $callbackData->Result->ConversationID,
            "TransactionID" => $callbackData->Result->TransactionID,
            "ReceiptNo"  => $callbackData->Result->ResultParameters->ResultParameter[0]->Value,
            "Conversation ID"=> $callbackData->Result->ResultParameters->ResultParameter[1]->Value,
            "FinalisedTime" => $callbackData->Result->ResultParameters->ResultParameter[2]->Value,
            "Amount" => $callbackData->Result->ResultParameters->ResultParameter[3]->Value,
            "TransactionStatus" => $callbackData->Result->ResultParameters->ResultParameter[4]->Value,
            "ReasonType" => $callbackData->Result->ResultParameters->ResultParameter[5]->Value,
            //"TransactionReason"no value
            "DebitPartyCharges" => $callbackData->Result->ResultParameters->ResultParameter[7]->Value,
            "DebitAccountType" => $callbackData->Result->ResultParameters->ResultParameter[8]->Value,
            "InitiatedTime" => $callbackData->Result->ResultParameters->ResultParameter[9]->Value,
            "Originator Conversation ID" => $callbackData->Result->ResultParameters->ResultParameter[10]->Value,
            "CreditPartyName" => $callbackData->Result->ResultParameters->ResultParameter[11]->Value,
            "DebitPartyName" => $callbackData->Result->ResultParameters->ResultParameter[12]->Value
        ];

        return json_encode($result);
    }
}
