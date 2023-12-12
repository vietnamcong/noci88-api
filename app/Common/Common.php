<?php
// namespace App\Common;

use Illuminate\Support\Str;

if (!function_exists('moneyFormat')) {
    function moneyFormat($money, $decimals = 2, $skipFormat = false)
    {
        if ($skipFormat) {
            return number_format($money, $decimals);
        }

        return number_format($money, $decimals);
    }
}

if (!function_exists('moneyConvert')) {
    function moneyConvert($money, $revert = false)
    {
        if ($revert) {
            return $money * 1000;
        }

        return $money / 1000;
    }
}

if (!function_exists('eeziepayMoneyConvert')) {
    function eeziepayMoneyConvert($money, $revert = false)
    {
        if ($revert) {
            return moneyConvert($money) / 100;
        }

        return moneyConvert($money, true) * 100;
    }
}

if (!function_exists('getCurrentCurrency')) {
    function getCurrentCurrency()
    {
        $currentLanguage = session()->get(getConfig('language_prefix'), 'vi');

        $memberLanguage = getGuard()->check() ? getGuard()->user()->lang : $currentLanguage;

        return getConfig('currency.' . $memberLanguage);
    }
}

if (!function_exists('getCurrentLanguage')) {
    function getCurrentLanguage()
    {
        return session()->get(getConfig('language_prefix'), 'vi');
    }
}

if (!function_exists('getBankLogo')) {
    function getBankLogo($bank, $eeziepay = false)
    {
        $bankLogos = getConfig('bank_logo.banking');

        if ($eeziepay) {
            $bankLogos = getConfig('bank_logo.eeziepay');
        }

        foreach ($bankLogos as $bankCode => $bankLogo) {
            $tmpbank = $bank;

            if ($eeziepay) {
                $tmpbank = Str::substr($bank, 0, strpos($bank, '.'));
            }

            if (Str::contains(Str::lower($tmpbank), Str::lower($bankCode))) {
                return $bankLogo;
            }
        }

        return getConfig('bank_logo.banking.techcombank');
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

function toSql($query)
{
    return sql_binding($query->toSql(), $query->getBindings());
}

function sql_binding($sql, $bindings)
{
    $boundSql = str_replace(['%', '?'], ['%%', '%s'], $sql);
    foreach ($bindings as &$binding) {
        if ($binding instanceof \DateTime) {
            $binding = $binding->format('\'Y-m-d H:i:s\'');
        } elseif (is_string($binding)) {
            $binding = "'$binding'";
        }
    }
    $boundSql = vsprintf($boundSql, $bindings);
    return $boundSql;
}
