-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

DROP PROCEDURE IF EXISTS initBlocs;
DELIMITER $$ CREATE PROCEDURE initBlocs()
    BEGIN
    DECLARE bloc_id INT DEFAULT 0;
    DECLARE iter INT DEFAULT 0;
    DECLARE itercol INT DEFAULT 0;
    START TRANSACTION;

    iterloop : LOOP
        IF iter < 3 THEN
            SET iter = iter +1;
            INSERT INTO llx_linesfromproductmatrix_bloc(ref, label, date_creation, tms, fk_user_creat, fk_user_modif, fk_rank, fk_status)
            VALUES('BLOCTEST', 'BLOCTEST', now(), current_timestamp, 1, 1, 1, 1);

            SET bloc_id = LAST_INSERT_ID();
            COMMIT;

            colloop : LOOP
                IF itercol < 3 THEN

                    INSERT INTO llx_linesfromproductmatrix_blochead( fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
                    VALUES (bloc_id, 'Catégorie', now(), 0, current_timestamp, 1, 1);

                    INSERT INTO llx_linesfromproductmatrix_blochead( fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
                    VALUES ( bloc_id, 'Type', now(), 1, current_timestamp, 1, 1);
                    SET itercol = itercol +1;
                ELSE
                    LEAVE colloop;
                END IF;
                END LOOP colloop;
                SET itercol = 0;
        ELSE
            LEAVE iterloop;
        END IF;
        END LOOP iterloop;


    END$$ DELIMITER ;
    CALL initBlocs();




