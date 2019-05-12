<!doctype html>
<html lang="en">

<head>
    <title>Get Comment</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body>
    <div class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light main-menu">
            <a class="navbar-brand" href="<?php $_PHP_SELF ?>">Nhóm 3</a>
            <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false" aria-label="Toggle navigation"></button>
            <div class="collapse navbar-collapse" id="collapsibleNavId">
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php $_PHP_SELF ?>">Trang chủ </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    <div class="content my-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <form method="post">
                        <div class="form-group">
                            <label for="token">Access Token</label>
                            <input type="text" class="form-control" name="token" id="token" placeholder="EAAA...">
                        </div>
                        <div class="form-group">
                            <label for="groupId">ID Nhóm</label>
                            <input type="text" class="form-control" name="groupId" id="groupId" placeholder="123456">
                        </div>
                        <div class="form-group">
                            <label for="hashtag">Hashtag cần quét</label>
                            <input type="text" class="form-control" name="hashtag" id="hashtag" placeholder="#abc">
                        </div>
                        <div class="form-group">
                            <button name="scanHashtag" id="scanHashtag" class="btn btn-primary">Quét</button>
                        </div>
                    </form>
                </div>
                <?php
                if (isset($_POST['scanHashtag'])) {
                    set_time_limit(0);
                    error_reporting(0);
                    $token = $_POST["token"];
                    $id_group = $_POST["groupId"];
                    $hashtag = $_POST["hashtag"];
                    $HashTag_Link = "https://j2team.junookyo.com/hashtags.html";
                    $Hashtag_Array = [
                        "Hmm, có vẻ bài viết còn thiếu cái gì đó thì phải. À đúng rồi là hashtag đó ",
                        "1,2,3,5... Anh có đánh rơi nhịp nào không? Rơi hashtag rồi kìa ",
                        "Thiếu hashtag = vé đi ra đảo mát. Bổ sung ngay nhé ",
                        "Ơ kìa, hashtag đâu rồi? Hãy thêm hashtag vì một cộng đồng tiến hóa nhé ",
                        "Bài viết không có hashtag kìa. Thêm vào ngay nhé ",
                    ];
                    $post = json_decode(request('https://graph.facebook.com/v3.2/' . $id_group . '/feed?fields=id,message,created_time,from&limit=100&access_token=' . $token), true);
                    $timelocpost = date('Y-m-d');
                    $logpost     = file_get_contents("log.txt");
                    $admin       = file_get_contents("ul.txt");

                    for ($i = 0; $i < 100; $i++) {
                        $idpost      = $post['data'][$i]['id'];
                        $messagepost = $post['data'][$i]['message'];
                        $time        = $post['data'][$i]['created_time'];
                        $uid         = $post['data'][$i]['from']['id'];
                        /*Check ngoại lệ*/
                        if (strpos($admin, $uid) === FALSE) {
                            /* Check time Post */
                            if (strpos($time, $timelocpost) !== false) {
                                /* Check hashtag */
                                if (strpos(strtolower($messagepost), $hashtag) === FALSE) {
                                    /* Check trùng  */
                                    if (strpos($logpost, $idpost) === FALSE) {
                                        /* Send Comment  */
                                        $name = $post['data'][$i]['from']['name'];
                                        $Hashtag_List = "\n" . 'Danh sách hashtag bắt buộc sử dụng khi đăng bài: ' . "\n" . $HashTag_Link;
                                        $randIndex = array_rand($Hashtag_Array);
                                        $comment = $Hashtag_Array[$randIndex] . $name . "." . $Hashtag_List;
                                        request('https://graph.facebook.com/' . urlencode($idpost) . '/comments?method=post&message=' . urlencode($comment) . '&access_token=' . $token);
                                        $luulog = fopen("log.txt", "a");
                                        fwrite($luulog, $idpost . "\n");
                                        fclose($luulog);
                                    }
                                }
                            }
                        } else {
                            $luulog = fopen("log.txt", "a");
                            fwrite($luulog, $idpost . "\n");
                            fclose($luulog);
                            echo 'admin';
                        }
                    }
                }
                function request($url)
                {
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        return FALSE;
                    }

                    $options = array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HEADER => FALSE,
                        CURLOPT_FOLLOWLOCATION => TRUE,
                        CURLOPT_ENCODING => '',
                        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
                        CURLOPT_AUTOREFERER => TRUE,
                        CURLOPT_CONNECTTIMEOUT => 15,
                        CURLOPT_TIMEOUT => 15,
                        CURLOPT_MAXREDIRS => 5,
                        CURLOPT_SSL_VERIFYHOST => 2,
                        CURLOPT_SSL_VERIFYPEER => 0
                    );

                    $ch = curl_init();
                    curl_setopt_array($ch, $options);
                    $response  = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    unset($options);
                    return $http_code === 200 ? $response : FALSE;
                }
                ?>
                <div class="col-md-12">
                    <h3>Danh sách ID bài viết đã được nhắc nhở:</h3>
                    <?php
                    $array = explode("\n", file_get_contents('log.txt'));
                    foreach ($array as $value) {
                        if ($value != "") echo "<li><a href='https://facebook.com/$value' target='_blank'>" . $value . "</a></li>";
                    };
                    ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.3.1.slim.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-3.3.1.js"></script>
</body>