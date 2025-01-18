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
               </td>
           </tr> <!-- Closing opening tr located in header.inc , we're in two columns-->
           <tr>
           
               <td colspan="2">
                   <? if (!empty($DebugLevel) && ($DebugLevel & 0x100) && hasPerms('admin')) { // mask values in db_funcs.inc

                   ?>
                       <hr />
                       <table border="1" cellpadding="10">
                           <tr>
                               <td style="vertical-align:top;width:50%">
                                   <pre><? 
                                        echo "<b>SESSION:</b> <br />"; print_r($_SESSION); 
                                        echo "<b>GET:</b> <br />"; print_r($_GET);
                                        if (1) { // needs work ZZ
                                            echo "<B>Variables</B> <br />:";
                                            $varArray = array();
                                            foreach(get_defined_vars() as $varName => $varValue) {
                                                if ($varName == 'GLOBALS') {
                                                    foreach($varValue as $name=>$var)
                                                    {
                                                        $length = strlen(serialize($var));
                                                        if (!is_array($var) && $length < 100) {
                                                            array_push($varArray, $name);
                                                        }
                                                    }
                                                }
                                            }
                                            // another pass thru sorted
                                            asort($varArray);
                                            foreach($varArray as $varName) {
                                                echo "$varName: $GLOBALS[$varName]\n";
                                            }
                                        }
                                        ?>
                                   </pre>
                               </td>
                               <td style="vertical-align:top;width:50%" >
                                   <pre><? echo "<b>POST:</b> </br >"; print_r($_POST);?></pre>
                               </td>
                           </tr>
                       </table>
                   <? } ?>
                   <!-- BOTTOM CUSTOM FOOTER -->
               </td>
           </tr>
       </table>
             
</body>


</html>
