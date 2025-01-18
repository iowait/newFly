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

To change your password, just enter the new value below.<br />
<?=$PasswordRule?>
<p>
    <i>A friendly reminder.  Please be aware all your activity on this site after login is recorded.</i>
</p>

    <form method='post' action='reset_password.php'>

      <table cellpadding = '10'>
        <tr>
          <td>
            Password:
          </td>

          <td>
            <input type='password' name='pass1' size='20'>
          </td>

        </tr>
        <tr>
          <td>
            Repeat Password:
          </td>

          <td>
            <input type='password' name='pass2' size='20'>
          </td>

        </tr>

        <tr>
          <td colspan='2'>
            <input type='hidden' name='action' value='edit'>
            <input type='hidden' name='emailAddr' value='<?=$emailAddr?>'>
            <input type='hidden' name='reset' value='<?=$reset?>'>
            <input type='submit' value='Change password'>
          </td>
        </tr>
      </table>



    </form>



