<!DOCTYPE html>
<html lang="es">
    <head> 
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
        <title>Weekly panel</title> 
    </head>
    <body>
<?php
    require_once "vbfunctions.php";
    require_once "utils.php";
    try {
        echo "Loading default data... ";
        require_once "./forum_data.php"; # require will throw an error and stop executing if file is not found
        echo "OK<br/>\n";
        echo "Retrieving data from spreadsheet... ";
        # http://www.ravelrumba.com/blog/json-google-spreadsheets/
        $spreadsheet_data = getData(FEED);
        if ($spreadsheet_data) {
            echo "OK<br/>\n";
            $today = new DateTime("now", new DateTimeZone('Europe/Madrid'));
            $current_cal_week = $today->format("W");
            $post_title = "Carreritas semana #" . $current_cal_week;
            $post_msg = "";
            echo "Entries for current calendar week (" . $today->format("Y-m-d") . ", CW " . $current_cal_week ."):<br/>\n";
            foreach ($spreadsheet_data as $row) {
                if(!empty($row["Fecha"]) && !empty($row["Foreros"])) { // We need valid date and people running!
                    $norm_fecha = str_replace("/", "-", $row["Fecha"]);
                    $loop_cal_week = getCalendarWeek($norm_fecha);
                    if ($loop_cal_week == $current_cal_week) {
                        // 23/03/2013 16:30 Medio Maratón Azkoitia-Azpeitia 21097m Azpeitia (Gipuzkoa) Vredaman, Sukarr
                        $entry = $row["Fecha"] . " " . $row["Hora inicio"];
                        if(!empty($row["Observaciones"])) {
                            // Check that is a valid url! http://de3.php.net/manual/en/filter.filters.validate.php
                            if (filter_var($row["Observaciones"], FILTER_VALIDATE_URL) === FALSE) {
                                echo $row["Observaciones"] . " seems not a valid url!";
                            } else {
                                $entry .= " [url=" . $row["Observaciones"] . "]" . $row["Prueba"] . "[/url] ";
                            }
                        } else {
                            $entry .= " " . $row["Prueba"] . " ";
                        }
                        $entry .= $row["Distancia"] . " " . $row["Localidad"] . " [b]" . $row["Foreros"] . "[/b]";
                        $post_msg .= $entry . "\n";
                    }
                }
            }
            if(!empty($post_msg)) {
?>
                <blockquote style="border:1px solid #D4D4D4;background-color:#E5EECC;padding:15px 5px;">
                    <?php echo $post_msg; ?>
                </blockquote>
                <blockquote style="border:1px solid #d6cfb7;background-color:#eee6cc;padding:15px 5px;">
                    <?php echo bbc2html($post_msg); ?>
                </blockquote>
<?php
                // Only one post per day -> lock file!
                //$today = new DateTime("now", new DateTimeZone('Europe/Madrid'));
                $date_string = $today->format("Ymd");
                if(!file_exists($date_string)) {
                    $vbff = new vBForumFunctions(FORUM_BASE_URL);
                    // Check cookie
                    if ($vbff->loggedin) {
                        echo "Cookie exists. Trying to get security token... ";
                        if ($vbff->getSecurityToken()){
                            echo "OK: " . $vbff->securitytoken . "<br/>\n";
                        } else {
                            throw new Exception("ERROR: No security token");
                        }
                    } else {
                        echo "Cookie not found, trying to log in... ";
                        if($vbff->login(FORUM_USERNAME, FORUM_PASSWORD)) {
                            echo "OK<br/>\n";
                        } else {
                            throw new Exception("Error: unable to log in!");
                        }
                    }
                    echo "Trying to post... ";
                    if(!$vbff->posts->postReply(FORUM_THREAD, $post_msg, $post_title)) {
                    //if(!false){
                        echo "Error: something went wrong when trying to post!";
                    } else {
                        // Check directory permissions!!
                        if(!touch($date_string)) {
                            echo "Couldn't create file " . $date_string . " under " . realpath(dirname(__FILE__));
                        } else {
                            echo "OK<br/>\n";
                        }
                    }
                } else {
                    echo "Error: only one post per day";
                }
            } else {
                echo "Error: no valid message for CW #" . $current_cal_week . ", nothing to post";
            }
        } else {
            echo "No data available for CW #" . $current_cal_week . ", nothing to post";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
?>
    </body>
</html>
