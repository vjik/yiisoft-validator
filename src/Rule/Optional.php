<?php

declare(strict_types=1);

namespace Yiisoft\Validator\Rule;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\ValidationContext;

final class Optional extends Rule
{
    private ?Rules $rules = null;
    private bool $checkEmpty = true;

    /**
     * @var callable|null
     */
    private $emptyCallback = null;

    public function __construct(Rule ...$rules)
    {
        $this->setRules($rules);
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        if ($this->checkDoValidate($context)) {
            return $this->rules->validate($value, $context);
        }

        return new Result();
    }

    private function checkDoValidate(?ValidationContext $context): bool
    {
        if (!$context || !$context->getDataSet() || !$context->getAttribute()) {
            return true;
        }

        $dataSet = $context->getDataSet();
        $attribute = $context->getAttribute();

        if (!$dataSet->hasAttribute($attribute)) {
            return false;
        }

        if ($this->checkEmpty) {
            $value = $dataSet->getAttributeValue($attribute);
            return $this->emptyCallback === null
                ? !empty($value)
                : !($this->emptyCallback)($value);
        }

        return true;
    }

    public function rules(Rule ...$rules): self
    {
        $new = clone $this;
        $new->setRules($rules);
        return $new;
    }

    public function checkEmpty(bool $checkEmpty): self
    {
        $new = clone $this;
        $new->checkEmpty = $checkEmpty;
        return $new;
    }

    /**
     * @param callable|null $callback
     *
     * @return self
     */
    public function emptyCallback($callback): self
    {
        $new = clone $this;
        $new->emptyCallback = $callback;
        return $new;
    }

    private function setRules(array $rules): void
    {
        $this->rules = new Rules($rules);
    }
}
