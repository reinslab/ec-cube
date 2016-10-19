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
        $objUtil = new PaymentUtil($this->app);
        
        if (empty($this->subData)) {
            $this->subData = array(
                'member_max_process' => $this->app['config']['GmoPaymentGateway']['const']['GMO_MEMBER_MAX_PROCESS'],
                'credit_token' => 0,
                'card_regist_flg'=> 0,
                'enable_payment_type' => array(),
                'kanri_server_url' => null,
                'server_url' => null,
                'connect_server_type' => null,
                'site_id' => null,
                'site_pass' => null,
                'ShopID' => null,
                'ShopPass' => null,
            );
        } 
        $arrPayments = $objUtil->getPaymentTypeNames($this->subData['credit_token']);
        //***Code for php < 5.4***
        $that = &$this->app;
        //***End***
        $builder
            ->add('enable_payment_type', 'choice', array(
                'label' => '有効にする決済方法',
                'choices' => $arrPayments,
                'expanded' => true,
                'multiple' => true,
                'data' => $this->subData['enable_payment_type'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ 決済方法が選択されていません。')),
                ),
            ))
            
            ->add('credit_token', 'choice', array(
                'choices' => array(
                    0 => '通常',
                    1 => 'トークン方式',
                ),
                'data' => $this->subData['credit_token'],
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('card_regist_flg', 'choice', array(
                'choices' => array(
                    0 => '利用しない',
                    1 => '利用する',
                ),
                'data' => !array_key_exists('card_regist_flg', $this->subData)? $this->subData['card_regist_flg'] = 0 : $this->subData['card_regist_flg'],
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('member_max_process', 'text', array(
                'label' => 'GMOメンバーID再発行時処理件数',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => !array_key_exists('member_max_process', $this->subData) ? $this->subData['member_max_process'] = $this->app['config']['GmoPaymentGateway']['const']['GMO_MEMBER_MAX_PROCESS'] : $this->subData['member_max_process'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '入力されていません。')),
                    new Assert\Regex(array('pattern' => "/^[0-9]+$/", 'match' => true, 'message' => '数字以外の文字が含まれています。')),
                ),
            ))
            ->add('server_url', 'text', array(
                'label' => '接続先サーバーURL',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => $this->subData['server_url'],
                'constraints' => array(
                    new Assert\Url(),
                ),
            ))
            ->add('kanri_server_url', 'text', array(
                'label' => '管理画面サーバーURL',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => $this->subData['kanri_server_url'],
                'constraints' => array(
                    new Assert\Url(),
                ),

            ))
            ->add('connect_server_type', 'choice', array(
                'label' => '接続先',
                'choices' => array(1 => 'テスト環境', 2 => '本番環境', 3 => '入力指定'),
                'expanded' => true,
                'data' => $this->subData['connect_server_type'],
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('site_id', 'text', array(
                'label' => 'サイトID',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ サイトIDが入力されていません。')),
                ),
                'data' => $this->subData['site_id'],
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('site_pass', 'text', array(
                'label' => 'サイトパスワード',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => $this->subData['site_pass'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ サイトパスワードが入力されていません。')),
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('ShopID', 'text', array(
                'label' => 'ショップID ',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => $this->subData['ShopID'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ショップIDが入力されていません。')),
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('ShopPass', 'text', array(
                'label' => 'ショップパスワード',
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'data' => $this->subData['ShopPass'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ショップパスワードが入力されていません。')),
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->addEventListener(FormEvents::POST_BIND, function ($event) use($builder, &$that){
                $form = $event->getForm();
                $listPaymentType = $form['enable_payment_type']->getData();
                if (count($listPaymentType) > 0) {
                    if (in_array($that['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'], $listPaymentType) 
                            || in_array($that['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN'], $listPaymentType)) {
                        if (is_null($form['credit_token']->getData())) {
                            $form['credit_token']->addError(new FormError("※ 認証方式が選択されていません。"));
                        } else {
                            if ($form['credit_token']->getData() == '0' && is_null($form['card_regist_flg']->getData())) {
                                $form['card_regist_flg']->addError(new FormError("※ カード情報登録が選択されていません。"));
                            }
                        }
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'config';
    }
}
