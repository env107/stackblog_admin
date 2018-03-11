<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AdminModel extends RelationModel{

    protected $_link = array(
        "admin" => array(
            "mapping_type" => self::BELONGS_TO,
            "foreign_key"  => "id",
            "parent_key"=>"creater",
            "mapping_fields"=>"name"
        ),

    );

}