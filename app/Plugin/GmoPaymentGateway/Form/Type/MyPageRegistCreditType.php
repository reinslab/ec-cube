<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;

class MyPageRegistCreditType extends AbstractType
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build payment type form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('CardNo', 'text', array(
                'label' => 'カード番号',
                'attr' => array(
                    'class' => 'form-control',
                    'minlength' => '12',
                    'maxlength' => '16',
                    'autocomplete' => 'off',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ カード番号が入力されていません。')),
                    new Assert\Length(array(
                        'min' => 12, 
                        'max' => 16, 
                        'minMessage' => "※ カード番号が12桁～16桁の範囲ではありません。", 
                        'maxMessage' => "※ カード番号が12桁～16桁の範囲ではありません。")),
                    new Assert\Regex(array(
                        'pattern' => "/^[0-9]+$/", 
                        'match' => true, 
                        'message' => '※ カード番号は数字で入力してください。')),
                ),
            ))

            ->add('card_name1', 'text', array(
                'label' => '名義人',
                'attr' => array(
                    'class' => 'form-control',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ 名義人が入力されていません。')),
                    new Assert\Length(array(
                        'max' => 50, 
                        'maxMessage' => "※ 名義人は50文字以下で入力してください。")),
                    new Assert\Regex(array(
                        'pattern' => "/[^a-zA-Z\d\s]/", 
                        'match' => false, 
                        'message' => '※ 名義人は英数字で入力してください。')),
                ),
            ))

            ->add('expire_month', 'choice', array(
                'label' => '有効期限(月)',
                'empty_value' => '--',
                'required' => true,
                'choices' => array(
                    '01' => '01',
                    '02' => '02',
                    '03' => '03',
                    '04' => '04',
                    '05' => '05',
                    '06' => '06',
                    '07' => '07',
                    '08' => '08',
                    '09' => '09',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
                'attr' => array(
                    'class' => 'form-control',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ カード有効期限月が入力されていません。')),
                ),
            ))

            ->add('expire_year', 'choice', array(
                'label' => '有効期限(年)',
                'empty_value' => '--',
                'required' => true,
                'choices' => array(
                    '15' => '15',
                    '16' => '16',
                    '17' => '17',
                    '18' => '18',
                    '19' => '19',
                    '20' => '20',
                    '21' => '21',
                    '22' => '22',
                    '23' => '23',
                    '24' => '24',
                    '25' => '25',
                    '26' => '26',
                    '27' => '27',
                    '28' => '28',
                    '29' => '29',
                    '30' => '30',
                ),
                'attr' => array(
                    'class' => 'form-control',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ カード有効期限年が入力されていません。')),
                ),
            ))

            ->add('CardSeq', 'text', array(
                'read_only' => true,
                'attr' => array(
                    'class' => 'form-control',
                ),
            ))

            ->add('mode', 'hidden', array(
                'data' => 'add'
            ))

            // ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
            ->addEventListener(FormEvents::POST_BIND, function ($event) use($builder) {
                $form = $event->getForm();
                $expire_month = $form['expire_month']->getData();
                $expire_year = $form['expire_year']->getData();
                if (!empty($expire_month) && !empty($expire_year)) {
                    if (strtotime('-1 month') > strtotime('20' . $expire_year . '/' . $expire_month . '/1')) {
                        $form['expire_year']->addError(new FormError("※ 有効期限が過ぎたカードは利用出来ません。"));
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'my_page_regist_credit';
    }
}