<!doctype html>
<html>
<head>
	<title>Inscription</title>
</head>
<body>
<?php

use Entities\Application, 
    Entities\Developer,
    Entities\Device,
    Entities\Invitation,
    Entities\Tester,
    Entities\Version;

require_once __DIR__ . '/../core/index.php';

$entityManager = initDoctrine();
echo '<p>Entity manager is ready</p>' . PHP_EOL;

## PUT YOUR TEST CODE BELOW

// Retrieve all testers
$testers = $entityManager->getRepository('Entities\Tester')->findAll();

echo '<ul>';
foreach ($testers AS $tester) {
    echo '<li>Tester: ' . $tester->getName() . '</br > Devices: ' . PHP_EOL;
    	
    	$devices = $tester->getDevices();
    	
    	// If the tester have no devices
    	if ($devices->isEmpty()) {
    		echo 'No devices registered yet.' . PHP_EOL;
    		
    	// Display all devices from the tester
    	} else {
    		echo '<ul>' . PHP_EOL;
    		foreach ($tester->getDevices() AS $device) {
	        	echo '<li>Device: ' . $device->getModel() . ', UDID: ' . $device->getUdid() . '</li>' . PHP_EOL;
	        }
	        echo '</ul>' . PHP_EOL;
        }
    echo '</li>' . PHP_EOL;
}
echo '</ul>' . PHP_EOL;

?>
</body>
</html>