<?php

namespace Core\Helpers\GMO;

use Core\Helpers\GMO\Payment\ShopApi;
use Core\Helpers\GMO\Payment\SiteApi;

class GMO
{
    protected $shopApi;
    protected $siteApi;
    protected $data;

    public function __construct()
    {
        $params = ['public_key' => getConfig('public_key'), 'hash_key' => getConfig('gmo.hash_key')];
        $this->shopApi = new ShopApi(getConfig('gmo.url'), getConfig('gmo.shop_id'), getConfig('gmo.shop_pass'), $params);
        $this->siteApi = new SiteApi(getConfig('gmo.url'), getConfig('gmo.site_id'), getConfig('gmo.site_pass'));
    }

    /**
     * @param $memberId
     * @param $orderId
     * @param $amount
     * @param $cardSeq
     * @param array $params
     * @param string $jobCd
     * @return array|null
     * @throws \Exception
     */
    public function createPayment($memberId, $orderId, $amount, $cardSeq, $params = [], $jobCd = 'CAPTURE')
    {
        $this->data = [
            'memberId' => $memberId,
            'orderId' => $orderId,
            'amount' => $amount,
            'cardSeq' => $cardSeq,
            'jobCd' => $jobCd
        ];

        $registerPayment = $this->shopApi->entryTran($orderId, $jobCd, $amount);

        if (!$registerPayment['success']) {
            return $registerPayment;
        }

        $params['member_id'] = $memberId;
        $params['card_seq'] = $cardSeq;
        $params['method'] = getConstant('GMO.PAYMENT_METHOD.CREDIT_CARD');
        $chargePayment = $this->siteApi->execTran($registerPayment['result']['access_id'], $registerPayment['result']['access_pass'], $orderId, $params);
        $chargePayment['result']['access_id'] = $registerPayment['result']['access_id'];
        $chargePayment['result']['access_pass'] = $registerPayment['result']['access_pass'];

        return $chargePayment;
    }

    /**
     * PayPay
     *
     * @param $memberId
     * @param $orderId
     * @param $amount
     * @param string $jobCd
     * @param array $params
     * @return array|null
     * @throws \Exception
     */
    public function createPaymentPayPay($memberId, $orderId, $amount, $jobCd = 'CAPTURE', $params = [])
    {
        $this->data = [
            'memberId' => $memberId,
            'orderId' => $orderId,
            'amount' => $amount,
            'jobCd' => $jobCd
        ];

        $registerPayment = $this->shopApi->entryTranPaypay($orderId, $jobCd, $amount);

        if (!$registerPayment['success']) {
            return $registerPayment;
        }

        $params['client_field_1'] = $memberId;

        $chargePayment = $this->shopApi->execTranPaypay($registerPayment['result']['access_id'], $registerPayment['result']['access_pass'], $orderId, $params);
        $chargePayment['result']['access_id'] = $registerPayment['result']['access_id'];
        $chargePayment['result']['access_pass'] = $registerPayment['result']['access_pass'];

        return $chargePayment;
    }

    /**
     * @param $accessId
     * @param $accessPass
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function changePayment($accessId, $accessPass, $data = [])
    {
        return $this->shopApi->alterTran($accessId, $accessPass, $data);
    }

    /**
     * @param $accessId
     * @param $accessPass
     * @param $insDate
     * @return array|null
     * @throws \Exception
     */
    public function cancelPayment($accessId, $accessPass, $insDate)
    {
        if (empty($insDate)) {
            throw new \Exception('Missing payment created date.');
        }

        $jobCd = $this->getJobCdCancel($insDate);

        return $this->shopApi->alterTran($accessId, $accessPass, ['job_cd' => $jobCd]);
    }

    /**
     * Cancel PayPay transcation
     *
     * @param $accessId
     * @param $accessPass
     * @param $orderId
     * @param $amount
     * @param int $tax
     * @return array|null
     * @throws \Exception
     */
    public function cancelPaymentPaypay($accessId, $accessPass, $orderId, $amount, $tax = 0)
    {
        return $this->shopApi->cancelTranPaypay($accessId, $accessPass, $orderId, $amount, $tax);
    }

    /**
     * @param $orderId
     * @return array|null
     * @throws \Exception
     */
    public function searchPayment($orderId)
    {
        return $this->shopApi->searchTrade($orderId);
    }

    /**
     * @param $memberId
     * @param $cardNo
     * @param $expire
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function saveCard($memberId, $cardNo, $expire, $data = [])
    {
        /*
        if (!isProduction()) {
            $token = $this->shopApi->getToken($cardNo, $expire);

            if (!$token['success']) {
                return $token;
            }

            return $this->siteApi->saveCardByToken($memberId, $token['result']['token'][0], $data);
        }
        */

        return $this->siteApi->saveCard($memberId, $cardNo, $expire, $data);
    }

    /**
     * @param $memberId
     * @param $cardSeq
     * @return array|null
     * @throws \Exception
     */
    public function deleteCard($memberId, $cardSeq)
    {
        return $this->siteApi->deleteCard($memberId, $cardSeq);
    }

    /**
     * @param $memberId
     * @param $cardSeq
     * @param $cardNo
     * @param $expire
     * @return array|null
     * @throws \Exception
     */
    public function updateCard($memberId, $cardSeq, $cardNo, $expire)
    {
        /*
        if (!isProduction()) {
            $token = $this->shopApi->getToken($cardNo, $expire);

            if (!$token['success']) {
                return $token;
            }

            return $this->siteApi->updateCardByToken($memberId, $cardSeq, $token['result']['token'][0]);
        }
        */

        return $this->siteApi->updateCard($memberId, $cardSeq, $cardNo, $expire);
    }

    /**
     * ErrorCode E01240002 | List cards was empty
     *
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function searchCard($memberId)
    {
        $result = $this->siteApi->searchCard($memberId, 0);

        if (!$result['success'] && strpos($result['response'], 'E01240002') !== false) {
            return ['success' => true, 'result' => []];
        }

        if (!$result['success'] || !isset($result['result']['card_seq'])) {
            return $result;
        }

        $oneCard = $result['result'];
        unset($result['result']);
        $result['result'][] = $oneCard;

        return $result;
    }

    /**
     * @param $memberId
     * @param string $memberName
     * @return array|null
     * @throws \Exception
     */
    public function addMember($memberId, $memberName = '')
    {
        return $this->siteApi->saveMember($memberId, $memberName);
    }

    /**
     * @param $memberId
     * @param string $memberName
     * @return array|null
     * @throws \Exception
     */
    public function updateMember($memberId, $memberName = '')
    {
        return $this->siteApi->updateMember($memberId, $memberName);
    }

    /**
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function deleteMember($memberId)
    {
        return $this->siteApi->deleteMember($memberId);
    }

    /**
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function searchMember($memberId)
    {
        return $this->siteApi->searchMember($memberId);
    }

    /**
     * @param $memberId
     * @return array
     * @throws \Exception
     */
    public function findOrAddMember($memberId)
    {
        $isMember = $this->searchMember($memberId);

        if ($isMember['success']) {
            return [$memberId, false];
        }

        $createMember = $this->addMember($memberId);

        if (!$createMember['success']) {
            throw new \Exception($createMember['message']);
        }

        return [$memberId, true];
    }

    /**
     * @param $insDate
     * @return string
     * Get job Cd Cancel
     */
    public function getJobCdCancel($insDate)
    {
        switch (true) {
            case date('Y-m-d') == date('Y-m-d', strtotime($insDate)) :
                $jobCd = 'VOID';
                break;
            case date('Y-m') == date('Y-m', strtotime($insDate)) :
                $jobCd = 'RETURN';
                break;
            default:
                $jobCd = 'RETURNX';
        }

        return $jobCd;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $params
     * @return array|null
     * @throws \Exception
     */
    public function registerRecurringCredit($params)
    {
        return $this->shopApi->registerRecurringCredit($params);
    }

    public function isCreditRegistered($memberId)
    {
        $isRegistered = session()->get($memberId . '__credit_registered');
        if ($isRegistered) {
            return true;
        }

        $isMember = $this->checkCard($memberId);

        if (isset($isMember['success']) && $isMember['success']) {
            session()->put($memberId . '__credit_registered', true);
            return true;
        }

        return false;
    }

    /**
     * Validate card use credit member code
     *
     * @param $memberId
     * @return array|null
     * @throws \Exception
     */
    public function checkCard($memberId)
    {
        $orderId = 'CardAuthen' . date('ymdhis') . mt_rand(1000, 9999);
        $jobCd = 'CHECK';
        $amount = 0;
        $registerPayment = $this->shopApi->entryTran($orderId, $jobCd, $amount);

        if (!$registerPayment['success']) {
            return $registerPayment;
        }

        $params['member_id'] = $memberId;
        $params['card_seq'] = 0;
        $params['method'] = getConstant('GMO.PAYMENT_METHOD.CREDIT_CARD'); // 支払い方法区分 => 一括
        $chargePayment = $this->siteApi->execTran($registerPayment['result']['access_id'], $registerPayment['result']['access_pass'], $orderId, $params);
        $chargePayment['result']['access_id'] = $registerPayment['result']['access_id'];
        $chargePayment['result']['access_pass'] = $registerPayment['result']['access_pass'];

        return $chargePayment;
    }

    /**
     * Get link type URL
     *
     * @param $newCreditFlag
     * @param string $memberId same as $credit_member_code
     * @return string
     */
    public function getCreditRegisterUrl($newCreditFlag, $memberId)
    {
        if ($newCreditFlag || !$this->isCreditRegistered($memberId)) {
            return $this->generateCreditRegisterUrl($memberId);
        }

        return '';
    }

    /**
     * Generate link type URL
     *
     * @param string $creditMemberCode
     * @param string $dateTime
     * @param string $userInfo
     * @return string
     */
    public function generateCreditRegisterUrl($creditMemberCode, $dateTime = '', $userInfo = 'pc')
    {
        $siteId = getConfig('gmo.site_id');
        $shopId = getConfig('gmo.shop_id');
        $sitePass = getConfig('gmo.site_pass');
        $orderId = $creditMemberCode;
        $memberId = $creditMemberCode;
        $datetime = ($dateTime == '') ? date('YmdHis') : $dateTime;
        $lang = 'ja';
        $memberPassString = md5($siteId . $orderId . $shopId . $sitePass . $datetime);

        return getConfig('gmo.url_link_type') . $shopId . '/Member/Edit?SiteID=' . $siteId . '&ShopID=' . $shopId
            . "&MemberID={$memberId}&MemberName=&MemberPassString={$memberPassString}&RetURL=None&DateTime={$datetime}&Lang={$lang}&Confirm=0&UserInfo={$userInfo}&Enc=utf-8";
    }
}
