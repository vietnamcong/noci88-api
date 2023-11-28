<?php

namespace Core\Helpers\GMO\Payment;

/**
 * Site API of GMO Payment.
 *
 * Site ID (サイト ID)
 * --SiteID string(13) not null.
 *
 * Site password (サイトパスワード)
 * --SitePass string(20) not null.
 *
 * $data = array('key' => 'value', ...)
 *   It contains not required and conditional required fields.
 *
 * Return result
 *   It will be return only one or multiple records.
 *   Multiple records joined with '|' whatever success or failed.
 */
class SiteApi extends Api
{
    /**
     * Site api constructor
     *
     * @param $host
     * @param $siteId
     * @param $sitePass
     * @param array $params
     */
    public function __construct($host, $siteId, $sitePass, $params = [])
    {
        if (!is_array($params)) {
            $params = [];
        }
        $params['site_id'] = $siteId;
        $params['site_pass'] = $sitePass;
        parent::__construct($host, $params);
    }

    /**
     * Register the member information in the specified site.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) unique not null.
     *
     * Member name (会員名)
     * --MemberName string(255) null.
     *
     * @Output parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * @param $memberId
     * @param string $memberName
     * @return array|null
     * @throws \Exception
     */
    public function saveMember($memberId, $memberName = '')
    {
        $data = [
            'member_id' => $memberId,
            'member_name' => $memberName,
        ];

        return $this->callApi('saveMember', $data);
    }

    /**
     * Update the member information in the specified site.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) unique not null.
     *
     * Member name (会員名)
     * --MemberName string(255) null.
     *
     * @Output parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * @param $memberId
     * @param string $memberName
     * @return array|null
     * @throws \Exception
     */
    public function updateMember($memberId, $memberName = '')
    {
        $data = [
            'member_id' => $memberId,
            'member_name' => $memberName,
        ];

        return $this->callApi('updateMember', $data);
    }

    /**
     * Search the member information in the specified site.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * @Output parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string
     *
     * Member Name (会員名)
     * --MemberName string
     *
     * Delete flag (削除フラグ)
     * --DeleteFlag string
     *   0: undeleted.
     *
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function searchMember($memberId)
    {
        $data = ['member_id' => $memberId];
        return $this->callApi('searchMember', $data);
    }

    /**
     * Delete the member information from the specified site.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * @Output parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function deleteMember($memberId)
    {
        $data = ['member_id' => $memberId];
        return $this->callApi('deleteMember', $data);
    }

    /**
     * Register the card information to the specified member.
     *
     * In addition, it confirms the effectiveness communicates
     * with the card company using a shop ID which is set on the site.
     *
     * Maximum only 10 records can be saved.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * Card registration serial number mode (カード登録連番モード)
     * --SeqMode string(1) null default 0.
     *
     *   Allowed values:
     *     0: Logical mode (default)
     *     1: Physical mode
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq int(1) conditional null.
     *
     *   This filed is conditional required.
     *   Null value when create, not null when update.
     *
     * Default flag (デフォルトフラグ)
     * --DefaultFlag string(1) null default 0.
     *
     *   Allowed values:
     *     0: it is not the default card (default)
     *     1: it will be the default card
     *
     * Card company abbreviation (カード会社略称)
     * --CardName string(10) null.
     *
     * Card number (カード番号)
     * --CardNo string(16) not null.
     *
     * Card password (カードパスワード)
     * --CardPass string(20) null.
     *
     *   The card password is required for settlement.
     *
     * Expiration date (有効期限)
     * --Expire string(4) not null.
     *
     *   Allowed format: YYMM
     *
     * Holder name (名義人)
     * --HolderName string(50) null.
     *
     * @Output parameters.
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq integer(1)
     *
     * Card number (カード番号)
     * --CardNo string(16)
     *   Asterisk with the exception of the last four digits.
     *   下 4 桁を除いて伏字
     *
     * Destination code (仕向先コード)
     * --Forward string(7)
     *   Destination code when performing a validity check.
     *   有効性チェックを行ったときの仕向先 コード
     *
     * @param $memberId
     * @param $cardNo
     * @param $expire
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function saveCard($memberId, $cardNo, $expire, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['member_id'] = $memberId;
        $data['card_no'] = $cardNo;
        $data['expire'] = $expire;

        return $this->callApi('saveCard', $data);
    }

    /**
     * Update the card information.
     *
     * @param $memberId
     * @param $cardSeq
     * @param $cardNo
     * @param $expire
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function updateCard($memberId, $cardSeq, $cardNo, $expire, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }
        $data['card_seq'] = $cardSeq;
        return $this->saveCard($memberId, $cardNo, $expire, $data);
    }

    /**
     * @param $memberId
     * @param $token
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function saveCardByToken($memberId, $token, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }
        $data['member_id'] = $memberId;
        $data['token'] = $token;
        return $this->callApi('saveCard', $data);
    }

    /**
     * @param $memberId
     * @param $cardSeq
     * @param $token
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function updateCardByToken($memberId, $cardSeq, $token, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['member_id'] = $memberId;
        $data['card_seq'] = $cardSeq;
        $data['token'] = $token;

        return $this->callApi('saveCard', $data);
    }

    /**
     * Search the card information of the specified member.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * Card registration serial number mode (カード登録連番モード)
     * --SeqMode string(1) not null.
     *
     *   Allowed values:
     *     0: Logical mode
     *     1: Physical mode
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq int(1) null.
     *
     *   Registration serial number of the referenced card.
     *
     * @Output parameters.
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq integer(1)
     *
     * Default flag (デフォルトフラグ)
     * --DefaultFlag string(1)
     *
     * Card name (カード会社略称)
     * --CardName string(10)
     *
     * Card number (カード番号)
     * --CardNo string(16)
     *
     * Expiration date (有効期限)
     * --Expire string(4)
     *
     * Holder name (名義人)
     * --HolderName string(50)
     *
     * Delete flag (￼削除フラグ)
     * --DeleteFlag string(1)
     *
     * @param $memberId
     * @param $seqMode
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function searchCard($memberId, $seqMode, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['member_id'] = $memberId;
        $data['seq_mode'] = $seqMode;

        return $this->callApi('searchCard', $data);
    }

    /**
     * Delete the card information of the specified member.
     *
     * @Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * Card registration serial number mode (カード登録連番モード)
     * --SeqMode string(1) null.
     *
     *   Allowed values:
     *     0: Logical mode
     *     1: Physical mode
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq int(1) not null.
     *
     *   Registration serial number of the referenced card.
     *
     * @Output parameters.
     *
     * Card registration serial number (カード登録連番)
     * --CardSeq integer(1)
     *
     * @param $memberId
     * @param $cardSeq
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function deleteCard($memberId, $cardSeq, $data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $data['member_id'] = $memberId;
        $data['card_seq'] = $cardSeq;

        return $this->callApi('deleteCard', $data);
    }

    /**
     * Release au OpenID of the specified member.
     *
     * Input parameters.
     *
     * Member ID (会員 ID)
     * --MemberID string(60) not null.
     *
     * @Output parameters.
     *
     * Site ID (サイト ID)
     * --SiteID string(13)
     *
     * Member ID (会員 ID)
     * --MemberID string(60)
     *
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function deleteAuOpenID($memberId)
    {
        $data = ['member_id' => $memberId];
        return $this->callApi('deleteAuOpenID', $data);
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

        // If it doesn't exist cardseq or token.
        if (isset($data['card_seq']) || isset($data['token'])) {
            unset($data['card_no'], $data['expire'], $data['security_code']);
        }

        $this->addHttpParams();

        return $this->callApi('execTran', $data);
    }
}
