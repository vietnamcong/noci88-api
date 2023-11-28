<?php

namespace App\Models\Casts;

trait TransactionHistoryCasting
{
    protected function getMemberNameAttribute()
    {
        return $this->member ? $this->member->name : '';
    }

    public static function getStatus()
    {
        return [
            self::STATUS_WAITING => 'Đang chờ',
            self::STATUS_WIN => 'Thắng',
            self::STATUS_LOST => 'Thua',
            self::STATUS_TIE => 'Hòa',
            self::STATUS_CANCEL => 'Hủy',
            self::STATUS_RETURN_STAKE => 'Hoàn trả',
            self::STATUS_LIVE_COIN => 'Live coin',
        ];
    }

    public function getStatusText()
    {
        $class = data_get([
            self::STATUS_WAITING => 'info',
            self::STATUS_WIN => 'success',
            self::STATUS_LOST => 'danger',
            self::STATUS_TIE => 'primary',
            self::STATUS_CANCEL => 'warning',
            self::STATUS_RETURN_STAKE => 'dark',
            self::STATUS_LIVE_COIN => 'secondary',
        ], $this->status);

        $text = data_get(self::getStatus(), $this->status);

        return '<span class="label label-' . $class . '">' . mb_strtoupper($text) . '</span>';
    }

    public static function getProductType()
    {
        return [
            self::PT_SPORT_BOOK => 'SportsBook',
            self::PT_SBO_GAME => 'Games',
            self::PT_VIRTUAL_SPORTS => 'VirtualSports',
            self::PT_SBO_LIVE_CASINO => 'Casino',
            self::PT_SEAMLESS_GAME => 'SeamlessGame',
            self::PT_THIRD_PARTY_SPORTS_BOOK => 'ThirdPartySportsBook',
        ];
    }

    public static function getPorfolio()
    {
        return [
            self::PT_SPORT_BOOK => 'SportsBook',
            self::PT_SBO_GAME => 'Games',
            self::PT_VIRTUAL_SPORTS => 'VirtualSports',
            self::PT_SBO_LIVE_CASINO => 'Casino',
            self::PT_SEAMLESS_GAME => 'SeamlessGameProviderApi',
            self::PT_THIRD_PARTY_SPORTS_BOOK => 'ThirdPartySportsBook',
        ];
    }

    public function getProductTypeText()
    {
        return $this->product_type ? strtoupper(data_get(self::getProductType(), $this->product_type)) : '';
    }

    public function getPorfolioText()
    {
        return $this->product_type ? data_get(self::getPorfolio(), $this->product_type) : '';
    }

    public static function getGameProvider()
    {
        return [
            (string)self::GP_SBO => 'Sbo',
            (string)self::GP_WAN_MEI => 'Wan Mei',
            (string)self::GP_CQNINE => 'CQ9',
            (string)self::GP_PRAGMATIC_PLAY => 'Pragmatic Play',
            (string)self::GP_BIG_GAMING => 'Big Gaming',
            (string)self::GP_FLOW_GAMING => 'Flow Gaming',
            (string)self::GP_SEXY_GAMING => 'Sexy Gaming',
            (string)self::GP_SBO_GAME => 'SBO Game',
            (string)self::GP_JOKER_GAMING => 'Joker Gaming',
            (string)self::GP_REALTIME_GAMING => 'RealTimeGaming',
            (string)self::GP_IONLC => 'IONLC',
            (string)self::GP_WORLD_MATCH => 'WorldMatch',
            (string)self::GP_SBO_LOTS => 'SBO Slots',
            (string)self::GP_FUNKY_GAMES => 'FunkyGames',
            (string)self::GP_IDN_LIVE => 'IdnLive',
            (string)self::GP_SA_GAMING => 'SaGaming',
            (string)self::GP_EVOLUTION_GAMING => 'EvolutionGaming',
            (string)self::GP_YGGDRASIL => 'Yggdrasil',
            (string)self::GP_ALLBET => 'AllBet',
            (string)self::GP_MICRO_GAMING => 'MicroGaming',
            (string)self::GP_GREEN_DRAGON => 'GreenDragon',
            (string)self::GP_FLOW_GAMING_HUB => 'FlowGamingHub',
            (string)self::GP_PRAGMATIC_PLAY_CASINO => 'PragmaticPlayCasino',
            (string)self::GP_TWELVE_LIVE => 'TwelveLive',
            (string)self::GP_GAMATRON => 'Gamatron',
            (string)self::GP_SABA_SPORTS => 'SABA Sports',
            (string)self::GP_GIOCO_PLUS => 'GiocoPlus',
            (string)self::GP_MPOKER => 'MPoker',
            (string)self::GP_YGR => 'YGR',
            (string)self::GP_DIGITAIN => 'Digitain',
            (string)self::GP_TC_GAMING => 'TCGaming',
            (string)self::GP_AFB_SPORTS => 'AFB Sports',
            (string)self::GP_AFB_GAMING => 'AFBGaming',
            (string)self::GP_YEE_BET => 'YeeBet',
            (string)self::GP_JILI_GAMING => 'JiLiGaming',
            (string)self::GP_BTI_SPORTS => 'BTi Sports',
            (string)self::GP_NINE_GAMING => 'NineGaming',
            (string)self::GP_OG_LIVE => 'OGLive',
            (string)self::GP_AFB_CASINO => 'AFBCasino',
            (string)self::GP_PLAYTECH => 'PlayTech',
            (string)self::GP_PLAYTECH_LIVE_CASINO => 'PlayTech Live Casino',
        ];
    }

    public function getGameProviderText()
    {
        return $this->game_provider ? data_get(self::getGameProvider(), $this->game_provider) : '';
    }

    public static function getGameType()
    {
        return [
            self::PT_SPORT_BOOK => 'SportsBook',
            self::PT_SBO_GAME => 'Games',
            self::PT_VIRTUAL_SPORTS => 'VirtualSports',
            self::PT_SBO_LIVE_CASINO => 'Casino',
            self::PT_SEAMLESS_GAME => 'SeamlessGame',
            self::PT_THIRD_PARTY_SPORTS_BOOK => 'ThirdPartySportsBook',
        ];
    }

    public function getGameTypeText()
    {
        return $this->game_type ? data_get(self::getGameType(), $this->game_type) : '';
    }

    public function getWinLossText()
    {
        $diff = $this->win_loss - $this->amount;

        $class = '';
        if ($diff > 0) {
            $class = 'text-success';
        }
        if ($diff < 0) {
            $class = 'text-danger';
        }

        return '<span class="' . $class . '">' . moneyFormat($diff) . '</span>';
    }

    public function getFsDetailText()
    {
        if (!$this->is_fs) {
            return 'Chưa nhận';
        }

        $fsDetail = json_decode($this->fs_detail);

        $fsRate = data_get($fsDetail, 'fs_rate');
        $fsRate ? ($fsRate . '%<br/>') : '';

        $fsAt = data_get($fsDetail, 'fs_at');

        return $fsRate . $fsAt;
    }
}
