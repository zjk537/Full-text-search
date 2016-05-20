<?php
/**
 * build xs single index
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-29
 */
require_once 'cbnweek_funcs.php';
require_once '../lib/helper.php';

$q = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($q)) {
    echo "param ids is empty";
    exit();
}

$ids  = array();
$qids = explode(',', $q);
$i    = 0;
foreach ($qids as $id) {
    if ((int) $id != 0) {
        $ids[$i] = (int) $id;
        $i++;
    }
}
import_post($ids);

function import_post($ids)
{
    try {
        $xs       = new XS(XS_CONF);
        $objIndex = $xs->index;
        xsDelData($objIndex, $ids);
        echo "success";
    } catch (Exception $e) {
        echo 'Error:' . $e->getMessage() . "\n";
    }
}
