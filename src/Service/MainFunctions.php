<?php

declare(strict_types=1);

namespace Banking\CommissionTask\Service;

class MainFunctions
{
    //array values:
    private $cash_in;
    private $cash_out_natural;
    private $cash_out_legal;
    private $currencies;

    private $fees;
    private $csv_file;

    public function __construct(string $file, array $cash_in = [],
      array $cash_out_natural = [], array $cash_out_legal = [],
      array $currencies = [])
    {
        $this->cash_in = $cash_in;
        $this->cash_out_natural = $cash_out_natural;
        $this->cash_out_legal = $cash_out_legal;
        $this->currencies = $currencies;

        $this->fees = new FeeCalc($cash_in, $cash_out_natural, $cash_out_legal, $currencies);
        $csv_file = $file;

        $array = $this->readFile($csv_file);
        if (!empty($array)) {
            //print_r($array);
            //echo "\n";
            $this->iterateArray($array);
        } else {
            echo "Wrong file format\n\n";
        }
    }

    public function readFile(string $csv_file): array
    {
        $csv = [];

        if (file_exists($csv_file)) {
            $lines = file($csv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            while (!is_numeric($lines[0][0])) { //for csv BOM format
                $lines[0] = substr($lines[0], 1);
            }

            foreach ($lines as $key => $value) {
                $csv[$key] = str_getcsv($value, ';');
            }
        }

        return $csv;
    }

    public function iterateArray(array $csv)
    {
        $fees = $this->fees;

        foreach ($csv as $key => $value) {
            if ($csv[$key][3] === 'cash_in') {
                $moneyAmount = $csv[$key][4];
                $currency = $csv[$key][5];
                $res = $fees->cashinCalcFee($moneyAmount, $currency);
                $precision = $fees->checkPrecision($currency);
                $resStr = number_format((float) $res, $precision, '.', '');
                echo $resStr."\n";
            } elseif ($csv[$key][3] === 'cash_out') {
                if ($csv[$key][2] === 'natural') {
                    $moneyAmount = $csv[$key][4];
                    $currency = $csv[$key][5];

                    $week_count = 0;
                    $week_amount = 0;

                    //week_count and week_amount passed by reference
                    $fees->calcWeekSpendings($csv, $key, $week_amount, $week_count);

                    $res = $fees->cashoutCalcNaturalFee($moneyAmount, $week_amount,
                $week_count, $currency);

                    $precision = $fees->checkPrecision($currency);
                    $resStr = number_format((float) $res, $precision, '.', '');
                    echo $resStr."\n";
                } elseif ($csv[$key][2] === 'legal') {
                    $moneyAmount = $csv[$key][4];
                    $currency = $csv[$key][5];
                    $res = $fees->cashoutCalcLegalFee($moneyAmount, $currency);
                    $precision = $fees->checkPrecision($currency);
                    $resStr = number_format((float) $res, $precision, '.', '');
                    echo $resStr."\n";
                }
            }
        }
    }
}
