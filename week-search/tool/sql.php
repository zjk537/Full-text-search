<?php
class db_mysql
{
 var $connid;
 var $dbname;
 var $querynum = 0;
 var $debug = 1;
 var $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
 var $replace = array('union   select', 'load_file   (', 'into   outfile');

 /**
  * @desc 连接mysql数据库
  *
  * @return resource
  */
 function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = '')
 {
  $func = $pconnect == 1 ? 'mysql_pconnect' : 'mysql_connect';
  if(!$this->connid = @$func($dbhost, $dbuser, $dbpw))
  {
   $this->halt('Can not connect to MySQL server');
   return false;
  }
  mysql_query("set names utf8");
  if($this->version() > '4.1')
  {
   //character_set_connection: 连接数据库的字符集设置类型，如果php没有指明连接数据库使用的字符集类型就按照服务器端默认的字符设置
   //character_set_results: 数据库给客户端返回时使用的字符集设定，如果没有指明，使用服务器默认的字符集
   //character_set_client: 客户端使用的字符集，相当于网页中的字符集设置.如果character_set_clien指定的是binary，则MySQL就会把SQL语句按照character_set_connection指定的编码解释执行.
   $serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
   $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
   //通过mysql_query设置字符集
   $serverset && mysql_query("SET $serverset", $this->connid);
  }
  if($dbname && !@mysql_select_db($dbname , $this->connid))
  {
   $this->halt('Cannot use database '.$dbname);
   return false;
  }
  $this->dbname = $dbname;
  return $this->connid;
 }

 /**
  * @desc 选择数据库
  *
  * @return boolean
  */
 function select_db($dbname)
 {
  if(!@mysql_select_db($dbname , $this->connid)) return false;
  $this->dbname = $dbname;
  return true;
 }

 /**
     * @desc 发送一条 MySQL 查询
     *
     * @return resource
     */
 function query($sql , $type = '')
 {
  $func = $type == 'UNBUFFERED' ? 'mysql_unbuffered_query' : 'mysql_query';
  if(!($query = @$func($sql , $this->connid)) && $type != 'SILENT')
  {
   $this->halt('MySQL Query Error', $sql);
   return false;
  }
  $this->querynum++;
  return $query;
 }

 /**
  * @desc 发送一条 MySQL 查询，并返回关联数组
  *
  * @return array
  */
 function select($sql, $keyfield = '')
 {
  $array = array();
  $result = $this->query($sql);
  while($r = $this->fetch_array($result))
  {
   if($keyfield)
   {
    $key = $r[$keyfield];
    $array[$key] = $r;
   }
   else
   {
    $array[] = $r;
   }
  }
  $this->free_result($result);
  return $array;
 }

 /**
  * @desc 添加一条记录
  *
  * @return boolean
  */
 function insert($tablename, $array)
 {
  return $this->query("INSERT INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')");
 }

 /**
  * @desc 修改记录
  *
  * @return boolean
  */
 function update($tablename, $array, $where = '')
 {
  if($where)
  {
   $sql = '';
   foreach($array as $k=>$v)
   {
    $sql .= ", `$k`='$v'";
   }
   $sql = substr($sql, 1);
   $sql = "UPDATE `$tablename` SET $sql WHERE $where";
  }
  else
  {
   //REPLACE的运行与INSERT很相似。只有一点例外，假如表中的一个旧记录与一个用于PRIMARY KEY或一个UNIQUE索引的新记录具有相同的值，则在新记录被插入之前，旧记录被删除。
   $sql = "REPLACE INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')";
  }
  return $this->query($sql);
 }

 /**
  * @desc 返回表的主键
  *
  * @return string
  */
 function get_primary($table)
 {
  $result = $this->query("SHOW COLUMNS FROM $table");
  while($r = $this->fetch_array($result))
  {
   if($r['Key'] == 'PRI') break;
  }
  $this->free_result($result);
  return $r['Field'];
 }

 /**
  * @desc 返回表的所有字段
  *
  * @return array
  */
 function get_fields($table)
 {
  $fields = array();
  $result = $this->query("SHOW COLUMNS FROM $table");
  while($r = $this->fetch_array($result))
  {
   $fields[] = $r['Field'];
  }
  $this->free_result($result);
  return $fields;
 }

 /**
  * @desc 取得1条记录，返回记录集数组
  *
  * @return array
  */
 function get_one($sql, $type = '')
 {
  $query = $this->query($sql, $type);
  $rs = $this->fetch_array($query);
  $this->free_result($query);
  return $rs ;
 }

 /**
  * @desc 从结果集中取得一行作为关联数组，或数字数组，或二者兼有 
  *
  * @return array
  */
 function fetch_array($query, $result_type = MYSQL_BOTH)
 {
  return mysql_fetch_array($query, $result_type);
 }
 
 /**
  * @desc 取得上一步 INSERT 操作产生的 ID ,如果上一查询没有产生 AUTO_INCREMENT 的值，则 mysql_insert_id() 返回 0
  *
  * @return unknown
  */
 function insert_id()
 {
  return mysql_insert_id($this->connid);
 }
 
 /**
  * 以上的几个方法作用比较大
  */
 
 /**
  * @desc 取得前一次 MySQL 操作所影响的记录行数。对INSERT，UPDATE 或 DELETE 有效。
  *
  * @return int
  */
 function affected_rows()
 {
  return mysql_affected_rows($this->connid);
 }

 /**
  * @desc  取得结果集中行的数目,仅对 SELECT 语句有效。
  *
  * @param unknown_type $query
  * @return unknown
  */
 function num_rows($query)
 {
  return mysql_num_rows($query);
 }

 /**
  * @desc 取得结果集中字段的数目
  *
  * @return int
  */
 function num_fields($query)
 {
  return mysql_num_fields($query);
 }

 /**
  * @desc 返回 MySQL 结果集中一个单元的内容
  *
  * @return mixed
  */
 function result($query, $row)
 {
  return @mysql_result($query, $row);
 }

 /**
  * @desc 释放结果内存
  *
  * @return boolean
  */
 function free_result(&$query)
 {
  return mysql_free_result($query);
 }

 /**
  * @desc 从结果集中取得一行作为枚举数组
  *
  * @return array 
  */
 function fetch_row($query)
 {
  return mysql_fetch_row($query);
 }

 /**
  * @desc 返回安全的SQL语句字符串
  *
  * @return string
  */
 function escape($string)
 {
  if(!is_array($string))
  return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysql_real_escape_string(preg_replace($this->search, $this->replace, $string), $this->connid));
  foreach($string as $key=>$val)
  $string[$key] = $this->escape($val);
  return $string;
 }

 /**
  * @desc 查询表信息
  *
  * @return array
  */
 function table_status($table)
 {
  return $this->get_one("SHOW TABLE STATUS LIKE '$table'");
 }

 /**
  * @desc 显示所有的表
  *
  * @return array
  */
 function tables()
 {
  $tables = array();
  $result = $this->query("SHOW TABLES");
  while($r = $this->fetch_array($result))
  {
   $tables[] = $r['Tables_in_'.$this->dbname];
  }
  $this->free_result($result);
  return $tables;
 }

 /**
  * @desc 查询表是否存在
  *
  * @return boolean
  */
 function table_exists($table)
 {
  $tables = $this->tables($table);
  return in_array($table, $tables);
 }

 /**
  * @desc 检查字段是否存在表中
  *
  * @return boolean
  */
 function field_exists($table, $field)
 {
  $fields = $this->get_fields($table);
  return in_array($field, $fields);
 }
 /**
  * @desc 获取mysql版本
  */
 function version()
 {
  return mysql_get_server_info($this->connid);
 }
 /**
  * @desc 关闭 MySQL 连接
  */
 function close()
 {
  return mysql_close($this->connid);
 }

 /**
  * @desc 返回上一个 MySQL 操作产生的文本错误信息 
  * 
  * @return string
  */
 function error()
 {
  return @mysql_error($this->connid);
 }

 /**
  * @desc 返返回上一个 MySQL 操作中的错误信息的数字编码
  * 
  * @return int
  */
 function errno()
 {
  return intval(@mysql_errno($this->connid)) ;
 }

 /**
  * @desc 显示错误信息，还有点完善
  */
 function halt($message = '', $sql = '')
 {
  $this->errormsg = "<b>MySQL Query : </b>$sql <br /><b> MySQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message";
  if($this->debug)
  {
   $msg = (defined('IN_ADMIN') || DEBUG) ? $this->errormsg : "Bad Request. $LANG[illegal_request_return]";
   echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
   exit;
  }
 }
}
?>