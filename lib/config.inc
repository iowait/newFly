<?  /* Copyright (C) 1995-2020  John Murtari
This file is part of FLY flight management software
FLY is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FLY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FLY.  If not, see <https://www.gnu.org/licenses/>. */ ?>
<?

include_once("mode.inc");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);


// Language
define ("TRANSLATE", false);
define ("DEFAULT_LANGUAGE", "en_US");

// Globals
define ("PHOTO_DIR", "/" .FLY_DIR. "/photos");
define ("MINUTES_DIR", "/MembersOnly");

// You MUST CHANGE THE FOLLOWING
// define ("ROOT_DIR", "/pub/comwww/soaringtools");  // absolute path to top of web tree for main web site

// Date limits for ACK duty searches, used with < for 1 week coverage
define ("ACK_DAY_LIMIT", 8);  

date_default_timezone_set("America/New_York");

# MonthNames and YearName used for input checking
$MonthNames = array('January', 'February', 'March', 'April', 'May', 'June',
	            'July', 'August', 'September', 'October', 'November', 'December');
$YearNames = array('2017', '2018', '2019', '2020', '2021');

#
# BELOW - try to keep in alphabetical order
$AccessTypes = Array('ADMIN','CFIG','BOARD','MEMBER','NONE','SCHEDULES','TOW');

$BadgeList = Array('A', 'B', 'C', 'Bronze', 'Silver', 'Silver Altitude', 'Silver Distance',
                   'Silver Duration',
                   'Gold', 'Gold Altitude', 'Gold Distance', 'Diamond', 'Diamond Altitude',
                   'Diamond Distance',
                   'Diamond Goal', '750K Diploma', '1000K Diploma', '2000K Diploma', 'None');

// $CheckoutTypes - defined in lib/mode.inc

// Coordinators
// and Duty associated with these begins with this string, then _
$CoordTypes = Array('BOARD','CASH', 'CFIG', 'COM', 'JUNIOR', 'LOG', 'SCHED', 'TOW', 'WINGRUNNER');

// $DutyContacts - define in lib/mode.inc

# Info on gliders
# Items which appear for each aircraft, 'Yes' indicates protected for login only

$GliderLayout = Array(
    'Aircraft Type' => array('dbCol' => 'type', 'dbType' => 'enum', 'select' => 'GliderTypes', 'access' => 'SUPER',
                             'width' => 15, 'newRow' => 1, 'req' => 1),
    'Manufacturer'  => array('dbCol' => 'mfg', 'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                             'width' => 15, 'newRow' => 0, 'req' => 1),
    'Model'         => array('dbCol' => 'model', 'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                             'width' => 15, 'newRow' => 0, 'req' => 1),
    'Description'   => array('dbCol' => 'descrip', 'dbType' => 'textarea', 'select' => '', 'access' => 'SUPER',
                             'width' => 70, 'newRow' => 1, 'req' => 1),
    'POH URL'       => array('dbCol' => 'pohURL',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                             'width' => 15, 'newRow' => 1, 'req' => 0),
    'Velocity never exceed (vne)' => array('dbCol' => 'vne',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                                           'width' => 15, 'newRow' => 1, 'req' => 0),
    'Velocity rough air (vra)' => array('dbCol' => 'vrough',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                                        'width' => 15, 'newRow' => 0, 'req' => 0),
    'Best glide' => array('dbCol' => 'vglide',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                          'width' => 15, 'newRow' => 1, 'req' => 0),
    'Min sink'   => array('dbCol' => 'vsink',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                          'width' => 15, 'newRow' => 0, 'req' => 0),
    'Stall'      => array('dbCol' => 'vstall',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                          'width' => 15, 'newRow' => 1, 'req' => 0),
    'Glide ratio' => array('dbCol' => 'glideRatio',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                           'width' => 15, 'newRow' => 0, 'req' => 0),
    'Max Gs'     => array('dbCol' => 'maxG',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                          'width' => 15, 'newRow' => 0, 'req' => 0),
    'Seats'      => array('dbCol' => 'seats',  'dbType' => 'enum', 'select' => 'SeatTypes', 'access' => 'SUPER',
                          'width' => 1, 'newRow' => 1, 'req' => 1)
);

$GliderStatusLayout = array(

    'Tail #'   => array('dbCol' => 'tailNum',  'dbType' => 'text', 'select' => '', 'access' => 'SUPER',
                      'width' => 15, 'newRow' => 1, 'req' => 1),
    'Status'   => array('dbCol' => 'status',  'dbType' => 'enum', 'select' => 'GliderStatus', 'access' => 'SUPER',
                      'width' => 15, 'newRow' => 1, 'req' => 1,
	              'help' => 'GREEN - no noteworthy issues,&#10ORANGE - minor issue with aircraft should be brought to attention of pilots, still airworthy,&#10RED - DO NOT FLY, significant issue effects airworthiness'),
    'MX Notes' => array('dbCol' => 'mxNotes',  'dbType' => 'textarea', 'select' => '', 'access' => 'SUPER',
                        'width' => 70, 'newRow' => 1, 'req' => 0),
    'Annual Expire'       => array('dbCol' => 'annualExpire',  'dbType' => 'date', 'select' => '', 'access' => 'SUPER',
                                   'width' => 15, 'newRow' => 1, 'req' => 1),
    'Registration Expire' => array('dbCol' => 'regExpire',  'dbType' => 'date', 'select' => '', 'access' => 'SUPER',
                                   'width' => 15, 'newRow' => 1, 'req' => 1)
);

$GliderTypes = Array('Glider', 'Tow', 'Other');  # these two must agree with database and edit_glider_content.php

$GliderStatus = Array('GREEN', 'ORANGE', 'RED', 'none');

# The following structure defines the layout of data displayed on the edit member page
# The access permission and notes fields always appear at the end, not defined here
# dbCol - the field name (Members table) where stored
# dbType -  text (char), enum, set, id 
#           enum or set use 'select' for variable containing allowed values
#           id - indicates memberID is used
# Important to keep the first four items in order, used for flat search display
$MemberLayout = array (
    'First Name' => array('dbCol' => 'firstName',  'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 10, 'newRow' => 1, 'req' => 1),
    'MI'         => array('dbCol' => 'middleName', 'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 2, 'newRow' => 0, 'req' => 0),
    'Last Name'  => array('dbCol' => 'lastName',   'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                     'width' => 15, 'newRow' => 0, 'req' => 1),
    'Suffix'         => array('dbCol' => 'suffix', 'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 4, 'newRow' => 0, 'req' => 0),
    'Street Address'  => array('dbCol' => 'street','dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                               'width' => 20, 'newRow' => 1, 'req' => 0),
    'City'       => array('dbCol' => 'city',  'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 15, 'newRow' => 1, 'req' => 1),
    'State/Prov' => array('dbCol' => 'state', 'dbType' => 'enum', 'select' => 'StateList', 'access' => 'MEMBER',
                          'width' => 10, 'newRow' => 1, 'req' => 1),
    'Postal Code'=> array('dbCol' => 'zip',   'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 10, 'newRow' => 0, 'req' => 1),
    'Phone'      => array('dbCol' => 'phone', 'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 10, 'newRow' => 1, 'req' => 1,
	                  'help' => 'Preferred phone number you want other members to use for contacting you'), 
    'Alternate'  => array('dbCol' => 'phone2','dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 10, 'newRow' => 0, 'req' => 0,
	                  'help' => 'Alternate phone number (if any) you want other members to use for contacting you'), 
    'E-Mail Addr'=> array('dbCol' => 'emailAddr',  'dbType' => 'text', 'select' => '', 'access' => 'MEMBER',
                          'width' => 40, 'newRow' => 1, 'req' => 1),
    'Membership Type' => array('dbCol' => 'memberType', 'dbType' => 'enum', 'select' => 'MemberTypes', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 1, 'req' => 1,
	                       'help' => 'Display the memberships types in local use, in addition:&#10Staff - is used for Club staff with no other membership.&#10Other - is used as a temporary placeholder for a new membership type.&#10Inactive - is used to mark users pending complete deletion from the Club or to simply move a user to the Inactive list.'), 
    'Glider Rating'   => array('dbCol' => 'pilot', 'dbType' => 'enum', 'select' => 'PilotTypes', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 0, 'req' => 1,
                               'help' => 'Use "Not Applicable" for members who are not a pilot or in training.'),
    'Other Ratings'   => array('dbCol' => 'otherPilot', 'dbType' => 'set', 'select' => 'OtherPilotTypes', 'access' => 'MEMBER',
                               'width' => 10, 'newRow' => 1, 'req' => 0,
	                  'help' => 'Excluding glider, what other pilot certificates do you hold.'), 
    'Mentor'     => array('dbCol' => 'mentorID',   'dbType' => 'id', 'select' => 'id', 'access' => 'SUPER',
                          'width' => 10, 'newRow' => 1, 'req' => 0,
	                  'help' => 'What CFIG is acting as your mentor at the Club.'), 
    'Member Since'   => array('dbCol' => 'memberSince', 'dbType' => 'enum', 'select' => 'MemberSinceTypes', 'access' => 'MEMBER',
                               'width' => 4, 'newRow' => 0, 'req' => 0,
	                       'help' => 'When did you first become a Club Member.'),
    'Checked Out'=> array('dbCol' => 'checkout',   'dbType' => 'set', 'select' => 'CheckoutTypes', 'access' => 'SUPER',
                          'width' => 10, 'newRow' => 1, 'req' => 0,
	                  'help' => 'What aircraft or club duties have you been approved for locally.'), 
    'Badges Earned'   => array('dbCol' => 'badge', 'dbType' => 'set', 'select' => 'BadgeList', 'access' => 'MEMBER',
                               'width' => 10, 'newRow' => 1, 'req' => 0),
    'Coordinator'     => array('dbCol' => 'coord', 'dbType' => 'set', 'select' => 'CoordTypes', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 1, 'req' => 0,
	                       'help' => 'Additional lead roles at the Club, used by this system.'), 
    'Tow Medical'     => array('dbCol' => 'towMed', 'dbType' => 'date', 'select' => '', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 1, 'req' => 0,
	                       'help' => 'Limited use just for Tow pilot tracking.'), 
    'Biennial Flt Review' => array('dbCol' => 'bfr', 'dbType' => 'date', 'select' => '', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 0, 'req' => 0,
	                       'help' => "Helps track a licensed glider pilots Biennial Flight Review date."), 
    'Access' => array('dbCol' => 'access', 'dbType' => 'set', 'select' => 'AccessTypes', 'access' => 'SUPER',
                               'width' => 10, 'newRow' => 1, 'req' => 1, 'default' => 'MEMBER',
	                       'help' => 'Controls what screens you see and what fields you can edit for yourself or of other club members.&#10ADMIN - complete access, unsafe operations&#10CFIG,BOARD,TOW,SCHEDULES - allowed to edit aircraft status, member info (a superuser).&#10MEMBER - normal membership access, change your personal info, photos, About me line'), 
    'Notes' => array('dbCol' => 'notes', 'dbType' => 'textarea', 'select' => '', 'access' => 'SUPER',
                               'width' => 80, 'newRow' => 1, 'req' => 0, 'rows' => 2,
	                  'help' => 'Special info not reported elsewhere, plain text field, superusers only'), 
    'About Me' => array('dbCol' => 'about', 'dbType' => 'textarea', 'select' => '', 'access' => 'MEMBER',
                               'width' => 80, 'newRow' => 1, 'req' => 0, 'rows' => 2,
	                  'help' => 'One line, whatever you want to share with other members!') 
);
        

# $MemberTypes define in lib/mode.inc

$MemberSinceTypes = array();  # we force this to a text area of dates used for membership
for ($i = 1950; $i <= date('Y'); $i++) {
    array_push($MemberSinceTypes, $i);
}

$OtherPilotTypes = array('SEL', 'MEL', 'Commercial SEL', 'Commercial MEL', 'CFI', 'CFII', 
                         'ATP', 'Instrument', 'Tail Wheel', 'Tow', 'None');


# Add pages here to require login, display custom title
$PageInformation =
    array(

        "account.php"       =>  array ("protected" => "Yes", "title" => "Member Control Panel"),

        "active_members.php"=>  array ("protected" => "Yes", "title" => "Member Info"),
        
        "aircraft.php"      =>  array ("protected" => "No", "title" => "Aircraft"),

        "edit_glider.php"   => array ("protected" => "Yes", "title" => "Edit Glider"),

        "edit_glider_status.php"   => array ("protected" => "Yes", "title" => "Edit Glider Status"),

        "edit_member.php"   => array ("protected" => "Yes", "title" => "Edit Member Info" ,"cache-control" => "Cache-Control: private, max-age=300" ),

        "edit_sched.php"    => array ("protected" => "Yes", "title" => "Edit Schedule"),

        "event_sched.php"   => array ("protected" => "Yes", "title" => "Special Event Schedule"),

        "flying_sched.php"  =>  array ("protected" => "Yes", "title" => "Flying Sched"),

        "forgot.php"        => array ("protected" => "No", "title" => "Forgot Password"),

        "glider_status.php" => array ("protected" => "Yes", "title" => "Aircraft Status"),

        "glider_status_log.php" => array ("protected" => "Yes", "title" => "Aircraft Status Log"),

        "hfl.php" => array ("protected" => "No", "title" => "High-Far-Long"),
        
        "hflConfirm.php" => array ("protected" => "Yes", "title" => "High-Far-Long"),
       
        "hflShow.php" => array ("protected" => "No", "title" => "High-Far-Long"),
        
        "hflSubmit.php" => array ("protected" => "Yes", "title" => "High-Far-Long"),
        
        "load_calendar.php" => array ("protected" => "Yes", "title" => "Load event calendar"),

        "login.php"         => array ("protected" => "No", "title" => "Login/Registration"),

        "logout.php"        => array ("protected" => "Yes", "title" => "Logout"),

        "password.php"      => array ("protected" => "Yes", "title" => "Change your password"),
        
    );


$PilotTypes = array('Pre Solo', 'Post Solo', 'Private', 'Commercial', 'CFI-G', 'Not Applicable');

$SeatTypes = array('1', '2');

$StateList =
    array("Select", "none", "Non US",
          "Alabama", "Alaska", "Arizona", "Arkansas",
          "California", "Colorado", "Connecticut", "Delaware",
          "District of Columbia", "Florida", "Georgia", "Hawaii",
          "Idaho", "Illinois", "Indiana", "Iowa",
          "Kansas", "Kentucky", "Louisiana", "Maine",
          "Maryland", "Massachusetts", "Michigan", "Minnesota",
          "Mississippi", "Missouri", "Montana", "Nebraska",
          "Nevada", "New Hampshire", "New Jersey", "New Mexico",
          "New York", "North Carolina", "North Dakota", "Ohio",
          "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island",
          "South Carolina", "South Dakota", "Tennessee", "Texas",
          "Utah", "Vermont", "Virginia", "Washington",
          "West Virginia", "Wisconsin", "Wyoming"
    );

$CountryList =
    array(
        "" => "Select", "US" => "United States",   "CA" => "Canada",  "UK" => "United Kingdom",
        "AF" => "Afghanistan", "AL" => "Albania", "AG" => "Algeria", "AN" => "Andorra",
        "AO" => "Angola", "AV" => "Anguilla", "AC" => "Antigua and Barbuda", "AR" => "Argentina",
        "AM" => "Armenia", "AA" => "Aruba", "AT" => "Ashmore and Cartier Islands", "AS" => "Australia",
        "AU" => "Austria", "AJ" => "Azerbaijan", "BF" => "Bahamas", "BA" => "Bahrain",
        "BG" => "Bangladesh", "BB" => "Barbados", "BS" => "Bassas Da India", "BO" => "Belarus",
        "BE" => "Belgium", "BH" => "Belize", "BN" => "Benin", "BD" => "Bermuda",
        "BT" => "Bhutan", "BL" => "Bolivia", "BK" => "Bosnia and Herzegovina", "BC" => "Botswana",
        "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", 
        "VI" => "British Virgin Islands",
        "BX" => "Brunei", "BU" => "Bulgaria", "UV" => "Burkina Faso", "BM" => "Burma",
        "BY" => "Burundi", "CB" => "Cambodia", "CM" => "Cameroon",
        "CV" => "Cape Verde", "CJ" => "Cayman Islands", "CT" => "Central African Republic", "CD" => "Chad",
        "CI" => "Chile", "CH" => "China", "KT" => "Christmas Island", "IP" => "Clipperton Island",
        "CK" => "Cocos (Keeling) Islands", "CO" => "Colombia", "CN" => "Comoros", "CF" => "Congo",
        "CG" => "Congo, Democratic Republic Of The", "CW" => "Cook Islands", "CR" => "Coral Sea Islands", 
        "CS" => "Costa Rica",
        "IV" => "Cote D'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus",
        "EZ" => "Czech Republic", "DA" => "Denmark", "DJ" => "Djibouti", "DO" => "Dominica",
        "DR" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "ES" => "El Salvador",
        "EK" => "Equatorial Guinea", "ER" => "Eritrea", "EN" => "Estonia", "ET" => "Ethiopia",
        "EU" => "Europa Island", "FK" => "Falkland Islands (Islas Malvinas)", "FO" => "Faroe Islands", 
        "FJ" => "Fiji",
        "FI" => "Finland", "FR" => "France", "FG" => "French Guiana", "FP" => "French Polynesia",
        "FS" => "French Southern and A. Lands", "GB" => "Gabon", "GA" => "Gambia, The", "GZ" => "Gaza Strip",
        "GG" => "Georgia", "GM" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar",
        "GO" => "Glorioso Islands", "GR" => "Greece", "GL" => "Greenland", "GJ" => "Grenada",
        "GP" => "Guadeloupe", "GT" => "Guatemala", "GK" => "Guernsey", "GV" => "Guinea",
        "PU" => "Guinea-Bissau", "GY" => "Guyana", "HA" => "Haiti", "HM" => "Heard Island and Mcdonald Islands",
        "HO" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IC" => "Iceland",
        "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IZ" => "Iraq",
        "EI" => "Ireland", "IM" => "Isle Of Man", "IS" => "Israel", "IT" => "Italy",
        "JM" => "Jamaica", "JN" => "Jan Mayen", "JA" => "Japan", "JE" => "Jersey",
        "JO" => "Jordan", "JU" => "Juan De Nova Island", "KZ" => "Kazakhstan", "KE" => "Kenya",
        "KR" => "Kiribati", "KU" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos",
        "LG" => "Latvia", "LE" => "Lebanon", "LT" => "Lesotho", "LI" => "Liberia",
        "LY" => "Libya", "LS" => "Liechtenstein", "LH" => "Lithuania", "LU" => "Luxembourg",
        "MC" => "Macau", "MK" => "Macedonia", "MA" => "Madagascar", "MI" => "Malawi",
        "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta",
        "RM" => "Marshall Islands", "MB" => "Martinique", "MR" => "Mauritania", "MP" => "Mauritius",
        "MF" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia, Federated States Of", "MD" => "Moldova",
        "MN" => "Monaco", "MG" => "Mongolia", "MW" => "Montenegro", "MH" => "Montserrat",
        "MO" => "Morocco", "MZ" => "Mozambique", "WA" => "Namibia", "NR" => "Nauru",
        "NP" => "Nepal", "NL" => "Netherlands", "NT" => "Netherlands Antilles", "NC" => "New Caledonia",
        "NZ" => "New Zealand", "NU" => "Nicaragua", "NG" => "Niger", "NI" => "Nigeria",
        "NE" => "Niue", "NM" => "No Man's Land", "NF" => "Norfolk Island", "KN" => "North Korea",
        "NO" => "Norway", "OS" => "Oceans", "MU" => "Oman", "PK" => "Pakistan",
        "PS" => "Palau", "PM" => "Panama", "PP" => "Papua New Guinea", "PF" => "Paracel Islands",
        "PA" => "Paraguay", "PE" => "Peru", "RP" => "Philippines", "PC" => "Pitcairn Islands",
        "PL" => "Poland", "PO" => "Portugal", "QA" => "Qatar", "RE" => "Reunion",
        "RO" => "Romania", "RS" => "Russia", "RW" => "Rwanda", "SH" => "Saint Helena",
        "SC" => "Saint Kitts and Nevis", "ST" => "Saint Lucia", "SB" => "Saint Pierre and Miquelon", 
        "VC" => "Saint Vincent and The Grenadines",
        "WS" => "Samoa", "SM" => "San Marino", "TP" => "Sao Tome and Principe", "SA" => "Saudi Arabia",
        "SG" => "Senegal", "SR" => "Serbia", "SE" => "Seychelles", "SL" => "Sierra Leone",
        "SN" => "Singapore", "LO" => "Slovakia", "SI" => "Slovenia", "BP" => "Solomon Islands",
        "SO" => "Somalia", "SF" => "South Africa", "SX" => "South Georgia &amp; S.S.I.", "KS" => "South Korea",
        "SP" => "Spain", "PG" => "Spratly Islands", "CE" => "Sri Lanka", "SU" => "Sudan",
        "NS" => "Suriname", "SV" => "Svalbard", "WZ" => "Swaziland", "SW" => "Sweden",
        "SZ" => "Switzerland", "SY" => "Syria", "TW" => "Taiwan", "TI" => "Tajikistan",
        "TZ" => "Tanzania", "TH" => "Thailand", "TO" => "Togo", "TL" => "Tokelau",
        "TN" => "Tonga", "TD" => "Trinidad and Tobago", "TE" => "Tromelin Island", "TS" => "Tunisia",
        "TU" => "Turkey", "TX" => "Turkmenistan", "TK" => "Turks and Caicos Islands", "TV" => "Tuvalu",
        "UG" => "Uganda", "UP" => "Ukraine", "AE" => "United Arab Emirates",
        "UY" => "Uruguay", "UZ" => "Uzbekistan", "NH" => "Vanuatu",
        "VT" => "Vatican City", "VE" => "Venezuela", "VM" => "Vietnam", "VQ" => "Virgin Islands",
        "WF" => "Wallis and Futuna", "WE" => "West Bank", "WI" => "Western Sahara", "YM" => "Yemen",
        "ZA" => "Zambia",  "ZI" => "Zimbabwe"
    );


?>
