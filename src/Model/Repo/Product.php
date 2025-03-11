<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Product extends \XLite\Model\Repo\Product
{
    public function findProductIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('p')
            ->where('p.enabled = :enabled')
            ->andWhere('p.zoho_id IS NULL')
            ->setParameter('enabled', 1)
            ->select('p.product_id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }

    public function findProductIdsToSyncInZoho()
    {
        return $this->createQueryBuilder('p')
            ->where('p.enabled = :enabled')
            ->andWhere('p.zoho_id IS NOT NULL')
            ->andWhere('p.zoho_last_synced < :timeLimit')
            ->setParameter('enabled', 1)
            ->setParameter('timeLimit', time() - 3600)
            ->orderBy('p.zoho_last_synced', 'DESC')
            ->select('p.product_id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }
}
