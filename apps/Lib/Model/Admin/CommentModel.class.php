<?php

class CommentModel extends RelationModel
{

    protected $_link = array(
        'Member'=>array(
            'mapping_type'   => BELONGS_TO,
            'class_name'     => 'Member',
            'foreign_key'    => 'userid',
            'mapping_fields' => 'username',
            'as_fields'      => 'username',
        ),

        'Product'=>array(
            'mapping_type'   => BELONGS_TO,
            'class_name'     => 'Product',
            'foreign_key'    => 'product_id',
            'mapping_fields' => 'title',
            'as_fields'      => 'title:product_name',
        ),

    );

}