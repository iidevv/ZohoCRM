<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XLite\Core\Config;
use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Cart extends \XLite\Model\Repo\Cart
{

    public function findDealIdsToCreateInZoho()
    {
        $dateFrom = Config::getInstance()->Iidev->ZohoCRM->deals_from_date;
        $date = new \DateTime($dateFrom);

        $abandonmentThreshold = \XLite\Model\Order::getAbandonmentTime();
        $maxAbandonedCartDate = \XLite\Core\Converter::time() - $abandonmentThreshold;

        return $this->createQueryBuilder('c')
            ->leftJoin('c.zohoDeal', 'zd')
            ->andWhere('zd.zoho_id IS NULL')
            ->andWhere('zd.skipped = false OR zd.skipped IS NULL')

            ->innerJoin('c.profile', 'p')
            ->andWhere('p.login <> :empty_login')
            ->setParameter('empty_login', '')

            ->innerJoin('c.items', 'items')
            ->andWhere('items.item_id IS NOT NULL')
            ->groupBy('c.order_id')

            ->andWhere('c.lastVisitDate <= :max_abandoned_cart_date')
            ->setParameter('max_abandoned_cart_date', $maxAbandonedCartDate)

            ->andWhere('c.lost = 0')

            ->andWhere('c.date > :dateFrom')
            ->setParameter('dateFrom', $date->getTimestamp())

            ->select('c.order_id')
            ->orderBy('c.lastVisitDate', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findDealIdsToUpdateInZoho()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.zohoDeal', 'zd')
            ->andWhere('zd.zoho_id IS NOT NULL')
            ->andWhere('zd.synced = false')
            ->andWhere('zd.closed_won = false')
            ->andWhere('zd.order_id IS NOT NULL')
            ->andWhere('zd.skipped = false OR zd.skipped IS NULL')
            ->select('c.order_id')
            ->orderBy('c.order_id', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
