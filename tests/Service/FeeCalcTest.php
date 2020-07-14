<?php

declare(strict_types=1);

namespace Banking\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use Banking\CommissionTask\Service\FeeCalc;

class FeeCalcTest extends TestCase
{
    /**
     * @var FeeCalc
     */
    private $feeCalc;

    public function setUp(): void
    {
        //array values:
        $cash_in = ['fee_percent' => 0.0003, 'fee_limit' => 5];

        $cash_out_natural = ['fee_percent' => 0.003, 'free_of_charge_sum' => 1000,
        'free_of_charge_times' => 3, ];

        $cash_out_legal = ['fee_percent' => 0.003, 'fee_minimum' => 0.5];

        $currencies = [
        //EUR:USD - 1:1.1497, EUR:JPY - 1:129.53
        'eur_usd' => 1.1497, 'eur_jpy' => 129.53, ];

        $this->feeCalc = new FeeCalc($cash_in, $cash_out_natural, $cash_out_legal, $currencies);
    }

    /**
     * @param string $amount
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForCashinCalcFeeTesting
     */
    public function testCashinCalcFee(string $amount, string $currency, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->feeCalc->cashinCalcFee($amount, $currency)
        );
    }

    /**
     * @param string $amount
     * @param float $weekSpending
     * @param int $weekTimes
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForCashoutCalcNaturalFeeTesting
     */
    public function testCashoutCalcNaturalFee(string $amount, float $weekSpendings,
        int $weekTimes, string $currency, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->feeCalc->cashoutCalcNaturalFee($amount, $weekSpendings,
        $weekTimes, $currency)
        );
    }

    /**
     * @param string $amount
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForCashoutCalcLegalFeeTesting
     */
    public function testcashoutCalcLegalFee(string $amount, string $currency, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->feeCalc->cashoutCalcLegalFee($amount, $currency)
        );
    }

    /**
     * @param array $arr
     * @param int $key
     * @param float $week_amount
     * @param int $week_count
     * @param float $expectation
     *
     * @dataProvider dataProviderForCalcWeekSpendingsTesting
     */
    public function testCalcWeekSpendings(array $arr, int $key, float &$week_amount,
        int &$week_count, float $expectation)
    {
        //week_count and week_amount passed by reference
        $this->feeCalc->calcWeekSpendings($arr, $key, $week_amount,
          $week_count, $expectation);

        $this->assertEquals(
            $expectation,
            $week_amount
        );
    }

    public function dataProviderForCashinCalcFeeTesting(): array
    {
        return [
            'cashin 200 EUR' => ['200', 'EUR', '0.06'],
            'cashin 200 JPY' => ['200', 'JPY', '1'],
            'cashin 200 USD' => ['200', 'USD', '0.06'],
            'cashin 20000000 EUR' => ['20000000', 'EUR', '5'],
        ];
    }

    public function dataProviderForCashoutCalcNaturalFeeTesting(): array
    {
        return [
            'cashout 1200 EUR natural person 1, week 1' => ['1200', 0, 0, 'EUR', '0.6'],
            'cashout 1000 EUR natural person 1, week 1' => ['1000', 1200, 1, 'EUR', '3'],
            'cashout 1200 EUR natural person 1, week 1' => ['1200', 2200, 2, 'EUR', '3.6'],
            'cashout 2000 EUR natural person 1, week 1' => ['2000', 3400, 3, 'EUR', '6'],
            'cashout 1200 JPY natural person 1, week 1' => ['1200', 5400, 2, 'JPY', '4'],
            'cashout 2000 USD natural person 1, week 1' => ['2000', 5409.264, 3, 'USD', '6'],

            'cashout 1200 JPY natural person 2, week 2' => ['1200', 0, 2, 'JPY', '0'],
            'cashout 2000 USD natural person 2, week 2' => ['2000', 9.264, 3, 'USD', '2.59'],
        ];
    }

    public function dataProviderForCashoutCalcLegalFeeTesting(): array
    {
        return [
            'cashout 1200 EUR legal person' => ['1', 'EUR', '0.5'],
            'cashout 1000 EUR legal person' => ['1000', 'EUR', '3'],
            'cashout 1200 JPY legal person' => ['1200', 'USD', '3.6'],
            'cashout 2000 USD legal person' => ['2000', 'JPY', '65'],
        ];
    }

    public function dataProviderForCalcWeekSpendingsTesting(): array
    {
        $arr =
      [[0 => '2014-12-31', 1 => '4', 2 => 'natural', 3 => 'cash_out', 4 => '1.00', 5 => 'EUR'],
      [0 => '2015-01-01', 1 => '4', 2 => 'natural', 3 => 'cash_out', 4 => '1000.00', 5 => 'EUR'],
      [0 => '2015-01-01', 1 => '4', 2 => 'natural', 3 => 'cash_out', 4 => '2000.00', 5 => 'USD'],
      [0 => '2015-01-01', 1 => '5', 2 => 'natural', 3 => 'cash_out', 4 => '2000.00', 5 => 'JPY'],];

        return [
            'day 1, person 1, natural cash out of 1.00 EUR' => [$arr, 0, 0, 0, 0],
            'day 2, person 1, natural cash out of 1000.00 EUR' => [$arr, 1, 0, 0, 1],
            'day 2, person 1, natural cash out of 2000.00 USD' => [$arr, 2, 0, 0, 1001],
            'day 2, person 2, natural cash out of 2000.00 JPY' => [$arr, 3, 0, 0, 0],
        ];
    }
}
