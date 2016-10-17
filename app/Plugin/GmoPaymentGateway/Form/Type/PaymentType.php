<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Form\Type;

use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;

class PaymentType extends AbstractType {

    private $app;

    public function __construct(\Eccube\Application $app) {
        $this->app = $app;
    }

    /**
     * Build payment type form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $year = $this->getZeroYear(date('Y'), date('Y') + 15);
        $month = $this->getZeroMonth();
        $this->app['request']->request->all();
        $Order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
        if (is_null($Order))
            return;

        $objUtil = new PaymentUtil($this->app);
        $Payment = $Order->getPayment();
        if (!is_null($Payment)) {
            $paymentInfo = $objUtil->getPaymentTypeConfig($Payment->getId())->getArrPaymentConfig();
        }

        $arrPayMethod = $objUtil->getCreditPayMethod();
        $listPayMethod = array();
        if(isset($paymentInfo['credit_pay_methods'])){
            foreach ((array) $paymentInfo['credit_pay_methods'] as $pay_method) {
                if (!is_null($arrPayMethod[$pay_method])) {
                    $listPayMethod[$pay_method] = $arrPayMethod[$pay_method];
                }
            }
        }
        $securitycd_option_constraints = array(
            new Assert\Length(array('min' => 3, 'max' => 4, 'minMessage' => "※ セキュリティコードが3桁～4桁の範囲ではありません。", 'maxMessage' => "※ セキュリティコードが3桁～4桁の範囲ではありません。")),
            new Assert\Regex(array('pattern' => "/^[0-9]+$/", 'match' => true, 'message' => '※ セキュリティコードに数字以外の文字が含まれています。')),
        );
        
        if (isset($paymentInfo["use_securitycd_option"]) && "0" == $paymentInfo["use_securitycd_option"] && "1" == $paymentInfo["use_securitycd"]) {
            $securitycd_option_constraints[] = new Assert\NotBlank(array('message' => '※ セキュリティコードが入力されていません。'));
        }

        $builder
                ->add('card_no', 'text', array(
                    'label' => 'カード番号',
                    'attr' => array(
                        'class' => 'lockon_card_row',
                        'minlength' => '12',
                        'maxlength' => '16',
                        'autocomplete' => 'off',
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ カード番号が入力されていません。')),
                        new Assert\Length(array('min' => 12, 'max' => 16, 'minMessage' => "※ カード番号が12桁～16桁の範囲ではありません。", 'maxMessage' => "※ カード番号が12桁～16桁の範囲ではありません。")),
                        new Assert\Regex(array('pattern' => "/^[0-9]+$/", 'match' => true, 'message' => '※ カード番号に数字以外の文字が含まれています。')),
                    ),
                ))
                ->add('security_code', 'text', array(
                    'label' => 'セキュリティコード',
                    'attr' => array(
                        'class' => 'lockon_card_row',
                        'maxlength' => '4',
                        'autocomplete' => 'off',
                    ),
                    'required' => false,
                    'constraints' => $securitycd_option_constraints,
                ))
                ->add('card_name1', 'text', array(
                    'attr' => array(
                        'class' => 'lockon_card_row',
                        'maxlength' => '25',
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ カード名義人名:名が入力されていません。')),
                        new Assert\Regex(array('pattern' => "/[^a-zA-Z\d\s]/", 'match' => false, 'message' => '※ カード名義人名:名は英数字で入力してください。')),
                        new Assert\Length(array('min' => 0, 'max' => 25, 'minMessage' => "※ カード名義人名:名は25字以下で入力してください。", 'maxMessage' => "※ カード名義人名:名は25字以下で入力してください。")),
                    ),
                ))
                ->add('card_name2', 'text', array(
                    'attr' => array(
                        'class' => 'lockon_card_row',
                        'maxlength' => '24',
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ カード名義人名:姓が入力されていません。')),
                        new Assert\Regex(array('pattern' => "/[^a-zA-Z\d\s]/", 'match' => false, 'message' => '※ カード名義人名:姓は英数字で入力してください。')),
                        new Assert\Length(array('min' => 0, 'max' => 24, 'minMessage' => "※ カード名義人名:名は24字以下で入力してください。", 'maxMessage' => "※ カード名義人名:姓は24字以下で入力してください。")),
                    ),
                ))
                ->add('register_card', 'checkbox', array(
                    'label' => ' このカードを登録する。',
                    'required' => false,
                ))
                ->add('expire_month', 'choice', array(
                    'choices' => $month,
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ カード有効期限月が入力されていません。')),
                        new Assert\Length(array('min' => 0, 'max' => 2, 'minMessage' => "※ カード有効期限月は2字以下で入力してください。", 'maxMessage' => "※ カード有効期限月は2字以下で入力してください。")),
                        new Assert\Regex(array('pattern' => "/^[0-9]+$/", 'match' => true, 'message' => '※ カード有効期限月は数字で入力してください。')),
                    ),
                ))
                ->add('expire_year', 'choice', array(
                    'choices' => $year,
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ カード有効期限年が入力されていません。')),
                        new Assert\Length(array('min' => 0, 'max' => 2, 'minMessage' => "※ カード有効期限年は2字以下で入力してください。", 'maxMessage' => "※ カード有効期限年は2字以下で入力してください。")),
                        new Assert\Regex(array('pattern' => "/^[0-9]+$/", 'match' => true, 'message' => '※ カード有効期限年は数字で入力してください。')),
                    ),
                ))
                ->add('method', 'choice', array(
                    'choices' => $listPayMethod,
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => '※ 支払い方法が入力されていません。')),
                    ),
                ))
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
    public function getName() {
        return 'gmo_payment';
    }

    /**
     * Get zero month
     *
     * @return type
     */
    public function getZeroMonth() {
        $month_array = array();
        for ($i = 1; $i <= 12; $i++) {
            $val = sprintf('%02d', $i);
            $month_array[$val] = $val;
        }

        return $month_array;
    }

    /**
     * Get zero year
     *
     * @param type $star_year
     * @param type $end_year
     * @param type $year
     * @return type
     */
    public function getZeroYear($star_year, $end_year, $year = '') {
        if ($year)
            $this->setStartYear($year);

        $year = $star_year;
        if (!$year)
            $year = DATE('Y');

        $end_year = $end_year;
        if (!$end_year)
            $end_year = (DATE('Y') + 3);

        $year_array = array();

        for ($i = $year; $i <= $end_year; $i++) {
            $key = substr($i, -2);
            $year_array[$key] = $key;
        }

        return $year_array;
    }
    
}
