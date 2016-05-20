<?php
/**
 *
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-27
 */

require_once 'XS.php';

define('XS_CONF', 'cbnweek');

function xsImportData($objIndex, $data, $isUpdate = false)
{
    $title   = $data['chapt_title'];
    $id      = $data['id'];
    $content = $data['chapt_content'];

    if (!is_string($content)) {
        throw new XSException('Invalid chapt: ' . $id . ' ---' . $title);
    }

    $doc = new XSDocument;
    $doc->setFields($data);
    $doc->setCharset("utf-8");
    if ($isUpdate) {
        $objIndex->update($doc);
    } else {
        $objIndex->add($doc);
    }
}

function xsDelData($objIndex, $ids)
{
    try {
        $objIndex->del($ids);
    } catch (Exception $e) {
        throw new XSException('Delete index exception: ' . $e->getMessage());
    }
}
