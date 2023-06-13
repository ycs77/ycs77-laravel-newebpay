<?php

namespace Ycs77\NewebPay;

use Ycs77\NewebPay\Enums\RespondType;

class NewebPayClose extends BaseNewebPay
{
    /**
     * The newebpay trade data.
     */
    protected array $tradeData = [];

    /**
     * The newebpay boot hook.
     */
    public function boot(): void
    {
        $this->tradeData['TimeStamp'] = $this->timestamp;

        $this->setApiPath('/API/CreditCard/Close');
        $this->setBackgroundSender();

        $this->setVersion();
        $this->setRespondType();
        $this->setNotifyURL();
    }

    /**
     * 串接版本
     */
    public function setVersion(string $version = null)
    {
        $this->tradeData['Version'] = $version ?? $this->config->get('newebpay.version');

        return $this;
    }

    /**
     * 回傳格式
     *
     * 回傳格式可設定 JSON 或 String。
     */
    public function setRespondType(RespondType $type = null)
    {
        $this->tradeData['RespondType'] = $type
            ? $type->value
            : $this->config->get('newebpay.respond_type')->value;

        return $this;
    }

    /**
     * 付款完成後的通知連結
     *
     * 以幕後方式回傳給商店相關支付結果資料
     * 僅接受 port 80 或 443。
     */
    public function setNotifyURL(string $url = null)
    {
        $this->tradeData['NotifyURL'] = $this->config->get('app.url').($url ?? $this->config->get('newebpay.notify_url'));

        return $this;
    }

    /**
     * 設定請退款的模式
     *
     * @param  string  $no  訂單編號
     * @param  int  $amt  訂單金額
     * @param  string  $type  編號類型
     *                        'order' => 使用商店訂單編號追蹤
     *                        'trade' => 使用藍新金流交易序號追蹤
     */
    public function setCloseOrder(string $no, int $amt, string $type = 'order')
    {
        if ($type === 'order') {
            $this->tradeData['MerchantOrderNo'] = $no;
            $this->tradeData['IndexType'] = 1;
        } elseif ($type === 'trade') {
            $this->tradeData['TradeNo'] = $no;
            $this->tradeData['IndexType'] = 2;
        }

        $this->tradeData['Amt'] = $amt;

        return $this;
    }

    /**
     * 設定請款或退款
     *
     * @param  string  $type  類型
     *                        'pay': 請款
     *                        'refund': 退款
     */
    public function setCloseType(string $type = 'pay')
    {
        if ($type === 'pay') {
            $this->tradeData['CloseType'] = 1;
        } elseif ($type === 'refund') {
            $this->tradeData['CloseType'] = 2;
        }

        return $this;
    }

    public function setCancel(bool $isCancel = false)
    {
        $this->tradeData['Cancel'] = $isCancel;

        return $this;
    }

    /**
     * Get request data.
     */
    public function getRequestData(): array
    {
        $postData = $this->encryptDataByAES($this->tradeData, $this->hashKey, $this->hashIV);

        return [
            'MerchantID_' => $this->merchantID,
            'PostData_' => $postData,
        ];
    }
}
