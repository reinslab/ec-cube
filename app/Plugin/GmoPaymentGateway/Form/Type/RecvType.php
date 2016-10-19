<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RecvType extends AbstractType
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build result recept type form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ShopID', 'text', array(
                'label' => 'ShopID',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '13',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ShopIDが入力されていません。')),
                    new Assert\Length(array('max' => 13, 'maxMessage' => "※ ShopIDは{{ limit }}字以下で入力してください。")),
                    new Assert\Regex(array('pattern' => '/^[a-zA-Z0-9]*$/', 'message' => '※ ShopIDは英数字で入力してください。')),
                ),
            ))
            ->add('ShopPass', 'text', array(
                'label' => 'ShopPass',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '10',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ShopPassが入力されていません。')),
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ ShopIDは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 10, 'maxMessage' => "※ ShopPassは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AccessID', 'text', array(
                'label' => 'AccessID',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '32',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[a-zA-Z0-9]*$/', 'message' => '※ AccessIDは英数字で入力してください。')),
                    new Assert\Length(array('max' => 32, 'maxMessage' => "※ AccessIDは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AccessPass', 'text', array(
                'label' => 'AccessPass',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '32',
                ),
                'constraints' => array(
//                    new Assert\NotBlank(array('message' => '※ AccessPassが入力されていません。')),
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ AccessPassは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 32, 'maxMessage' => "※ AccessPassは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('OrderID', 'text', array(
                'label' => 'OrderID',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '27',
                ),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ OrderIDが入力されていません。')),
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ OrderIDは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 27, 'maxMessage' => "※ OrderIDは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Status', 'text', array(
                'label' => 'Status',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ Statusは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 50, 'maxMessage' => "※ Statusは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('JobCd', 'text', array(
                'label' => 'JobCd',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ JobCdは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 50, 'maxMessage' => "※ JobCdは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Amount', 'text', array(
                'label' => 'Amount',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '9',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{9}$/', 'message' => '※ Amountは数字で入力してください。')),
                    new Assert\Length(array('max' => 9, 'maxMessage' => "※ Amountは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Tax', 'text', array(
                'label' => 'Tax',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '9',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{9}$/', 'message' => '※ Taxは数字で入力してください。')),
                    new Assert\Length(array('max' => 9, 'maxMessage' => "※ Taxは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Currency', 'text', array(
                'label' => 'Currency',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '3',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ Currencyは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 3, 'maxMessage' => "※ Currencyは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Forward', 'text', array(
                'label' => 'Forward',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '7',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ Forwardは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 7, 'maxMessage' => "※ Forwardは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Method', 'text', array(
                'label' => 'Method',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '1',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{1}$/', 'message' => '※ Methodは数字で入力してください。')),
                    new Assert\Length(array('max' => 1, 'maxMessage' => "※ Methodは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('PayTimes', 'text', array(
                'label' => 'PayTimes',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '2',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{2}$/', 'message' => '※ PayTimesは数字で入力してください。')),
                    new Assert\Length(array('max' => 2, 'maxMessage' => "※ PayTimesは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('TranID', 'text', array(
                'label' => 'TranID',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '28',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ TranIDは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 28, 'maxMessage' => "※ TranIDは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('Approve', 'text', array(
                'label' => 'Approve',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '7',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ Approveは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 7, 'maxMessage' => "※ Approveは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('TranDate', 'text', array(
                'label' => 'TranDate',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '14',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ TranDateは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 14, 'maxMessage' => "※ TranDateは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('ErrCode', 'text', array(
                'label' => 'ErrCode',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ ErrCodeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 50, 'maxMessage' => "※ ErrCodeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('ErrInfo', 'text', array(
                'label' => 'ErrInfo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '50',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ ErrInfoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 50, 'maxMessage' => "※ ErrInfoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('PayType', 'text', array(
                'label' => 'PayType',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '3',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ PayTypeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 3, 'maxMessage' => "※ PayTypeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('CvsCode', 'text', array(
                'label' => 'CvsCode',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '5',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ CvsCodeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 5, 'maxMessage' => "※ CvsCodeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('CvsConfNo', 'text', array(
                'label' => 'CvsConfNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '20',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ CvsConfNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 20, 'maxMessage' => "※ CvsConfNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('CvsReceiptNo', 'text', array(
                'label' => 'CvsReceiptNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '32',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ CvsReceiptNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 32, 'maxMessage' => "※ CvsReceiptNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('EdyReceiptNo', 'text', array(
                'label' => 'EdyReceiptNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ EdyReceiptNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 16, 'maxMessage' => "※ EdyReceiptNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('EdyOrderNo', 'text', array(
                'label' => 'EdyOrderNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '40',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ EdyOrderNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 40, 'maxMessage' => "※ EdyOrderNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('SuicaReceiptNo', 'text', array(
                'label' => 'SuicaReceiptNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '9',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ SuicaReceiptNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 9, 'maxMessage' => "※ SuicaReceiptNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('SuicaOrderNo', 'text', array(
                'label' => 'SuicaOrderNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '40',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ SuicaOrderNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 40, 'maxMessage' => "※ SuicaOrderNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('CustID', 'text', array(
                'label' => 'CustID',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '11',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ CustIDは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 11, 'maxMessage' => "※ CustIDは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('BkCode', 'text', array(
                'label' => 'BkCode',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '5',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ BkCodeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 5, 'maxMessage' => "※ BkCodeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('ConfNo', 'text', array(
                'label' => 'ConfNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '20',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ ConfNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 20, 'maxMessage' => "※ ConfNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('PaymentTerm', 'text', array(
                'label' => 'PaymentTerm',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '14',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ PaymentTermは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 14, 'maxMessage' => "※ PaymentTermは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('EncryptReceiptNo', 'text', array(
                'label' => 'EncryptReceiptNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '128',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ EncryptReceiptNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 128, 'maxMessage' => "※ EncryptReceiptNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('FinishDate', 'text', array(
                'label' => 'FinishDate',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '14',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ FinishDateは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 14, 'maxMessage' => "※ FinishDateは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('ReceiptDate', 'text', array(
                'label' => 'ReceiptDate',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '14',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ ReceiptDateは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 14, 'maxMessage' => "※ ReceiptDateは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('WebMoneyManagementNo', 'text', array(
                'label' => 'WebMoneyManagementNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ WebMoneyManagementNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 16, 'maxMessage' => "※ WebMoneyManagementNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('WebMoneySettleCode', 'text', array(
                'label' => 'WebMoneySettleCode',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '25',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ WebMoneySettleCodeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 25, 'maxMessage' => "※ WebMoneySettleCodeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AuPayInfoNo', 'text', array(
                'label' => 'AuPayInfoNo',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '16',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ AuPayInfoNoは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 16, 'maxMessage' => "※ AuPayInfoNoは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AuPayMethod', 'text', array(
                'label' => 'AuPayMethod',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '2',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ AuPayMethodは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 2, 'maxMessage' => "※ AuPayMethodは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AuCancelAmount', 'text', array(
                'label' => 'AuCancelAmount',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '7',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ AuCancelAmountは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 7, 'maxMessage' => "※ AuCancelAmountは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('AuCancelTax', 'text', array(
                'label' => 'AuCancelTax',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '7',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ AuCancelTaxは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 7, 'maxMessage' => "※ AuCancelTaxは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('DocomoSettlementCode', 'text', array(
                'label' => 'DocomoSettlementCode',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '12',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[[:graph:][:space:]]+$/i', 'message' => '※ DocomoSettlementCodeは英数記号で入力してください。')),
                    new Assert\Length(array('max' => 12, 'maxMessage' => "※ DocomoSettlementCodeは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('DocomoCancelAmount', 'text', array(
                'label' => 'DocomoCancelAmount',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '6',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{6}$/', 'message' => '※ DocomoCancelAmountは数字で入力してください。')),
                    new Assert\Length(array('max' => 6, 'maxMessage' => "※ DocomoCancelAmountは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->add('DocomoCancelTax', 'text', array(
                'label' => 'DocomoCancelTax',
                'required' => false,
                'attr' => array(
                    'class' => 'lockon_card_row',
                    'maxlength' => '6',
                ),
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d{6}$/', 'message' => '※ DocomoCancelTaxは数字で入力してください。')),
                    new Assert\Length(array('max' => 6, 'maxMessage' => "※ DocomoCancelTaxは{{ limit }}字以下で入力してください。")),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gmo_payment_recv';
    }
}
