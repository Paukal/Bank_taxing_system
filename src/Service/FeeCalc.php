<?php

declare(strict_types=1);

namespace Banking\CommissionTask\Service;

/*
Main fee calculations
*/

class FeeCalc
{
    private $cash_in; //array with cash in values(fees, limits, etc)
    private $cash_out_natural; //array with cash out values(fees, limits, etc)
    private $cash_out_legal; //array with cash out values(fees, limits, etc)
    private $currencies; //array with currency values

    private $math;

    public function __construct(array $cash_in = [],
        array $cash_out_natural = [], array $cash_out_legal = [],
        array $currencies = [])
    {
        $this->cash_in = $cash_in;
        $this->cash_out_natural = $cash_out_natural;
        $this->cash_out_legal = $cash_out_legal;
        $this->currencies = $currencies;

        $this->math = new Math(3); //3- for better precision
    }

    //calculates cash in fee.
    public function cashinCalcFee(string $amount, string $currency): string
    {
        $amnt = floatval($amount);
        $amnt = $this->switchToEuro($currency, $amnt);
        $fee = 0;

        if ($amnt * $this->cash_in['fee_percent'] < $this->cash_in['fee_limit']) {
            $fee = $amnt * $this->cash_in['fee_percent'];
        } else {
            $fee = $this->cash_in['fee_limit'];
        }

        //funkcijoj atlikti roundinima
        $fee = $this->switchFromEuro($currency, $fee);
        $fee = $this->roundUp($fee, $currency, 2);

        return strval($fee);
    }

    //calculates cash out fee for natural people.
    public function cashoutCalcNaturalFee(string $amount, float $weekSpendings,
        int $weekTimes, string $currency): string
    {
        $amnt = floatval($amount);
        $amnt = $this->switchToEuro($currency, $amnt);
        $fee = 0;
        $limitBalance = $this->cash_out_natural['free_of_charge_sum']
                - ($amnt + $weekSpendings);

        if ($weekSpendings - $this->cash_out_natural['free_of_charge_sum'] >= 0
                || $weekTimes > $this->cash_out_natural['free_of_charge_times']) {
            $fee = $amnt * $this->cash_out_natural['fee_percent'];
        } elseif ($limitBalance < 0) {
            $fee = $limitBalance * $this->cash_out_natural['fee_percent'] * (-1);
        }

        //funkcijoj atlikti roundinima
        $fee = $this->switchFromEuro($currency, $fee);
        $fee = $this->roundUp($fee, $currency, 2);

        return strval($fee);
    }

    //calculates cash out fee for legal people.
    public function cashoutCalcLegalFee(string $amount, string $currency): string
    {
        $amnt = floatval($amount);
        $amnt = $this->switchToEuro($currency, $amnt);
        $fee = $this->cash_out_legal['fee_minimum'];

        if ($amnt * $this->cash_out_legal['fee_percent']
                > $this->cash_out_legal['fee_minimum']) {
            $fee = $amnt * $this->cash_out_legal['fee_percent'];
        }

        //funkcijoj atlikti roundinima
        $fee = $this->switchFromEuro($currency, $fee);
        $fee = $this->roundUp($fee, $currency, 2);

        return strval($fee);
    }

    public function switchFromEuro(string $currency, float $amount): float
    {
        switch ($currency) {
                    case 'USD':
                            $amount = $this->math->multiply(strval($amount),
                            strval($this->currencies['eur_usd']));
                            break;
                    case 'JPY':
                            $amount = $this->math->multiply(strval($amount),
                            strval($this->currencies['eur_jpy']));
                            break;
            }

        return floatval($amount);
    }

    public function switchToEuro(string $currency, float $amount): float
    {
        switch ($currency) {
                    case 'USD':
                            $amount = $this->math->divide(strval($amount),
                            strval($this->currencies['eur_usd']));
                            break;
                    case 'JPY':
                            $amount = $this->math->divide(strval($amount),
                            strval($this->currencies['eur_jpy']));
                            break;
            }

        return floatval($amount);
    }

    //calculates week spendings for particual person.
    public function calcWeekSpendings(array $arr, int $key, float &$week_amount,
        int &$week_count)
    {
        $date = date_create_from_format('Y-m-d', $arr[$key][0]);
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));
        $day = intval($date->format('d'));
        $weekDay = date('w', mktime(0, 0, 0, $month, $day, $year));
        $weekDay = intval($weekDay);

        if ($weekDay === 0) { //sunday is 0 by default
                    $weekDay = 7; //for formating purposes
        }

        foreach ($arr as $key2 => $value) {
            if ($key2 === $key) {
                break;
            }

            if (intval($arr[$key2][1]) === intval($arr[$key][1])
                        && $arr[$key2][3] === 'cash_out' && $arr[$key2][2] === 'natural') {
                $date2 = date_create_from_format('Y-m-d', $arr[$key2][0]);
                $year2 = intval($date2->format('Y'));
                $month2 = intval($date2->format('m'));
                $day2 = intval($date2->format('d'));

                $diff = date_diff($date, $date2);
                $datediff = $diff->format('%a');
                //echo "diff between dates: " . $datediff . "\n";

                if ($datediff < 7) {
                    $weekDay2 = date('w', mktime(0, 0, 0, $month2, $day2, $year2));
                    $weekDay2 = intval($weekDay2);

                    if ($weekDay2 === 0) { //sunday is 0 by default
                        $weekDay2 = 7; //for formating purposes
                    }

                    if ($weekDay > $weekDay2 || $weekDay === $weekDay2) {
                        $euros = $this->switchToEuro($arr[$key2][5],
                                            floatval($arr[$key2][4]));
                        ++$week_count;
                        $week_amount = floatval($this->math->add(strval($week_amount),
                                            strval($euros)));
                    }
                }
            }
        }
    }

    public function roundUp($value, $currency, $places = 0)
    {
        $places = $this->checkPrecision($currency);

        $mult = pow(10, $places);

        return ceil($value * $mult) / $mult;
    }

    public function checkPrecision($currency): int
    {
        $prec = 0;

        switch ($currency) {
                    case 'JPY':
                            $prec = 0;
                            break;
                    case 'EUR':
                            $prec = 2;
                            break;
                    case 'USD':
                            $prec = 2;
                            break;
            }

        return $prec;
    }
}
