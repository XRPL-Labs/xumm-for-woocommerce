<?php

namespace Xrpl\XummForWoocommerce\Tests\Helper\Mock\Responses\Transaction;

class XRP
{
    public static function getResponse() : array
    {
        $object = new \stdClass();
        $object->body = '{
            "result":{
                "Account": "rBtttd61FExHC68vsZ8dqmS3DfjFEceA1A",
                "Amount": "1610547979",
                "Destination": "rwuN3FUqJd1jGwYG9b87mQJUh831e7Wd6q",
                "Fee": "3000",
                "Flags": 2147483648,
                "LastLedgerSequence": 78019731,
                "Sequence": 71008775,
                "SigningPubKey": "03B89ACF054BFCBA99FD26DFFBA2EE60F851E0946E9989B3D870DE687525C366E4",
                "TransactionType": "Payment",
                "TxnSignature": "3045022100EADF331E99D1A6B145FD7A63A8251EF5BB8514938CBA22F42759405F64A5D07F0220623476600963DD9FFF3DF910E772782C1417EF4769D5F47C7D14F6D2076B9491",
                "date": 730580941,
                "hash": "ABE156A5834134FAA6088C7295C82F9C8BD10672E8029333D6616636866D7530",
                "inLedger": 78019719,
                "ledger_index": 78019719,
                "meta": {
                    "AffectedNodes": [
                        {
                            "ModifiedNode": {
                                "FinalFields": {
                                    "Account": "rBtttd61FExHC68vsZ8dqmS3DfjFEceA1A",
                                    "Balance": "32213446885676",
                                    "Flags": 0,
                                    "OwnerCount": 0,
                                    "Sequence": 71008776
                                },
                                "LedgerEntryType": "AccountRoot",
                                "LedgerIndex": "82650075DB3B3D7EC66B2F71B0BE85B6371C15F6D80D390017D3EF2A9E39A1D5",
                                "PreviousFields": {
                                    "Balance": "32215057436655",
                                    "Sequence": 71008775
                                },
                                "PreviousTxnID": "C3CE5AE8A233034B781DA1F2BB778F1F4D899ECB777F8B046DCF30AF365D95B8",
                                "PreviousTxnLgrSeq": 78019715
                            }
                        },
                        {
                            "ModifiedNode": {
                                "FinalFields": {
                                    "Account": "rwuN3FUqJd1jGwYG9b87mQJUh831e7Wd6q",
                                    "Balance": "41734231180",
                                    "Flags": 0,
                                    "OwnerCount": 0,
                                    "Sequence": 62893596
                                },
                                "LedgerEntryType": "AccountRoot",
                                "LedgerIndex": "EC87E16E24C175F34E2DAAAE4F40D5DD419DA9EABFC384960AFC3D09CB0E04FA",
                                "PreviousFields": {
                                    "Balance": "40123683201"
                                },
                                "PreviousTxnID": "7E00C2569FF2E35511E243E1426E5A8859DBE0CE45F45B02CEC51E539D97B797",
                                "PreviousTxnLgrSeq": 76508043
                            }
                        }
                    ],
                    "TransactionIndex": 0,
                    "TransactionResult": "tesSUCCESS",
                    "delivered_amount": "1610547979"
                },
                "validated": true
            }
          }';

        return json_decode(json_encode($object), true);
    }
}
