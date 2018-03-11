<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class CategoryModel extends RelationModel{

    protected $_link = array(
        "category" => array(
            "mapping_type" => self::BELONGS_TO,
            "foreign_key"  => "id",
            "parent_key"=>"parent",
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