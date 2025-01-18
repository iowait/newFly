<?
// General Constants
define('FEET_TO_METERS', 0.3048);
define('METERS_TO_FEET', 3.2808);
define('METERS_TO_NM', 0.000539957);

// We define a class to do IGC stuff, 
// UNITS ALL IN METERS unless VERY clearly specified other
class IGC {

    // needed for trig calcs on distance
    const RADIANS = 57.29577951;  // degrees/radian
    const RADIUS_EARTH = 6378000; 

    // thresholds for validity
    const LIMIT_ALTITUDE_AGL = 15; // crossed during takeoff/landing
    const LIMIT_RADIUS = 1000;     // takeoff/landing occurs within this distance
    const LIMIT_TIME_INTERVAL = 60;// need to see data at least every 60 seconds
    const LIMIT_DATA_LOSS = 120;   // we allow 120 seconds most between readings
    const LIMIT_CONSECUTIVE_ERRORS = 1;  // we get bad alts,distances, how many in a row is allowed
    const FLYING_SPEED = 20;       // 20 m/sec, 40 KTS -

    private $file;  // the igc file
    private $DEBUG = 0;
    private $valid = false; // gets set to true if parse succeeds

    // airport info
    private $airportLat = 0;
    private $airportLon = 0;
    private $airportElev = 0;

    // these times are all seconds (midnight is zero)
    private $takeoffTime = 0; 
    private $landingTime = 0; 
    private $fileStartTime = 0;
    private $fileEndTime = 0;

    // used for exception time stamping, in HH:MM:SS
    private $dataRecordTime = '';

    // misc
    private $takeoffTimeHMS = ''; // in HH:MM:SS local time zone
    private $maxAlt = 0;
    private $minAlt = 0;
    private $maxVelocity = 0; // meters/sec
    private $date = '';  // yyyy-mm-dd
    private $deviceType = '';  // what did the recording
    private $gpsType = '';     // what was used for gps
    private $satelliteChange = 0;   // count of F record types

    // max distance from airport, meters
    private $maxDist = 0;

    function __construct($igcFile, $airportLat, $airportLon, $airportElev, $debug = 0) { // just basic stuff, file there
        if (! file_exists($igcFile)) {
            throw new Exception("Sorry, could not find file: $igcFile");
        }
        
        $this->file = $igcFile;
        $this->airportLat = $airportLat;
        $this->airportLon = $airportLon;
        $this->airportElev = $this->minAlt = $this->maxAlt = $airportElev;
        $this->DEBUG = $debug;

        $this->parseIGC($this->file);

    } // end __construct

    // This function returns the distance in meters between
    // two decimal lat/lon coordinates
    // The type is used to define the expected distance,
    // it defaults to 'DATA', incremental change in B records
    // vs. 'ANY' - could be any two points
    function distance($timeDelta, $lat1, $lon1, $lat2, $lon2, $type='DATA', $line) {

        static $consecutiveErrors = 0;
        static $lastDist = 0;  // if we get an error, return the last

        if ($msg = self::validateLatLon($lat1, $lat2)) {
            return($msg);
        }

       // convert input to radians
        $lat1 /= self::RADIANS;
        $lon1 /= self::RADIANS;
        $lat2 /= self::RADIANS;
        $lon2 /= self::RADIANS;

        // Haversine Formula 
        // now some trig! https://www.geeksforgeeks.org/program-distance-two-points-earth/
        $dist = self::RADIUS_EARTH * acos( (sin($lat1) * sin($lat2)) + 
                                          cos($lat1) * cos($lat2) * cos($lon2 - $lon1) );
        
        // set a speed limit when comparing successive sample data
        if ($type == 'DATA') { // how far can we go in one interval, 116 Kts = 60 meters/sec
            $LIMIT =  71; //  71 m/sec = 136 knots, default, we allow for sample errors
            if ($timeDelta) { // we can calculate a speed
                $speed = $dist/$timeDelta;
            }
        } else {
            $LIMIT =  1000000; // a million meters, 1000KM, about 500 miles
            $speed = 0;
        }

        $dist = round($dist);
        $speed = round($speed);
        if ($dist >= 0 && $speed < $LIMIT) {
            $consecutiveErrors = 0; // reset
            $lastDist = $dist;
            return($dist);
        } else {
            if ($consecutiveErrors++ > self::LIMIT_CONSECUTIVE_ERRORS) {
                throw new Exception("$this->dataRecordTime, Line: $line -> Speed $speed in m/sec exceeded $LIMIT over distance $dist meters.");
            } else {
                return($lastDist); // the last valid
            }
        } // end if-else, valid distance

    } // end function distance

    // GET functions, units meters and seconds
    public function getDate() {
        if ($this->valid) {
            return($this->date);
        } else {
            return(0);
        }
    } // end getDate

    public function getDeviceType() {
        if ($this->valid) {
            return($this->deviceType);
        } else {
            return(0);
        }
    } // end getDate

    public function getGpsType() {
        if ($this->valid) {
            return($this->gpsType);
        } else {
            return(0);
        }
    } // end getDate

    public function getFltTime() {
        if ($this->valid) {
            $diff = $this->landingTime - $this->takeoffTime;
            
            if ($diff > 300) {
                return($diff);
            } else {
                return(0);
            }
        } else {
            return(0);
        }
    } // end getFltTime
 
    public function getMaxAlt() {
        if ($this->valid) {
            return($this->maxAlt);
        } else {
            return(0);
        }
    } // end getMaxAlt

    public function getMaxDist() {
        if ($this->valid) {
            return($this->maxDist);
        } else {
            return(0);
        }
    } // end getMaxDist

    public function getSatelliteChange() {
        if ($this->valid) {
            return($this->satelliteChange);
        } else {
            return(0);
        }
    } // end getDate

    public function getTakeoffTime() {
        if ($this->valid) {
            return($this->takeoffTime);
        } else {
            return(0);
        }
    } // end getTakeOffTime

    public function getTakeOffTimeHMS() {
        if ($this->valid) {
            return($this->takeoffTimeHMS);
        } else {
            return(0);
        }
    } // end getTakeOffTime

    public function getLandingTime() {
        if ($this->valid) {
            return($this->landingTime);
        } else {
            return(0);
        }
    } // end getLandingTime

    // the big function, read the IGC and pick it apart
    // Different Record type definitions we care about
    // B1101355206343N00006198WA0058700558  << - as it appears in file
    // B,110135,5206343N,00006198W,A,00587,00558
    // B: record type is a basic tracklog record
    //    110135: <time> tracklog entry was recorded at 11:01:35 i.e. just after 11am
    //    5206343N: <lat> i.e. 52 degrees 06.343 minutes North
    //    00006198W: <long> i.e. 000 degrees 06.198 minutes West
    //    A: <alt valid flag> confirming this record has a valid altitude value
    //    00587: <altitude from pressure sensor>
    //    00558: <altitude from GPS> 
    // We look for the next three: date, type of device, and gps in use.
    // HFDTE140620:  the date of the track
    // HFFTYFRTYPE:XCSOAR,XCSOAR Android 6.8.15 Jun 13 2020
    // HFGPS:Internal GPS (Android)

    function parseIGC() {
        $fh = fopen($this->file, 'r');
        $count = 0;
        $all = array();
        $recType = '';
        $lastTimeSecs = $lastLon = $lastLat = 0;  // before/after comparisons 

        if (0) { // if you need to confirm distance calc B1200004206670N07655402W
            $lat1 = 53.320555; // 53 & 19.230' N  B1200005319230N00143780WA003730052300000
            $lon1 = -1.729722; //  1 & 43.780' W  
            // 1 minute later, no change in altitude
            $lat2 = 53.318611; // 53 & 19.110' N  B1201005319110N00141980WA03730052300000
            $lon2 = -1.699722; //  1 & 41.980' W  
            $dist = $this->distance($timeSecs, $lat1, $lon1,$lat2, $lon2);
            
            echo "Distance is $dist, should be 2004.3678382716137\n";
            exit;
        }

        while ($line = fgets($fh)) { // we have input
            $count++;
            $count = sprintf('%03d', $count);
            // set the record type
            preg_match('/(.{1}).*/', $line, $all);
            $recType = $all[1];
            if ($this->DEBUG) echo "$count:recType($recType):  ";

            switch ($recType) {

                case 'B': 
                    // FIRST - get the values converted to our units
                    // function to do this ZZ, string of sizes, return array of values
                    $regex = "/(.{1})(.{6})(.{8})(.{9})(.{1})(.{5})(.{5})/";
                    preg_match($regex, $line, $all);
                    $time = $all[2];
                    $timeSecs = $this->toSeconds($time); // seconds since midnight
                    $valid  = $all[5];
                    $altPrs = $all[6]; // IGNORED
                    $alt  = $all[7]; // this is the one we use
                    $lat  = $all[3]; // YIKES, degrees with decimal minutes, DDMM.ddd[N|S]
                    $lon  = $all[4]; // DDDMM.ddd[E|W]

                    if ($valid != 'A') { // sanity check
                        echo "CHANGE - valid($valid)";
                    }

                    $timeSecs = $this->toSeconds($time);
                    $timeFormatted = $this->toHMS($time);
                    $this->dataRecordTime = $timeFormatted;

                    // we want decimal degrees, no minutes
                    // first latitude
                    $regex = "/(.{2})(.{2})(.{3})(.{1})/";
                    preg_match($regex, $lat, $allLat);
                    $latDeg = $allLat[1];
                    $latMin = $allLat[2];
                    $latDec = $allLat[3];
                    $nors   = $allLat[4];
                    // echo "latDeg($latDeg) latMin($latMin) latDec($latDec)  ";
                    if ($nors == 'N') {
                        $corr = 1;
                    } else if ($nors == 'S') {
                        $corr = -1;;
                    } else {
                        throw new Exception("$this->dataRecordTime,  Line: $count -> Bad latitude, got $lat");
                    }
                    $lat = ($latDeg + ($latMin + $latDec/1000.0)/60) * $corr;
                    $lat = sprintf("%3.6f", $lat);

                    // then longitude
                    $regex = "/(.{3})(.{2})(.{3})(.{1})/";
                    preg_match($regex, $lon, $allLon);
                    $lonDeg = $allLon[1];
                    $lonMin = $allLon[2];
                    $lonDec = $allLon[3];
                    $nors   = $allLon[4];
                    // echo "lonDeg($lonDeg) lonMin($lonMin) lonDec($lonDec)  ";
                    if ($nors == 'E') {
                       $corr = 1;
                    } else if ($nors == 'W') {
                        $corr = -1;
                    } else {
                        throw new Exception("$this->dataRecordTime, Line: $count -> Bad longitude, got $lon");
                    }
                    $lon = ($lonDeg + ($lonMin + $lonDec/1000)/60) * $corr;
                    $lon = sprintf("%3.6f", $lon);

                    // SECONDS - all the data converted, first data?
                    if (!$lastTimeSecs) { // set all our last variables
                        $lastTimeSecs = $timeSecs;
                        $lastLat = $lat;
                        $lastLon = $lon;
                        $lastAlt = $alt;
                        $this->fileStartTime = $timeSecs;   // first data
                        break; // READ THE NEXT RECORD
                    }
                    // difference in time between samples
                    $timeDelta = $timeSecs - $lastTimeSecs;

                    // GET HERE - we can calculate deltas based on the last record
                    // store new last values
                    $distance = $this->distance($timeDelta, $lastLat, $lastLon, $lat, $lon, 'DATA', $count);
                    $lastLon = $lon;
                    $lastLat = $lat;

                   // track our distance from the airport
                    $airportDist = $this->distance($timeDelta, $lat, $lon, 
                                                  $this->airportLat, $this->airportLon, 'ANY', $count);
                    if ($airportDist > $this->maxDist) {
                        $this->maxDist = $airportDist;
                    }

                    // track our altitude
                    if ($alt > $this->maxAlt) {
                        $this->maxAlt = $alt;
                    } else if ($alt < $this->minAlt) {
                        $this->minAlt = $alt;
                    }

                    $velocity = $distance/($timeSecs - $lastTimeSecs); // m/sec
                    
                    if ($velocity > $this->maxVelocity) {
                        $this->maxVelocity = $velocity;
                    }
                    $lastTimeSecs = $timeSecs;
                    $this->fileEndTime = $timeSecs; // last data we saw

                    if ($this->DEBUG) echo "$timeFormatted, $lat, $lon,  $alt -> $airportDist/$distance/$velocity\n";

                    // still valid?
                    $this->trackValid($timeSecs, $alt, $distance, $airportDist);
                    break;

                case 'F':  // satellite count changes
                    $this->satelliteChange++;
                    break;

                case 'H':  // we look for three subtypes

                    if (strstr($line, 'HFDTE')) {
                        $date = substr($line, 5, 6);
                        $this->date = $this->toMySqlDate($date);
                        if ($this->DEBUG) echo "Date of: $this->date\n";
                    } else if (strstr($line , 'HFFTYFRTYPE:')) {
                        $this->deviceType = substr($line, 12, 50);
                        if ($this->DEBUG) echo "Device type: $this->deviceType";
                    } else if (strstr($line, 'HFGPS:')) {
                        $this->gpsType = substr($line, 6, 50);
                        if ($this->DEBUG) echo "GPS type: $this->gpsType";
                    } 
                    
                    break;

                case 'G': // ignore
                case 'I':
                case 'A':
                    break;



                default:
                    echo "  $line";
                    break;

            } // end switch
                    
                        
        } // end while

        if ($this->takeoffTime && $this->landingTime && $this->date && 
            $this->deviceType && $this->gpsType) {
            $this->valid = true; // looks good!
        }

        if ($this->DEBUG) echo "Max Distance from Airport: $this->maxDist meters, Max/Min Altitude(MSL): $this->maxAlt/$this->minAlt meters\n";

    } // end function parseIGC

    // this function takes an IGC time HHMMSS, and converts to HH:MM:SS
    // correct for time zone
    function toHMS($igcTime) {
        if (strlen($igcTime) != 6) { // something really wrong
            throw new Exception("Bad time record length: $igcTime");
        }
        $hour = substr($igcTime, 0, 2);
        $min  = substr($igcTime, 2, 2);
        $sec  = substr($igcTime, 4, 2);

        // time zone
        $hour += (HFL_TIMEZONE/60);

        // reasonable time, later than 4 in the morning or 8 at night
        if (empty($hour) || $min > 60 || $sec > 60 || $hour < 4 || $hour > 20) {
            throw new Exception("$this->dataRecordTime, Bad time record time: $igcTime");
        }

        return("$hour:$min:$sec");
    } // end function toHMS
        
    // this function takes DDMMYY and convert to MySQL date YYYY-MM-DD
    function toMySqlDate($igcDate) {
        if (strlen($igcDate) != 6 || preg_match('/[A-Za-z]/', $igcDate)) {
            throw new Exception("$this->dataRecordTime, Bad IGC date record, got: $igcDate");
        }
        
        $year = substr($igcDate, 4, 2);
        $month = substr($igcDate, 2, 2);
        $day  = substr($igcDate, 0, 2);
        
        return("20$year-$month-$day");
    } // end function toMySqlDate

    // this function just takes HH:MM:SS and returns seconds
    function toSeconds($igcTime) {
        $seconds = 0;

        $hour = substr($igcTime, 0, 2);
        $min  = substr($igcTime, 2, 2);
        $sec  = substr($igcTime, 4, 2);

        $seconds = $hour*3600 + $min*60 + $sec;
        
        return($seconds);
    } // end function toSeconds

    // does the track still look valid, compares last with current
    // values, checks limits
    // return 0 if OK, message on error, records warnings
    private function trackValid($time, $alt, $dist, $airportDist) {

        static $lastTime = 0;
        static $lastAlt = 0;
        static $lastDist = 0;
        static $lastAirportDist = 0;

        if (!$lastTime) { // first time, initialize
            $lastTime = $time;
            $lastAlt = $alt;
            $lastDist = $dist;
            $lastAirportDist = $airportDist;
            return(0);
        }

        $deltaTime = $time - $lastTime;
        $velocity  = $dist / $deltaTime;

        if ($deltaTime > self::LIMIT_DATA_LOSS) { // too much time from last data point
            throw new Exception("$this->dataRecordTime, MISSING DATA, time interval between records of $deltaTime seconds too large");
        }

        // FIRST, need a takeoff close to the airport
        if (!$this->takeoffTime) {
            if ($velocity > self::FLYING_SPEED && 
                $alt > self::LIMIT_ALTITUDE_AGL + $this->airportElev) { // in the air!
                
                if ($airportDist < self::LIMIT_RADIUS) { // near the airport
                    $this->takeoffTime = $time;
                    $secsLeft = $time/
                    $hours = round($time/3600);
                    $minutes = round($time%3600 / 60);
                    $this->takeoffTimeHMS = "$hours:$minutes";
                    if ($this->DEBUG) echo "TAKEOFF\n";
                } else { // trouble
                    throw new Exception("$this->dataRecordTime, TAKEOFF at $time seconds, but too far from airport " . AIRPORT_NAME . "($airportDist feet)");
                }
            } // end if - flying

        } else if ($time - $this->takeoffTime < 60) { // we allow 60 seconds to stabilize flight
            // nothing to do

        } else if (!$this->landingTime && $this->takeoffTime) { // are we down?
            if ($velocity < self::FLYING_SPEED &&
                $alt < self::LIMIT_ALTITUDE_AGL + $this->airportElev) { // on the ground
                
                if ($airportDist < self::LIMIT_RADIUS) { // near airport
                    $this->landingTime = $time;
                    if ($this->DEBUG) echo "LANDING\n";
                } else { // trouble
                    throw new Exception("$this->dataRecordTime, LANDING at $time, but too far from airport " . AIRPORT_NAME . "($airportDist)");
                }
            } // end if - flying

        } 

        $lastTime = $time;
        $lastAlt  = $alt;
        $lastDist = $dist;
        $lastAirportDist = $airportDist;
        return(0);
    } // end function validateTrack

    // this function validates lat/lon looks reasonable,
    // returns zero on success, message otherwise
    public static function validateLatLon($lat, $lon) {

        // formatting of lat/lon - we expect DD.decimal
        if (preg_match('/["\' ]/', $lat) || preg_match('/["\' ]/', $lat) ) {
            return("Invalid chars in lat($lat) and/or lon($lon)");
        }
        
        // range of lat/lon
        if (abs($lat) >90 || abs($lon) > 180) {
            return("Invalid value for lat($lat) and/or $lon($lon)");
        }
    } // end function validateLatLon


} # end IGC class definition


// This is the function used to perform the hi-far-long calculations, 
// ALL internal calculations are done in METERS (IGC defaults), unit conversion
//  occurs just for the input/return values if necessary.
// INPUTS:  airportLat/Lon(Deg.decimal), elev - elevation(feet), the igcFile
//          units default HFL_UNITS (US or METRIC)
// RETURNS:  high(feet or meters), far(miles.decimal, meters.decimal), long(minutes.decimal)
//           date, start/end time(local airport time)
// RETURN VALUE: zero if no error, message otherwise.

function getHighFarLong($airportLat, $airportLon, $airportElev, $igcFile, 
                       &$high, &$far, &$long, &$date, &$startTime, &$endTime, 
                       &$takeoffHMS, $units = HFL_UNITS, $debugOn = 0) {

    
    if ($units == 'US') { // all internal calculations are done in meters
        $airportElev = $airportElev * FEET_TO_METERS;
    }

    // formatting of lat/lon and range of values
    if ($msg = IGC::validateLatLon($airportLat, $airportLon)) {
        return($msg);
    }
    
    // field elevation looks correct
    if ($airportElev < 10 || $airportElev > 3000) {
        return("Airport elevation our of range ($elev) meters");
    }
    
    // file exists
    if (! file_exists($igcFile)) {
        return("Could not find IGC file: $igcFile");
    }   
    
    // parse the file, this will raise an exception if not valid
    $igc = new IGC($igcFile, $airportLat, $airportLon, $airportElev, $debugOn);

    // internal calcs done in meters, do we need to convert
    $conversionHigh = 1;
    $conversionFar  = .001;
    $labelHigh = 'meters';
    $labelFar  = 'km';
    if ($units == 'US') { // convert
        $conversionHigh = METERS_TO_FEET;
        $conversionFar = METERS_TO_NM;
        $labelHigh = 'feet';
        $labelFar  = 'nm';
    }
 
    $date = $igc->getDate();
    $high = round($igc->getMaxAlt() * $conversionHigh) . " $labelHigh";
    $far  = round($igc->getMaxDist() * $conversionFar, 2) . " $labelFar";
    $long = round($igc->getFltTime()/60, 1) . " minutes";
    $startTime = $igc->getTakeOffTime();
    $endTime  = $igc->getLandingTime();
    $takeoffHMS = $igc->getTakeOffTimeHMS() . " GMT";
    


} # end getHighFarLong


?>