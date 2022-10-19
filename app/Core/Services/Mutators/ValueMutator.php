<?php

namespace App\Core\Services\Mutators;

use App\Core\Exceptions\Mutators\MutatorException;
use App\Core\Exceptions\Mutators\ValidationException;

/**
 * Class ValueMutator
 * @package App\Core
 */
class ValueMutator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * ValueFormatter constructor.
     *
     * @param ValidatorInterface $validator
     * @param FormatterInterface $formatter
     */
    public function __construct(
        ValidatorInterface $validator,
        FormatterInterface $formatter
    ) {
        $this->validator = $validator;
        $this->formatter = $formatter;
    }

    /**
     * Validate and format the value
     *
     * @param string $value
     * @return string
     * @throws MutatorException
     */
    public function mutate(string $value): string
    {
        if (!$this->validator->validate($value)) {
            throw new ValidationException($this->validator->getErrors());
        }

        return $this->formatter->format($value);
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }
}
