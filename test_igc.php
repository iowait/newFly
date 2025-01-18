<?

require('IGC.php');
require('mode.inc');

$igcFile = $argv[1];

try {
    $msg = getHighFarLong(AIRPORT_LAT, AIRPORT_LON, AIRPORT_ELEV, $igcFile, $high, $far, $long, 
                   $date, $startTime, $endTime, $takeoffHMS, 'US', 1);
} catch (Exception $e) {
    $msg = 'Caught exception: '. $e->getMessage(). "\n";
}

if (!$msg) {

    print("\nFile: $igcFile,  date: $date,  start/stop: $startTime, $endTime\n");
    print("   High: $high, Far: $far,  Long: $long\n");
    print("   Takeoff: $takeoffHMS\n");
    print("SUCCESS\n");
} else {
    print("ERROR: $msg\n");
}
?>