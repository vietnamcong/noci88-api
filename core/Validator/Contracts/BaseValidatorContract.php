<?php

namespace Core\Validator\Contracts;

use Core\Validator\Concerns\BaseValidatesAttributes;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;

class BaseValidatorContract extends Validator
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
        $this->setCustomMessages($this->_customMessages);
    }
}
