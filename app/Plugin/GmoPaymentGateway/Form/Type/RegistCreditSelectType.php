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
class RegistCreditSelectType extends AbstractType
{
    private $listPayMethod;
    public function __construct($listPayMethod = null)
    {
        $this->listPayMethod = $listPayMethod;
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
            ->add('CardSeq', 'radio', array(
                'required' => false,
              
            ))
            ->add('method', 'choice', array(
                'attr' => array(
                    'class' => 'lockon_card_row',
                ),
                'choices' => $this->listPayMethod,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ 支払い方法が入力されていません。')),
                ),
            ))
            ->addEventListener(FormEvents::POST_BIND, function ($event) use($builder){
                $form = $event->getForm();
                if (!isset($_POST['CardSeq']) || is_null($_POST['CardSeq'])) {
                    $form['CardSeq']->addError(new FormError("※ カード番号が選択されていません。"));
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gmo_regist_credit';
    }

}
