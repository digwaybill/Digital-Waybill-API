<?php
require_once( 'digital_waybill.inc.php' );

$order = new DigitalWaybill\Order(true);
$ps = new DigitalWaybill\PickupStop;
$ds = new DigitalWaybill\DeliverStop;

$order->customerNumber = 'DYN833';
$order->costCenter = 'AJ Omnicare';
$order->orderType = 'Pickup';
$order->readyTime = time() + (60*30); // 30 min from now
$order->roundTrip = false;

$ps->company = 'my company pickup';
$ps->address = '1639 11th Street';
$ps->suite = '210';
$ps->city = 'Santa Monica';
$ps->state = 'CA';
$ps->postal_code = '90049';
$ps->country = 'United States';
$ps->contactName = 'Bradley Snyder';
$ps->contactPhone = '8005753510';

$ds->company = 'L 4 Rodeo Drive LLC';
$ds->address = '268 North Rodeo Drive';
$ds->suite = '';
$ds->city = 'Beverly Hills';
$ds->state = 'CA';
$ds->postal_code = '90210';
$ds->country = 'United States';
$ds->contactName = 'N/A';
$ds->contactPhone = 'N/A';

$ds->serviceType = 'Emergency';
$ds->package = 'Medicine';
$ds->vehicle = 'v2';
$ds->weight = 100;
$ds->numberOfPieces = 200;
$ds->specialInstructions = 'special instructions';
$ds->notes = 'my notes';

try {
    $order->setPickup($ps);
    $order->setDeliver($ds);

    $cid = 2000105850;
    $key = 'f1d621905cece65bcbbb5018adacdd39adacdd39';
    $cnum = 'DYN833';
    $pass = 'pass';

    $auth = new DigitalWaybill\Auth( DigitalWaybill\Auth::AUTH_TYPE_QUICKENTRY, $cid, $key, $cnum, $pass );

    $orderNumber = $order->send($auth);
    printf( "Successfully placed order #%d\n", $orderNumber );
} catch( Exception $e )
{
    printf( "An exception occurred: %s\n", $e->getMessage() );
}

?>
