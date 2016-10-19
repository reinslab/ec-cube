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

namespace Plugin\TransportCSVexportB2\Controller;

use Eccube\Application;
use Plugin\TransportCSVexportB2\Controller\Util\PluginUtil;
use Plugin\TransportCSVexportB2\Form\Type\ConfigType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Controller to handle module setting screen
 */
class ConfigController
{

    /**
     * Edit config
     *
     * @param Application $app
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function edit(Application $app, Request $request)
    {
        $this->app = $app;
        
        $objTCSV =& PluginUtil::getInstance($this->app);
        $tpl_subtitle = $objTCSV->getName();

        // Get module code from dtb_plugin
        $self = Yaml::parse(__DIR__ . '/../config.yml');
        $Plugin = $this->app['eccube.repository.plugin']->findOneBy(array('code' => $self['code']));

        if (is_null($Plugin)) {
            $error = "例外エラー<br />プラグインが存在しません。";
            $error_title = 'エラー';
            return $this->app['view']->render('error.twig', compact('error', 'error_title'));
        }

        $subData = $objTCSV->getUserSettings();

        $Result = $this->app['eccube.repository.payment']
            ->findBy(
                array('del_flg' => 0),
                array('rank' => 'DESC')
            );

		$Payments = array();
		foreach ($Result as $payment) {
			$Payments[$payment->getId()] = array(
				'id'     => $payment->getId(),
				'name'   => 'payment_type' . $payment->getId(),
				'pm' => $payment->getMethod(),
			);
		}

        $configFrom = new ConfigType($this->app, $subData);
        $form = $this->app['form.factory']->createBuilder($configFrom)->getForm();

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            if ($form->isValid()) {

                $formData = $form->getData();
                
                $this->app['orm.em']->getConnection()->beginTransaction();
                $objTCSV->registerUserSettings($formData);
                $this->app['orm.em']->getConnection()->commit();

                $this->app->addSuccess('admin.register.complete', 'admin');
                return $this->app->redirect($this->app['url_generator']->generate('plugin_TransportCSVexportB2_config'));
            }
        }
        
        return $this->app['view']->render('TransportCSVexportB2/View/admin/transport_csv_export_b2_config.twig',
            array(
                'form' => $form->createView(),
                'tpl_subtitle' => $tpl_subtitle,
                'subData' => $subData,
                'paymentData' => $Payments,
            ));
    }

}
