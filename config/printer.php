<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Printer Connection Settings
    |--------------------------------------------------------------------------
    |
    | Supported connection types: "windows", "network", "file"
    |
    | For "windows": address is the printer name (e.g. "POS-80")
    | For "network": address is the IP (e.g. "192.168.1.100")
    | For "file": address is the local path (e.g. "LPT1" or "/dev/usb/lp0")
    |
    */

    'connection_type' => env('PRINTER_CONNECTION_TYPE', 'windows'),

    'address' => env('PRINTER_ADDRESS', 'POS-80'),

    'port' => env('PRINTER_PORT', 9100),

    'logo_path' => env('PRINTER_LOGO_PATH', public_path('logo-black.png')),
];
