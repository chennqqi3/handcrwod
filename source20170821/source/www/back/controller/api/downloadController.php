<?php

/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 14/08/2017
 * Time: 13:18
 */
class downloadController extends APIController
{
    public function __construct(){
        parent::__construct();
    }

    public function checkPriv($action, $utype, $priv_group = UTYPE_NONE, $priv = UTYPE_NONE)
    {
        parent::checkPriv($action, UTYPE_NONE);
    }
    public function link()
    {
        $fpath = $_GET['path'];
        $arrPath = explode("/", $fpath);
        var_dump($arrPath);

        if (count($arrPath) < 4)
        {
            print "該当ファイルは存在しません。";
            exit;
        }
        $path = ATTACH_PATH . $arrPath[0] . "/" . $arrPath[1] . "/" . $arrPath[2];
        if (!file_exists($path))
        {
            print "該当ファイルは存在しません。";
            exit;
        }
        $filename = $arrPath[3];
        $sz = filesize($path);
        $fp = fopen($path, "rb");

        if ($fp) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            ob_clean();
            flush();
            readfile($path);
            exit;
        }
    }

}