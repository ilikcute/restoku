<?php

use Illuminate\Contracts\Console\Kernel;
use PhpOffice\PhpSpreadsheet\IOFactory;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    $filePath = 'Data Penjualan Mei 2026..xlsx';
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName('Input');

    // Dump row 5 columns W to CA
    $cols = [];
    foreach (range('W', 'Z') as $c) {
        $cols[] = $c;
    }
    foreach (range('A', 'Z') as $c1) {
        foreach (range('A', 'Z') as $c2) {
            $cols[] = $c1.$c2;
        }
    }

    $row5 = [];
    foreach ($cols as $col) {
        if ($col >= 'W' && strlen($col) == 1) {
            $row5[$col] = $sheet->getCell($col.'5')->getFormattedValue();
        } elseif (strlen($col) == 2 && $col <= 'BZ') {
            $row5[$col] = $sheet->getCell($col.'5')->getFormattedValue();
        }
    }

    print_r($row5);

} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n".$e->getTraceAsString();
}
