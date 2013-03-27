<?php
    require_once "vbfunctions.php";
/*
curl -v -A "Mozilla/5.0 (X11; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0" -d vb_login_username=mataete -d vb_login_password= -d vb_login_password_hint=Contrase%C3B1a -d securitytoken=guest -d do=login -d vb_login_md5password=9eb22467d3920e91a945020f8acf7553 -d vb_login_md5password_utf=9eb22467d3920e91a945020f8acf7553 http://www.elatleta.com/foro/login.php
*/

    $form_data = $_REQUEST;
    $redirection = "weekly_races.php";
    $result_msg = "Message has been successfully posted";
    try {
        switch($form_data["action"]) {
            case "week_panel":
                $forum_base_url = $form_data["forum_base_url"];
                $forum_username = $form_data["forum_username"];
                $forum_password = $form_data["forum_password"];
                $forum_thread = $form_data["forum_thread"];
                $post_msg = urldecode($form_data["post_msg"]);
                $post_title = $form_data["post_title"];
                $security_answer = $form_data["answer"];

                //TODO: implement a way to prevent more than 1 post per day -> touch <date>?
                if($security_answer == "cuatro") {
                    $vbff = new vBForumFunctions($forum_base_url);
                    if(!$vbff->login($forum_username, $forum_password)) {
                        $result_msg = "Error: unable to log in!";
                        break;
                    }
                    if (!$vbff->posts->postReply($forum_thread, $post_msg, $post_title)) {
                        $result_msg = "Error: something went wrong when trying to post!";
                    }
                } else {
                    $result_msg = "Error: no valid answer for security question: ". $security_answer;
                }
                $redirection .= "?result=" . urlencode($result_msg);
                break;
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    header("Location: " . $redirection);
exit();
?>
