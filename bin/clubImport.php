#!/usr/bin/php
<?php 
 /* Copyright (C) 1995-2020  John Murtari
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
along with FLY.  If not, see <https://www.gnu.org/licenses/>. */

// This is a starter script to assist with loading club membership into the 
// FLY database Members table.  We'd normally recommend taking data in whatever
// and first move it into a new MySQL table and massage the data in that table
// to get it close to what is expected in Members.

// This script will then process each row in the new table against the column values
// expected for Members, using MemberLayout and the types specified.  Definitely
// a work in progress

echo " ";
require("php_includes.inc"); // after the first output to avoid session init issues...

if (empty($argv[1])) {
    echo "\nERROR - must supply name of table having data to import.\n";
    exit(1);
}
$inTable = $argv[1];

echo "\n\nImport script starting, will read from table $inTable...  ";

// read each row from the input table, we assume you the programmer know the
// column headings;]
// WE DO Assume you have an id column, just auto increment to distinguish rows
if ($msg = DB_Query($Conn, "SELECT * FROM $inTable", $results)) {
    echo "\nERROR, can't read $inTable, got: $msg";
    exit(1);
}

echo "read " . count($results) . " rows of data.\n";

$count = 0;
$added = 0;

// it's all or nothing
if ($msg = DB_Transaction($Conn, 'START')) {
    echo "ERROR - can't start transaction\n";
    exit(1);
}

foreach ($results as $row) {
    // raw stuff we are getting, make the variable name match the dbCol values in Members
    // when possible
    $count++;
    $id = $row['id'];
    if (empty($id)) {
        echo "ERROR - input table must have an 'id' column, unique numeric values\n";
        exit(1);
    }

    List($firstName,$lastName) = explode (' ', $row['Member']);  // Joe smith
    $emailAddr = $row['Email'];

    // make sure we got something for name, email that looks correct
    if (!isset($firstName) || !isset($lastName) || !isset($emailAddr) ||
        strlen($firstName) < 3 || strlen($lastName) < 3 || strlen($emailAddr) < 10) {
        echo "ERROR - id($id), bad data for name($firstName $lastName) or email($emailAddr), skipping\n";
        continue;
    }
    echo "Processing id($id), $firstName $lastName....";
    $phone = empty($row['Phone']) ? 'missing phone' : $row['Phone'];
    $city  = empty($row['City']) ? 'missing city' : $row['City'];
    $state = $row['state'];
    $zip   = 12345;
    $memberType = $row['type'];
    $towMed = empty($row['towMed']) ? '' : $row['towMed'];
    $bfr    = empty($row['bfr']) ? '' : $row['bfr'];
    $access = '';
    $suffix = $row['suffix'];
    $about  = $row['about'];
    $notes  = $row['notes'];
    $memberSince = $row['years'];
    addToSet('access', 'MEMBER');  // everyone has MEMBER

    # we take care of glider and other ratings
    # set some access permissions also
    $x = $row['rating']; // expect one of ->  P,  CFIG,  STU,  C,  CFIG/A
    $pilot = 'Pre Solo';
    $otherPilot = '';
    if ($x == 'P') {
        $pilot = 'Private';
    } else if ($x == 'CFIG') {
        $pilot = 'CFI-G';
        addToSet('access', 'CFIG');
    } else if ($x == 'STU') {
        $pilot = 'Pre Solo';
    } else if ($x == 'C') {
        $pilot = 'Commercial';
    } else if ($x == 'CFIG/A') {
        $pilot = 'CFI-G';
        $otherPilot = 'CFI,SEL';
        addToSet('access', 'CFIG');
    } 
    if (!empty($row['Tow']) && $row['Tow'] == 'X') { // must have at lease SEL
        addToSet('otherPilot', 'SEL');
        addToSet('access', 'TOW');
    }

    # take care of coordinator roles
    $coord = '';
    $x = empty($row['Duty']) ? '' : $row['Duty'];
    if ($x) {
        $found = 0;
        foreach ($CoordTypes as $y) {
            if (stristr($x, $y)) {
                addToSet('coord', $y);
                $found = 1;
            }
        } // end for all coordinator roles
        if (!$found) { //unexpected
            echo "   WARN - id($id) - unexpected value for Duty($x)\n";
        }
    } // end if - had a role
    
    # set access perms for BOARD members
    if (!empty($row['Board'])) {
        addToSet('access', 'BOARD');
    }

    # handle checkout, a BIG SET of values
    # what's in the incoming data as columns
    $found = 0;  # should get at least one....
    $checkout = '';
    foreach ($CheckoutTypes as $col) {
        $theirCol = $col;  // we assume their column heading matches our set value
        // a few are different
        if ($col == '2-33') {
            $theirCol = 'T33';
        } else if ($col == '1-26') {
            $theirCol = 'I26';
        } else if ($col == '1-34') {
            $theirCol = 'I34';
        } else if ($col == 'Pawnee Tow') {
            $theirCol = 'Tow';
        }

        if (!empty($row[$theirCol])) { 
            addToSet('checkout', $col);
            $found++;
        }
    } // end for -- all types
    
    if (!$found) {
        echo "   WARNING - id($id) $firstName $lastName, no checkouts found\n";
        //print_r($row);
        //exit;
    }

    // dummy values where we don't have anything
    $middleName = '';
    $street = '';
    $phone2 = '';
    $mentorID = 0;
    $badge = '';
    
    // taken from edit_member
    # First store anything we might have gotten in a POST, don't want to lose it on
    # an error
    $missingList = '';  # did we get all the mandatory fields, we'll confirm below
    $super = 1;

    foreach ($MemberLayout as $item => $params) {
        $field = $params['dbCol'];
        $req = $params['req'];
        $privNeeded = $params['access'];
        
        $fieldVal = $$field;  // could be an array for a SET
        
        // if it's a field only a SUPER user can edit, and we're not, skip it
        if ($privNeeded == 'SUPER' && !$super) {
            continue;
        }
        
        // is it a required field?
        if ((!$fieldVal || (!is_array($fieldVal) && stristr($fieldVal, 'none')) ) && 
            ( ($privNeeded == 'MEMBER' && $req) || $super && $req)) {
            $missingList .= " $item, ";
        }
               
        if (is_array($fieldVal)) {
            # echo "<br>$field is ARRAY: ".print_r($fieldVal)."</br>";
            $fieldVal = implode(',', $fieldVal);
                $$field = $fieldVal;
        }
        $updateInfo[$field] = $fieldVal;
        
    } # end for -all fields
    
    if (!$missingList) {
        
        if ($msg = DB_Insert($Conn, 'Members', $updateInfo, $memberID)) {
            DisplayMessage("Insert new member", $msg, 1);
        }
        echo "   user ($id) has been inserted.\n";
        $added++;
    } else {
        echo "   user missing $missingList.\n";
    }
    
} // end for - all rows

// it's all or nothing
if ($msg = DB_Transaction($Conn, 'COMMIT')) {
    echo "ERROR - can't Commit transaction\n";
    exit(1);
}

echo "\nScript completed with no fatal errors, $count processed, $added users added\n";
?>
