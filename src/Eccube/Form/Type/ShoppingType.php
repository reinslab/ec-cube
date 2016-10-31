<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ShoppingType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $payments = $options['payments'];
        $payment = $options['payment'];
        $message = $options['message'];
// A => 受注 TODO
        $order = $options['order'];

        //印刷商品判定
        $flgPrintItem = $this->app['eccube.service.product']->isPrintProductByOrder($order);

        $chkArr = array();
        if ( $flgPrintItem ) {
        	$chkArr[] = new Assert\NotBlank(array('message' => 'ファイルを選択してください。'));
        }
        $chkArr[] = new Assert\File(array('maxSize' => $this->app['config']['pdf_size'] . 'M','maxSizeMessage' => 'PDFファイルは' . $this->app['config']['pdf_size'] . 'M以下でアップロードしてください。'));
// A => 受注 TODO

        $builder
            ->add('payment', 'entity', array(
                'class' => 'Eccube\Entity\Payment',
                'property' => 'method',
                'choices' => $payments,
                'data' => $payment,
                'expanded' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))

            ->add('pdffile', 'file', array(
                'label' => '入稿データ選択',
                'mapped' => false,
                'required' => $flgPrintItem,
                'constraints' => $chkArr
                ,
            ))

            ->add('message', 'textarea', array(
                'required' => false,
                'data' => $message,
                'constraints' => array(
                    new Assert\Length(array('min' => 0, 'max' => 3000))),
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'payments' => array(),
            'payment' => null,
            'message' => null,
            'order' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'shopping';
    }
}
