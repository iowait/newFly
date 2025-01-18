    <h2>Step 1/4 - ICG file confirmation</h2>
    <p>This allows you to see a ground track and barograph of your flight and confirm you have the correct file. Select a file to view by using the 'Browse' button. 
    </p>
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://SoaringTools.org/jsigc/lib/leaflet/leaflet.css" />
    <link rel="stylesheet" href="https://SoaringTools.org/jsigc/lib/leaflet-awesome-markers/leaflet.awesome-markers.css" />
    <link rel="stylesheet" href="https://SoaringTools.org/jsigc/igcviewer.css" />

    <label for="fileControl">The display may initially show the wrong altitude or time units.  To correct, just choose another option, allow the screen to refresh, and then select your desire value:</label><br />
    <input id="fileControl" type="file" />
    <p id="continue">
    <script>
     $('#fileControl').change(function () {
         if (this.files.length > 0) {
             var fileInput = document.getElementById('fileControl');
             var files = fileInput.files;
             var file = files.item(0);
             // console.log("Test logging");
             // console.log(file);
             var reader = new FileReader();
             var igcData;
             reader.readAsText(file);
             reader.onload = function() {
                igcData = reader.result;
                document.getElementById('continue').innerHTML = 'After you confirm the track shown below for accuracy, press "Submit" to proceed with submission of this file: ' + file.name + '\
<form method=post action=hfl.php>\
  <input type=hidden name=action value=confirm> \
  <input type=hidden name=file value=' + file.name + '>\
  <input type=hidden name=igcData value="'+ igcData + '">\
 <input type=submit value=Submit>\
</form>';             };           
             reader.onerror = function() {
               alert(reader.error);
             };
          }
     });
    </script>
    <h2> Preferences </h2>
    <p>
        <label for="altitudeUnits">Altitude units:</label>
        <select id="altitudeUnits" autocomplete="off">
            <option value="metres">Meters</option>
            <option selected value="feet">Feet</option>
        </select>
    </p>

    <p>
        <label for="timeZoneSelect">Time zone:</label>
        <select id="timeZoneSelect">
        </select>
    </p>

    <div id="igcFileDisplay">
        <h2> Flight Information </h2>

        <table id="headerInfo">
            <tbody></tbody>
        </table>

        <div id="task">
            <h2> Task </h2>
            <ul>
            </ul>
        </div>

        <div id="mapWrapper">
            <div id="map"></div>
            <div id="slider">
                <label for="timeSlider">Time:</label>
                <button id="timeBack"><span class="fa fa-caret-left"></span></button>
                <input type="range" id="timeSlider" step="1" value="0" min="0" max="100" />
                <button id="timeForward"><span class="fa fa-caret-right"></span></button>
                <p id="timePositionDisplay"></p>
            </div>
        </div>
        <div id="barogram"></div>
    </div>

    <hr />
    <i>The graphs are produced by JavaScript IGC Viewer (jsigc). A <a href='https://www.SoaringTools.Org/jsigc/LICENSE.txt'>free browser-based tool</a> for viewing GPS tracks and barograph traces from gliding loggers. The files must be in the International Gliding Commission (IGC) format. <br />jsigc - &copy; 2015-2016 Alistair Malcolm Green and Richard Brisbourne.  </i>

    <script src="https://SoaringTools.org/jsigc/lib/moment.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/moment-timezone-with-data.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/jquery-2.1.3.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/jquery.flot.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/jquery.flot.axislabels.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/jquery.flot.resize.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/jquery.flot.crosshair.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/leaflet/leaflet.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/leaflet/Semicircle.js"></script>
    <script src="https://SoaringTools.org/jsigc/lib/leaflet-awesome-markers/leaflet.awesome-markers.min.js"></script>
    <script src="https://SoaringTools.org/jsigc/parseigc.js"></script>
    <script src="https://SoaringTools.org/jsigc/mapcontrol.js"></script>
    <script src="https://SoaringTools.org/jsigc/igcviewer.js"></script>
