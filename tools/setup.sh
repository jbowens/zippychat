#!/bin/sh
echo "Installing Zippy Chat"

# Check that ESPRIT_TOP is set
if [[ -z "$ESPRIT_TOP" ]]; then
    echo "[ERROR] \$ESPRIT_TOP is not set."
    exit 1;
else
    echo "\$ESPRIT_TOP = $ESPRIT_TOP"
fi

# Check that ZC_DBUSER is set
if [[ -z "$ZC_DBUSER" ]]; then
    echo "[ERROR] \$ZC_DBUSER is not set."
    exit 1;
else
    echo "\$ZC_DBUSER = $ZC_DBUSER"
fi

# Check that ZC_DBPASS is set
if [[ -z "$ZC_DBPASS" ]]; then
    echo "[ERROR] \$ZC_DBPASS is not set."
    exit 1;
else
    echo "\$ZC_DBPASS = $ZC_DBPASS"
fi

# Check that ZC_DBNAME is set
if [[ -z "$ZC_DBNAME" ]]; then
    echo "[ERROR] \$ZC_DBNAME is not set."
    exit 1;
else
    echo "\$ZC_DBNAME = $ZC_DBNAME"
fi

# Check that ZC_TOP is set
if [[ -z "$ZC_TOP" ]]; then
    echo "[ERROR] \$ZC_TOP is not set."
    exit 1;
else
    echo "\$ZC_TOP = $ZC_TOP"
fi

# Create the esprit database tables
echo "Creating the esprit tables"
cat $ESPRIT_TOP/scripts/setup/create-database-mysql.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME

# Create the Zippy Chat tables
echo "Creating Zippy Chat tables"
cat $ZC_TOP/sql/create-username-changes.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME 
cat $ZC_TOP/sql/create-rooms.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME
cat $ZC_TOP/sql/create-messages.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME
cat $ZC_TOP/sql/create-chat-sessions.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME
cat $ZC_TOP/sql/create-404logs.sql | mysql --user=$ZC_DBUSER --password=$ZC_DBPASS $ZC_DBNAME

mkdir $ZC_TOP/twigcache
chmod a+rwx $ZC_TOP/twigcache
