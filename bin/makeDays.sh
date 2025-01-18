#!/bin/bash
year=2019
let month=4
while (($month < 12)); do
  shortYear=${year/20//}
  cal $month $year |   sed '1,2d; s/^\(..\) .. .. .. .. .. \(..\).*/\1\n\2/' |  sed "/^ *$/d; s/^ /0/; s%\(.*\)%$month/\1$shortYear%"
  let month++
done
