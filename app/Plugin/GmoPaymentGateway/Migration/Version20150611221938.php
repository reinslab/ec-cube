<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Common\Constant;
class Version20150611221938 extends AbstractMigration
{

    public function up(Schema $schema)
    {   
        $app = new \Eccube\Application();
        $app->initDoctrine();
        $app->boot();

        $this->createPagelayout($app, 'gmo_mypage_change_card', 10, 'MYページ/カード情報編集');
    }

    public function down(Schema $schema)
    {
        $app = new \Eccube\Application();
        $app->initDoctrine();
        $app->boot();
        $pageLayoutRepo = $app['orm.em']->getRepository('Eccube\Entity\PageLayout');
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4', '3.0.5','3.0.6');
        in_array(Constant::VERSION, $listOldVersion) ? $pageLayoutRepo->setApp($app) : $pageLayoutRepo->setApplication($app);
        $pageLayout = $pageLayoutRepo->findOneBy(array('url' => 'gmo_mypage_change_card'));
        if(!is_null($pageLayout)){
            $app['orm.em']->remove($pageLayout);
            $app['orm.em']->flush();
        }
    }

    protected function createPagelayout($app, $url, $deviceId, $name)
    {
        $deviceTypeRepo = $app['orm.em']->getRepository('Eccube\Entity\Master\DeviceType');
        $pageLayoutRepo = $app['orm.em']->getRepository('Eccube\Entity\PageLayout');
        //Start select method set application follow version, method setApp on 3.0.7 changed to setApplication
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4', '3.0.5','3.0.6');
        in_array(Constant::VERSION, $listOldVersion) ? $pageLayoutRepo->setApp($app) : $pageLayoutRepo->setApplication($app);
        //End of select method set application follow version.
        $deviceType = $deviceTypeRepo->find($deviceId);
        $pageLayout = $pageLayoutRepo->findOneBy(array('url' => $url));
        if (is_null($pageLayout)) {
            $pageLayout = $pageLayoutRepo->newPageLayout($deviceType);
        }
        $pageLayout->setCreateDate(new \DateTime());
        $pageLayout->setUpdateDate(new \DateTime());
        $pageLayout->setName($name);
        $pageLayout->setUrl($url);
        $pageLayout->setMetaRobots('noindex');
        $pageLayout->setEditFlg('2');
        $app['orm.em']->persist($pageLayout);
        $app['orm.em']->flush();
    }
}
