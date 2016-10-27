<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

class Helper_CSV {
    
    public static function getFieldGmoMemberCSV(){
        return array(
            array(
                'entity_name' => 'Plugin\\GmoPaymentGateway\\Entity\\GmoMember',
                'field_name' => 'Customer',
                'disp_name' => '会員ID',
                'reference_field_name' => 'id'
            ),
            array(
                'entity_name' => 'Plugin\\GmoPaymentGateway\\Entity\\GmoMember',
                'field_name' => 'name',
                'disp_name' => '会員名'
            ),
            array(
                'entity_name' => 'Plugin\\GmoPaymentGateway\\Entity\\GmoMember',
                'field_name' => 'new_member_id',
                'disp_name' => 'GMOメンバーID（新）'
            ),
            array(
                'entity_name' => 'Plugin\\GmoPaymentGateway\\Entity\\GmoMember',
                'field_name' => 'old_member_id',
                'disp_name' => 'GMOメンバーID（旧）'
            ),
            array(
                'entity_name' => 'Plugin\\GmoPaymentGateway\\Entity\\GmoMember',
                'field_name' => 'customer_create_date',
                'disp_name' => '登録日'
            )
        );
    }
}