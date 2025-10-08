#!/bin/bash

# MySQL credentials
USER="dolphin123"
PASSWORD="dolphin123"
DB="dolphin_db"

# Query to get foreign key relationships
mysql -u "$USER" -p"$PASSWORD" -e "
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    information_schema.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = '$DB'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY
    TABLE_NAME;
" 
