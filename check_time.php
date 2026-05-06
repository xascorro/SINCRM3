<?php
include('database/dbconfig.php');

echo "--- PHP CONFIG ---\n";
echo "Timezone: " . date_default_timezone_get() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n--- DB CONFIG ---\n";
$res = mysqli_query($connection, "SELECT NOW() as db_now, @@session.time_zone as tz");
$row = mysqli_fetch_assoc($res);
echo "DB Current Time: " . $row['db_now'] . "\n";
echo "DB Session TZ: " . $row['tz'] . "\n";

echo "\n--- SYSTEM CONFIG ---\n";
echo "System Time: " . shell_exec('date') . "\n";
?>