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

namespace Eccube\Form\Type\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MyPageHistoryType extends AbstractType
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
        $config = $this->app['config'];
        
        $chkArr = array();
       	$chkArr[] = new Assert\NotBlank(array('message' => 'ファイルを選択してください。'));
        $chkArr[] = new Assert\File(array('maxSize' => $this->app['config']['pdf_size'] . 'M','maxSizeMessage' => 'PDFファイルは' . $this->app['config']['pdf_size'] . 'M以下でアップロードしてください。'));

        $builder
            ->add('pdffile', 'file', array(
                'label' => '入稿データ選択',
                'mapped' => false,
                'required' => true,
                'constraints' => $chkArr,
            ));
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getName()
    {
        return 'mypage_history';
    }
}
