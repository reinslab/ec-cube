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

class WellDirectOrderExtension extends AbstractTypeExtension
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
        $builder
            ->add('pdffile', 'file', array(
                'label' => '���e�f�[�^�I��',
                'mapped' => false,
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '�t�@�C����I�����Ă��������B')),
                    new Assert\File(array(
                        'maxSize' => $this->app['config']['pdf_size'] . 'M',
                        'maxSizeMessage' => 'PDF�t�@�C����' . $this->app['config']['pdf_size'] . 'M�ȉ��ŃA�b�v���[�h���Ă��������B',
                    )),
                ),
            ));

        $builder->add('reins_order_id', 'text', array(
                'label' => '��V�X�e���󒍔ԍ�',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->app['config']['stext_len'],
                    )),
                ),
    		));
        $builder->add('denpyo_number', 'text', array(
                'label' => '�`�[�ԍ�',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->app['config']['stext_len'],
                    )),
                ),
    		));
        $builder->add('box_num', 'text', array(
                'label' => '�z����',
                'required' => false,
                'constraints' => array(
					new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
		            new Assert\NotBlank(),
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
    	return 'order';
    }

}
