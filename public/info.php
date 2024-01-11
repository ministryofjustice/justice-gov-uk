<?php

# Fill our vars and run on cli
# $ php -f db-connect-test.php

$dbname = getenv("DB_NAME");
$dbuser = getenv("DB_USER");
$dbpass = getenv("DB_PASSWORD");
$dbhost = getenv("DB_HOST");

$connect = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");
mysqli_select_db($connect, $dbname) or die("Could not open the db '$dbname'");

$test_query = "SHOW TABLES FROM $dbname";
$result = mysqli_query($connect, $test_query);

$tblCnt = 0;
while ($tbl = mysqli_fetch_array($result)) {
    $tblCnt++;
    echo $tbl[0]."<br />\n";
}

if (!$tblCnt) {
    echo "There are no tables<br />\n";
} else {
    echo "There are $tblCnt tables<br />\n";
}

phpinfo(INFO_MODULES);
