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

namespace Plugin\TransportCSVimportB2\Form\Type;

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
        if (empty($this->subData)) {
            $this->subData = array(
                'order_status' => null,
            );
        } 
        
        $arrOrderStatus = array();
        if ($OrderStatus = $this->app['eccube.repository.order_status']->findAllArray()) {
            foreach ($OrderStatus as $val) {
                $arrOrderStatus[$val['id']] = $val['name'];
            }
        }

        $builder
            ->add('order_status', 'choice', array(
                'label' => '対応状況',
                'multiple' => true,
                'expanded' => true,
                'choices' => $arrOrderStatus,
                'data' => $this->subData['order_status'],
                'empty_value' => '選択してください',
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'config';
    }
}
