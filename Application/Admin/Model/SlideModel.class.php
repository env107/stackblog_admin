<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class SlideModel extends RelationModel{

    protected $_link = array(
        "category" => array(
            "mapping_type" => self::BELONGS_TO,
            "foreign_key"  => "cid",
            "parent_key"=>"id",
            "mapping_fields"=>"title,nickname"
        ),
        "admin" => array(
            "mapping_type" => self::BELONGS_TO,
            "foreign_key"  => "creater",
            "parent_key"=>"id",
            "mapping_fields"=>"name"
        ),

    );

}