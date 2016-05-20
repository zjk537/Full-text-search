<?php
require_once 'sql.php';

function getDBConn()
{
    $db_mysql = new db_mysql();
    $db_mysql->connect('210.14.149.2', 'hask', '2CZbfFqrALAbWMLw', 'hask');
    return $db_mysql;
}

function getTotalCount()
{
    $dbConn = getDBConn();
    $result = $dbConn->select('SELECT count(0) FROM _store_yd_chapt');
    if ($result) {
        return $result[0][0];
    }
    return 0;
}

function getPageResults($startIndex, $pageSize)
{
    $dbConn   = getDBConn();
    $result   = $dbConn->select('SELECT id,chapt_title,chapt_brief,chapt_content,chapt_reporter,chapt_time FROM _store_yd_chapt order by chapt_time LIMIT ' . $startIndex . ',' . $pageSize);
    $pResults = array();
    if ($result) {
        foreach ($result as $key => $value) {
            $pResults[$key]['id']             = $value['id'];
            $pResults[$key]['chapt_title']    = removeHtml($value['chapt_title']);
            $pResults[$key]['chapt_brief']    = removeHtml($value['chapt_brief']);
            $pResults[$key]['chapt_content']  = removeHtml($value['chapt_content']);
            $pResults[$key]['chapt_reporter'] = $value['chapt_reporter'];
            $pResults[$key]['chapt_time']     = $value['chapt_time'];
        }
    }
    return $pResults;
}

function getSingleResult($id)
{
    $dbConn = getDBConn();
    $result = $dbConn->select('SELECT id,chapt_title,chapt_brief,chapt_content,chapt_reporter,chapt_time FROM _store_yd_chapt WHERE id = ' . $id);
    if ($result) {
        $sResult['id']             = $result[0]['id'];
        $sResult['chapt_title']    = removeHtml($result[0]['chapt_title']);
        $sResult['chapt_brief']    = removeHtml($result[0]['chapt_brief']);
        $sResult['chapt_content']  = removeHtml($result[0]['chapt_content']);
        $sResult['chapt_reporter'] = $result[0]['chapt_reporter'];
        $sResult['chapt_time']     = $result[0]['chapt_time'];
    }
    return $sResult;
}

function removeHtml($str)
{
    return str_replace(array("\r\n", "\r", "\n", "\t"), '', strip_tags($str));
}
