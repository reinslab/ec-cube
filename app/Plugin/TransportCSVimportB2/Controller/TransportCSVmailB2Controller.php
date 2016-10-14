<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Plugin\TransportCSVimportB2\Controller;

use Eccube\Application;
use Eccube\Entity\MailHistory;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransportCSVmailB2Controller
{
    public function mailAll(Application $app, Request $request)
    {

        $builder = $app['form.factory']->createBuilder('mail');
/*
        $event = new EventArgs(
            array(
                'builder' => $builder,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_MAIL_MAIL_ALL_INITIALIZE, $event);
*/
        $form = $builder->getForm();

        $ids = '';
        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            $mode = $request->get('mode');

            $ids = $request->get('ids');

            // テンプレート変更の場合は. バリデーション前に内容差し替え.
            if ($mode == 'change') {
                if ($form->get('template')->isValid()) {
                    /** @var $data \Eccube\Entity\MailTemplate */
                    $MailTemplate = $form->get('template')->getData();
                    $form = $builder->getForm();

                    $event = new EventArgs(
                        array(
                            'form' => $form,
                            'MailTemplate' => $MailTemplate,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_MAIL_MAIL_ALL_CHANGE, $event);

                    $form->get('template')->setData($MailTemplate);
                    $form->get('subject')->setData($MailTemplate->getSubject());
                    $form->get('header')->setData($MailTemplate->getHeader());
                    $form->get('footer')->setData($MailTemplate->getFooter());
                }
            } else if ($form->isValid()) {
                switch ($mode) {
                    case 'confirm':
                        // フォームをFreezeして再生成.

                        $builder->setAttribute('freeze', true);
                        $builder->setAttribute('freeze_display_text', true);

                        $data = $form->getData();

                        $tmp = explode(',', $ids);

                        $Order = $app['eccube.repository.order']->find($tmp[0]);

                        if (is_null($Order)) {
                            throw new NotFoundHttpException('order not found.');
                        }

                        $Shippings = $Order->getShippings();
                        $arrShippingB2 = array();
                        foreach ($Shippings as $Shipping) {
                            $TransportCSVimportB2 = $app['orm.em']
                                ->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')
                                ->findOneBy(array('order_id' => $tmp[0], 'shipping_id' => $Shipping->getId()));
                            if ($TransportCSVimportB2) {
                                $arrShippingB2[] = 'お名前　　：'. $Shipping->getName01(). $Shipping->getName02(). '様';
                                $arrShippingB2[] = '送り状番号：'. $TransportCSVimportB2->getInvoiceNumber();
                            }
                        }

                        $MailTemplate = $form->get('template')->getData();

                        $template = $data['header'];
                        if ($arrShippingB2 && $MailTemplate->getName() == 'ヤマトB2発送メール') {
                            $template = str_replace('送り状番号：', '', $template);
                            $template = str_replace('お名前　　：', implode("\n", $arrShippingB2), $template);
                        }

                        $body = $this->createBody($app, $template, $data['footer'], $Order);

                        $form = $builder->getForm();
/*
                        $event = new EventArgs(
                            array(
                                'form' => $form,
                                'MailTemplate' => $MailTemplate,
                                'Order' => $Order,
                            ),
                            $request
                        );
                        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_MAIL_MAIL_ALL_CONFIRM, $event);
*/
                        $form->setData($data);
                        $form->get('template')->setData($MailTemplate);

                        return $app->render('TransportCSVimportB2/View/admin/transport_csv_import_b2_mail_all_confirm.twig', array(
                            'form' => $form->createView(),
                            'body' => $body,
                            'ids' => $ids,
                        ));
                        break;

                    case 'complete':

                        $data = $form->getData();

                        $ids = explode(',', $ids);

                        $header = $data['header'];

                        foreach ($ids as $value) {

                            $Order = $app['eccube.repository.order']->find($value);

	                        $Shippings = $Order->getShippings();
	                        $arrShippingB2 = array();
	                        foreach ($Shippings as $Shipping) {
	                            $TransportCSVimportB2 = $app['orm.em']
	                                ->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')
	                                ->findOneBy(array('order_id' => $value, 'shipping_id' => $Shipping->getId()));
	                            if ($TransportCSVimportB2) {
	                                $arrShippingB2[] = 'お名前　　：'. $Shipping->getName01(). $Shipping->getName02(). '様';
	                                $arrShippingB2[] = '送り状番号：'. $TransportCSVimportB2->getInvoiceNumber();
	                            }
	                        }

                            $MailTemplate = $form->get('template')->getData();

	                        if ($arrShippingB2 && $MailTemplate->getName() == 'ヤマトB2発送メール') {
	                            $data['header'] = str_replace('送り状番号：', '', $data['header']);
	                            $data['header'] = str_replace('お名前　　：', implode("\n", $arrShippingB2), $data['header']);
	                        }

                            $body = $this->createBody($app, $data['header'], $data['footer'], $Order);

                            // メール送信
                            $app['eccube.service.mail']->sendAdminOrderMail($Order, $data);

                            // 送信履歴を保存.
                            $MailHistory = new MailHistory();
                            $MailHistory
                                ->setSubject($data['subject'])
                                ->setMailBody($body)
                                ->setMailTemplate($MailTemplate)
                                ->setSendDate(new \DateTime())
                                ->setOrder($Order);
                            $app['orm.em']->persist($MailHistory);

                            $data['header'] = $header;
                        }

                        $app['orm.em']->flush($MailHistory);
/*
                        $event = new EventArgs(
                            array(
                                'form' => $form,
                                'MailHistory' => $MailHistory,
                            ),
                            $request
                        );
                        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_MAIL_MAIL_ALL_COMPLETE, $event);
*/
                        return $app->redirect($app->url('admin_order_mail_complete'));
                        break;
                    default:
                        break;
                }
            }
        } else {
            foreach ($_GET as $key => $value) {
                $ids = str_replace('ids', '', $key) . ',' . $ids;
            }
            $ids = substr($ids, 0, -1);
        }

        return $app->render('TransportCSVimportB2/View/admin/transport_csv_import_b2_mail_all.twig', array(
            'form' => $form->createView(),
            'ids' => $ids,
        ));
    }


    private function createBody($app, $header, $footer, $Order)
    {
        return $app->renderView('Mail/order.twig', array(
            'header' => $header,
            'footer' => $footer,
            'Order' => $Order,
        ));
    }
}
