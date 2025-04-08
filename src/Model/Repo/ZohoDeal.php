<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ZohoDeal extends \XLite\Model\Repo\ARepo
{
    public function findClosedDealIdsToUpdateInZoho()
    {
        return $this->createQueryBuilder('zd')
            ->leftJoin('zd.order_id', 'o')
            ->andWhere('zd.zoho_id IS NOT NULL')
            ->andWhere('zd.closed_won = true OR zd.order_id IS NULL')
            ->andWhere('zd.skipped = false OR zd.skipped IS NULL')
            ->select('zd.id')
            ->orderBy('zd.id', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
