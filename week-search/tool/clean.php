<?php
/**
 * clean xusearch index
 * @authors zjk (zhangjie1@yicai.com)
 * @date    2016-04-28
 */

require_once '../lib/helper.php';

$xs    = new XS(XS_CONF);
$index = $xs->index;
$index->clean();
$data = array(
    'id'             => 0,
    'chapt_title'    => 0,
    'chapt_brief'    => 0,
    'chapt_content'  => 0,
    'chapt_reporter' => 0,
    'chapt_time'     => 0,
);

$doc = new XSDocument;
$doc->setFields($data);
$index->add($doc);
echo "success";
