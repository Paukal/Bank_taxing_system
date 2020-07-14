<?php

declare(strict_types=1);

namespace Banking\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use Banking\CommissionTask\Service\Math;

class MathTest extends TestCase
{
    /**
     * @var Math
     */
    private $math;

    public function setUp(): void
    {
        $this->math = new Math(2);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderForAddTesting
     */
    public function testAdd(string $leftOperand, string $rightOperand, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->math->add($leftOperand, $rightOperand)
        );
    }

    /**
     * @param string $dividend
     * @param string $divisor
     * @param string $expectation
     *
     * @dataProvider dataProviderForDivideTesting
     */
    public function testDivide(string $dividend, string $divisor, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->math->divide($dividend, $divisor)
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderForMultiplyTesting
     */
    public function testMultiply(string $leftOperand, string $rightOperand, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->math->multiply($leftOperand, $rightOperand)
        );
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'add 2 natural numbers' => ['1', '2', '3.00'],
            'add negative number to a positive' => ['-1', '2', '1.00'],
            'add natural number to a float' => ['1', '1.05123', '2.05'],
        ];
    }

    public function dataProviderForDivideTesting(): array
    {
        return [
            'divide 2 natural numbers' => ['6', '2', '3.00'],
            'divide negative number to a positive' => ['-4', '-2', '2.00'],
            'divide natural number to a float' => ['3', '2', '1.50'],
        ];
    }

    public function dataProviderForMultiplyTesting(): array
    {
        return [
            'multiply 2 natural numbers' => ['5', '2', '10.00'],
            'multiply negative number to a positive' => ['-10', '-2', '20.00'],
            'multiply natural number to a float' => ['2', '2.3', '4.60'],
        ];
    }
}
