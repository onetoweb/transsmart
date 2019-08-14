<?php

require 'vendor/autoload.php';

use Onetoweb\Transsmart\Client;

$client = new Client('email@example.com', 'password', 'account', true);

// example booking shipment
$response = $client->bookShipment([[
    'reference' => 'Shipment_'.time(),
    'carrier' => 'DPD',
    'serviceLevelTime' => 'CLASSIC',
    'value' => 10,
    'valueCurrency' => 'EUR',
    'packages' => [[
        'lineNo' => 1,
        'packageType' => 'BOX',
        'quantity' => 1,
        'measurements' => [
            'length' => 20,
            'width' => 20,
            'height' => 20,
            'weight' => 20
        ]
    ]],
    'addresses' => [[
        'type' => 'RECV',
        'name' => 'Onetoweb B.V.',
        'addressLine1' => 'Oudestraat',
        'houseNo' => '216',
        'city' => 'Kampen',
        'zipCode' => '8261 CA',
        'country' => 'NL',
    ], [
        'type' => 'SEND',
        'name' => 'Onetoweb B.V.',
        'addressLine1' => 'Oudestraat',
        'houseNo' => '216',
        'city' => 'Kampen',
        'zipCode' => '8261 CA',
        'country' => 'NL',
    ]]
]], 'PRINT');


foreach($response as $shipment) {
    
    foreach($shipment['packageDocs'] as $packageDoc) {
        
        // write files to disk 
        file_put_contents($shipment['reference'].'.'.$packageDoc['fileFormat'], base64_decode($packageDoc['data']));
        
    }
}
