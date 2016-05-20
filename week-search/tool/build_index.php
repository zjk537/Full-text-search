<?php
/**
 *
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-27
 */

require_once 'cbnweek_funcs.php';
require_once '../lib/helper.php';

$startTime = time();

$count = 0; // show progess
$total = getTotalCount();

$i     = 0;
$pSize = 100;
$quit  = false;

try {
    $xs       = new XS(XS_CONF);
    $objIndex = $xs->index;
} catch (Exception $e) {
    echo 'Error:' . $e->getMessage() . "\n";
}

while ($total > 0) {

    // the last page chang page size
    if (($i + $pSize) > $total) {
        $quit  = true;
        $pSize = $total - $i;
    }

    $pResults = getPageResults($i, $pSize);
    foreach ($pResults as $key => $val) {

        try {
            xsImportData($objIndex, $val);
            ++$count;
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage() . "<br/>";
        }
    }

    $i += $pSize;

    echo 'Index created:' . $i . ' count:' . $count . ' total:' . $total . '---' . (int) ($count / $total * 100) . "%<br/>";
    if ($quit) {
        break;
    }
}

echo 'Spend Time:' . time() - $startTime . "<br/>";
