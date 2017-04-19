

<?php
    $url = 'http://image.baidu.com/channel/listjson?pn='. $_GET['pn'] .
            '&rn='. $_GET['rn'] .
            '&tag1='. $_GET['tag1'] .
            '&tag2=全部&ftags='. $_GET['tag2'] .'&ie=utf8';
    echo file_get_contents($url);
