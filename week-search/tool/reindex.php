<?php
/**
 * build xs single index
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-29
 */
require_once 'cbnweek_funcs.php';
require_once '../lib/helper.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
import_post($id);

function import_post($id)
{
    $objData = getSingleResult($id);
    try {
        $xs       = new XS(XS_CONF);
        $objIndex = $xs->index;
        xsImportData($objIndex, $objData, true);
        echo "success";
    } catch (Exception $e) {
        echo 'Error:' . $e->getMessage() . "\n";
    }
}
