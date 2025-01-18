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
require("IGC.php"); // class file for HFL calcs

// Do the correct query to display HFL results
$entryDefaults = array ('action' => 'show', 'countLimit' => 'All', 'pilot' => 'All', 
                        'entryType' => 'All', 'timePeriod' => 'All', 'glider' => 'All',
                        'startDate' => 'All', 'endDate' => 'All');

foreach ($entryDefaults as $entry => $default) {
    $x = script_param($entry); // needed to remove error from php 5.4.19 of using empty(script_param($entry))
    if (empty($x)) {
        $$entry = $default;
    } else {
        $$entry = $x;
    }
    //errorLog("Value of $entry is ".$$entry);
} // end for - all params
 
 
/* create the query, we have two placeholder examples
$sqlOne = "SELECT date, pilot, glider, extraName, units, high, far, 
                   `long`, notes, updated FROM hfl 
                    $where $orderBy $limit"; 

$sqlTwo =  "SELECT h.date, h.pilot,
          extraName, units, h.high, h.far, h.long, notes, updated FROM hfl h 

  INNER JOIN (   
     SELECT date,  MAX($entryType) AS best FROM hfl
         GROUP BY $timePeriod(date) ) r  ON

  h.$entryType = r.best and $timePeriod(h.date) = DAY(r.date) 

  $having
  ORDER BY far DESC";
*/

// process all the criteria, they'll be part of either a 'WHERE' or 'HAVING'
$criteria = '';
foreach (array('pilot','glider','startDate', 'endDate') as $field) {
    $value = $$field;
    $compare = '='; // default
    if ($value != 'All') {
        if ($field == 'pilot') {
            $value = '%' . preg_replace('/ /', '%', $value) . '%'; 
            $compare = 'LIKE';
        } else if ($field == 'startDate') {
            $compare = '>=';
            $field = 'date';
        } else if ($field == 'endDate') {
            $compare = '<=';
            $field = 'date';
        }
        if (empty($criteria)) {
            $criteria = "$field $compare '$value' ";
        } else {
            $criteria .= " AND $field $compare '$value' ";
        }
        } // non Any field
} // for all criteria

// starting values - always
$where = '';
$having = '';
$orderBy = 'ORDER BY date DESC';    
 
$limit = '';
if ($countLimit != 'All') {
    $limit = "LIMIT $countLimit";
}


if ($timePeriod == 'All' && $entryType == 'All') { // simple query

    if (!empty($criteria)) {
        $where = "WHERE $criteria";
    }
        
    $sql = "SELECT date, pilot, glider, extraName, units, high, far, 
                   `long`, notes, updated FROM hfl 
                    $where $orderBy $limit";
    
} else if ($timePeriod != 'All' AND $entryType != 'All') {
    
    if (!empty($criteria)) {
        $having = "HAVING $criteria";
        $where  = "WHERE $criteria";
    }
    
    $sql =  "SELECT h.date, pilot, glider, extraName, units, high, far, 
                     `long`, notes, updated FROM hfl h INNER JOIN (   
     SELECT date,  MAX(`$entryType`) AS best FROM hfl $where
         GROUP BY $timePeriod(date) ) r  ON
     h.$entryType = r.best and $timePeriod(h.date) = $timePeriod(r.date) 
  $having
  ORDER BY `$entryType` DESC";

} else { // no good
    DisplayMessage("Process your search", "Both Time Period and Entry Type must be selected together or not at all.");
}

if ($action == 'show') { // get the data

    errorLog("Query is($action): $sql");

    if ($msg = DB_Query($Conn, $sql, $results)) {
        DisplayMessage("Check for " . HFL . " results", "Could not retrieve data: $msg");
    }

}

include('hflShow_content.php');
include('footer.inc');

/*
mysql> SELECT hlfID, date,MAX(far) FROM hfl GROUP BY WEEK(date) ORDER BY WEEK(date) DESC, far DESC;
+-------+---------------------+----------+
| hlfID | date                | MAX(far) |
+-------+---------------------+----------+
|     1 | 2020-09-25 23:47:00 |      3.0 |   <<< WRONG IDs...
|     5 | 2020-09-19 18:25:00 |      4.0 |
|     2 | 2020-08-20 19:50:00 |      5.0 |
|     3 | 2020-06-14 17:29:00 |      2.5 |
+-------+---------------------+----------+

select l.* 
from hfl l
inner join (
  select 
    date, max(far) as farthest 
  from hfl 
  group by week(date)
) r
  on l.far = r.farthest and l.date = r.date
order by far desc

mysql> select l.hlfID,l.date,l.far  from hfl l inner join (   select      date, max(far) as farthest    from hfl    group by month(date) ) r   on l.far = r.farthest and MONTH(l.date) = MONTH(r.date) order by far desc;
+-------+---------------------+-----+
| hlfID | date                | far |
+-------+---------------------+-----+
|     6 | 2020-08-20 19:50:00 | 5.0 |
|     5 | 2020-09-19 18:25:00 | 4.0 |
|     3 | 2020-06-14 17:29:00 | 2.5 |
+-------+---------------------+-----+

mysql> select l.hlfID,l.date,l.far  from hfl l inner join (   select      date, max(far) as farthest    from hfl    group by week(date) ) r   on l.far = r.farthest and week(l.date) = week(r.date) order by far desc;
+-------+---------------------+-----+
| hlfID | date                | far |
+-------+---------------------+-----+
|     6 | 2020-08-20 19:50:00 | 5.0 |
|     5 | 2020-09-19 18:25:00 | 4.0 |
|     4 | 2020-09-25 23:47:00 | 3.0 |
|     3 | 2020-06-14 17:29:00 | 2.5 |
+-------+---------------------+-----+

mysql> SELECT hlfID,date,far,fileName FROM hfl ORDER BY date;
+-------+---------------------+------+------------------------------+
| hlfID | date                | far  | fileName                     |
+-------+---------------------+------+------------------------------+
|     3 | 2020-06-14 17:29:00 | 2.50 | one2020-06-14-XCS-AAA-01.igc |
|     2 | 2020-08-20 19:50:00 | 4.50 | one2020-08-20-XCS-AAA-01.igc |
|     6 | 2020-08-20 19:50:00 | 5.00 | two2020-08-20-XCS-AAA-01.igc |
|     5 | 2020-09-19 18:25:00 | 4.00 | two2020-09-19-XCS-AAA-01.igc |
|     7 | 2020-09-24 11:47:00 | 1.60 | 2020-09-25-XCS-AAA-02.igc    |
|     1 | 2020-09-25 23:47:00 | 2.50 | one2020-09-25-XCS-AAA-02.igc |
|     4 | 2020-09-25 23:47:00 | 3.00 | two2020-09-25-XCS-AAA-02.igc |
+-------+---------------------+------+------------------------------+

mysql> select l.hlfID,l.date,l.far  from hfl l inner join (   select      date, max(far) as farthest    from hfl    group by day(date) ) r   on l.far = r.farthest and day(l.date) = day(r.date) order by far desc;
+-------+---------------------+------+
| hlfID | date                | far  |
+-------+---------------------+------+
|     6 | 2020-08-20 19:50:00 | 5.00 |
|     5 | 2020-09-19 18:25:00 | 4.00 |
|     4 | 2020-09-25 23:47:00 | 3.00 |
|     3 | 2020-06-14 17:29:00 | 2.50 |
|     7 | 2020-09-24 11:47:00 | 1.60 |



SELECT h.hlfID, h.date, h.far FROM hfl h 
  INNER JOIN (   
   SELECT  date, MAX(far) AS farthest FROM hfl    
     GROUP BY DAY(date) ) r  ON
   h.far = r.farthest and DAY(h.date) = DAY(r.date) 
     HAVING date > '2020-06-13' AND date < '2020-09-01'
     ORDER BY far DESC;

*/

?>

