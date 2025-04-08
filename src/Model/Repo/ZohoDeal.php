<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ZohoDeal extends \XLite\Model\Repo\ARepo
{
    public function findClosedWonDealIdsToUpdateInZoho()
    {
        return $this->createQueryBuilder('zd')
            ->leftJoin('zd.order_id', 'o')
            ->andWhere('zd.zoho_id IS NOT NULL')
            ->andWhere('zd.closed_won = true')
            ->andWhere('zd.skipped = false OR zd.skipped IS NULL')
            ->select('o.order_id')
            ->orderBy('o.order_id', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
