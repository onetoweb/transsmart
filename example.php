<?php

require 'vendor/autoload.php';

use Onetoweb\Transsmart\Client;

$client = new Client('email@example.com', 'password', 'account', true);

// book shipment
$response = $client->bookShipment([[
    'reference' => 'Shipment_reference',
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

// get documents from shipment
foreach ($response as $shipment) {
    
    foreach ($shipment['packageDocs'] as $packageDoc) {
        
        // write files to disk 
        file_put_contents($shipment['reference'].'.'.$packageDoc['fileFormat'], base64_decode($packageDoc['data']));
        
    }
}

// retrieve single shipment
$client->retrieveShipment('Shipment_reference');

// retrieve multiple shipments
$client->retrieveShipments();

// delete shipment
$client->deleteShipment('Shipment_reference');

// get shipment manifest list
$client->getShipmentManifestList([
    'date' => '2019-08-15',
    'carrier' => 'DPD'
]);

// manifest shipments
$client->manifestShipments([
    'from' => '2019-08-15',
    'to' => '2019-08-15',
    'carrier' => 'DPD'
]);

// calculate rates
$client->calculateRates([[
    'reference' => 'Shipment_reference',
    'carrier' => 'DPD',
    'serviceLevelTime' => 'CLASSIC',
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
]]);

// print document
$client->printDocument('Shipment_reference');

// get shipment status
$client->getShipmentStatus('Shipment_reference');

// get shipments statuses
$client->getShipmentsStatuses([
    'dateFrom' => '2019-08-15 00:00:00',
    'dateTo' => '2019-08-15 23:59:59'
]);

// get address
$client->getAddress('Address_id');

// get addresses
$client->getAddresses();

// create address
$client->createAddress([[
    'name' => 'Onetoweb',
    'addressLine1' => 'Oudestraat',
    'houseNumber' => '216 K',
    'zipCode' => '8261 CA',
    'city' => 'Kampen',
    'country' => 'NL',
    'type' => 'SEND',
    'contact' => [
        'name' => 'contact',
        'phone' => '038-7110308',
        'email' => 'info@onetoweb.com',
    ]
]]);

// update address
$client->updateAddress('Address_id', [
    'id' => 'Address_id',
    'name' => 'updated name',
    'contact' => [
        'name' => 'contact',
    ]
]);

// delete address
$client->deleteAddress('Address_id');

// get carriers
$client->getCarriers();

// get carrier
$client->getCarrier('Carrier_nr');

// get costcenters
$client->getCostcenters();

// get costcenter
$client->getCostcenter('Costcenter_nr');

// get incoterms
$client->getIncoterms();

// get incoterm
$client->getIncoterm('Incoterm_nr');

// get mail types
$client->getMailTypes();

// get mail type
$client->getMailType('MailType_nr');

// get package definitions
$client->getPackageDefinitions();

// get package definition
$client->getPackageDefinition('PackageDefinition_nr');

// get service level times
$client->getServiceLevelTimes();

// get service level 
$client->getServiceLevelTime('ServiceLevelTime_nr');

// get service level others
$client->getServiceLevelOthers();

// get service level other
$client->getServiceLevelOther('ServiceLevelOther_nr');

// get booking profiles
$client->getBookingProfiles();

// get booking profile
$client->getBookingProfile('BookingProfile_nr');

// get pickup locations
$client->getPickupLocations([
    'zipCode' => '8261AL',
    'countryTo' => 'NL',
    'provider' => 'DPD'
]);