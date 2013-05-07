<?php
/**
 * Current script overcomes restriction from hosting provider (OVH) 
 * to set a shell script which triggers via curl a target php script
 * So approach is to set a task that calls present script via cli and
 * from here make a request to target script and send results via email
**/
if (php_sapi_name() == "cli") {
    $target_url = "";
    // host based configuration
    switch (gethostname()) {
        case "your_local_box":
            $utils_path = "./../blablabla/Utils.php";
            $target_url = "http://localhost/xxxx/yyyy.php";
            break;
        default:
            $utils_path = "./../blablabla/Utils.php";
            $target_url = "http://www.mywebsite.com/xxxx/yyyy.php";
    }
    require_once $utils_path;
    // credentials in clear text, php curl will manipulate afterwards
    $ba_credentials = array("username"=>"your_username", "password"=>"your_password");
    // trigger request using curl
    $result = Utils::request($target_url, false, false, array(), false, $ba_credentials);
    // sending email to provide feedback
    $recipients = "your_email_address@server.com";
    $message = $result;
    $subject = "Your task name";
    $result_email = Utils::sendEmail($recipients, $subject, $message);
} else {
    echo "Execution only via command line interface";
}
?>
