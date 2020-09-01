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


INSERT INTO llx_linesfromproductmatrix_bloc(rowid, ref, label, date_creation, tms, fk_user_creat, fk_user_modif, fk_rank, fk_status)
VALUES(1, 'BLOCTEST', 'BLOCTEST', now(), current_timestamp, 1, 1, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (1, 1, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (2, 1, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (3, 1, 'Catégorie', now(), 0, current_timestamp, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (4, 1, 'Type', now(), 1, current_timestamp, 1, 1);




INSERT INTO llx_linesfromproductmatrix_bloc(rowid, ref, label, date_creation, tms, fk_user_creat, fk_user_modif, fk_rank, fk_status)
VALUES(2, 'BLOCTEST', 'BLOCTEST', now(), current_timestamp, 1, 1, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (5, 2, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (6, 2, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (7, 2, 'Catégorie', now(), 0, current_timestamp, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (8, 2, 'Type', now(), 1, current_timestamp, 1, 1);




INSERT INTO llx_linesfromproductmatrix_bloc(rowid, ref, label, date_creation, tms, fk_user_creat, fk_user_modif, fk_rank, fk_status)
VALUES(3, 'BLOCTEST', 'BLOCTEST', now(), current_timestamp, 1, 1, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (9, 3, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (10, 3, 'Catégorie', now(), 0, current_timestamp, 1, 1);
INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (11, 3, 'Catégorie', now(), 0, current_timestamp, 1, 1);

INSERT INTO llx_linesfromproductmatrix_blochead(rowid, fk_bloc, label, date_creation, type, tms, fk_user_creat, fk_rank)
VALUES (12, 3, 'Type', now(), 1, current_timestamp, 1, 1);




