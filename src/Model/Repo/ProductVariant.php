<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ProductVariant extends \XC\ProductVariants\Model\Repo\ProductVariant
{
    public function findVariantIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('v')
            ->where('v.enabled = :enabled')
            ->andWhere('v.zoho_id IS NULL')
            ->setParameter('enabled', 1)
            ->select('v.id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }

    public function findVariantIdsToSyncInZoho()
    {
        return $this->createQueryBuilder('v')
            ->where('v.enabled = :enabled')
            ->andWhere('v.zoho_id IS NOT NULL')
            ->andWhere('v.zoho_last_synced < :timeLimit')
            ->setParameter('enabled', 1)
            ->setParameter('timeLimit', time() - 3600)
            ->orderBy('v.zoho_last_synced', 'DESC')
            ->select('v.id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }
}
