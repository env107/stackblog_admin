<?php
return array(
	//'配置项'=>'配置值'
    /* 数据库设置 */
    'DB_TYPE'               =>  'Mysql',     // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'stackblog',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'stk_',    // 数据库表前缀
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8

    'TMPL_L_DELIM'          =>  '<{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}>',            // 模板引擎普通标签结束标记

    "URL_MODEL"=>3,
    'DEFAULT_MODULE' => 'Admin',
    'DEFAULT_CONTROLLER' => 'Index',
    'LOGIN_EXPIRES'=>60*60*1,
    
    

);