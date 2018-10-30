<?php

$start = 16;
$end   = (16*16*16*16)-1;

print "DROP TABLE mac_library;\n\n";
print "CREATE TABLE mac_library (\n";
print "id serial primary key,\n";
print "mac_addr char(17) unique not null,\n";
print "tstamp timestamp\n";
print ");\n";

for($i=$start; $i <= $end; $i++) {
	print ("INSERT INTO mac_library (mac_addr) VALUES ('00:02:01:00:");
	$last =sprintf("%04X",$i);
	print substr($last,0,2);
	print ":";
	print substr($last,2,4);
	print ("');\n");
}

