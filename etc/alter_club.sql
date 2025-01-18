# change to what the new club has, mismatches will be blank
ALTER TABLE Members MODIFY COLUMN memberType ENUM('Associate Active', 'Associate Limited', 'College Junior',        'Junior', 'Senior Active', 'Inactive', 'Other', 'None') NOT NULL DEFAULT 'None';

# change $MemberTypes to agree


# change checkout for club aircraft
ALTER TABLE Members MODIFY COLUMN checkout SET('ASK 21', '1-26', '1-34', '2-33', 'Discus CS', 'Duo Discus', 'Pawnee', 'Supercub', 'Pawnee Tow', 'Supercub Tow', 'Cash', 'CFI', 'Com', 'Line', 'Log', 'Wing Runner') NOT NULL;

# NEW COLUMN ALL Needed Coord
ALTER TABLE Members MODIFY COLUMN coord SET('BOARD', 'CASH', 'CFIG', 'COM', 'JUNIOR', 'LOG', 'SCHED', 'TOW', 'WINGRUNNER') NOT NULL;

# NEW COLUMN ALL TowMed
ALTER TABLE Members ADD COLUMN towMed DATE NOT NULL AFTER mentorID;

# NEW COLUMN just club
# hhsc specific

# add id column for easier reference to raw data table
ALTER TABLE hhsc ADD COLUMN id INT NOT NULL AUTO_INCREMENT KEY;

# add cols for biennial flight review and tow medical
ALTER TABLE Members ADD COLUMN bfr DATE NOT NULL;
ALTER TABLE Members ADD COLUMN towMed DATE NOT NULL;

ALTER TABLE Members ADD COLUMN suffix CHAR(4) NOT NULL;
ALTER TABLE Members ADD COLUMN notes CHAR(80) NOT NULL;
ALTER TABLE Members ADD COLUMN about CHAR(80) NOT NULL;

## July 2

ALTER TABLE Members ADD COLUMN memberSince CHAR(4) NOT NULL DEFAULT '' AFTER mentorID;
ALTER TABLE Members MODIFY COLUMN notes TEXT NOT NULL;
ALTER TABLE Members MODIFY COLUMN about TEXT NOT NULL;