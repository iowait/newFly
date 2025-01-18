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
require("./php_includes.inc");
require("./lib/header.inc");

$action = script_param('action');

if (!$action) {
  DisplayMessage( "Requested reset", "NO ACTION", 0);
}

// create lists of members, different duty types
// generate list of OPS, TOW, CFIG, CREW, AC and stored
// in variable variables, e.g.
// OPS_results - array of results of query
// OPS_count - count of results
// OPS_index - index position in results
$memberScan = array("OPS"  => "access = 'MEMBER'",
                    "TOW"  => "access LIKE '%TOW%'",
                    "CFIG" => "access LIKE '%CFIG%'",
                    "CREW" => "pilot != 'Pre Solo'",
                    "AC"   => "SELECT tailNum, seats, status FROM Glider G, GliderStatus S  
                                      WHERE G.type='Glider' AND G.GliderID=S.GliderID");

foreach ($memberScan as $type => $where) {
  if ($type == 'AC') {
    $sql = $where;
  } else {
    $sql = "SELECT memberID FROM Members WHERE $where";
  }
  
  $results = $type . "_results";
  if ($msg = DB_Query($Conn, $sql, $$results)) { // variable variable
	  DisplayMessage("Reset flying, can satisfy query: $sql", "query failed ($msg)");
  }
  
  $count = $type . "_count";
  $$count = count($$results);
  
  // setup index for each
  $indexVar = $type . "_index";

  //DB_Log("INFO", "Type: $type, count: " . $$count);
  
} // end for - all lists needed, OPS_results, TOW_results, etc...


// Convenience function to assign duties for a normal FLY day,
// OPS/TOW/CFIG
function insertDuties($calID, $dutyArray) {

  global $Conn;

  // insert Duty
  foreach ($dutyArray as $duty) {
    // skip CREW, only done for reservations
    if ($duty == 'CREW') {
      continue;
    }
    unset($insert);
    $insert['CalendarID'] = $calID;
    $insert['MemberID'] = getNextDutyMember($duty);
    $insert['type']     = $duty;
    $insert['notes']    = "N/A";  
    if ($msg = DB_Insert($Conn, 'Duty', $insert, $id)) {
      DisplayMessage("Insert Duty entries",$msg, 1);
    }
  } # end for - all duties
} // end insertDuties

// Insert an aircraft reservation, 1/2 seats
// date - the date alone
$StartHour = 10;
function insertReservation($date) {
  global $Conn;
  global $AC_results, $AC_count;  // variable variable, loaded earlier
  global $StartHour;
  STATIC $index = 0;   // keep track of the last glider assigned

  // add a PLANE calendar event
  unset($insert);
  $insert['date'] = "$date $StartHour:00";
  $insert['etype'] = 'PLANE';

  if ($msg = DB_Insert($Conn, 'Calendar', $insert, $calID)) {
    DisplayMessage("Insert Calendar for PLAN entries",$msg, 1);
  }

  
  // insert into Reservations
  unset($insert);
  $insert['CalendarID'] = $calID;
  $insert['tailNum'] = $AC_results[$index]['tailNum'];
  $insert['startTime'] = "$date $StartHour:00";
  $StartHour++;
  $insert['stopTime']  = "$date $StartHour:00";
  if ($StartHour > 16) {
    $StartHour = 10;
  }

  if ($msg = DB_Insert($Conn, 'Reservations', $insert, $id)) {
    DisplayMessage("Insert Reservation entries",$msg, 1);
  }

  // find crew, insert into Duty
  $numSeats = $AC_results[$index]['seats'];
  while ($numSeats--) {
    unset($insert);
    $insert['CalendarID'] = $calID;
    $insert['MemberID'] = getNextDutyMember('CREW');
    $insert['type'] = 'CREW';

    if ($msg = DB_Insert($Conn, 'Duty', $insert, $id)) {
      DisplayMessage("Insert Reservation duty entries",$msg, 1);
    }
  }

  $index++; //move on to next glider
  if ($index >= $AC_count) {
    $index = 0;
  }


} // end insertReservations


// Convenience function to return the next available duty
// person for a specific duty type on a day,  TOW, OPS, CFIG
// Checks to make only one duty/day, uses globals set earlier!
function getNextDutyMember($duty) {
  global $MembersOnDuty,
    $CREW_results, $CREW_count, $CREW_index,
    $OPS_results, $OPS_count, $OPS_index,
    $TOW_results, $TOW_count, $TOW_index,
    $CFIG_results, $CFIG_count, $CFIG_index;

  $dutyVar = $duty . "_results";
  $dutyIndexVar = $duty . "_index";
  $dutyCountVar = $duty . "_count";

  $loopCount = 0; // safety check in case we over schedule, no one avail

  do {
    $nextMember = $$dutyVar[$$dutyIndexVar++]['memberID'];
    // DB_Log("INFO", "getNextDutyMember for $duty, potential $nextMember");
    if ($$dutyIndexVar >= $$dutyCountVar) {
      $$dutyIndexVar = 0;
    }
    if (++$loopCount > $$dutyCountVar) {
      DisplayMessage("getNextDutyMember", "no one available for $duty, only $dutyCountVar available");
    }
  } while (in_array($nextMember, $MembersOnDuty));

  // found someone no in the array
  array_push($MembersOnDuty, $nextMember);
  return($nextMember);

} // end getNextDutyMember

//require("debug.inc");
?>

<div  id="divContent">


    <?php
    if (!hasPerms('admin')) { # we do stuff
        DisplayMessage( "Reset demo data", "NO PERMISSIONS FOR THIS", 0);
    }


    if ($action == 'event') { # event schedule

      // typical entry
      // | CalendarID | date                | etype   | title     | notes               | gliderID |
      // |        395 | 2020-04-30 00:00:00 | SPECIAL | Big Event | This is a big event |        0 |

      // purge what's there
      if ($msg = DB_Query($Conn, "DELETE FROM Calendar WHERE etype = 'SPECIAL'", $results)) {
	  DisplayMessage("Reset purge existing events",  "query failed ($msg)");
      }

      $sql = "INSERT INTO Calendar (date, etype, title, notes) VALUES 
       (DATE_ADD(NOW(), INTERVAL 7  DAY), 'SPECIAL', 'Club BBQ', '7 pm at the Airport, all are welcome.  Contact joe@smoe.com for details'),
       (DATE_ADD(NOW(), INTERVAL 14 DAY), 'SPECIAL', 'BOARD Meeting', '7 pm at the Airport, all members are welcome.'),
       (DATE_ADD(NOW(), INTERVAL 21 DAY), 'SPECIAL', 'FREE FLYING!',  '2-5 pm weather permitting. Don\'t miss out on this special.'),
       (DATE_ADD(NOW(), INTERVAL 28 DAY), 'SPECIAL', 'Safety Meeting', '7 pm at the Airport. Mandatory.  Contact mary@smoe.com for details'),
       (DATE_ADD(NOW(), INTERVAL 29 DAY), 'SPECIAL', 'BOARD Meeting', '7 pm at the Airport, all members are welcome.'),
       (DATE_ADD(NOW(), INTERVAL 42 DAY), 'SPECIAL', 'Wash & Wax the Fleet', '9-3 pm at the Airport.  Free Pizza & drinks!'),
       (DATE_ADD(NOW(), INTERVAL 51 DAY), 'SPECIAL', 'BOARD Meeting', '7 pm at the Airport, all members are welcome.'),
       (DATE_ADD(NOW(), INTERVAL 57 DAY), 'SPECIAL', 'Club Dinner', '7 pm at Joe\'s Bar & Grill, all are welcome. Please confirm with joe@smoe.com by two weeks prior.')";
      if ($msg = DB_Query($Conn, $sql, $results)) {
	DisplayMessage("Reset/Insert events",  "query failed ($msg)");
      }

    } else if ($action == 'fly') { # flying days/reservations

      // Calendar Table
      // | CalendarID | date                | etype  | title | notes           | gliderID |
      // |        425 | 2020-05-23 00:00:00 | FLYING |       |                 |        0 | - ops/tow
      // |        426 | 2020-05-12 00:00:00 | FLYING |       |                 |        0 | -- no OPS, just plane fly
      // |        427 | 2020-05-12 13:00:00 | PLANE  |       |                 |        0 | -- no OPS, just plane fly
      // |        428 | 2020-05-24 00:00:00 | FLYING |       | nothing special |        0 |
      // |        429 | 2020-05-24 13:00:00 | PLANE  |       | Solo            |        0 |

      // Reservations
      // | id | CalendarID | GliderID | startTime           | stopTime            |
      // |  5 |        427 |        1 | 2020-05-12 13:00:00 | 2020-05-12 14:00:00 |
      // |  6 |        429 |        4 | 2020-05-24 13:00:00 | 2020-05-24 14:00:00 |

      // Duty
      // | id    | CalendarID | MemberID | type | ack | ackCode | ackSuper | notes |
      // |  5004 |        425 |       86 | OPS  |   0 | 0       | 0        |       |
      // |  5005 |        425 |        0 | OPS  |   0 | 0       | 0        |       |
      // |  5006 |        425 |       20 | TOW  |   0 | 0       | 0        |       |
      // |  5007 |        425 |        0 | TOW  |   0 | 0       | 0        |       |
      // |  5008 |        425 |       41 | CFIG |   0 | 0       | 0        |       |
      // |  5009 |        425 |        0 | CFIG |   0 | 0       | 0        |       |

      // |  5010 |        426 |        0 | OPS  |   0 | 0       | 0        |       | - no normal duty, just placeholder

      // |  5013 |        427 |       44 | CREW |   0 | 0       | 0        |       |
      // |  5014 |        427 |       41 | CREW |   0 | 0       | 0        |       |

      // |  5018 |        429 |       80 | CREW |   0 | 0       | 0        |       |

      // We have several loops to fill in the calendar
      // We specify a repeating set of flying events that will be used
      // FLYING - just normal FLY day, random count of OPS/TOW/CFIG (WED, SAT, SUN)
      // PLANE_n  - plane reservation outside normal fly day (TUE, FRI), n - number to schedule
      // FLYINGPLANE_n - both, n-number to schedule
      $eventType = array('FLYING', 'FLYINGPLANE_2', 'FLYINGPLANE_3', 'FLYING', 'FLYINGPLANE_2');
      $eventIndex = 0;

 
      $EVENT_LIMIT = 25;
      // starting values for calendarID field, the other ids are not used for x-ref.
      $calID = 100;

      // We want to start on the next Saturday, get the starting offset from where we are now
      // %u give us number for day of the week, 1 Monday - 7 Sunday,  Sat = 6
      $eventOffset = 0; // days
      $midWeekOffset = 0; // flying mon-fri 
      $dayOfWeek = strftime("%u", time());
      $daysAway = 6 - $dayOfWeek;
      if ($daysAway < 0) {
        $eventOffset += 7; // start next Sat
      } else {
        $eventOffset = $daysAway;
      }
      DB_Log("INFO", "dayOfWeek($dayOfWeek), daysAway($daysAway), eventOffset($eventOffset)");

      if ($msg = DB_Transaction($Conn, 'START')) {
	DisplayMessage("START a reset update", $msg);
      }

      if ($msg = DB_Query($Conn, 'DELETE FROM Calendar' , $junk)) { // cascade should take care of others
	DisplayMessage("Reset flying, purging Calendar", "query failed ($msg)");
      }

      // set index counters to 0
      $dutyArray = array('OPS', 'TOW', 'CFIG', 'CREW');
      foreach ($dutyArray as $duty) {
	$dutyIndexVar = $duty . "_index";
	$$dutyIndexVar = 0;
      }

      # we keep track of full flying days, use SAT/SUN
      $fullFlyCount = 0;

      for ($eventCount = 0; $eventCount < $EVENT_LIMIT; $eventCount++) {  // limit on total events added

	  $event = $eventType[$eventIndex++];
	  if (strstr($event, '_')) {
	    list($type, $count) = explode('_', $event);
	  } else {
	    $type = $event;
	    $count = 0;
	  }

          $StartHour = 10; // used for ac reservations
	  unset($insert);

	  if (strstr($event, 'FLYING')) { // got a FLYING or FLYINGPLANE, 
            // both start with a FLYING day with full staff
	    // insert Calendar
            // below also used in aircraft res
            $dateAlone = strftime("%Y-%m-%d", time() + 
                                  (($eventOffset+$midWeekOffset) * 86400)); // days 
	    $insert['date'] = $dateAlone;
	    $insert['etype'] = 'FLYING';
	    $insert['notes'] = 'Should be great flying day';

 
            // keep track of whose got duty, you can only do ONE thing in a day
            $MembersOnDuty = array();

	    if ($msg = DB_Insert($Conn, 'Calendar', $insert, $calID)) {
	      DisplayMessage("Insert Calendar entries",$msg, 1);
	    }

	    // how many ops/tow/cfig people
	    // insert Duty
            insertDuties($calID, $dutyArray);

	    if ($calID % 2) { // more
              insertDuties($calID, $dutyArray);
	    } // end if - insert two of each

            // Do we need to insert aircraft reservations
            while ($count--) {
              insertReservation($dateAlone);
            } // end while - more ac reservations
	    
	  } // end if - flying day with full staff

          $fullFlyCount++;
          if ($fullFlyCount % 2) {
            $eventOffset += 1;
          } else {
            $eventOffset +=6;
          }
	  if ($eventIndex >= count($eventType)) {
	    $eventIndex = 0;
	  }
	  
      } // end for - event cycle
	
      
      if ($msg = DB_Transaction($Conn, 'COMMIT')) {
	DisplayMessage("COMMIT a reset update", $msg);
      }
      
      $i = 0; // restart
      

    } else {

            DB_Log('ERROR', "Bad reset action. Got $action");
 
    } // end if - update

    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
