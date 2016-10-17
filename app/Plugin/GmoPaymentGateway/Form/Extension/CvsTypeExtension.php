<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;

/**
 * Extension of CVS payment edit form.
 */
class CvsTypeExtension extends AbstractTypeExtension
{

    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build csv form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $paymentId = $this->app['request']->attributes->get('id');
        $GmoPaymentMethod = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->findOneBy(array('id' => $paymentId));
        if (!is_null($GmoPaymentMethod)) {
            $memo03 = $GmoPaymentMethod->getMemo03();
            if ($memo03 != $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS'])
                return;
        } else {
            return;
        }

        $builder
            ->add('conveni', 'choice', array(
                'label' => 'コンビニ選択',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'expanded' => true,
                'multiple' => true,
                'choices' => array(
                    '00001' => 'ローソン',
                    '00002' => 'ファミリーマート',
                    '00004' => 'サークルKサンクス',
                    '00005' => 'ミニストップ',
                    '00006' => 'デイリーヤマザキ',
                    '00007' => 'セブンイレブン',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ コンビニエンスストアが入力されていません。')
                    ),
                ),
            ))
            ->add('payment_term_day', 'text', array(
                'label' => '支払期限',
                'required' => false,
                'attr' => array(
                    'class' => 'width54',
                    'maxlength' => '2',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => '/\D/',
                        'match'   => false,
                        'message' => '※ 支払期限は数字で入力してください。')
                    ),                    
                    new Assert\LessThanOrEqual(
                        array('value' => 30,
                            'message' => '※ 支払い期限は最大30日まで設定可能です。')
                    ),                    
                ),
            ))
            ->add('enable_mail', 'choice', array(
                'label' => 'PGメール送信',
                'expanded' => true,
                'choices' => array(
                    1 => '利用する',
                    0 => '利用しない',
                ),
                'mapped' => false,
                'attr' => array(
                    'onclick' => 'show()',
                )
            ))
            ->add('enable_cvs_mails', 'choice', array(
                'label' => 'コンビニ単位PGメール送信有無',
                'expanded' => true,
                'multiple' => true,
                'choices' => array(
                    '00001' => 'ローソン',
                    '00002' => 'ファミリーマート',
                    '00004' => 'サークルKサンクス',
                    '00005' => 'ミニストップ',
                    '00006' => 'デイリーヤマザキ',
                    '00007' => 'セブンイレブン',
                ),
                'mapped' => false,
            ))
            ->add('register_disp1', 'text', array(
                'label' => 'POSレジ表示欄1',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄1は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp2', 'text', array(
                'label' => 'POSレジ表示欄2',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄2は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp3', 'text', array(
                'label' => 'POSレジ表示欄3',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄3は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp4', 'text', array(
                'label' => 'POSレジ表示欄4',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄4は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
                
            ->add('register_disp5', 'text', array(
                'label' => 'POSレジ表示欄5',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄5は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp6', 'text', array(
                'label' => 'POSレジ表示欄6',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄6は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp7', 'text', array(
                'label' => 'POSレジ表示欄7',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄7は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('register_disp8', 'text', array(
                'label' => 'POSレジ表示欄8',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ POSレジ表示欄8は16字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp1', 'text', array(
                'label' => 'レシート表示欄1',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄1は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp2', 'text', array(
                'label' => 'レシート表示欄2',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄2は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp3', 'text', array(
                'label' => 'レシート表示欄3',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄3は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp4', 'text', array(
                'label' => 'レシート表示欄4',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄4は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp5', 'text', array(
                'label' => 'レシート表示欄5',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄5は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp6', 'text', array(
                'label' => 'レシート表示欄6',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄6は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp7', 'text', array(
                'label' => 'レシート表示欄7',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄7は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp8', 'text', array(
                'label' => 'レシート表示欄8',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄8は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp9', 'text', array(
                'label' => 'レシート表示欄9',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄9は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp10', 'text', array(
                'label' => 'レシート表示欄10',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '30',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 16,
                            'maxMessage' => '※ レシート表示欄10は30字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp11', 'text', array(
                'label' => 'お問合せ先',
                'required' => true,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '42',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ お問合せ先が入力されていません。')),
                    new Assert\Length(array('max'        => 42,
                                            'maxMessage' => '※ お問合せ先は42字以下で入力してください。')),
                    ),
            ))
            ->add('receipts_disp12_1', 'text', array(
                'required' => true,
                'attr' => array(
                    'class' => 'width54',
                    'minlength' => '2',
                    'maxlength' => '4',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2, 
                        'max' => 4, 
                        'minMessage' => '※ お問合せ先電話番号1は2字以上で入力してください。', 
                        'maxMessage' => '※ お問合せ先電話番号1は4字以下で入力してください。')),
                    new Assert\Regex(array('pattern' => '/\A\d+\z/', 'message' => '※ お問合せ先電話番号1は数字で入力してください。')),
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先電話番号1は数字で入力してください。')
                    ),
                ),
            ))
            ->add('receipts_disp12_2', 'text', array(
                'required' => true,
                'attr' => array(
                    'class' => 'width54',
                    'minlength' => '2',
                    'maxlength' => '4',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2, 
                        'max' => 4, 
                        'minMessage' => '※ お問合せ先電話番号2は2字以上で入力してください。', 
                        'maxMessage' => '※ お問合せ先電話番号2は4字以下で入力してください。')),
                    new Assert\Regex(array('pattern' => '/\A\d+\z/', 'message' => '※ お問合せ先電話番号2は数字で入力してください。')),
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先電話番号2は数字で入力してください。')
                    ),
                ),
            ))
            ->add('receipts_disp12_3', 'text', array(
                'required' => true,
                'attr' => array(
                    'class' => 'width54',
                    'minlength' => '2',
                    'maxlength' => '4',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2, 
                        'max' => 4, 
                        'minMessage' => '※ お問合せ先電話番号3は2字以上で入力してください。', 
                        'maxMessage' => '※ お問合せ先電話番号3は4字以下で入力してください。')),
                    new Assert\Regex(array('pattern' => '/\A\d+\z/', 'message' => '※ お問合せ先電話番号3は数字で入力してください。')),
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先電話番号3は数字で入力してください。')
                    ),
                ),
            ))
            ->add('receipts_disp13_1', 'choice', array(
                'required' => true,
                'choices' => array(
                    '' => '', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'
                ),
                'attr' => array(
                    'class' => 'width54',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先受付時間1が入力されていません。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp13_2', 'choice', array(
                'required' => true,
                'choices' => array(
                    '' => '', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                    '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
                    '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
                    '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
                    '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
                    '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
                ),
                'attr' => array(
                    'class' => 'width54',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先受付時間2が入力されていません。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp13_3', 'choice', array(
                'required' => true,
                'choices' => array(
                    '' => '', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'
                ),
                'attr' => array(
                    'class' => 'width54',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先受付時間3が入力されていません。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('receipts_disp13_4', 'choice', array(
                'required' => true,
                'choices' => array(
                    '' => '', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                    '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
                    '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
                    '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
                    '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
                    '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
                ),
                'attr' => array(
                    'class' => 'width54',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ お問合せ先受付時間4が入力されていません。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title1', 'text', array(
                'label' => '決済完了案内タイトル',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ 決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body1', 'textarea', array(
                'label' => '決済完了案内本文',
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ 決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('ClientField1', 'text', array(
                'label' => '自由項目1',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 100,
                            'maxMessage' => '※ 自由項目1は100字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('ClientField2', 'text', array(
                'label' => '自由項目2',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '100',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 100,
                            'maxMessage' => '※ 自由項目2は100字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00001', 'text', array(
                'label' => '',
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ ローソン決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00001', 'textarea', array(
                'label' => 'ローソン決済完了案内本文',
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ ローソン決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00002', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ ファミリーマート決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00002', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ ファミリーマート決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00004', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ サークルKサンクス決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00004', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ サークルKサンクス決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00005', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ ミニストップ決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00005', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ ミニストップ決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00006', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ デイリーヤマザキ決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00006', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ デイリーヤマザキ決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title_00007', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 50,
                            'maxMessage' => '※ セブンイレブン決済完了案内タイトルは50字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body_00007', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000'
                ),
                'constraints' => array(
                    new Assert\Length ( array(
                            'max'=> 1000,
                            'maxMessage' => '※ セブンイレブン決済完了案内本文は1000字以下で入力してください。'
                    ))
                ),
                'mapped' => false,
            ))
            ->add('use_securitycd', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('use_securitycd_option', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('TdFlag', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('TdTenantName', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('EdyAddInfo1', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('EdyAddInfo2', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('SuicaAddInfo1', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('SuicaAddInfo2', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('SuicaAddInfo3', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('SuicaAddInfo4', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('Currency', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('PaymentTermSec', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ));
            //***Code for php < 5.4***
            $that = &$this->app;
            //***End***
            $builder->addEventListener(FormEvents::POST_BIND, function ($event) use($builder, &$that) {
                $form = $event->getForm();
                $count = 0;
                if ($form['receipts_disp12_1']->getData() != '') {
                    $count++;
                }
                if ($form['receipts_disp12_2']->getData() != '') {
                    $count++;
                }
                if ($form['receipts_disp12_3']->getData() != '') {
                    $count++;
                }
                if ($count != 0 && $count != 3) {
                    $form['receipts_disp12_1']->addError(new FormError('※ 全て入力してください。'));
                }

                $objUtil = new PaymentUtil($that);
                $isHaveProhibitedChar = false;
                for ($i = 0; $i < mb_strlen($form['receipts_disp11']->getData()); $i=$i+3){
                    $tmp = mb_substr($form['receipts_disp11']->getData(), $i , 3);
                    if($objUtil->isProhibitedChar($tmp))
                    {
                        $form['receipts_disp11']->addError(new FormError('※ お問い合わせ先に禁止されている文字「'.$tmp.'」が含まれています。'));
                        $isHaveProhibitedChar = true;
                        break;
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }

    public function getExtendedType()
    {
        return 'payment_register';
    }

}
