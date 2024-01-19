<?php

if (getenv('WP_ENV') !== 'development') {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    exit;
}

## -------------------------------------------------------------------------
## -------------------------------------------------------------------------

$dbname = getenv("DB_NAME");

# connect to the DB
$connect = mysqli_connect(
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
) or die("Unable to Connect to " . getenv("DB_HOST"));

# select the DB by name
mysqli_select_db($connect, $dbname) or die("Could not open the db '$dbname'");

# query the tables
$result = mysqli_query($connect, "SHOW TABLES FROM $dbname");

# display tables for inspection
$tblCnt = 0;
while ($tbl = mysqli_fetch_array($result)) {
    $tblCnt++;
    echo $tbl[0] . "<br />\n";
}

# confirm the result
if (!$tblCnt) {
    echo "There are no tables<br />\n";
} else {
    echo "There are $tblCnt tables<br />\n";
}

# output all settings concerning the PHP installation
phpinfo();
