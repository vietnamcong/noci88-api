<?php

namespace Core\Validator;

use Illuminate\Support\Facades\Lang;
use Prettus\Validator\LaravelValidator;
use Illuminate\Support\Arr;

/**
 * Class BaseValidator
 * @package App\Validator\Base
 */
class BaseValidator extends LaravelValidator
{
    const RULE_DEFAULT = [];
    const MESSAGE_DEFAULT = [];

    protected $_model = null;
    protected $rules = [];
    protected $messages = [];
    protected $_data = [];

    /**
     * @return null
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param null $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Get attribute name of model
     *
     * @return array
     */
    protected function _getAttributeNames()
    {
        return (array)Lang::get('models.' . app($this->getModel())->getTable() . '.attributes');
    }

    public function with(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data, $this->_data);

        return $this;
    }

    /**
     * Get all data or get data by key
     *
     * @param $key
     * @param $default
     * @return array
     */
    public function getData($key = null, $default = null)
    {
        if ($key) {
            return Arr::get($this->_data, $key, $default);
        }
        return $this->_data;

    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = array_replace_recursive($this->_data, $data);
    }

    /**
     * Add rule and message
     *
     * @param array $rules
     * @param array $messages
     * @param bool $default
     * @return $this
     */
    protected function _addRulesMessages($rules = [], $messages = [], $default = true)
    {
        $this->_setRules($rules, $default);
        $this->_setMessages($messages, $default);
        return $this;
    }

    /**
     * Set rules
     *
     * @param array $rules
     * @param bool $default
     */
    protected function _setRules($rules = [], $default = true)
    {
        if ($default) {
            $this->rules = array_merge($this->_getRulesDefault(), $rules);
            return;
        }

        $this->rules = $rules;
    }

    /**
     * Set message
     *
     * @param array $messages
     * @param bool $default
     */
    protected function _setMessages($messages = [], $default = true)
    {
        if ($default) {
            $this->messages = array_merge($this->_getMessagesDefault(), $messages);
            return;
        }

        $this->messages = $messages;
    }

    /**
     * Get rule default
     *
     * @return array
     */
    protected function _getRulesDefault()
    {
        return static::RULE_DEFAULT;
    }

    /**
     * Set message default
     *
     * @return array
     */
    protected function _getMessagesDefault()
    {
        return static::MESSAGE_DEFAULT;
    }

    /**
     * @return array
     */
    public function customErrorsBag()
    {
        $all = [];
        $errorsMessage = $this->errorsBag()->messages();

        foreach ($errorsMessage as $key => $messages) {
            $messages = is_array($messages) ? reset($messages) : $messages;
            $each = collect([$key => $messages])->all();
            $all = array_merge($all, $each);
        }

        return $all;
    }

    /**
     * Before validate, validate, after validate
     *
     * @param null $action
     * @return bool
     */
    public function passes($action = null)
    {
        $this->setData($this->data);
        $rules = $action ? $this->getRules($action) : $this->rules;
        $validator = $this->validator->make($this->data, $rules, $this->messages)->setAttributeNames($this->_getAttributeNames());

        // Before validate. Ex: validateCreate() -> _beforeValidateCreate()
        $beforeMethod = '_beforeValidate' . ucfirst($action);
        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($validator);
        } elseif (method_exists($this, '_before' . ucfirst($action))) {
            $this->{'_before' . ucfirst($action)}($validator);
        }

        // validate
        $fails = $validator->fails();

        // After validate. Ex: validateCreate() -> _afterValidateCreate()
        $afterMethod = '_afterValidate' . ucfirst($action);
        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($validator);
        } elseif (method_exists($this, '_after' . ucfirst($action))) {
            $this->{'_after' . ucfirst($action)}($validator);
        }

        // error
        if ($fails || !empty($validator->errors()->messages())) {
            $this->errors = $validator->messages();
            return false;
        }

        return true;
    }
}
