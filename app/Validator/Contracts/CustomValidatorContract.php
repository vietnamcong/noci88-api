<?php

namespace App\Validator\Contracts;

use Core\Validator\Concerns\BaseValidatesAttributes;
use Illuminate\Contracts\Translation\Translator;
use Core\Validator\Contracts\BaseValidatorContract;
use Illuminate\Support\Str;
use Countable;
use Illuminate\Validation\Rules\Unique;

class CustomValidatorContract extends BaseValidatorContract
{
    use BaseValidatesAttributes;

    /**
     * @param Translator $translator
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        $this->implicitRules[] = Str::studly('required_select');
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public static function validateMaxLength($attribute, $value, $parameters)
    {
        return mb_strlen($value) <= $parameters[0];
    }

    protected function replaceMaxLength($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $this->getDisplayableAttribute($parameters[0]), $message);
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateRequiredSelect($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateKatakana($attribute, $value, $parameters)
    {
        $result = preg_match('/^([゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ]+)$/u', $value, $matches);

        return $result ? true : false;
    }

    public function validateNotJapanese($attribute, $value, $parameters)
    {
        return !$this->isJapanese($value);
    }

    protected function isKanji($str)
    {
        return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
    }

    /**
     * Detect Hiragana character
     *
     * @param $str
     * @return bool
     */
    protected function isHiragana($str)
    {
        return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
    }

    /**
     * Detect Katakana character
     *
     * @param $str
     * @return bool
     */
    protected function isKatakana($str)
    {
        return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
    }

    /**
     * Detect Japanese
     *
     * @param $str
     * @return bool
     */
    protected function isJapanese($str)
    {
        return $this->isKanji($str) || $this->isHiragana($str) || $this->isKatakana($str) || strlen($str) != strlen(utf8_decode($str));
    }

    public function validateWithoutSpace($attribute, $value, $parameters)
    {
        return preg_match('/^\S*$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateNumber($attribute, $value, $parameters)
    {
        $regex = "/^[0-9]+$/";

        return preg_match($regex, $value);
    }

    public function validateCaptcha($attribute, $value, $parameters)
    {
        return captcha_check($value);
    }

    public function validateUniqueInSensitive($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'unique_in_sensitive');

        [$connection, $table, $idColumn] = $this->parseTable($parameters[0]);

        $column = $this->getQueryColumn($parameters, $attribute);

        $id = null;

        $value = strtolower($value);

        $verifier = $this->getPresenceVerifier($connection);

        $extra = $this->getUniqueExtra($parameters);

        if ($this->currentRule instanceof Unique) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }

        return $verifier->getCount(
                $table, $column, $value, $id, $idColumn, $extra
            ) == 0;
    }
}
