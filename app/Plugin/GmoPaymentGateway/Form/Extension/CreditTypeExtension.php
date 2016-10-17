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
use Plugin\GmoPaymentGateway\Controller\Util\CommonUtil;
/**
 * Extension of Credit payment edit form.
 */
class CreditTypeExtension extends AbstractTypeExtension
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build credit form
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
            if ($memo03 != $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'])
                return;
        } else {
            return;
        }

        $builder
            ->add('JobCd', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    'CAPTURE' => '即時売上',
                    'AUTH' => '仮売上',
                    'SAUTH' => '簡易オーソリ',
                    'CHECK' => '有効性チェック',
                ),
                'label' => 'カード番号',
                'attr' => array(
                    'class' => 'gmo_payment_form',
                    // 'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※ 処理区分が入力されていません。')
                    ),
                    // new Assert\Regex(array('pattern' => '/\A\d+\z/')),
                ),
                'mapped' => false,
            ))
            ->add('credit_pay_methods', 'choice', array(
                'choices' => array(
                    '1-0' => '一括払い',
                    '2-2' => '分割2回払い',
                    '2-3' => '分割3回払い',
                    '2-4' => '分割4回払い',
                    '2-5' => '分割5回払い',
                    '2-6' => '分割6回払い',
                    '2-7' => '分割7回払い',
                    '2-8' => '分割8回払い',
                    '2-9' => '分割9回払い',
                    '2-10' => '分割10回払い',
                    '2-11' => '分割11回払い',
                    '2-12' => '分割12回払い',
                    '2-13' => '分割13回払い',
                    '2-14' => '分割14回払い',
                    '2-15' => '分割15回払い',
                    '2-16' => '分割16回払い',
                    '2-17' => '分割17回払い',
                    '2-18' => '分割18回払い',
                    '2-19' => '分割19回払い',
                    '2-20' => '分割20回払い',
                    '2-21' => '分割21回払い',
                    '2-22' => '分割22回払い',
                    '2-23' => '分割23回払い',
                    '2-24' => '分割24回払い',
                    '2-26' => '分割26回払い',
                    '2-30' => '分割30回払い',
                    '2-32' => '分割32回払い',
                    '2-34' => '分割34回払い',
                    '2-36' => '分割36回払い',
                    '2-37' => '分割37回払い',
                    '2-40' => '分割40回払い',
                    '2-42' => '分割42回払い',
                    '2-48' => '分割48回払い',
                    '2-50' => '分割50回払い',
                    '2-54' => '分割54回払い',
                    '2-60' => '分割60回払い',
                    '2-72' => '分割72回払い',
                    '2-84' => '分割84回払い',
                    '3-0' => 'ボーナス一括',
                    '4-2' => 'ボーナス分割2回払い',
                    '5-0' => 'リボ払い',
                ),
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '※  支払種別が入力されていません。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('use_securitycd', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    1 => '利用する',
                    0 => '利用しない',
                ),
                'mapped' => false,
            ))
            ->add('use_securitycd_option', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    1 => '許可',
                    0 => '不許可',
                ),
                'mapped' => false,
            ))
            ->add('TdFlag', 'choice', array(
                'expanded' => true,
                'choices' => array(
                    1 => '利用する',
                    0 => '利用しない',
                ),
                'mapped' => false,
            ))
            ->add('TdTenantName', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '18',
                ),
                'constraints' => array(
                    new Assert\Length(
                        array('max' => 18,
                              'maxMessage' => '※ 3Dセキュア表示店舗名は18字以下で入力してください。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('order_mail_title1', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Length(
                        array('max' => 50,
                              'maxMessage' => '※ 決済完了案内タイトルは50字以下で入力してください。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('order_mail_body1', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'area',
                    'maxlength' => '1000',
                ),
                'constraints' => array(
                    new Assert\Length(
                        array('max' => 1000,
                              'maxMessage' => '※ 決済完了案内本文は1000字以下で入力してください。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('ClientField1', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '100',
                ),
                'constraints' => array(
                    new Assert\Length(
                        array('max' => 100,
                              'maxMessage' => '※ 自由項目1は100字以下で入力してください。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('ClientField2', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'width222',
                    'maxlength' => '100',
                ),
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 100,
                        'maxMessage' => '※ 自由項目2は100字以下で入力してください。')
                    ),
                ),
                'mapped' => false,
            ))
            ->add('enable_mail', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('enable_cvs_mails', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('PaymentTermDay', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('PaymentTermSec', 'hidden', array(
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
                
                $objUtil = new PaymentUtil($that);
                $objCommmonUtil = new CommonUtil($that);

                if($objCommmonUtil->isBlank($form['TdTenantName']->getData()) == false){
                    if($objUtil->convTdTenantName($form['TdTenantName']->getData()) == ''){
                        $form['TdTenantName']->addError(new FormError('※ 変換後のデータが25byte以内の必要があります。文字によってバイト数が変動しますので文字を変える等して下さい。'));
                    }
                }
                
                $pattern = "/^[[:graph:][:space:]]+$/i";
                if (strlen($form['TdTenantName']->getData()) > 0 && !preg_match($pattern, $form['TdTenantName']->getData())) {
                    $form['TdTenantName']->addError(new FormError("※ 3Dセキュア表示店舗名は英数記号で入力してください。"));
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
