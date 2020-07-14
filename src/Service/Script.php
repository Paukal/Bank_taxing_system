<?php

declare(strict_types=1);

namespace Banking\CommissionTask\Service;

include 'MyAutoloader.php';

//array values:
$cash_in = ['fee_percent' => 0.0003, 'fee_limit' => 5];

$cash_out_natural = ['fee_percent' => 0.003, 'free_of_charge_sum' => 1000,
'free_of_charge_times' => 3, ];

$cash_out_legal = ['fee_percent' => 0.003, 'fee_minimum' => 0.5];

$currencies = [
//EUR:USD - 1:1.1497, EUR:JPY - 1:129.53
'eur_usd' => 1.1497, 'eur_jpy' => 129.53, ];

$csv_file = $argv[1];

new MainFunctions($csv_file, $cash_in, $cash_out_natural, $cash_out_legal,
    $currencies);
