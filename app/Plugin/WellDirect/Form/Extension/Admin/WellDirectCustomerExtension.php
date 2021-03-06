<?php
/*
 * Plugin Name : ProductOption
 *
 * Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\WellDirect\Form\Extension\Admin;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints as Assert;

class WellDirectCustomerExtension extends AbstractTypeExtension
{
	private $app;

    public function __construct(\Silex\Application $app)
    {
    	$this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
var_dump("duapi");
exit;
        $builder->add('reins_order_code', 'text', array(
                'label' => '取引先コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->app['config']['stext_len'],
                    )),
                ),
    		));

        $builder->add('section_name', 'text', array(
                'label' => '部署名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->app['config']['stext_len'],
                    )),
                ),
    		));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
    	return 'customer';
    }

}
