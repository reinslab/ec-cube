<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\TransportCSVexportB2\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    private $app;
    private $subData;

    public function __construct(\Eccube\Application $app, $subData = null)
    {
        $this->app = $app;
        $this->subData = $subData;
    }

    /**
     * Build config type form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Result = $this->app['eccube.repository.payment']
            ->findBy(
                array('del_flg' => 0),
                array('rank' => 'DESC')
            );

        $Payments = array();
        foreach ($Result as $payment) {
            $Payments[$payment->getId()] = array(
                'id'     => $payment->getId(),
                'name'   => 'payment_type' . $payment->getId(),
                'pm' => $payment->getMethod(),
            );
        }

        if (empty($this->subData)) {
            $this->subData = array(
                'billing_code' => null,
                'fare_number' => null,
                'yamato_deliv_type' => null,
                'order_status' => null,
            );
            
            foreach ($Payments as $p) {
                $this->subData[$p['name']] = null;
            }
        } 

        $arrResult = $this->app['orm.em']->getRepository('\Plugin\TransportCSVexportB2\Entity\TransportCSVexportB2DelivType')->getDelivList();
        $arrOrderStatus = array();
        if ($OrderStatus = $this->app['eccube.repository.order_status']->findAllArray()) {
            foreach ($OrderStatus as $val) {
                $arrOrderStatus[$val['id']] = $val['name'];
            }
        }

        $arrDelivList = array();
        $arrPaymentList = array();

        foreach ($arrResult as $key => $val) {
            if (substr($val['id'], -2, 1) != 2) {
                $arrDelivList[$val['id']] = $val['name'];
            } else {
                if (substr($val['id'], 3, 1) == 0) {
                    $arrPaymentList[$val['id']] = $val['name'];
                }
            }
        }
        
        $builder
            ->add('billing_code', 'text', array(
                'label' => 'ご請求先顧客コード',
                'required' => false,
                'data' => $this->subData['billing_code'],
            ))
            ->add('fare_number', 'text', array(
                'label' => '運賃管理番号',
                'required' => false,
                'data' => $this->subData['fare_number'],
            ))
            ->add('yamato_deliv_type', 'choice', array(
                'label' => 'ヤマト運輸',
                'choices' => $arrDelivList,
                'data' => $this->subData['yamato_deliv_type'],
                'empty_value' => '選択してください',
            ))
            ->add('order_status', 'choice', array(
                'label' => '対応状況',
                'multiple' => true,
                'expanded' => true,
                'choices' => $arrOrderStatus,
                'data' => $this->subData['order_status'],
                'empty_value' => '選択してください',
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
            
            foreach ($Payments as $p) {
                $builder
                    ->add($p['name'], 'choice', array(
                        'label' => $p['name'],
                        'choices' => $arrPaymentList,
                        'data' => $this->subData[$p['name']],
                        'empty_value' => '選択してください',
                ));
            }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'config';
    }
}
