<?php
/*****
 * parseAuth.php
 * By: BrokenRadix(.com)
 * Usage: `php parseAuth.php auth.log`
 * Notes: Parse the Linux auth.log for IP addresses which entered
 *      a password for a username, then logged in correctly.
 *****/
ini_set("auto_detect_line_endings", true);  // Get all line endings

if(count($argv) < 2){   // Check for vaild argument list
    echo "Error: needs an file argument.\n";
    exit;
}

// CUSTOMIZE your log Strings
$failed_string = "Failed password for invalid user";
$success_string = "Accepted password for";

$my_file = file($argv[1]);  // Get file data
if($my_file == false){      // Check for valid file
    echo "Error: invalid file.\n";
    exit;
}

$failed_ips = array();      // Array for the failed login attempts
$success_ips = array();     // Array for the successful login attempts

foreach($my_file as $line_number => $line){             // Parse each line of the file
    $current_string = array();                          // Each line can be an array
    if (strpos($line,$success_string) !== false){       // Check for your success string
        $current_string = explode(" ", $line);
        //print_r($current_string);                     // UNCOMMENT to customize the next few lines
        $ip = $current_string[10];                      // CUSTOMIZE this location for your log’s IP
        $user = $current_string[8];                     // CUSTOMIZE this location for your log’s Username
        $success_ips[$ip][$user][] = $line_number;      // Add to the storage array
    }elseif(strpos($line,$failed_string) !== false){    // Check for your failure string (do the same as above)
        $current_string = explode(" ", $line);
        //print_r($current_string);
        $ip = $current_string[12];
        $user = $current_string[10];
        $failed_ips[$ip][$user] = $line_number;    
    }//if else
}//foreach line in file

$merged_ips = array();  // For the IP’s that have both success and failure
foreach($failed_ips as $current_ip => $data){   // Check each failure …
    if( !empty($success_ips[$current_ip]) ){    // for a success
        $merged_ips[$current_ip]["failed"] = $data;
        $merged_ips[$current_ip]["success"] = $success_ips[$current_ip];
    }//if
}//foreach failed

if(count($merged_ips) == 0){    // Exit if no matches
    echo "No matches found. Exiting.\n\n";
    exit;
}

//print_r($merged_ips); // show all attempts and line numbers
// Display the potential logins in a human readable format.
foreach ($merged_ips as $current_ip => $value) {
    echo "[$current_ip] Actual Username: ".key($value["success"])."\n";
    echo "[$current_ip] Try the following: ";
    foreach($value["failed"] as $username => $line_number){
        echo $username." ";
    }//foreach
    echo "\n\n";
}//foreach merged

?>