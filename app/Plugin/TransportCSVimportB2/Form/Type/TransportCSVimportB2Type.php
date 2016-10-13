<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\TransportCSVimportB2\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TransportCSVimportB2Type extends AbstractType
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
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
        $app = $this->app;

        $builder
            ->add('import_file', 'file', array(
                'label' => false,
                'mapped' => false,
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'ファイルを選択してください。')),
                    new Assert\File(array(
                        'maxSize' => $app['config']['csv_size'] . 'M',
                        'maxSizeMessage' => 'CSVファイルは' . $app['config']['csv_size'] . 'M以下でアップロードしてください。',
                    )),
                ),
            ))
            ->add('send_flg', 'hidden', array(
                'data' => 1
            ))
            ->add('status_flg', 'hidden', array(
                'data' => 1
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

        
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_import_b2';
    }
}
