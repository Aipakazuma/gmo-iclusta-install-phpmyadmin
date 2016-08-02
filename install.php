<?php

$downloadFileName = 'phpMyAdmin-4.0.10.16-all-languages.tar.gz';
$downloadUrl = 'https://files.phpmyadmin.net/phpMyAdmin/4.0.10.16/' . $downloadFileName;

$downloadRenameFilePath = __DIR__ . '/' . $downloadFileName;

$databaseHost = '';

$phpMyAdminConfig = <<<EOT
<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * phpMyAdmin sample configuration, you can use it as base for
 * manual configuration. For easier setup you can use setup/
 *
 * All directives are explained in documentation in the doc/ folder
 * or at <http://docs.phpmyadmin.net/>.
 *
 * @package PhpMyAdmin
 */

/*
 * This is needed for cookie based authentication to encrypt password in
 * cookie
 */
\$cfg['blowfish_secret'] = 'a8b7c6d'; /* YOU MUST FILL IN THIS FOR COOKIE AUTH! */

/*
 * Servers configuration
 */
\$i = 0;

/*
 * First server
 */
\$i++;
/* Authentication type */
\$cfg['Servers'][\$i]['auth_type'] = 'cookie';
/* Server parameters */
\$cfg['Servers'][\$i]['host'] = '$databaseHost';
\$cfg['Servers'][\$i]['connect_type'] = 'tcp';
\$cfg['Servers'][\$i]['compress'] = false;
/* Select mysql if your server does not have mysqli */
\$cfg['Servers'][\$i]['extension'] = 'mysql';
\$cfg['Servers'][\$i]['AllowNoPassword'] = false;

/*
 * phpMyAdmin configuration storage settings.
 */

/* User used to manipulate with storage */
// \$cfg['Servers'][\$i]['controlhost'] = '';
// \$cfg['Servers'][\$i]['controluser'] = 'pma';
// \$cfg['Servers'][\$i]['controlpass'] = 'pmapass';

/* Storage database and tables */
// \$cfg['Servers'][\$i]['pmadb'] = 'phpmyadmin';
// \$cfg['Servers'][\$i]['bookmarktable'] = 'pma__bookmark';
// \$cfg['Servers'][\$i]['relation'] = 'pma__relation';
// \$cfg['Servers'][\$i]['table_info'] = 'pma__table_info';
// \$cfg['Servers'][\$i]['table_coords'] = 'pma__table_coords';
// \$cfg['Servers'][\$i]['pdf_pages'] = 'pma__pdf_pages';
// \$cfg['Servers'][\$i]['column_info'] = 'pma__column_info';
// \$cfg['Servers'][\$i]['history'] = 'pma__history';
// \$cfg['Servers'][\$i]['table_uiprefs'] = 'pma__table_uiprefs';
// \$cfg['Servers'][\$i]['tracking'] = 'pma__tracking';
// \$cfg['Servers'][\$i]['designer_coords'] = 'pma__designer_coords';
// \$cfg['Servers'][\$i]['userconfig'] = 'pma__userconfig';
// \$cfg['Servers'][\$i]['recent'] = 'pma__recent';
/* Contrib / Swekey authentication */
// \$cfg['Servers'][\$i]['auth_swekey_config'] = '/etc/swekey-pma.conf';

/*
 * End of servers configuration
 */

/*
 * Directories for saving/loading files from server
 */
\$cfg['UploadDir'] = '';
\$cfg['SaveDir'] = '';

/**
 * Defines whether a user should be displayed a "show all (records)"
 * button in browse mode or not.
 * default = false
 */
//\$cfg['ShowAll'] = true;

/**
 * Number of rows displayed when browsing a result set. If the result
 * set contains more rows, "Previous" and "Next".
 * default = 30
 */
//\$cfg['MaxRows'] = 50;

/**
 * disallow editing of binary fields
 * valid values are:
 *   false    allow editing
 *   'blob'   allow editing except for BLOB fields
 *   'noblob' disallow editing except for BLOB fields
 *   'all'    disallow editing
 * default = blob
 */
//\$cfg['ProtectBinary'] = 'false';

/**
 * Default language to use, if not browser-defined or user-defined
 * (you find all languages in the locale folder)
 * uncomment the desired line:
 * default = 'en'
 */
//\$cfg['DefaultLang'] = 'en';
//\$cfg['DefaultLang'] = 'de';

/**
 * default display direction (horizontal|vertical|horizontalflipped)
 */
//\$cfg['DefaultDisplay'] = 'vertical';


/**
 * How many columns should be used for table display of a database?
 * (a value larger than 1 results in some information being hidden)
 * default = 1
 */
//\$cfg['PropertiesNumColumns'] = 2;

/**
 * Set to true if you want DB-based query history.If false, this utilizes
 * JS-routines to display query history (lost by window close)
 *
 * This requires configuration storage enabled, see above.
 * default = false
 */
//\$cfg['QueryHistoryDB'] = true;

/**
 * When using DB-based query history, how many entries should be kept?
 *
 * default = 25
 */
//\$cfg['QueryHistoryMax'] = 100;

/*
 * You can find more configuration options in the documentation
 * in the doc/ folder or at <http://docs.phpmyadmin.net/>.
 */
?>
EOT;

/**
 * @param $url
 * @param $downloadRenameFilePath
 */
function curlGet($url, $downloadRenameFilePath)
{
    $ch = curl_init($url);
    $tmp = tmpfile();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$filename) {
            $regex = '/^Content-Disposition: attachment; filename="*(.+?)"*$/i';
            if (preg_match($regex, $header, $matches)) {
                $filename = rtrim($matches[1]);
            }
            return strlen($header);
        },
        CURLOPT_FOLLOWLOCATION => true,   // リダイレクトをたどる(302のときとかLocationヘッダをたどる)
        CURLOPT_MAXREDIRS => 5,           // CURLOPT_FOLLOWLOCATIONでたどる最大数
        CURLOPT_FILE => $tmp,             // 転送内容が書き込まれるファイル
        CURLOPT_FAILONERROR => true,      // 400以上のコードが返ってきたら失敗と判断する
        CURLOPT_SSL_VERIFYPEER => false,  // 証明書の検証を行わない
    ]);
    try {
        if (!curl_exec($ch)) {
            throw new ErrorException(curl_error());
            /**
             * 取れない場合もあるらしい。。。？
             * } elseif ($filename === null) {
             * throw new ErrorException('ヘッダのContent-Dispositionフィールドからファイル名を取得できませんでした。');
             *
             */
        } elseif (!@rename(stream_get_meta_data($tmp)['uri'], $downloadRenameFilePath)) {
            throw new ErrorException(error_get_last()['message']);
        }
    } catch (ErrorException $e) {
        echo $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
        return false;
    }

    return true;
}

/**
 * main関数
 */
function main($array)
{
    extract($array);
    try {
        // ファイルをダウンロード
        if (! curlGet($downloadUrl, $downloadRenameFilePath)) {
            throw new Exception('ファイルダウンロードに失敗しました。');
        }

        // 展開コマンド実行
        $tarOutput = '';
        $tarStatus = false;
        exec('tar zxvf ' . $downloadRenameFilePath, $tarOutput, $tarStatus);
        if ($tarStatus !== 0) {
            throw new Exception('解凍に失敗しました。');
        }

        // 設定ファイルを作成
        $phpMyAdminPath = str_replace('.tar.gz', '', $downloadRenameFilePath);
        $fp = fopen($phpMyAdminPath . '/' . 'config.inc.php', 'a');
        fwrite($fp, $phpMyAdminConfig);
        fclose($fp);

    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
}

// start
main(compact('downloadUrl', 'downloadRenameFilePath', 'phpMyAdminConfig'));
