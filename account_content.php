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
<div class="clsContent-mid5">

   <h3><span style="font-size: 24px;">
   Member Control Panel</span></h3>
   <p>This gives you access to control your account information.  Depending on the permissions you've been assigned you will also be able to update schedules/events and other items at the site.</p>
   <?=isset($warning) ? $warning: ''?>
   <table>
       <tr>
           <td>
               <UL>
	       <? if (SHOW_FLY_SCHEDULE) { ?>
                   <LI><a href='flying_sched.php'>View the entire flying schedule.</a></LI>
	       <? } ?>
                   <LI><a href='event_sched.php'>View the special club events schedule.</a></LI>
                   <LI><a href='active_members.php?type=Active'>View all active members.</a></LI>
                   <LI><a href='active_members.php?type=Inactive'>View all inactive members.</a></LI>
               </UL>
           </td>
           <td>
               <ul>
	       <? if (SHOW_FLY_SCHEDULE) { ?>
 		   <LI><a href='flying_sched.php?limit=1'>Show just my duty days.</a></LI>
	       <? } ?>
                   <LI><a href='edit_member.php'>View/Update your personal info. </a></LI>
                   <LI><a href='password.php'>Update your password. </a></LI>
                   <LI><a href='hfl.php'><b><?=HFL?> - A friendly way to show off your flight!</b></a>
                       
                </ul>
           </td>
       </tr>

       <?
       if (hasPerms('admin')) { ?>
           
           <tr>
               <td colspan="2">
                   <b>ADMIN ONLY BELOW - <i>proceed with Caution!</i></b>
               </td>
           </tr>
           <tr>
               <td>
                   <ul>
                       <LI><a href='admin.php?action=login'>Show recent login activity/password resets.</a></LI>
                       
                       <LI><a href='admin.php?action=photo'>Show recent photo upload activity.</a></LI>
                       <LI><a href='admin.php?action=messages'>Show recent user messages & errors</a></LI>
                       
                       <LI><a href='admin.php?action=log'>Show recent log activity.</a></LI>
                      
                   </ul>
               </td>
               <td>
                   <ul>
                       <LI><a href="load_calendar.php">Load an OPS/TOW/CFIG-G/Event schedule.</a></LI>
                       <LI><a href="reset.php?action=event">Reset the Event Schedule (INSTANTLY, no confirmation).</a></LI>
                       <LI><a href="reset.php?action=fly">Reset the Flying Schedule (INSTANTLY, no confirmation).</a></LI>
                   </ul>
               </td>
           </tr>
       <? } ?>
   </table>
   

   </UL>

</div>

