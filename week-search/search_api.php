<?php
/**
 *
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-29
 */
require_once dirname(__FILE__) . '/lib/helper.php';
// 支持的 GET 参数列表
// k: 查询语句
// t: 过去xx内
// order: 排序方式
// p: 显示第几页，每页数量为 XSSearch::PAGE_SIZE 即 10 条

//
// variables
$attr = array();
$eu   = '';
$__   = array('k', 't', 'order', 'p');
foreach ($__ as $_) {
    $$_ = isset($_GET[$_]) ? $_GET[$_] : '';
}
$attr      = array();
$k         = get_magic_quotes_gpc() ? stripslashes($k) : $k;
$attr['k'] = $k;

$resultData = array();
try {

    $xs     = new XS(XS_CONF);
    $search = $xs->search;
    $search->setCharset('utf-8');
    if (empty($k)) {
        // just show hot query
        $hot = $search->getHotQuery(10);
    } else {
        // fuzzy search
        $search->setFuzzy(false);

        // set query
        $search->setQuery($k);
        // sort?
        if ($order) {
            if ($order == "acs") {
                $search->setSort('chapt_time', true);
            } else {
                $search->setSort('chapt_time');
            }
            $attr['order'] = $order;
        } else {
            $search->setSort('chapt_time');
        }

        $p = max(1, intval($p));
        $n = 20;
        $search->setLimit($n, ($p - 1) * $n);

        // get the result
        $search_begin = microtime(true);
        $docs         = $search->search();
        $search_cost  = microtime(true) - $search_begin;

        $results = array();
        foreach ($docs as $key => $val) {
            $results[$key]['id']             = $val['id'];
            $results[$key]['chapt_title']    = $search->highlight(htmlentities($val['chapt_title'], ENT_COMPAT, 'utf-8'));
            $results[$key]['chapt_brief']    = $search->highlight(htmlentities($val['chapt_brief'], ENT_COMPAT, 'utf-8'));
            $results[$key]['chapt_content']  = $search->highlight(htmlentities($val['chapt_content'], ENT_COMPAT, 'utf-8'));
            $results[$key]['chapt_reporter'] = $val['chapt_reporter'];
            $results[$key]['chapt_time']     = $val['chapt_time'];
        }
        $resultData['results'] = $results;

        // get other result
        $resultData['count'] = $search->getLastCount();
        $resultData['total'] = $search->getDbTotal();

        // try to corrected, if resul too few
        if ($count < 1 || $count < ceil(0.001 * $total)) {
            $corrected = $search->getCorrectedQuery();
        }

        // get related query
        $resultData['related'] = $search->getRelatedQuery();
    }
} catch (Exception $e) {
    echo 'Error:' . $e->getMessage() . "<br/>";
}

echo json_encode($resultData);
