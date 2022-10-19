<?php

namespace Tests\Unit\Core\Services\Mutators;

use App\Core\Exceptions\Mutators\ValidationException;
use App\Core\Services\Mutators\FormatterInterface;
use App\Core\Services\Mutators\ValueMutator;
use App\Core\Services\Mutators\ValidatorInterface;
use Mockery as M;
use Tests\TestCase;

/**
 * Class ValueMutatorTest
 * @package Tests\Unit\Mutators
 * @SuppressWarnings(PHP.MD)
 */
class ValueMutatorTest extends TestCase
{
    /**
     * Test that the mutator returns strings that are correctly formatted when
     * the validator returns true
     *
     * @test
     * @group mutator
     * @group taxid
     */
    public function valueMutatorReturnsFormattedStringsWhenValidatorReturnsTrue(): void
    {
        $before = $this->faker->unique()->word;
        $after = $this->faker->unique()->word;

        $formatter = M::mock(FormatterInterface::class);
        $formatter->expects('format')->with($before)->andReturns($after);

        $validator = M::mock(ValidatorInterface::class);
        $validator->expects('validate')->andReturns(true);

        $valueMutator = new ValueMutator($validator, $formatter);
        $result = $valueMutator->mutate($before);

        self::assertSame($after, $result);
    }

    /**
     * Test that the mutator throws an exception with the correct amount of
     * errors when the validator returns false
     *
     * @test
     * @group taxid
     * @group mutator
     */
    public function valueMutatorThrowsExceptionWithErrorsWhenValidatorReturnsFalse(): void
    {
        $before = $this->faker->unique()->word;

        $errors = [
            $this->faker->sentence,
            $this->faker->sentence,
        ];

        $formatter = M::mock(FormatterInterface::class);
        $formatter->shouldNotHaveReceived('format');

        $validator = M::mock(ValidatorInterface::class);
        $validator->expects('validate')->andReturns(false);
        $validator->expects('getErrors')->andReturns($errors);

        $valueMutator = new ValueMutator($validator, $formatter);
        $exceptionThrown = false;

        try {
            $valueMutator->mutate($before);
        } catch (ValidationException $e) {
            $exceptionThrown = true;
            self::assertSame($errors, $e->getErrors());
            self::assertSame($errors[0], $e->getMessage());
        }

        self::assertTrue($exceptionThrown);
    }
}
