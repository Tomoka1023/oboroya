<?php
// サイトを置いたサブディレクトリ。ドメイン直下なら ''（空文字）にする
define('BASE_PATH', '/oboroya');   // 例: https://example.com/oboroya/ に置いた場合
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].BASE_PATH);
?>