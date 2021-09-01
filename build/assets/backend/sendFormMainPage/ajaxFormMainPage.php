<?php

define('TELEGRAM_TOKEN', '');                                                               //ID БОТА
define('TELEGRAM_CHATID', '');                                                              //ID Диалога
define('userfilesLink', '/assets/userfiles/video/');                                        //Директория файлов

function message_to_telegram($text) {
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => TELEGRAM_CHATID,
                'text' => $text,
            ),
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            CURLOPT_PROXYAUTH => CURLAUTH_BASIC,
        )
    );
    curl_exec($ch);
}

function clearPost($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function translit($s) {
    $s = (string) $s; 
    $s = strip_tags($s); 
    $s = str_replace(array("\n", "\r"), " ", $s); 
    $s = preg_replace("/\s+/", ' ', $s); 
    $s = trim($s); 
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); 
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
    $s = str_replace(" ", "-", $s); 
    return $s; 
  }


if($_POST['commandPost'] == "sendVideo"){
    $nameVideo = clearPost($_POST['nameVideo']);                           
    $phoneVideo = clearPost($_POST['phoneVideo']);
    $emailVideo = clearPost($_POST['emailVideo']);                            //Если хочешь, дописывай проверку почты и телефона через Preg_match, мне лень)
    $commentVideo = clearPost($_POST['commentVideo']);
    $videoCheckApproval = clearPost($_POST['videoCheckApproval']);
    if($nameVideo != "" && $phoneVideo != "" && $emailVideo != "" && $commentVideo != ""){
        if ($videoCheckApproval == true){
            $allow = array('mp4','webm','mov','mpeg','flv','wmv');
            $deny = array(
                'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
                'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
                'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
            );
            $path = $_SERVER['DOCUMENT_ROOT'].userfilesLink;
            if (!isset($_FILES['fileVideo'])) {
                echo "Ошибка загрузки файла. Повторите попытку позже.";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
            } else {
                if(filesize($_FILES['fileVideo']) < 1073741824){
                    $file = $_FILES['fileVideo'];
                    if (!empty($file['error']) || empty($file['tmp_name'])) {
                        echo "Не удалось загрузить файл.";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                    } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
                        echo "Не удалось загрузить файл.";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                    } else {

                        $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
                        $name = mb_eregi_replace($pattern, '-', $file['name']);
                        $name = mb_ereg_replace('[-]+', '-', $name);
                        $parts = pathinfo($name);
                        $extation = substr($name, strrpos($name, '.') + 1);

                        if (empty($name) || empty($parts['extension'])) {
                            echo "Недопустимый тип файла";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                        } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
                            echo "Недопустимый тип файла";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                        } elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
                            echo "Недопустимый тип файла";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                        } else {
                            $fileFullName = translit($nameVideo.$phoneVideo).'.'.$extation;
                            if (move_uploaded_file($file['tmp_name'], $path . $fileFullName)) {
                                echo "Файл успешно загружен и отправлен на модерацию";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                                $text = "Отправка видео с сайта: ".PHP_EOL.PHP_EOL."От:".PHP_EOL."ФИО: ".$nameVideo.PHP_EOL."Телефон: ".$phoneVideo.PHP_EOL."Email: ".$emailVideo.PHP_EOL."Комментарий: ".$commentVideo.PHP_EOL."Ссылка на видео: ".$_SERVER['HTTP_HOST'].userfilesLink.$fileFullName;
                                message_to_telegram($text);
                            } else {
                                echo "Не удалось загрузить файл.";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                            }
                        }
                    }
                }else{
                    echo "Крч размер не тот, давай по новой и тут тоже https://getbootstrap.com/docs/5.1/components/alerts/";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
                }
            }
        }else{
            echo "Вы забыли подписать согласие и тут тоже https://getbootstrap.com/docs/5.1/components/alerts/";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
        }
    }else{
        echo "Тут сделай ошибку заполнения, ввиде алерта https://getbootstrap.com/docs/5.1/components/alerts/";     //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
    }
}else if($_POST['commandPost'] == "sendDeal"){
    $dealFio = clearPost($_POST['dealFio']);                           
    $dealPhone = clearPost($_POST['dealPhone']);
    $dealEmail = clearPost($_POST['dealEmail']);                            //Если хочешь, дописывай проверку почты и телефона через Preg_match, мне лень)
    $dealComm = clearPost($_POST['dealComm']);
    $usernameDealFrom = clearPost($_POST['usernameDealFrom']);                           
    $phoneDealFrom = clearPost($_POST['phoneDealFrom']);
    $emailDealFrom = clearPost($_POST['emailDealFrom']);                            //Если хочешь, дописывай проверку почты и телефона через Preg_match, мне лень)
    $dealCheckApproval = clearPost($_POST['dealCheckApproval']);
    if($dealFio != "" && $dealPhone != "" && $dealEmail != "" && $dealComm != "" && $usernameDealFrom != "" && $phoneDealFrom != "" && $emailDealFrom != ""){
        if ($dealCheckApproval == true){
            $text = "Заявка на сертификат с сайта: ".PHP_EOL.PHP_EOL."Кому:".PHP_EOL."ФИО: ".$dealFio.PHP_EOL."Телефон: ".$dealPhone.PHP_EOL."Email: ".$dealEmail.PHP_EOL.PHP_EOL."От:".PHP_EOL."ФИО: ".$usernameDealFrom.PHP_EOL."Телефон: ".$phoneDealFrom.PHP_EOL."Email: ".$emailDealFrom.PHP_EOL."Комментарий: ".$dealComm.PHP_EOL;
            message_to_telegram($text);
            echo "Отправлено";                                 //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ SUCCESS В НОРМАЛЬНОМ ВИДЕ
        }else{
            echo "Вы забыли подписать согласие и тут тоже https://getbootstrap.com/docs/5.1/components/alerts/";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
        }
    }else{
        echo "Тут сделай ошибку заполнения, ввиде алерта https://getbootstrap.com/docs/5.1/components/alerts/";     //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
    }
}else if($_POST['commandPost'] == "sendQuest"){
    $usernameQuest = clearPost($_POST['usernameQuest']);                           
    $phoneQuest = clearPost($_POST['phoneQuest']);
    $emailQuest = clearPost($_POST['emailQuest']);                            //Если хочешь, дописывай проверку почты и телефона через Preg_match, мне лень)
    $commentQuest = clearPost($_POST['commentQuest']);
    $questCheckApproval = clearPost($_POST['questCheckApproval']);
    if($usernameQuest != "" && $phoneQuest != "" && $emailQuest != "" && $commentQuest != ""){
        if ($questCheckApproval == true){
            $text = "Вопрос с сайта: ".PHP_EOL.PHP_EOL."ФИО: ".$usernameQuest.PHP_EOL."Телефон: ".$phoneQuest.PHP_EOL."Email: ".$emailQuest.PHP_EOL."Комментарий: ".$commentQuest.PHP_EOL;
            message_to_telegram($text);
            echo "Отправлено";                                 //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ SUCCESS В НОРМАЛЬНОМ ВИДЕ
        }else{
            echo "Вы забыли подписать согласие и тут тоже https://getbootstrap.com/docs/5.1/components/alerts/";    //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
        }
    }else{
        echo "Тут сделай ошибку заполнения, ввиде алерта https://getbootstrap.com/docs/5.1/components/alerts/";     //ТУТ ОБРАТИ ВНИМАНИЕ, ЧТО НАДО СВЕРСТАТЬ ОШИБКУ В НОРМАЛЬНОМ ВИДЕ
    }
}else{
    header('Location: /');
}



?>

