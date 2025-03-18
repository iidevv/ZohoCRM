<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Product extends \XLite\Model\Repo\Product
{
    public const SEARCH_ZOHO_PRODUCTS = 'zoho_products';

    /**
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndZohoProducts(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->leftJoin('p.zohoModel', 'zm')
            ->andWhere('zm.product_id IS NOT NULL')
            ->andWhere('zm.errors != :emptyString')
            ->setParameter('emptyString', '');
    }

    public function findProductIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.zohoModel', 'zm')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('zm.zoho_id IS NULL')
            ->andWhere('zm.skipped = false OR zm.skipped IS NULL')
            ->setParameter('enabled', 1)
            ->select('p.product_id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }

    public function findProductIdsToSyncInZoho()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.zohoModel', 'zm')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('zm.zoho_id IS NOT NULL')
            ->andWhere('zm.last_synced < :timeLimit')
            ->setParameter('enabled', 1)
            ->setParameter('timeLimit', time() - 3600)
            ->orderBy('zm.last_synced', 'DESC')
            ->select('p.product_id')
            ->setMaxResults(30)
            ->getQuery()->getSingleColumnResult();
    }
}
