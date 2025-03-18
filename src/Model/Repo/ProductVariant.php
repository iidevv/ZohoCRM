<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ProductVariant extends \XC\ProductVariants\Model\Repo\ProductVariant
{
    public const SEARCH_ZOHO_PRODUCT_VARIANTS = 'zoho_product_variants';

    /**
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndZohoProductVariants(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->leftJoin('v.zohoModel', 'zm')
            ->andWhere('zm.id IS NOT NULL')
            ->andWhere('zm.errors != :emptyString')
            ->setParameter('emptyString', '');
    }

    public function findVariantIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.zohoModel', 'zm')
            ->andWhere('zm.zoho_id IS NULL')
            ->select('v.id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }

    public function findVariantIdsToSyncInZoho()
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.zohoModel', 'zm')
            ->andWhere('zm.zoho_id IS NOT NULL')
            ->andWhere('zm.last_synced < :timeLimit')
            ->setParameter('timeLimit', time() - 3600)
            ->orderBy('zm.last_synced', 'DESC')
            ->select('v.id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }
}
