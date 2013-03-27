<!DOCTYPE html>
<html lang="es">
    <head> 
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
        <title>Mataete</title> 
    </head>
    <body>
<?php
    require_once "vbfunctions.php";
    require_once "utils.php";
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
            if(!empty($row["Fecha"])) { // We need a valid date!
                $norm_fecha = str_replace("/", "-", $row["Fecha"]);
                $loop_cal_week = getCalendarWeek($norm_fecha);
                if ($loop_cal_week == $current_cal_week) {
                    // 23/03/2013 16:30 Medio Maratón Azkoitia-Azpeitia 21097m Azpeitia (Gipuzkoa) Vredaman, Sukarr
                    # entry is in bbcode, display in html
                    $entry = $row["Fecha"] . " " . $row["Hora inicio"];
                    $display = $row["Fecha"] . " " . $row["Hora inicio"];
                    if(!empty($row["Observaciones"])) {
                        // Check that is a valid url! http://de3.php.net/manual/en/filter.filters.validate.php
                        if (filter_var($row["Observaciones"], FILTER_VALIDATE_URL) === FALSE) {
                            echo $row["Observaciones"] . " seems not a valid url!";
                        } else {
                            $entry .= " [URL=\"" . $row["Observaciones"] . "\"]" . $row["Prueba"] . "[/URL] ";
                            $display .= " <a href=\"" . $row["Observaciones"] . "\">" . $row["Prueba"] . "</a> ";
                        }
                    } else {
                        $entry .= " " . $row["Prueba"] . " ";
                        $display .= " " . $row["Prueba"] . " ";
                    }
                    $entry .= $row["Distancia"] . " " . $row["Localidad"] . " [B]" . $row["Foreros"] . "[/B]";
                    $display .= $row["Distancia"] . " " . $row["Localidad"] . " <strong>" . $row["Foreros"] . "</strong><br/>";
                    echo $display;
                    $post_msg .= $entry . "\n";
                }
            }
        }
        if(!empty($post_msg)) {
?>
            <form action="formPanel.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="week_panel">
                <input type="hidden" name="forum_base_url" value="<?php echo FORUM_BASE_URL; ?>">
                <input type="hidden" name="forum_username" value="<?php echo FORUM_USERNAME; ?>">
                <input type="hidden" name="forum_password" value="<?php echo FORUM_PASSWORD; ?>">
                <input type="hidden" name="forum_thread" value="<?php echo FORUM_THREAD; ?>">
                <input type="hidden" name="post_msg" value="<?php echo urlencode($post_msg); ?>">
                <input type="hidden" name="post_title" value="<?php echo $post_title; ?>">
                Para evitar abusos: ¿cuánto suman dos más dos? <input id="answer" name="answer" type="text" placeholder="bufff..."/>
                <input type="submit" id="submit_button" value="Postear panel"/>
	        </form>
<?php
        } else {
            echo "No message built for CW #" . $current_cal_week . ", nothing to post";
        }
    } else {
        echo "ERROR!<br/>\n";
    }
?>
    </body>
</html>
