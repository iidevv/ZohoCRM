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
            ->setParameter('enabled', 0)
            ->select('p.product_id')
            ->setMaxResults(100)
            ->getQuery()->getSingleColumnResult();
    }
}
