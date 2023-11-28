<?php

namespace Core\Helpers\GMO\Payment;

/**
 * Shop API of GMO Payment.
 *
 * Shop ID (ショップ ID)
 * --ShopID string(13) not null.
 *
 * Shop password (ショップパスワード)
 * --ShopPass string(10) not null.
 *
 * $data = array('key' => 'value', ...)
 *   It contains not required and conditional required fields.
 *
 * Return result
 *   It will be return only one or multiple records.
 *   Multiple records joined with '|' whatever success or failed.
 */
class ShopApi extends Api
{
    // Site id and site pass disable flag
    protected $disableSiteIdAndPass = false;

    // Shop id and shop pass disable flag
    protected $disableShopIdAndPass = false;

    /**
     * Object constructor
     *
     * @param $host
     * @param $shopId
     * @param $shopPass
     * @param array $params
     */
    public function __construct($host, $shopId, $shopPass, $params = [])
    {
        if (!is_array($params)) {
            $params = [];
        }
        $params['shop_id'] = $shopId;
        $params['shop_pass'] = $shopPass;
        parent::__construct($host, $params);
    }

    /**
     * Disable site_id and site_pass fields which not required for some api.
     */
    protected function disableSiteIdAndPass()
    {
        $this->disableSiteIdAndPass = true;
    }

    /**
     * Disable shop_id and shop_pass fields which not required for some api.
     */
    protected function disableShopIdAndPass()
    {
        $this->disableShopIdAndPass = true;
    }

    /**
     * Append default parameters.
     *
     * Remove shop_id and shop_pass if disabled.
     */
    protected function defaultParams()
    {
        if ($this->disableSiteIdAndPass === true) {
            unset($this->defaultParams['site_id'], $this->defaultParams['site_pass']);
        }
        if ($this->disableShopIdAndPass === true) {
            unset($this->defaultParams['shop_id'], $this->defaultParams['shop_pass']);
        }
        parent::defaultParams();
    }

    /**
     * @param $cardNo
     * @param $expire
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function getToken($cardNo, $expire, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['cardNo'] = $cardNo;
        $data['expire'] = $expire;

        $cardInfo = json_encode($data);

        $key = "-----BEGIN PUBLIC KEY-----\r\n" . wordwrap($this->defaultParams['public_key'], 65, "\n", true) . "\r\n-----END PUBLIC KEY-----";

        openssl_public_encrypt($cardInfo, $encrypted, $key);

        $param['encrypted'] = base64_encode($encrypted);
        $param['key_hash'] = $this->defaultParams['hash_key'];

        return $this->callApi('getToken', $param);
    }

    /**
     * Entry transcation.
     *
     * Is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of transaction password,
     * you can start trading.
     *
     * これ以降の決済取引で必要となる取引 ID と取引パスワードの発行を行い、取引を開始します。
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     CHECK: validity check (有効性チェック).
     *     CAPTURE: immediate sales (即時売上).
     *     AUTH: provisional sales (仮売上).
     *     SAUTH: simple authorization (簡易オーソリ).
     *
     * Product code (商品コード)
     * --ItemCode string(7) null.
     *
     *   The default is to apply the system fixed value ("0000990").
     *   If you enter a 7-digit less than the code, please to
     *   7 digits to fill the right-justified-before zero.
     *   省略時はシステム固定値("0000990")を適用。7 桁未満のコードを入力
     *   する場合は、右詰め・前ゼロを埋めて 7 桁にしてください。
     *
     * Amount (利用金額)
     * --Amount integer(7) conditional null.
     *
     * Tax (税送料)
     * --Tax integer(7) null.
     *
     * 3D secure use flag (3D セキュア使用フラグ)
     * --TdFlag string(1) null default 0.
     *
     *   Allowed values:
     *     0: No (default)
     *     1: Yes
     *
     * 3D secure display store name (3D セキュア表示店舗名)
     * --TdTenantName string(25) null.
     *
     *   BASE64 encoding value in the EUC-JP the display store name
     *   that was set by the accessor is set.
     *   Value after the conversion you need is within 25Byte.
     *   If omitted, store name is the "unspecified".
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $jobCd
     * @param int $amount
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function entryTran($orderId, $jobCd, $amount = 0, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['order_id'] = $orderId;
        $data['job_cd'] = $jobCd;
        $data['amount'] = $amount;

        return $this->callApi('entryTran', $data);
    }

    /**
     * Entry transcation of Au.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     * これ以降の決済取引で必要となる取引 ID と取引パスワードの発行を行い、取引を開始します。
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     AUTH: provisional sales (仮売上).
     *     CAPTURE: immediate sales (即時売上).
     *
     * Amount (利用金額)
     * --Amount integer(7) not null.
     *
     *   It must be less than or equal to 9,999,999 yen
     *   or more ¥ 1 in spending + tax postage or the vinegar.
     *   利用金額+税送料で1円以上 9,999,999 円以下である必要がありま す。
     *
     * Tax (税送料)
     * --Tax integer(7) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $jobCd
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranAu($orderId, $jobCd, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'job_cd' => $jobCd,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranAu', $data);
    }

    /**
     * Entry transcation of Au Continuance.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (課金利用金額)
     * --Amount integer(7) not null.
     *
     * Tax (課金税送料)
     * --Tax integer(7) null.
     *
     * First amount (初回課金利用金額)
     * --FirstAmount integer(7) not null.
     *
     * First tax (初回課金税送料)
     * --FirstTax integer(7) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param $firstAmount
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function entryTranAuContinuance($orderId, $amount, $firstAmount, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['order_id'] = $orderId;
        $data['amount'] = $amount;
        $data['first_amount'] = $firstAmount;

        return $this->callApi('entryTranAuContinuance', $data);
    }

    /**
     * Entry transcation of Cvs.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranCvs($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranCvs', $data);
    }

    /**
     * Entry transcation of Docomo.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     AUTH: provisional sales (仮売上).
     *     CAPTURE: immediate sales (即時売上).
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     *   It must be less than or equal to 9,999,999 yen
     *   or more ¥ 1 in spending + tax postage or the vinegar.
     *   利用金額+税送料で1円以上 9,999,999 円以下である必要がありま す。
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $jobCd
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranDocomo($orderId, $jobCd, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'job_cd' => $jobCd,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranDocomo', $data);
    }

    /**
     * Entry transcation of Docomo Continuance.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranDocomoContinuance($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranDocomoContinuance', $data);
    }

    /**
     * Entry transcation of Edy.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(5) not null.
     *
     * Tax (税送料)
     * --Tax integer(5) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranEdy($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranEdy', $data);
    }

    /**
     * Entry transcation of JcbPreca.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(8) not null.
     *
     * Tax (税送料)
     * --Tax integer(8) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranJcbPreca($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranJcbPreca', $data);
    }

    /**
     * Entry transcation of Jibun.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(8) not null.
     *
     * Tax (税送料)
     * --Tax integer(8) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranJibun($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranJibun', $data);
    }

    /**
     * Entry transcation of PayEasy.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranPayEasy($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranPayeasy', $data);
    }

    /**
     * Entry transcation of Paypal.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     AUTH: provisional sales (仮売上).
     *     CAPTURE: immediate sales (即時売上).
     *
     * Amount (利用金額)
     * --Amount integer(10) not null.
     *
     *   It must be less than or equal to 9,999,999 yen
     *   or more ¥ 1 in spending + tax postage or the vinegar.
     *   利用金額+税送料で1円以上 9,999,999 円以下である必要がありま す。
     *
     * Tax (税送料)
     * --Tax integer(10) null.
     *
     * Currency (通貨コード)
     * --Currency string(3) null.
     *
     *   Default: JPY
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $jobCd
     * @param $amount
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function entryTranPaypal($orderId, $jobCd, $amount, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['order_id'] = $orderId;
        $data['job_cd'] = $jobCd;
        $data['amount'] = $amount;

        return $this->callApi('entryTranPaypal', $data);
    }

    /**
     * Entry transcation of Sb.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     AUTH: provisional sales (仮売上).
     *     CAPTURE: immediate sales (即時売上).
     *
     * Amount (利用金額)
     * --Amount integer(5) not null.
     *
     *   It must be less than or equal to 9,999,999 yen
     *   or more ¥ 1 in spending + tax postage or the vinegar.
     *   利用金額+税送料で1円以上 9,999,999 円以下である必要がありま す。
     *
     * Tax (税送料)
     * --Tax integer(5) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $jobCd
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranSb($orderId, $jobCd, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'job_cd' => $jobCd,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranSb', $data);
    }

    /**
     * Entry transcation of Suica.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(5) not null.
     *
     * Tax (税送料)
     * --Tax integer(5) null.
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranSuica($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranSuica', $data);
    }

    /**
     * Entry transcation of Webmoney.
     *
     * It is carried out with the necessary become trading ID in
     * subsequent settlement trading the issuance of trading password,
     * and then start trading.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranWebmoney($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('entryTranWebmoney', $data);
    }

    /**
     * Execute transcation.
     *
     * Customers using the information of the card number and the
     * expiration date you entered, and conducted a settlement to
     * communicate with the card company, and returns the result.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Method (支払方法)
     * --Method string(1) conditional null.
     *
     *   Allowed values:
     *     1: 一括
     *     2: 分割
     *     3: ボーナス一括
     *     4: ボーナス分割
     *     5: リボ
     *
     * Pay times (支払回数)
     * --PayTimes integer(2) conditional null.
     *
     * Card number (カード番号)
     * --CardNo string(16) not null.
     *
     * Expiration date (有効期限)
     * --Expire string(4) not null.
     *
     *   Format: YYMM
     *
     * Token (トークン決済時のトークン)
     * --Token string(*) not null
     *
     * Security code (セキュリティーコード)
     * --SecurityCode string(4) null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * @Output parameters
     *
     * ACS (ACS 呼出判定)
     * --ACS string(1)
     *   0: ACS call unnecessary(ACS 呼出不要)
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Method (支払方法)
     * --Method string(1)
     *
     * Pay times (支払回数)
     * --PayTimes integer(2)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Transcation ID (トランザクション ID)
     * --TransactionId string(28)
     *
     * Transcation date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTran($accessId, $accessPass, $orderId, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;

        if (!isset($data['method']) || ($data['method'] != 2 && $data['method'] != 4)) {
            unset($data['pay_times']);
        }

        // If member id empty, unset site id and site pass.
        if (!isset($data['member_id']) || 0 > strlen($data['member_id'])) {
            $this->disableSiteIdAndPass();
        }

        // If it doesn't exist cardseq or token.
        if (isset($data['card_seq']) || isset($data['token'])) {
            unset($data['card_no'], $data['expire'], $data['security_code']);
        }

        $this->addHttpParams();

        return $this->callApi('execTran', $data);
    }

    /**
     * Execute transcation of Cvs.
     *
     * Customers to conduct settlement communicates with the subsequent
     * settlement center in the information you have entered,
     * and returns the result.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Convenience (支払先コンビニコード)
     * --Convenience string(5) not null.
     *
     * Customer name (氏名)
     * --CustomerName string(40) not null.
     *
     *   If you specify a Seven-Eleven, half corner symbol can not be used.
     *
     * Customer kana (フリガナ)
     * --CustomerKana string(40) not null.
     *
     * Telephone number (電話番号)
     * --TelNo string(13) not null.
     *
     * Payment deadline dates (支払期限日数)
     * --PaymentTermDay integer(2) null.
     *
     * Mail address (結果通知先メールアドレス)
     * --MailAddress string(256) null.
     *
     * Shop mail address (加盟店メールアドレス)
     * --ShopMailAddress string(256) null.
     *
     * Reserve number (予約番号)
     * --ReserveNo string(20) null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Member number (会員番号)
     * --MemberNo string(20) null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Register display item 1 (POS レジ表示欄 1)
     * --RegisterDisp1 string(32) null.
     *
     * Register display item 2 (POS レジ表示欄 2)
     * --RegisterDisp2 string(32) null.
     *
     * Register display item 3 (POS レジ表示欄 3)
     * --RegisterDisp3 string(32) null.
     *
     * Register display item 4 (POS レジ表示欄 4)
     * --RegisterDisp4 string(32) null.
     *
     * Register display item 5 (POS レジ表示欄 5)
     * --RegisterDisp5 string(32) null.
     *
     * Register display item 6 (POS レジ表示欄 6)
     * --RegisterDisp6 string(32) null.
     *
     * Register display item 7 (POS レジ表示欄 7)
     * --RegisterDisp7 string(32) null.
     *
     * Register display item 8 (POS レジ表示欄 8)
     * --RegisterDisp8 string(32) null.
     *
     * Receipts disp item 1 (レシート表示欄 1)
     * --ReceiptsDisp1 string(60) null.
     *
     * Receipts disp item 2 (レシート表示欄 2)
     * --ReceiptsDisp2 string(60) null.
     *
     * Receipts disp item 3 (レシート表示欄 3)
     * --ReceiptsDisp3 string(60) null.
     *
     * Receipts disp item 4 (レシート表示欄 4)
     * --ReceiptsDisp4 string(60) null.
     *
     * Receipts disp item 5 (レシート表示欄 5)
     * --ReceiptsDisp5 string(60) null.
     *
     * Receipts disp item 6 (レシート表示欄 6)
     * --ReceiptsDisp6 string(60) null.
     *
     * Receipts disp item 7 (レシート表示欄 7)
     * --ReceiptsDisp7 string(60) null.
     *
     * Receipts disp item 8 (レシート表示欄 8)
     * --ReceiptsDisp8 string(60) null.
     *
     * Receipts disp item 9 (レシート表示欄 9)
     * --ReceiptsDisp9 string(60) null.
     *
     * Receipts disp item 10 (レシート表示欄 10)
     * --ReceiptsDisp10 string(60) null.
     *
     * Contact Us (お問合せ先)
     * --ReceiptsDisp11 string(42) not null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Contact telephone number (お問合せ先電話番号)
     * --ReceiptsDisp12 string(12) not null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Contact Hours (お問合せ先受付時間)
     * --ReceiptsDisp13 string(11) not null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Convenience (支払先コンビニ)
     * --Convenience string(5)
     *
     * Confirm number (確認番号)
     * --ConfNo string(20)
     *
     * Receipt number (受付番号)
     * --ReceiptNo string(32)
     *
     * Payment deadline date and time (支払期限日時)
     * --PaymentTerm string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Settlement date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $convenience
     * @param $customerName
     * @param $customerKana
     * @param $telNo
     * @param $receiptsDisp11
     * @param $receiptsDisp12
     * @param $receiptsDisp13
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranCvs($accessId, $accessPass, $orderId, $convenience, $customerName, $customerKana, $telNo, $receiptsDisp11, $receiptsDisp12, $receiptsDisp13, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['convenience'] = $convenience;
        $data['customer_name'] = $customerName;
        $data['customer_kana'] = $customerKana;
        $data['tel_no'] = $telNo;
        $data['receipts_disp_11'] = $receiptsDisp11;
        $data['receipts_disp_12'] = $receiptsDisp12;
        $data['receipts_disp_13'] = $receiptsDisp13;

        return $this->callApi('execTranCvs', $data);
    }

    /**
     * Cancel CVS Payment.
     * 【CvsCancel】APIを使用することで、お支払い前に支払い手続きを行えないようにすることは可能です。
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status 成功時は以下のステータスが返却されます。
     * --Status CANCEL：支払い停止
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function cvsCancel($accessId, $accessPass, $orderId)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId
        ];

        return $this->callApi('cvsCancel', $data);
    }

    /**
     * Execute transcation of Docomo.
     *
     * Customers using the information of the card number and the
     * expiration date you entered, and conducted a settlement to
     * communicate with the card company, and returns the result.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Docomo disp item 1 (ドコモ表示項目 1)
     * --DocomoDisp1 string(40) null.
     *
     * Docomo disp item 2 (ドコモ表示項目 2)
     * --DocomoDisp2 string(40) null.
     *
     * Settlement result back URL (決済結果戻し URL)
     * --RetURL string(256) not null.
     *
     *   Set the result receiving URL for merchants to receive
     *   a settlement result from this service.
     *
     * Payment start date in seconds (支払開始期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Deadline of customers from the [settlement] run until
     *   you call the [payment procedure completion IF].
     *   Up to 86,400 seconds (1 day)
     *   If the call parameter is empty, it is processed in 120 seconds
     *   お客様が【決済実行】から【支払手続き完了 IF】を呼び出すまでの期限。
     *   最大 86,400 秒(1 日)
     *   呼出パラメータが空の場合、120 秒で処理される
     *
     * Display shop name (利用店舗名)
     * --DispShopName string(50) not null.
     *
     * Display phone number (連絡先電話番号)
     * --DispPhoneNumber string(13) not null.
     *
     * Display mail address (メールアドレス)
     * --DispMailAddress string(100) not null.
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start Limit Date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $retUrl
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranDocomo($accessId, $accessPass, $orderId, $retUrl, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['ret_url'] = $retUrl;

        return $this->callApi('execTranDocomo', $data);
    }

    /**
     * It will return the token that is required in subsequent settlement deal.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Docomo Display 1 (ドコモ表示項目 1)
     * --DocomoDisp1 string(40) null.
     *
     * Docomo Display 2 (ドコモ表示項目 2)
     * --DocomoDisp2 string(40) null.
     *
     * Ret URL (決済結果戻し URL)
     * --RetURL string(256) not null.
     *
     * Payment deadline seconds (支払期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Max: 86,400 (1 day)
     *
     * First month free flag (初月無料区分)
     * --FirstMonthFreeFlag string(1) not null.
     *
     *   Allowed values:
     *     0: first month you do not free
     *     1: first month it will be free
     *     0: 初月無料にしない
     *     1: 初月無料にする
     *
     * Confirm base date (確定基準日)
     * --ConfirmBaseDate string(2) not null.
     *
     *   Allowed values:
     *     10,15,20,25,31
     *
     * Display shop name (利用店舗名)
     * --DispShopName string(50) null.
     *
     * Display phone number (連絡先電話番号)
     * --DispPhoneNumber string(13) null.
     *
     * Display mail address (メールアドレス)
     * --DispMailAddress string(100) null.
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start limit date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $retUrl
     * @param $firstMonthFreeFlag
     * @param $confirmBaseDate
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranDocomoContinuance($accessId, $accessPass, $orderId, $retUrl, $firstMonthFreeFlag, $confirmBaseDate, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['ret_url'] = $retUrl;
        $data['first_month_free_flag'] = $firstMonthFreeFlag;
        $data['confirm_base_date'] = $confirmBaseDate;

        return $this->callApi('execTranDocomoContinuance', $data);
    }

    /**
     * Execute transcation of Edy.
     *
     * Customers is carried out settlement to communicate with
     * Rakuten Edy center with information that was input.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Mail address (メールアドレス)
     * --MailAddress string(256) null.
     *
     * Shop mail address (加盟店メールアドレス)
     * --ShopMailAddress string(256) null.
     *
     * Settlement start mail additional information (決済開始メール付加情報)
     * --EdyAddInfo1 string(180) null.
     *
     * Settlement completion mail additional information (決済完了メール付加情報)
     * --ClientField1 string(320) null.
     *
     * Payment deadline dates (支払期限日数)
     * --PaymentTermDay integer(2) null.
     *
     * Payment deadline seconds (支払期限秒)
     * --PaymentTermSec integer(5) null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Receipt number (受付番号)
     * --ReceiptNo string(16)
     *
     * Edy order number (Edy 注文番号)
     * --EdyOrderNo string(40)
     *
     * Payment deadline date and time (支払期限日時)
     * --PaymentTerm string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Settlement date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $mailAddress
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranEdy($accessId, $accessPass, $orderId, $mailAddress, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['mail_address'] = $mailAddress;

        return $this->callApi('execTranEdy', $data);
    }

    /**
     * Exec transcation of JcbPreca.
     *
     * It will return the settlement request result
     * communicates with JCB plica center.
     *
     * @Input parameters
     *
     * Version (バージョン)
     * --Version string(3) null.
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Card number (カード番号)
     * --CardNo string(32) not null.
     *
     * Approval number (認証番号)
     * --ApprovalNo string(16) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Take turns information (持ち回り情報)
     * --CarryInfo string(34) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   Return status of actual sales success.
     *     SALES: 実売上
     *
     * Amount (利用金額)
     * --Amount integer(5)
     *
     * Tax (税送料)
     * --Tax integer(5)
     *
     * Before balance (利用前残高)
     * --BeforeBalance integer(5)
     *
     * After balance (利用後残高)
     * --AfterBalance integer(5)
     *
     * Card activate status (カードアクティベートステータス)
     * --CardActivateStatus string(1)
     *   One of the flowing:
     *     0: deactivate
     *     1: Activate
     *     2: first use (it has been activation shot with our trading)
     *     0: 非アクティベート
     *     1: アクティベート
     *     2: 初回利用(当取引でアクティベートされた)
     *
     * Card term status (カード有効期限ステータス)
     * --CardTermStatus string(1)
     *   One of the flowing:
     *     0: expiration date
     *     1: expired
     *     2: use before the start
     *     0: 有効期限内
     *     1: 有効期限切れ
     *     2: 利用開始前
     *
     * Card invalid status (カード有効ステータス)
     * --CardInvalidStatus string(1)
     *   One of the flowing:
     *     0: Valid
     *     1: Invalid
     *     0: 有効
     *     1: 無効
     *
     * Card web inquiry status (カード WEB 参照ステータス)
     * --CardWebInquiryStatus string(1)
     *   One of the flowing:
     *     0: WEB query Allowed
     *     1: WEB query disabled
     *     0: WEB 照会可
     *     1: WEB 照会不可
     *
     * Card valid limit (カード有効期限)
     * --CardValidLimit string(8)
     *   Format: YYYYMMDD
     *
     * Card type code (券種コード)
     * --CardTypeCode string(4)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $cardNo
     * @param $approvalNo
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranJcbPreca($accessId, $accessPass, $orderId, $cardNo, $approvalNo, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['card_no'] = $cardNo;
        $data['approval_no'] = $approvalNo;

        return $this->callApi('execTranJcbPreca', $data);
    }

    /**
     * It will return the token that is required in subsequent settlement deal.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Payment description (振込内容)
     * --PayDescription string(40) null.
     *
     * Redirect URL (決済結果戻し URL)
     * --RedirectURL string(256) not null.
     *
     * Payment deadline seconds (支払期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Max: 86,400 (1 Day)
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start limit date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $retUrl
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranJibun($accessId, $accessPass, $orderId, $retUrl, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['ret_url'] = $retUrl;

        return $this->callApi('execTranJibun', $data);
    }

    /**
     * Execute transcation of PayEasy.
     *
     * Customers to conduct settlement communicates with the
     * subsequent settlement center in the information you have entered.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Customer name (氏名)
     * --CustomerName string(40) not null.
     *
     *   If you specify a Seven-Eleven, half corner symbol can not be used.
     *
     * Customer kana (フリガナ)
     * --CustomerKana string(40) not null.
     *
     * Telephone number (電話番号)
     * --TelNo string(13) not null.
     *
     * Payment deadline dates (支払期限日数)
     * --PaymentTermDay integer(2) null.
     *
     * Mail address (結果通知先メールアドレス)
     * --MailAddress string(256) null.
     *
     * Shop mail address (加盟店メールアドレス)
     * --ShopMailAddress string(256) null.
     *
     * Register display item 1 (ATM 表示欄 1)
     * --RegisterDisp1 string(32) null.
     *
     * Register display item 2 (ATM 表示欄 2)
     * --RegisterDisp2 string(32) null.
     *
     * Register display item 3 (ATM 表示欄 3)
     * --RegisterDisp3 string(32) null.
     *
     * Register display item 4 (ATM 表示欄 4)
     * --RegisterDisp4 string(32) null.
     *
     * Register display item 5 (ATM 表示欄 5)
     * --RegisterDisp5 string(32) null.
     *
     * Register display item 6 (ATM 表示欄 6)
     * --RegisterDisp6 string(32) null.
     *
     * Register display item 7 (ATM 表示欄 7)
     * --RegisterDisp7 string(32) null.
     *
     * Register display item 8 (ATM 表示欄 8)
     * --RegisterDisp8 string(32) null.
     *
     * Receipts disp item 1 (利用明細表示欄 1)
     * --ReceiptsDisp1 string(60) null.
     *
     * Receipts disp item 2 (利用明細表示欄 2)
     * --ReceiptsDisp2 string(60) null.
     *
     * Receipts disp item 3 (利用明細表示欄 3)
     * --ReceiptsDisp3 string(60) null.
     *
     * Receipts disp item 4 (利用明細表示欄 4)
     * --ReceiptsDisp4 string(60) null.
     *
     * Receipts disp item 5 (利用明細表示欄 5)
     * --ReceiptsDisp5 string(60) null.
     *
     * Receipts disp item 6 (利用明細表示欄 6)
     * --ReceiptsDisp6 string(60) null.
     *
     * Receipts disp item 7 (利用明細表示欄 7)
     * --ReceiptsDisp7 string(60) null.
     *
     * Receipts disp item 8 (利用明細表示欄 8)
     * --ReceiptsDisp8 string(60) null.
     *
     * Receipts disp item 9 (利用明細表示欄 9)
     * --ReceiptsDisp9 string(60) null.
     *
     * Receipts disp item 10 (利用明細表示欄 10)
     * --ReceiptsDisp10 string(60) null.
     *
     * Contact Us (お問合せ先)
     * --ReceiptsDisp11 string(42) not null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Contact telephone number (お問合せ先電話番号)
     * --ReceiptsDisp12 string(12) not null.
     *
     *   It is displayed on the Loppi · Fami voucher receipt.
     *
     * Contact Hours (お問合せ先受付時間)
     * --ReceiptsDisp13 string(11) not null.
     *
     *   Example: 09:00-18:00.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Customer number (お客様番号)
     * --CustID string(11)
     *
     * Storage institution number (収納機関番号)
     * --BkCode string(5)
     *
     * Confirm number (確認番号)
     * --ConfNo string(20)
     *
     * Encrypt receipt number (暗号化決済番号)
     * --EncryptReceiptNo string(128)
     *
     * Payment deadline date and time (支払期限日時)
     * --PaymentTerm string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Settlement date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $customerName
     * @param $customerKana
     * @param $telNo
     * @param $receiptsDisp11
     * @param $receiptsDisp12
     * @param $receiptsDisp13
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranPayEasy($accessId, $accessPass, $orderId, $customerName, $customerKana, $telNo, $receiptsDisp11, $receiptsDisp12, $receiptsDisp13, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['customer_name'] = $customerName;
        $data['customer_kana'] = $customerKana;
        $data['tel_no'] = $telNo;
        $data['receipts_disp_11'] = $receiptsDisp11;
        $data['receipts_disp_12'] = $receiptsDisp12;
        $data['receipts_disp_13'] = $receiptsDisp13;

        return $this->callApi('execTranPayeasy', $data);
    }

    /**
     * Return the settlement request result communicates with PayPal center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Item name (商品・サービス名)
     * --ItemName string(64) not null.
     *
     * Redirect URL (リダイレクト URL)
     * --RedirectURL string(200) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $itemName
     * @param $redirectUrl
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranPaypal($accessId, $accessPass, $orderId, $itemName, $redirectUrl, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['item_name'] = $itemName;
        $data['redirect_url'] = $redirectUrl;

        return $this->callApi('execTranPaypal', $data);
    }

    /**
     * Execute transcation of Sb.
     *
     * Customers to conduct settlement communicates with JR East Japan
     * (Suica Center) with the information you have entered.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Ret URL (決済結果戻し URL)
     * --RetURL string(256) not null.
     *
     * Payment deadline seconds (支払期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Max: 86,400 (1 Day)
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start limit date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $retUrl
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranSb($accessId, $accessPass, $orderId, $retUrl, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['ret_url'] = $retUrl;

        return $this->callApi('execTranSb', $data);
    }

    /**
     * Execute transcation of Suica.
     *
     * Customers to conduct settlement communicates with JR East Japan
     * (Suica Center) with the information you have entered.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Item name (商品・サービス名)
     * --ItemName string(40) not null.
     *
     * Mail address (メールアドレス)
     * --MailAddress string(256) not null.
     *
     * Shop mail address (加盟店メールアドレス)
     * --ShopMailAddress string(256) null.
     *
     * Settlement start mail additional information (決済開始メール付加情報)
     * --SuicaAddInfo1 string(256) null.
     *
     * Settlement completion mail additional information (決済完了メール付加情報)
     * --SuicaAddInfo2 string(256) null.
     *
     * Settlement contents confirmation screen additional information
     * (決済内容確認画面付加情報)
     * --SuicaAddInfo3 string(256) null.
     *
     * Settlement completion screen additional information (決済完了画面付加情報)
     * --SuicaAddInfo4 string(256) null.
     *
     * Payment deadline dates (支払期限日数)
     * --PaymentTermDay integer(2) null.
     *
     * Payment deadline seconds (支払期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Max: 86,400 (1 Day)
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Suica order number (Suica 注文番号)
     * --SuicaOrderNo string(40)
     *
     * Receipt number (受付番号)
     * --ReceiptNo string(9)
     *
     * Payment deadline date and time (支払期限日時)
     * --PaymentTerm string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Transcation date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $itemName
     * @param $mailAddress
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranSuica($accessId, $accessPass, $orderId, $itemName, $mailAddress, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['item_name'] = $itemName;
        $data['mail_address'] = $mailAddress;

        return $this->callApi('execTranSuica', $data);
    }

    /**
     * Execute transcation of Webmoney.
     *
     * It will return the settlement request result
     * communicates with WebMoney center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Item name (商品・サービス名)
     * --ItemName string(40) not null.
     *
     * Customer name (氏名)
     * --CustomerName string(40) not null.
     *
     * Mail address (メールアドレス)
     * --MailAddress string(256) null.
     *
     * Shop mail address (加盟店メールアドレス)
     * --ShopMailAddress string(256) null.
     *
     * Payment deadline dates (支払期限日数)
     * --PaymentTermDay integer(2) null.
     *
     * Redirect URL (リダイレクト URL)
     * --RedirectURL string(256) null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Client field flag (加盟店自由項目返却フラグ)
     * --ClientFieldFlag string(1) null.
     *
     *   Allowed values:
     *     0: does not return (default)
     *     1: return
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Payment deadline date and time (支払期限日時)
     * --PaymentTerm string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Transcation date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $orderId
     * @param $itemName
     * @param $customerName
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranWebmoney($orderId, $itemName, $customerName, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['order_id'] = $orderId;
        $data['item_name'] = $itemName;
        $data['customer_name'] = $customerName;

        return $this->callApi('execTranWebmoney', $data);
    }

    /**
     * Alter tran.
     *
     * Do the cancellation of settlement content to deal with the settlement
     * has been completed. It will be carried out cancellation communicates
     * with the card company using the specified transaction information.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Job cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     VOID: 取消
     *     RETURN: 返品
     *     RETURNX: 月跨り返品
     *       SALES: 実売上
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Transcation ID (トランザクション ID)
     * --TranID string(28)
     *
     * Transcation date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function alterTran($accessId, $accessPass, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;

        if (!isset($data['method']) || ($data['method'] != 2 && $data['method'] != 4)) {
            unset($data['pay_times']);
        }

        return $this->callApi('alterTran', $data);
    }

    /**
     * Search trade.
     *
     * It returns to get the status of the transaction information
     * for the specified order ID.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string(15)
     *   One of the following
     *     UNPROCESSED: 未決済
     *     AUTHENTICATED: 未決済(3D 登録済)
     *     CHECK: 有効性チェック
     *     CAPTURE: 即時売上
     *     AUTH: 仮売上
     *     SALES: 実売上
     *     VOID: 取消
     *     RETURN: 返品
     *     RETURNX: 月跨り返品
     *     SAUTH: 簡易オーソリ
     *
     * Process date (処理日時)
     * --ProcessDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Job cd (処理区分)
     * --JobCd string(10)
     *   One of the following
     *     CHECK: 有効性チェック
     *     CAPTURE: 即時売上
     *     AUTH: 仮売上
     *     SALES: 実売上
     *     VOID: 取消
     *     RETURN: 返品
     *     RETURNX: 月跨り返品
     *     SAUTH: 簡易オーソリ
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Item code (商品コード)
     * --ItemCode string(7)
     *
     * Amount (利用金額)
     * --Amount Integer(7)
     *
     * Tax (税送料)
     * --Tax Integer(7)
     *
     * Site ID (サイト ID)
     * --SiteID string(13)
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * Card number (カード番号)
     * --CardNo string(16)
     *
     * Expiration date (有効期限)
     * --Expire string(4)
     *
     * Method (支払方法)
     * --Method string(1)
     *   One of the following
     *     1: 一括
     *     2: 分割
     *     3: ボーナス一括
     *     4: ボーナス分割
     *     5: リボ
     *
     * Pay times (支払回数)
     * --PayTimes integer(2)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Transcation ID (トランザクション ID)
     * --TranID string(28)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function searchTrade($orderId)
    {
        $data = ['order_id' => $orderId];
        return $this->callApi('searchTrade', $data);
    }

    /**
     * It gets the transaction information of the specified order ID.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Pay type (決済方法)
     * --PayType string(2) not null.
     *
     *   Allowed values:
     *     0: クレジット
     *     1: モバイル Suica
     *     2: 楽天 Edy
     *     3: コンビニ
     *     4: Pay-easy
     *     5: PayPal
     *     7: WebMoney
     *     8: au かんたん
     *     9: ドコモケータイ払い
     *     10: ドコモ継続課金
     *     11: ソフトバンクまとめて支払い(B)
     *     12: じぶん銀行
     *     13: au かんたん継続課金
     *     14: NET CASH・nanaco ギフト決済
     *
     * @Output parameters
     *
     * Status (現状態)
     * --Status string(15)
     *   One of the following
     *     UNPROCESSED: 未決済
     *     AUTHENTICATED: 未決済(3D 登録済)
     *     CHECK: 有効性チェック
     *     CAPTURE: 即時売上
     *     AUTH: 仮売上
     *     SALES: 実売上
     *     VOID: 取消
     *     RETURN: 返品
     *     RETURNX: 月跨り返品
     *     SAUTH: 簡易オーソリ
     *
     * Process date (処理日時)
     * --ProcessDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Job cd (処理区分)
     * --JobCd string(10)
     *   One of the following
     *     CHECK: 有効性チェック
     *     CAPTURE: 即時売上
     *     AUTH: 仮売上
     *     SALES: 実売上
     *     VOID: 取消
     *     RETURN: 返品
     *     RETURNX: 月跨り返品
     *     SAUTH: 簡易オーソリ
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Item code (商品コード)
     * --ItemCode string(7)
     *
     * Amount (利用金額)
     * --Amount Integer(7)
     *
     * Tax (税送料)
     * --Tax Integer(7)
     *
     * Site ID (サイト ID)
     * --SiteID string(13)
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * Card number (カード番号)
     * --CardNo string(16)
     *
     * Expiration date (有効期限)
     * --Expire string(4)
     *
     * Method (支払方法)
     * --Method string(1)
     *   One of the following
     *     1: 一括
     *     2: 分割
     *     3: ボーナス一括
     *     4: ボーナス分割
     *     5: リボ
     *
     * Pay times (支払回数)
     * --PayTimes integer(2)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Transcation ID (トランザクション ID)
     * --TranID string(28)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * Pay type (決済方法)
     * --PayType string(2)
     *
     * @param $orderId
     * @param $payType
     * @return array|null
     * @throws \Exception
     */
    public function searchTradeMulti($orderId, $payType)
    {
        $data = ['order_id' => $orderId, 'pay_type' => $payType];
        return $this->callApi('searchTradeMulti', $data);
    }

    /**
     * Au cancel return.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(7) not null.
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(7) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *
     *   If success it will be returned the following status.
     *     CANCEL:キャンセル
     *     RETURN:返品
     *
     * Amount (利用金額)
     * --Amount integer(7)
     *
     * Tax (税送料)
     * --Tax integer(7)
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(7)
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(7)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $cancelAmount
     * @param int $cancelTax
     * @return array|null
     * @throws \Exception
     */
    public function auCancelReturn($accessId, $accessPass, $orderId, $cancelAmount, $cancelTax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'cancel_amount' => $cancelAmount,
            'cancel_tax' => $cancelTax,
        ];

        return $this->callApi('auCancelReturn', $data);
    }

    /**
     * Billing cancellation.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   Return status when cancel success.
     *     CANCEL:継続課金解約
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function auContinuanceCancel($accessId, $accessPass, $orderId)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
        ];

        return $this->callApi('auContinuanceCancel', $data);
    }

    /**
     * Au continuance charge cancel.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(7) not null.
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(7) null.
     *
     * Continuance month (課金月)
     * --ContinuanceMonth string(6) not null.
     *
     *   Format: yyyyMM
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Continuance month (課金月)
     * --ContinuanceMonth string(6)
     *
     * Status (現状態)
     * --Status string
     *
     *   If success it will be returned the following status.
     *     CANCEL:キャンセル
     *     RETURN:返品
     *
     * Amount (利用金額)
     * --Amount integer(7)
     *
     * Tax (税送料)
     * --Tax integer(7)
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(7)
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(7)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $continuanceMonth
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function auContinuanceChargeCancel($accessId, $accessPass, $orderId, $continuanceMonth, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['continuance_month'] = $continuanceMonth;

        return $this->callApi('auContinuanceChargeCancel', $data);
    }

    /**
     * Au sales.
     *
     * Do the actual sales for the settlement of provisional sales.
     *
     * In addition, it will make the amount of the check and
     * when the provisional sales at the time of execution.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(7) not null.
     *
     * Tax (税送料)
     * --Tax integer(7) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   Return status when sales definite success.
     *     SALES:実売上
     *
     * Amount (利用金額)
     * --Amount integer(7)
     *
     * Tax (税送料)
     * --Tax integer(7)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function auSales($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('auSales', $data);
    }

    /**
     * Cancel paypal auth.
     *
     * Make temporary sales cancellation processing of transactions
     * to communicate with the PayPal center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Transaction ID (トランザクション ID)
     * --TranID string(19)
     *
     * Transaction date (処理日時)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function cancelAuthPaypal($accessId, $accessPass, $orderId)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
        ];

        return $this->callApi('cancelAuthPaypal', $data);
    }

    /**
     * Cancel paypal transcation.
     *
     * Do the cancellation processing of transactions to
     * communicate with the PayPal center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(10) not null.
     *
     * Tax (税送料)
     * --Tax integer(10) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Transaction ID (トランザクション ID)
     * --TranID string(19)
     *
     * Transaction date (処理日時)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function cancelTranPaypal($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('cancelTranPaypal', $data);
    }

    /**
     * Change transcation.
     *
     * Settlement allow you to change the amount of money
     * to complete transactions.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Job Cd (処理区分)
     * --JobCd string not null.
     *
     *   Allowed values:
     *     CAPTURE: immediate sales(即時売上)
     *     AUTH: provisional sales(仮売上)
     *     SAUTH: simple authorization(簡易オーソリ)
     *
     * Amount (利用金額)
     * --Amount integer(7) not null.
     *
     * Tax (税送料)
     * --Tax integer(7) null.
     *
     * Display date (利用日)
     * --DisplayDate string(6) null.
     *
     *   Format: YYMMDD
     *
     * @Output parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32)
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Transaction ID (トランザクション ID)
     * --TranID string(28)
     *
     * Transaction date (処理日時)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $jobCd
     * @param $amount
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function changeTran($accessId, $accessPass, $jobCd, $amount, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['job_cd'] = $jobCd;
        $data['amount'] = $amount;

        return $this->callApi('changeTran', $data);
    }

    /**
     * Docomo cancel return.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(6) not null.
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(6) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *
     *   If success it will be returned the following status.
     *     CANCEL:キャンセル
     *
     * Amount (利用金額)
     * --Amount integer(6)
     *
     * Tax (税送料)
     * --Tax integer(6)
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(6)
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(6)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $cancelAmount
     * @param int $cancelTax
     * @return array|null
     * @throws \Exception
     */
    public function docomoCancelReturn($accessId, $accessPass, $orderId, $cancelAmount, $cancelTax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'cancel_amount' => $cancelAmount,
            'cancel_tax' => $cancelTax,
        ];

        return $this->callApi('docomoCancelReturn', $data);
    }

    /**
     * Make a reduced determination of billing data.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(6) not null.
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(6) null.
     *
     * Continuance month (継続課金年月)
     * --ContinuanceMonth string(6) not null.
     *
     *   Format: yyyyMM
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When the amount change success will be returned the following status.
     *     RUN:処理中
     *
     * Amount (利用金額)
     * --Amount integer(6)
     *
     * Tax (税送料)
     * --Tax integer(6)
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(6)
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(6)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $cancelAmount
     * @param $continuanceMonth
     * @param int $cancelTax
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceCancelReturn($accessId, $accessPass, $orderId, $cancelAmount, $continuanceMonth, $cancelTax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'cancel_amount' => $cancelAmount,
            'cancel_tax' => $cancelTax,
            'continuance_month' => $continuanceMonth,
        ];

        return $this->callApi('docomoContinuanceCancelReturn', $data);
    }

    /**
     * Make a reduced determination of billing data.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When the amount change success will be returned the following status.
     *     RUN:実行中
     *
     * Amount (利用金額)
     * --Amount integer(6)
     *
     * Tax (税送料)
     * --Tax integer(6)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceSales($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('docomoContinuanceSales', $data);
    }

    /**
     * Merchants will make the amount change of the basic data.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When the amount change success will be returned the following status.
     *     RUN-CHANGE:変更中
     *
     * Amount (利用金額)
     * --Amount integer(6)
     *
     * Tax (税送料)
     * --Tax integer(6)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceShopChange($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('docomoContinuanceShopChange', $data);
    }

    /**
     * It will do the Exit from the mobile terminal.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * Last month free flag (終了月無料区分)
     * --LastMonthFreeFlag string(1) not null.
     *
     *   Allowed values:
     *     0: not to last month free
     *     1: I want to last month Free
     *     0: 終了月無料にしない
     *     1: 終了月無料にする
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When the amount change success will be returned the following status.
     *     RUN-END:終了中
     *
     * Amount (利用金額)
     * --Amount integer(6)
     *
     * Tax (税送料)
     * --Tax integer(6)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param $lastMonthFreeFlag
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceShopEnd($accessId, $accessPass, $orderId, $amount, $lastMonthFreeFlag, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
            'last_month_free_flag' => $lastMonthFreeFlag,
        ];

        return $this->callApi('docomoContinuanceShopEnd', $data);
    }

    /**
     * It will do the amount change from the portable terminal.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * Docomo display item 1 (ドコモ表示項目 1)
     * --DocomoDisp1 string(40) null.
     *
     * Docomo display item 2 (ドコモ表示項目 2)
     * --DocomoDisp2 string(40) null.
     *
     * Settlement result back URL (決済結果戻し URL)
     * --RetURL string(256) not null.
     *
     *   Set the result receiving URL for merchants to receive a
     *   settlement result from this service.
     *
     *   Customer authentication on the KDDI center, if you cancel the payment
     *   operations and to send the results to the specified URL when you run
     *   the settlement process in this service via a redirect.
     *
     *   加盟店様が本サービスからの決済結果を受信する為の結果受信 URL を設定。
     *   KDDI センター上でお客様が認証、支払操作をキャンセルした場合や、
     *   本サービスにて決済処理を実行した場合に指定された URL に結果をリダイレクト経由で送信。
     *
     * Payment start date in seconds (支払開始期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Deadline of customers from the [settlement] run until
     *   you call the [payment procedure completion IF].
     *   Up to 86,400 seconds (1 day)
     *   If the call parameter is empty, it is processed in 120 seconds
     *   お客様が【決済実行】から【支払手続き完了 IF】を呼び出すまでの期限。
     *   最大 86,400 秒(1 日)
     *   呼出パラメータが空の場合、120 秒で処理される
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start Limit Date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param $retUrl
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceUserChange($accessId, $accessPass, $orderId, $amount, $retUrl, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['amount'] = $amount;
        $data['ret_url'] = $retUrl;

        return $this->callApi('docomoContinuanceUserChange', $data);
    }

    /**
     * It will do the Exit from the mobile terminal.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * Docomo display item 1 (ドコモ表示項目 1)
     * --DocomoDisp1 string(40) null.
     *
     * Docomo display item 2 (ドコモ表示項目 2)
     * --DocomoDisp2 string(40) null.
     *
     * Settlement result back URL (決済結果戻し URL)
     * --RetURL string(256) not null.
     *
     *   Set the result receiving URL for merchants to receive a
     *   settlement result from this service.
     *   Customer authentication on the KDDI center, if you cancel the payment
     *   operations and to send the results to the specified URL when you run
     *   the settlement process in this service via a redirect.
     *
     *   加盟店様が本サービスからの決済結果を受信する為の結果受信 URL を設定。
     *   KDDI センター上でお客様が認証、支払操作をキャンセルした場合や、
     *   本サービスにて決済処理を実行した場合に指定された URL に結果をリダイレクト経由で送信。
     *
     * Payment start date in seconds (支払開始期限秒)
     * --PaymentTermSec integer(5) null.
     *
     *   Deadline of customers from the [settlement] run until
     *   you call the [payment procedure completion IF].
     *   Up to 86,400 seconds (1 day)
     *   If the call parameter is empty, it is processed in 120 seconds
     *   お客様が【決済実行】から【支払手続き完了 IF】を呼び出すまでの期限。
     *   最大 86,400 秒(1 日)
     *   呼出パラメータが空の場合、120 秒で処理される
     *
     * Last month free flag (終了月無料区分)
     * --LastMonthFreeFlag string(1) not null.
     *
     *   Allowed values:
     *     0: not to last month free
     *     1: I want to last month Free
     *     0: 終了月無料にしない
     *     1: 終了月無料にする
     *
     * @Output parameters
     *
     * Access ID (アクセス ID)
     * --AccessID string(32)
     *
     * Token (トークン)
     * --Token string(256)
     *
     * Start URL (支払手続き開始 IF のURL)
     * --StartURL string(256)
     *
     * Start Limit Date (支払開始期限日時)
     * --StartLimitDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param $retUrl
     * @param $lastMonthFreeFlag
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function docomoContinuanceUserEnd($accessId, $accessPass, $orderId, $amount, $retUrl, $lastMonthFreeFlag, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['amount'] = $amount;
        $data['ret_url'] = $retUrl;
        $data['last_month_free_flag'] = $lastMonthFreeFlag;

        return $this->callApi('docomoContinuanceUserEnd', $data);
    }

    /**
     * Do the actual sales for the settlement of provisional sales.
     *
     * In addition, it will make the amount of the check and when
     *  the provisional sales at the time of execution.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(6) not null.
     *
     * Tax (税送料)
     * --Tax integer(6) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When cancellation success will be returned the following status.
     *     SALES
     *
     * Amount (利用金額)
     * --Amount integer(8)
     *
     * Tax (税送料)
     * --Tax integer(7)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function docomoSales($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('docomoSales', $data);
    }

    /**
     * Balance inquiry of card.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Card number (カード番号)
     * --CardNo string(32) not null.
     *
     * Approval number (認証番号)
     * --ApprovalNo string(16) not null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When cancellation success will be returned the following status.
     *     SALES: 実売上
     *
     * Amount (利用金額)
     * --Amount integer(5)
     *
     * Tax (税送料)
     * --Tax integer(5)
     *
     * @param $cardNo
     * @param $approvalNo
     * @return array|null
     * @throws \Exception
     */
    public function jcbPrecaBalanceInquiry($cardNo, $approvalNo)
    {
        $data = [
            'card_no' => $cardNo,
            'approval_no' => $approvalNo,
        ];

        return $this->callApi('jcbPrecaBalanceInquiry', $data);
    }

    /**
     * Cancel jcb preca.
     *
     * Do the cancellation of settlement content to deal
     * with the settlement has been completed.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When cancellation success will be returned the following status.
     *     CANCEL: キャンセル
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function jcbPrecaCancel($accessId, $accessPass, $orderId)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
        ];

        return $this->callApi('jcbPrecaCancel', $data);
    }

    /**
     * Paypal sales.
     *
     * Do the actual sales processing of transactions to
     * communicate with the PayPal center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(10) not null.
     *
     * Tax (税送料)
     * --Tax integer(10) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Transaction ID (トランザクション ID)
     * --TranID string(19)
     *
     * Transaction date (処理日時)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Status (ステータス)
     * --Status string
     *   Success status: AUTH_CANCEL
     *
     * Amount (利用金額)
     * --Amount integer(10)
     *
     * Tax (税送料)
     * --Tax integer(10)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function paypalSales($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax,
        ];

        return $this->callApi('paypalSales', $data);
    }

    /**
     * Cancel sb.
     *
     * Do the cancellation of settlement content to deal
     * with the settlement has been completed.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(5) not null.
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(5) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     *   When cancellation success will be returned the following status.
     *     CANCEL: キャンセル
     *
     * Cancel amount (キャンセル金額)
     * --CancelAmount integer(5)
     *
     * Cancel tax (キャンセル税送料)
     * --CancelTax integer(5)
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $cancelAmount
     * @param int $cancelTax
     * @return array|null
     * @throws \Exception
     */
    public function sbCancel($accessId, $accessPass, $orderId, $cancelAmount, $cancelTax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'cancel_amount' => $cancelAmount,
            'cancel_tax' => $cancelTax,
        ];

        return $this->callApi('sbCancel', $data);
    }

    /**
     * To analyze the results of the authentication service.
     *
     * @Input parameters
     *
     * 3D secure authentication result (3D セキュア認証結果)
     * --PaRes string not null.
     *
     * Transaction ID (取引 ID)
     * --MD string(32) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Forward (仕向先コード)
     * --Forward string(7)
     *
     * Method (支払方法)
     * --Method string(1)
     *
     * Pay times (支払回数)
     * --PayTimes integer(2)
     *
     * Approve (承認番号)
     * --Approve string(7)
     *
     * Transcation ID (トランザクション ID)
     * --TransactionId string(28)
     *
     * Transcation date (決済日付)
     * --TranDate string(14)
     *   Format: yyyyMMddHHmmss
     *
     * Check string (MD5 ハッシュ)
     * --CheckString string(32)
     *   MD5 hash of OrderID ~ TranDate + shop password
     *   OrderID~TranDate+ショップパスワー ドの MD5 ハッシュ
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100)
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100)
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100)
     *
     * @param $paRes
     * @param $md
     * @return array|null
     * @throws \Exception
     */
    public function tdVerify($paRes, $md)
    {
        $this->disableShopIdAndPass();

        $data = [
            'pa_res' => $paRes,
            'md' => $md,
        ];

        return $this->callApi('tdVerify', $data);
    }

    /**
     * To analyze the results of the authentication service
     *
     * @param $paRes
     * @param $md
     * @return array|null
     * @throws \Exception
     */
    public function secureTran($paRes, $md)
    {
        return $this->tdVerify($paRes, $md);
    }

    /**
     * Book sales process
     *
     * @param $accessId
     * @param $accessPass
     * @param $bookingDate
     * @param $amount
     * @return array|null
     * @throws \Exception
     */
    public function bookSalesProcess($accessId, $accessPass, $bookingDate, $amount)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'booking_date' => $bookingDate,
            'amount' => $amount,
        ];

        return $this->callApi('bookSalesProcess', $data);
    }

    /**
     * Search booking info
     *
     * @param $accessId
     * @param $accessPass
     * @return array|null
     * @throws \Exception
     */
    public function searchBookingInfo($accessId, $accessPass)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
        ];

        return $this->callApi('searchBookingInfo', $data);
    }

    /**
     * Unbook sales process
     *
     * @param $accessId
     * @param $accessPass
     * @return array|null
     * @throws \Exception
     */
    public function unbookSalesProcess($accessId, $accessPass)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
        ];

        return $this->callApi('unbookSalesProcess', $data);
    }

    /**
     * Entry transcation of Virtualaccount.
     *
     * @Input parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount
     * --Amount integer(8) not null.
     *
     * Tax
     * --Tax Number(7) null.
     *
     * @Output parameters
     *
     * Order ID
     * --OrderID string(27)
     *
     * Access ID
     * --AccessID string(32)
     *
     * AccessPass
     * --AccessPass string(32)
     *
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function entryTranVirtualaccount($orderId, $amount, $tax = 0)
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount,
            'tax' => $tax
        ];

        return $this->callApi('entryTranVirtualaccount', $data);
    }

    /**
     * Exec transcation of Virtualaccount.
     *
     * @Input parameters
     *
     * Version (バージョン)
     * --Version string(3) null.
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Trade Days
     * --TradeDays integer(2) not null.
     *
     * Client field 1 (加盟店自由項目 1)
     * --ClientField1 string(100) null.
     *
     * Client field 2 (加盟店自由項目 2)
     * --ClientField2 string(100) null.
     *
     * Client field 3 (加盟店自由項目 3)
     * --ClientField3 string(100) null.
     *
     * Trade Reason
     * --TradeReason string(64) null.
     *
     * Trade Client Name
     * --TradeClientName string(64) null.
     *
     * Trade Client Mailaddress
     * --TradeClientMailaddress string(256) null.
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $tradeDays
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranVirtualaccount($accessId, $accessPass, $orderId, $tradeDays, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;
        $data['trade_days'] = $tradeDays;

        return $this->callApi('execTranVirtualaccount', $data);
    }

    /**
     * @param $params
     * @return array|null
     * @throws \Exception
     */
    public function registerRecurringCredit($params)
    {
        $params['RegistType'] = 3; // 1: member, 2: card_no, 3: order_id, 4: token
        return $this->callApi('registerRecurringCredit', $params);
    }

    /**
     * @param $orderId
     * @param $jobCd
     * @param int $amount
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function entryTranPaypay($orderId, $jobCd, $amount = 0, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['order_id'] = $orderId;
        $data['job_cd'] = $jobCd;
        $data['amount'] = $amount;

        return $this->callApi('entryTranPaypay', $data);
    }

    /**
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function execTranPaypay($accessId, $accessPass, $orderId, $data = [])
    {
        // Disable shop id and shop pass.
        if (!is_array($data)) {
            $data = [];
        }

        $data['access_id'] = $accessId;
        $data['access_pass'] = $accessPass;
        $data['order_id'] = $orderId;

        $this->addHttpParams();

        return $this->callApi('execTranPaypay', $data);
    }

    /**
     * Cancel paypay transcation.
     *
     * Do the cancellation processing of transactions to
     * communicate with the PayPay center.
     *
     * @Input parameters
     *
     * Access ID (取引 ID)
     * --AccessID string(32) not null.
     *
     * Access pass (取引パスワード)
     * --AccessPass string(32) not null.
     *
     * Order ID (オーダーID)
     * --OrderID string(27) not null.
     *
     * Amount (利用金額)
     * --Amount integer(10) not null.
     *
     * Tax (税送料)
     * --Tax integer(10) null.
     *
     * @Output parameters
     *
     * Order ID (オーダーID)
     * --OrderID string(27)
     *
     * Status (現状態)
     * --Status string
     * 対象取引の取引状態を返却します。
     *    ・CANCEL：キャンセル
     *    ・RETURN：返金
     *    ・SALES：実売上（※）
     *  ・CAPTURE：即時売上（※）
     *    ※一部キャンセルの場合に返却されます。
     *
     * Cancel Amount (キャンセル利用金額)
     * --CancelAmount string(7)
     *
     * Cancel Tax (キャンセル税送料)
     * --CancelTax string(7)
     *
     * ErrCode
     * ErrInfo
     *
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function cancelTranPaypay($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        $data = [
            'access_id' => $accessId,
            'access_pass' => $accessPass,
            'order_id' => $orderId,
            'cancel_amount' => $amount,
            'cancel_tax' => $tax,
        ];

        return $this->callApi('paypayCancelReturn', $data);
    }
}
