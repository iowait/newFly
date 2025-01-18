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
$warn = "";
if ($super) {
$warning = "<p><font color='red'><b>WARNING</b> - you are logged in as a superuser (probably because you're a board member or CFI-G).
    When viewing the Active/Inactive user lists you will see an 'EDIT' link by each name.  This link allows you to change their information.
    Also, certain fields like Aircraft Check Out are only editable by you.
    <I>Be careful!</I></font></p>";

}

    ?>
    <div  id="divContent">

        <?php
        require 'account_content.php';
        ?>
    </div>

    <!--   ------------------ Content ends here ---------------------------------- -->
    <?php
    require 'footer.inc';
    ?>
