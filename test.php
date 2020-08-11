<?php
// Initialise the autoloader
require('./vendor/autoload.php');

use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Ramsey\Uuid\UuidFactory;

$factory = new UuidFactory();
$codec = new OrderedTimeCodec($factory->getUuidBuilder());

$factory->setCodec($codec);

$orderedTimeUuid = $factory->uuid1();

printf(
    "UUID: %s\nVersion: %d\nDate: %s\nNode: %s\nBytes: %s\n",
    $orderedTimeUuid->toString(),
    $orderedTimeUuid->getFields()->getVersion(),
    $orderedTimeUuid->getDateTime()->format('r'),
    $orderedTimeUuid->getFields()->getNode()->toString(),
    bin2hex($orderedTimeUuid->getBytes())
);