<?php

namespace Core\Helpers\GMO\Payment;

/**
 * Base API of GMO Payment.
 */
class Api
{
    // Api version
    const VERSION = '1.0.0';

    // User
    const GMO_USER = 'GMO-PG-PHP-1.0.0';

    // Version
    const GMO_VERSION = '100';

    // HTTP_USER_AGENT
    const HTTP_USER_AGENT = 'curl/7.30.0';

    // HTTP_ACCEPT
    const HTTP_ACCEPT = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

    // Multiple separator for API response
    const RESPONSE_SEPARATOR = '|';

    // API methods
    public static $apiMethods = [
        'getToken' => 'ext/api/credit/getToken',
        'entryTran' => 'payment/EntryTran.idPass',
        'execTran' => 'payment/ExecTran.idPass',
        'alterTran' => 'payment/AlterTran.idPass',
        'tdVerify' => 'payment/SecureTran.idPass',
        'changeTran' => 'payment/ChangeTran.idPass',
        'saveCard' => 'payment/SaveCard.idPass',
        'deleteCard' => 'payment/DeleteCard.idPass',
        'searchCard' => 'payment/SearchCard.idPass',
        'tradedCard' => 'payment/TradedCard.idPass',
        'saveMember' => 'payment/SaveMember.idPass',
        'deleteMember' => 'payment/DeleteMember.idPass',
        'searchMember' => 'payment/SearchMember.idPass',
        'updateMember' => 'payment/UpdateMember.idPass',
        'bookSalesProcess' => 'payment/BookSalesProcess.idPass',
        'unbookSalesProcess' => 'payment/UnbookSalesProcess.idPass',
        'searchBookingInfo' => 'payment/SearchBookingInfo.idPass',
        'searchTrade' => 'payment/SearchTrade.idPass',
        'entryTranSuica' => 'payment/EntryTranSuica.idPass',
        'execTranSuica' => 'payment/ExecTranSuica.idPass',
        'entryTranEdy' => 'payment/EntryTranEdy.idPass',
        'execTranEdy' => 'payment/ExecTranEdy.idPass',
        'entryTranCvs' => 'payment/EntryTranCvs.idPass',
        'execTranCvs' => 'payment/ExecTranCvs.idPass',
        'cvsCancel' => 'payment/CvsCancel.idPass',
        'entryTranPayEasy' => 'payment/EntryTranPayEasy.idPass',
        'execTranPayEasy' => 'payment/ExecTranPayEasy.idPass',
        'entryTranPaypal' => 'payment/EntryTranPaypal.idPass',
        'execTranPaypal' => 'payment/ExecTranPaypal.idPass',
        'paypalStart' => 'payment/PaypalStart.idPass',
        'cancelTranPaypal' => 'payment/CancelTranPaypal.idPass',
        'entryTranWebmoney' => 'payment/EntryTranWebmoney.idPass',
        'execTranWebmoney' => 'payment/ExecTranWebmoney.idPass',
        'webmoneyStart' => 'payment/WebmoneyStart.idPass',
        'paypalSales' => 'payment/PaypalSales.idPass',
        'cancelAuthPaypal' => 'payment/CancelAuthPaypal.idPass',
        'entryTranAu' => 'payment/EntryTranAu.idPass',
        'execTranAu' => 'payment/ExecTranAu.idPass',
        'auStart' => 'payment/AuStart.idPass',
        'auCancelReturn' => 'payment/AuCancelReturn.idPass',
        'auSales' => 'payment/AuSales.idPass',
        'deleteAuOpenID' => 'payment/DeleteAuOpenID.idPass',
        'entryTranDocomo' => 'payment/EntryTranDocomo.idPass',
        'execTranDocomo' => 'payment/ExecTranDocomo.idPass',
        'docomoStart' => 'payment/DocomoStart.idPass',
        'docomoCancelReturn' => 'payment/DocomoCancelReturn.idPass',
        'docomoSales' => 'payment/DocomoSales.idPass',
        'entryTranDocomoContinuance' => 'payment/EntryTranDocomoContinuance.idPass',
        'execTranDocomoContinuance' => 'payment/ExecTranDocomoContinuance.idPass',
        'docomoContinuanceSales' => 'payment/DocomoContinuanceSales.idPass',
        'docomoContinuanceCancelReturn' => 'payment/DocomoContinuanceCancelReturn.idPass',
        'docomoContinuanceUserChange' => 'payment/DocomoContinuanceUserChange.idPass',
        'docomoContinuanceUserEnd' => 'payment/DocomoContinuanceUserEnd.idPass',
        'docomoContinuanceShopChange' => 'payment/DocomoContinuanceShopChange.idPass',
        'docomoContinuanceShopEnd' => 'payment/DocomoContinuanceShopEnd.idPass',
        'docomoContinuanceStart' => 'payment/DocomoContinuanceStart.idPass',
        'entryTranJibun' => 'payment/EntryTranJibun.idPass',
        'execTranJibun' => 'payment/ExecTranJibun.idPass',
        'jibunStart' => 'payment/JibunStart.idPass',
        'entryTranSb' => 'payment/EntryTranSb.idPass',
        'execTranSb' => 'payment/ExecTranSb.idPass',
        'sbStart' => 'payment/SbStart.idPass',
        'sbCancel' => 'payment/SbCancel.idPass',
        'sbSales' => 'payment/SbSales.idPass',
        'entryTranAuContinuance' => 'payment/EntryTranAuContinuance.idPass',
        'execTranAuContinuance' => 'payment/ExecTranAuContinuance.idPass',
        'auContinuanceStart' => 'payment/AuContinuanceStart.idPass',
        'auContinuanceCancel' => 'payment/AuContinuanceCancel.idPass',
        'auContinuanceChargeCancel' => 'payment/AuContinuanceChargeCancel.idPass',
        'entryTranJcbPreca' => 'payment/EntryTranJcbPreca.idPass',
        'execTranJcbPreca' => 'payment/ExecTranJcbPreca.idPass',
        'jcbPrecaBalanceInquiry' => 'payment/JcbPrecaBalanceInquiry.idPass',
        'jcbPrecaCancel' => 'payment/JcbPrecaCancel.idPass',
        'searchTradeMulti' => 'payment/SearchTradeMulti.idPass',
        'entryTranVirtualaccount' => 'payment/EntryTranVirtualaccount.idPass',
        'execTranVirtualaccount' => 'payment/ExecTranVirtualaccount.idPass',
        // Line pay
        'entryTranLinepay' => 'payment/EntryTranLinepay.idPass',
        'execTranLinepay' => 'payment/ExecTranLinepay.idPass',
        'linepayStart' => 'payment/LinepayStart.idPass',
        // Pay pay
        'entryTranPaypay' => 'payment/EntryTranPaypay.idPass',
        'execTranPaypay' => 'payment/ExecTranPaypay.idPass',
        'paypayStart' => 'payment/PaypayStart.idPass',
        'paypayCancelReturn' => 'payment/PaypayCancelReturn.idPass',
        // Recurring
        'registerRecurringCredit' => 'payment/RegisterRecurringCredit.idPass',
    ];

    // Input parameters mapping
    public static $inputParams = [
        'access_id' => [
            'key' => 'AccessID',
            'length' => 32,
        ],
        'access_pass' => [
            'key' => 'AccessPass',
            'length' => 32,
        ],
        'account_timing_kbn' => [
            'key' => 'AccountTimingKbn',
            'max-length' => 2,
        ],
        'account_timing' => [
            'key' => 'AccountTiming',
            'max-length' => 2,
        ],
        'amount' => [
            'key' => 'Amount',
            'max-length' => 6,
            'integer' => true,
        ],
        'approve' => [
            'key' => 'Approve',
            'max-length' => 7,
        ],
        'approval_no' => [
            'key' => 'ApprovalNo',
            'max-length' => 16,
        ],
        'cancel_amount' => [
            'key' => 'CancelAmount',
            'max-length' => 6,
            'integer' => true,
        ],
        'cancel_tax' => [
            'key' => 'CancelTax',
            'max-length' => 6,
            'integer' => true,
        ],
        'card_name' => [
            'key' => 'CardName',
            'max-length' => 10,
        ],
        'card_no' => [
            'key' => 'CardNo',
            'min-length' => 10,
            'max-length' => 16,
        ],
        'card_pass' => [
            'key' => 'CardPass',
            'max-length' => 20,
        ],
        'card_seq' => [
            'key' => 'CardSeq',
            'allow' => [0, 1],
        ],
        'carry_info' => [
            'key' => 'CarryInfo',
            'max-length' => 34,
        ],
        'client_field_1' => [
            'key' => 'ClientField1',
            'max-length' => 100,
        ],
        'client_field_2' => [
            'key' => 'ClientField2',
            'max-length' => 100,
        ],
        'client_field_3' => [
            'key' => 'ClientField3',
            'max-length' => 100,
        ],
        'client_field_flag' => [
            'key' => 'ClientFieldFlag',
            'allow' => [0, 1],
        ],
        'commodity' => [
            'key' => 'Commodity',
            'max-length' => 48,
        ],
        'confirm_base_date' => [
            'key' => 'ConfirmBaseDate',
            'length' => 2,
        ],
        'continuance_month' => [
            'key' => 'ContinuanceMonth',
            'length' => 6,
        ],
        'convenience' => [
            'key' => 'Convenience',
            'max-length' => 5,
        ],
        'create_member' => [
            'key' => 'CreateMember',
            'allow' => [0, 1],
        ],
        'currency' => [
            'key' => 'Currency',
            'allow' => '/^[a-zA-Z]{3}$/',
        ],
        'customer_kana' => [
            'key' => 'CustomerKana',
            'max-length' => 40,
        ],
        'customer_name' => [
            'key' => 'CustomerName',
            'max-length' => 40,
        ],
        'default_flag' => [
            'key' => 'DefaultFlag',
            'allow' => [0, 1],
        ],
        'delete_flag' => [
            'key' => 'DeleteFlag',
            'allow' => [0, 1],
        ],
        'device_category' => [
            'key' => 'DeviceCategory',
            'allow' => [0, 1],
        ],
        'disp_mail_address' => [
            'key' => 'DispMailAddress',
            'max-length' => 100,
        ],
        'disp_phone_number' => [
            'key' => 'DispPhoneNumber',
            'max-length' => 13,
        ],
        'disp_shop_name' => [
            'key' => 'DispShopName',
            'max-length' => 50,
        ],
        'display_date' => [
            'key' => 'DisplayDate',
            'length' => 6,
        ],
        'docomo_disp_1' => [
            'key' => 'DocomoDisp1',
            'max-length' => 40,
        ],
        'docomo_disp_2' => [
            'key' => 'DocomoDisp2',
            'max-length' => 40,
        ],
        'eddy_add_info_1' => [
            'key' => 'EdyAddInfo1',
            'max-length' => 180,
        ],
        'eddy_add_info_2' => [
            'key' => 'EdyAddInfo2',
            'max-length' => 320,
        ],
        'expire' => [
            'key' => 'Expire',
            'allow' => '/^\d{4}$/',
        ],
        'encrypted' => [
            'key' => 'Encrypted',
            'max-length' => 320,
        ],
        'first_account_date' => [
            'key' => 'FirstAccountDate',
            'allow' => '/^\d{8}$/',
        ],
        'first_amount' => [
            'key' => 'FirstAmount',
            'max-length' => 7,
            'integer' => true,
        ],
        'first_tax' => [
            'key' => 'FirstTax',
            'max-length' => 7,
            'integer' => true,
        ],
        'first_month_free_flag' => [
            'key' => 'FirstMonthFreeFlag',
            'allow' => [0, 1],
        ],
        'forward' => [
            'key' => 'Forward',
            'max-length' => 7,
        ],
        'holder_name' => [
            'key' => 'HolderName',
            'max-length' => 50,
        ],
        'http_accept' => [
            'key' => 'HttpAccept',
        ],
        'http_user_agent' => [
            'key' => 'HttpUserAgent',
        ],
        'item_code' => [
            'key' => 'ItemCode',
            'max-length' => 7,
        ],
        'item_name' => [
            'key' => 'ItemName',
            'max-length' => 40,
        ],
        'key_hash' => [
            'key' => 'KeyHash',
            'max-length' => 320,
        ],
        'job_cd' => [
            'key' => 'JobCd',
            'allow' => [],
        ],
        'last_month_free_flag' => [
            'key' => 'LastMonthFreeFlag',
            'allow' => [0, 1],
        ],
        'md' => [
            'key' => 'MD',
            'max-length' => 32,
        ],
        'mail_address' => [
            'key' => 'MailAddress',
            'max-length' => 256,
        ],
        'member_id' => [
            'key' => 'MemberID',
            'max-length' => 60,
        ],
        'member_name' => [
            'key' => 'MemberName',
            'max-length' => 255,
        ],
        'member_no' => [
            'key' => 'MemberNo',
            'max-length' => 20,
        ],
        'method' => [
            'key' => 'Method',
            'allow' => [1, 2, 3, 4, 5],
        ],
        'order_id' => [
            'key' => 'OrderID',
            'max-length' => 27,
        ],
        'pa_res' => [
            'key' => 'PaRes',
        ],
        'process_date' => [
            'key' => 'ProcessDate',
            'length' => 14,
        ],
        'pay_description' => [
            'key' => 'PayDescription',
            'max-length' => 40,
        ],
        'pay_times' => [
            'key' => 'PayTimes',
            'max-length' => 2,
            'integer' => true,
        ],
        'pay_type' => [
            'key' => 'PayType',
            'allow' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
        ],
        'payment_term_day' => [
            'key' => 'PaymentTermDay',
            'max-length' => 2,
            'integer' => true,
        ],
        'payment_term_sec' => [
            'key' => 'PaymentTermSec',
            'max' => 86400,
            'integer' => true,
        ],
        'receipts_disp_1' => [
            'key' => 'ReceiptsDisp1',
            'max-length' => 60,
        ],
        'receipts_disp_2' => [
            'key' => 'ReceiptsDisp2',
            'max-length' => 60,
        ],
        'receipts_disp_3' => [
            'key' => 'ReceiptsDisp3',
            'max-length' => 60,
        ],
        'receipts_disp_4' => [
            'key' => 'ReceiptsDisp4',
            'max-length' => 60,
        ],
        'receipts_disp_5' => [
            'key' => 'ReceiptsDisp5',
            'max-length' => 60,
        ],
        'receipts_disp_6' => [
            'key' => 'ReceiptsDisp6',
            'max-length' => 60,
        ],
        'receipts_disp_7' => [
            'key' => 'ReceiptsDisp7',
            'max-length' => 60,
        ],
        'receipts_disp_8' => [
            'key' => 'ReceiptsDisp8',
            'max-length' => 60,
        ],
        'receipts_disp_9' => [
            'key' => 'ReceiptsDisp9',
            'max-length' => 60,
        ],
        'receipts_disp_10' => [
            'key' => 'ReceiptsDisp10',
            'max-length' => 60,
        ],
        'receipts_disp_11' => [
            'key' => 'ReceiptsDisp11',
            'max-length' => 42,
        ],
        'receipts_disp_12' => [
            'key' => 'ReceiptsDisp12',
            'max-length' => 12,
        ],
        'receipts_disp_13' => [
            'key' => 'ReceiptsDisp13',
            'max-length' => 11,
        ],
        'redirect_url' => [
            'key' => 'RedirectURL',
            'max-length' => 200,
        ],
        'register_disp_1' => [
            'key' => 'RegisterDisp1',
            'max-length' => 32,
        ],
        'register_disp_2' => [
            'key' => 'RegisterDisp2',
            'max-length' => 32,
        ],
        'register_disp_3' => [
            'key' => 'RegisterDisp3',
            'max-length' => 32,
        ],
        'register_disp_4' => [
            'key' => 'RegisterDisp4',
            'max-length' => 32,
        ],
        'register_disp_5' => [
            'key' => 'RegisterDisp5',
            'max-length' => 32,
        ],
        'register_disp_6' => [
            'key' => 'RegisterDisp6',
            'max-length' => 32,
        ],
        'register_disp_7' => [
            'key' => 'RegisterDisp7',
            'max-length' => 32,
        ],
        'register_disp_8' => [
            'key' => 'RegisterDisp8',
            'max-length' => 32,
        ],
        'reserve_no' => [
            'key' => 'ReserveNo',
            'max-length' => 20,
        ],
        'ret_url' => [
            'key' => 'RetURL',
            'max-length' => 256,
        ],
        'security_code' => [
            'key' => 'SecurityCode',
            'max-length' => 4,
        ],
        'seq_mode' => [
            'key' => 'SeqMode',
            'allow' => [0, 1],
        ],
        'service_name' => [
            'key' => 'ServiceName',
            'max-length' => 48,
        ],
        'service_tel' => [
            'key' => 'ServiceTel',
            'max-length' => 15,
        ],
        'shop_id' => [
            'key' => 'ShopID',
            'length' => 13,
        ],
        'shop_mail_address' => [
            'key' => 'ShopMailAddress',
            'max-length' => 256,
        ],
        'shop_pass' => [
            'key' => 'ShopPass',
            'length' => 10,
        ],
        'site_id' => [
            'key' => 'SiteID',
            'length' => 13,
        ],
        'site_pass' => [
            'key' => 'SitePass',
            'length' => 20,
        ],
        'status' => [
            'key' => 'Status',
            'max-length' => 15,
        ],
        'suica_add_info_1' => [
            'key' => 'SuicaAddInfo1',
            'max-length' => 256,
        ],
        'suica_add_info_2' => [
            'key' => 'SuicaAddInfo2',
            'max-length' => 256,
        ],
        'suica_add_info_3' => [
            'key' => 'SuicaAddInfo3',
            'max-length' => 256,
        ],
        'suica_add_info_4' => [
            'key' => 'SuicaAddInfo4',
            'max-length' => 256,
        ],
        'tax' => [
            'key' => 'Tax',
            'max-length' => 6,
            'integer' => true,
        ],
        'td_flag' => [
            'key' => 'TdFlag',
            'allow' => [0, 1],
        ],
        'td_tenant_name' => [
            'key' => 'TdTenantName',
            'max-length' => 25,
        ],
        'tel_no' => [
            'key' => 'TelNo',
            'max-length' => 13,
        ],
        'token' => [
            'key' => 'Token',
            'max-length' => 256,
        ],
        'tran_id' => [
            'key' => 'TranID',
            'max-length' => 28,
        ],
        'user' => [
            'key' => 'User',
        ],
        'version' => [
            'key' => 'Version',
        ],
        'trade_days' => [
            'key' => 'TradeDays',
            'max-length' => 2
        ],
        'trade_reason' => [
            'key' => 'TradeReason',
            'max-length' => 64
        ],
        'trade_client_name' => [
            'key' => 'TradeClientName',
            'max-length' => 64
        ],
        'trade_client_mailaddress' => [
            'key' => 'TradeClientMailaddress',
            'max-length' => 256
        ]
    ];

    // Output parameters mapping
    public static $outputParams = [
        'AccessID' => 'access_id',
        'AccessPass' => 'access_pass',
        'ACS' => 'acs',
        'AfterBalance' => 'after_balance',
        'Amount' => 'amount',
        'Approve' => 'approve',
        'BeforeBalance' => 'before_balance',
        'BkCode' => 'bk_code',
        'CancelAmount' => 'cancel_amount',
        'CancelTax' => 'cancel_tax',
        'CardActivateStatus' => 'card_activate_status',
        'CardInvalidStatus' => 'card_invalid_status',
        'CardName' => 'card_name',
        'CardNo' => 'card_no',
        'CardSeq' => 'card_seq',
        'CardTermStatus' => 'card_term_status',
        'CardTypeCode' => 'card_type_code',
        'CardValidLimit' => 'card_valid_limit',
        'CardWebInquiryStatus' => 'card_web_inquiry_status',
        'CheckString' => 'check_string',
        'ClientField1' => 'client_field_1',
        'ClientField2' => 'client_field_2',
        'ClientField3' => 'client_field_3',
        'ConfNo' => 'conf_no',
        'ContinuanceMonth' => 'continuance_month',
        'Convenience' => 'convenience',
        'CustID' => 'cust_id',
        'DefaultFlag' => 'default_flag',
        'DeleteFlag' => 'delete_flag',
        'EdyOrderNo' => 'edy_order_no',
        'EncryptReceiptNo' => 'encrypt_receipt_no',
        'Expire' => 'expire',
        'Encrypted' => 'encrypted',
        'Forward' => 'forward',
        'HolderName' => 'holder_name',
        'ItemCode' => 'item_code',
        'JobCd' => 'job_cd',
        'MemberID' => 'member_id',
        'MemberName' => 'member_name',
        'Method' => 'method',
        'OrderID' => 'order_id',
        'PaymentTerm' => 'payment_term',
        'PayTimes' => 'pay_times',
        'PayType' => 'pay_type',
        'ProcessDate' => 'process_date',
        'ReceiptNo' => 'receipt_no',
        'SiteID' => 'site_id',
        'StartLimitDate' => 'start_limit_date',
        'StartURL' => 'start_url',
        'Status' => 'status',
        'SuicaOrderNo' => 'suica_order_no',
        'Tax' => 'tax',
        'Token' => 'token',
        'TranDate' => 'tran_date',
        'TranID' => 'tran_id',
        'TransactionId' => 'transaction_id',
        'TradeDays' => 'trade_days',
        'TradeClientName' => 'trade_client_name',
        'TradeClientMailaddress' => 'trade_client_mailaddress'
    ];

    // Sandbox: https://pt01.mul-pay.jp/payment/
    protected $host;

    // Example: https://pt01.mul-pay.jp/payment/EntryTran.idPass
    protected $apiUrl;

    // Tran method: entry_tran -> EntryTran.idPass
    protected $method;

    // Post parameters for api call
    protected $params = [];

    // Default parameters
    protected $defaultParams = [];

    // Input parameters mapping
    protected $inputParamsMapping = [];

    /**
     * Object constructor.
     */
    public function __construct($host, $params = [])
    {
        $this->host = trim($host, '/');

        // Set default parameters.
        if ($params && is_array($params)) {
            $this->defaultParams = $params;
        }

        // Set input parameters mapping.
        $this->inputParamsMapping = self::$inputParams;
    }

    /**
     * Get input parameters mapping.
     */
    protected function getParamsMapping()
    {
        return $this->inputParamsMapping;
    }

    /**
     * Check required parameters exist.
     */
    protected function paramsExist()
    {
        // $required = self:getRequiredParams($this->method);
        $required = []; // @todo comment
        $params = [];
        foreach ($required as $key) {
            if (!array_key_exists($key, $this->params)) {
                $params[$key] = $key;
            }
        }

        return $params;
    }

    /**
     * Initial post parameters, such as user, version, api info.
     */
    protected function initParams()
    {
        $this->params = ['user' => self::GMO_USER, 'version' => self::GMO_VERSION];
        $this->defaultParams();
    }

    /**
     * Append default parameters.
     */
    protected function defaultParams()
    {
        if ($this->defaultParams) {
            $this->params = array_merge($this->params, $this->defaultParams);
        }
    }

    /**
     * Verify field by condition before call api
     *
     * @param $value
     * @param $condition
     * @return bool|string
     */
    public function verifyField($value, $condition)
    {
        $key = $condition['key'];

        // Check length.
        if (isset($condition['length'])) {
            if (strlen($value) != $condition['length']) {
                return sprintf('Field [%s] value length should be [%s].', $key, $condition['length']);
            }
        } else {
            if (isset($condition['min-length'])) {
                if (strlen($value) < $condition['min-length']) {
                    return sprintf('Field [%s] value length should be more than [%s].', $key, $condition['min-length']);
                }
            }

            if (isset($condition['max-length'])) {
                if (strlen($value) > $condition['max-length']) {
                    return sprintf('Field [%s] value length should not be more than [%s].', $key, $condition['max-length']);
                }
            }
        }

        // Check integer.
        if (isset($condition['integer']) && $condition['integer'] === true) {
            if (!is_numeric($value)) {
                return sprintf('Field [%s] value should be integer.', $key);
            }
        }

        // Check allowed values.
        if (isset($condition['allow'])) {
            if (is_array($condition['allow'])) {
                if (!in_array($value, $condition['allow'])) {
                    return sprintf('Field [%s] value should be one of [%s].', $key, implode(',', $condition['allow']));
                }
            } else {
                if (!preg_match($condition['allow'], $value)) {
                    return sprintf('Field [%s] value should be match regex [%s].', $key, $condition['allow']);
                }
            }
        }

        // Check allowed values.
        if (isset($condition['max'])) {
            $value = (int)$value;
            $max = (int)$condition['max'];
            if ($value > $max) {
                return sprintf('Field [%s] value should be larger than [%s].', $key, $max);
            }
        }

        return true;
    }

    /**
     * Add new parameters
     *
     * @param $params
     */
    public function addParams($params)
    {
        if ($params && is_array($params)) {
            $this->params = array_merge($this->params, $params);
        }
    }

    /**
     * Set param value
     *
     * @param $key
     * @param $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Get param value
     *
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public function getParam($key, $default = '')
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return $default;
    }

    /**
     * Post request with curl and return response
     *
     * @param $url
     * @param $params
     * @return array|null
     * @throws \Exception
     */
    protected function request($url, $params)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Append post fields.
        if ($params) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        }
        $response = curl_exec($ch);
        // Throw exception if curl error.
        $error = curl_error($ch);

        if ($error) {
            logInfo('Curl URL: ' . $url);
            logInfo('Curl params: ' . json_encode($params));
            throw new \Exception($error, curl_errno($ch));
        }

        // Close curl connect.
        curl_close($ch);

        // Process response before return.
        if ($response) {
            return strpos($response, '{') === 0 ? self::processResponseJson($response) : self::processResponse($response);
        }

        return null;
    }

    /**
     * Response separator
     *
     * @param $value
     * @return false|string[]
     */
    public static function responseSeparator($value)
    {
        return explode(self::RESPONSE_SEPARATOR, $value);
    }

    /**
     * @param $response
     * @return array
     */
    public static function processResponseJson($response)
    {
        $data = json_decode($response, true);

        $success = in_array("000", $data['resultCode']);
        $message = '';
        $result = [];

        if ($success) {
            $result = $data['tokenObject'];
        } else {
            foreach ($data['resultCode'] as $code) {
                if (empty($message)) {
                    $message = Consts::getErrorMessage($code);
                }

                $result[] = [
                    'ErrCode' => $code,
                    'ErrMessage' => Consts::getErrorMessage($code),
                ];
            }
        }

        return [
            'success' => $success,
            'multiple' => false,
            'response' => $response,
            'message' => $message,
            'result' => $result,
        ];
    }

    /**
     * Process curl response before return callback
     *
     * @param $response
     * @return array
     */
    public static function processResponse($response)
    {
        // mb_convert_encoding($value, 'UTF-8', 'SJIS');
        parse_str($response, $data);
        // API error or success.
        $success = isset($data['ErrCode']) ? false : true;
        // Check single or multiple of API response.
        $multiple = false;
        $first = current($data);
        $result = [];
        $message = '';
        if ($success && strpos($first, self::RESPONSE_SEPARATOR) === false) {
            foreach ($data as $key => $value) {
                if (isset(self::$outputParams[$key])) {
                    $key = self::$outputParams[$key];
                }
                $result[$key] = $value;
            }
        } else {
            $multiple = true;
            // Rearrange data with new structure.
            $data = array_map('self::responseSeparator', $data);
            foreach ($data as $key => $value) {
                if (isset(self::$outputParams[$key])) {
                    $key = self::$outputParams[$key];
                }
                foreach ($value as $k => $v) {
                    if (!isset($result[$k])) {
                        $result[$k] = [];
                    }
                    $result[$k][$key] = $v;
                }
            }
        }

        foreach ($result as $index => $error) {
            if (!isset($error['ErrInfo'])) {
                break;
            }

            if (empty($message)) {
                $message = Consts::getErrorMessage($error['ErrInfo']);
            }

            $result[$index]['ErrMessage'] = Consts::getErrorMessage($error['ErrInfo']);
        }

        // Return readable values after processed.
        return [
            'success' => $success,
            'multiple' => $multiple,
            'response' => $response,
            'message' => $message,
            'result' => $result,
        ];
    }

    /**
     * Add http parameters.
     */
    protected function addHttpParams()
    {
        // Add user agent.
        $this->defaultParams['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? self::HTTP_USER_AGENT;

        // Add accept.
        $this->defaultParams['http_accept'] = $_SERVER['HTTP_ACCEPT'] ?? self::HTTP_ACCEPT;
    }

    /**
     * Get api url.
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Execute api call method
     *
     * @param $method
     * @param array $params
     * @return array|null
     * @throws \Exception
     */
    public function callApi($method, $params = [])
    {
        $this->call($method, $params);
        return $this->execute();
    }

    /**
     * Pre-call api method
     *
     * @param $method
     * @param array $params
     * @throws \Exception
     */
    public function call($method, $params = [])
    {
        // Check api method exist.
        if (!isset(self::$apiMethods[$method])) {
            throw new \Exception(sprintf('API method %s does not exist.', $method));
        }
        $this->method = $method;
        $this->apiUrl = $this->host . '/' . self::$apiMethods[$method];
        // Initinial parameters.
        $this->initParams();
        // Add new params.
        $this->addParams($params);
    }

    /**
     * Execute call api and return results.
     */
    public function execute()
    {
        $uri = $this->getApiUrl();
        // Process parameters as GMO format.
        $params = $this->buildParams();
        return $this->request($uri, $params);
    }

    /**
     * Process parameters as GMO format.
     */
    protected function buildParams()
    {
        $params = [];
        $mapping = $this->getParamsMapping();
        foreach ($this->params as $key => $value) {
            if (isset($mapping[$key])) {
                $gmoKey = $mapping[$key]['key'];
                // Only convert fields which need to be convert.
                if (isset($mapping[$key]['encode']) && $mapping[$key]['encode'] === true) {
                    $value = mb_convert_encoding($value, 'SJIS', 'UTF-8');
                }
                $params[$gmoKey] = $value;
            }
        }
        return $params;
    }
}
