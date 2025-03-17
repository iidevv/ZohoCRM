<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XLite\Core\Config;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Order extends \XLite\Model\Repo\Order
{
    public const SEARCH_ZOHO_ORDERS = 'zoho_orders';

    /**
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndZohoOrders(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->leftJoin('o.zohoModel', 'zm')
            ->andWhere('zm.order_id IS NOT NULL')
            ->andWhere('zm.errors != :emptyString')
            ->setParameter('emptyString', '');
    }

    public function findOrderIdsToCreateInZoho()
    {
        $createOrdersFrom = (int) Config::getInstance()->Iidev->ZohoCRM->orders_from_number;

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.zohoModel', 'zm')
            ->andWhere('zm.zoho_id IS NULL')
            ->andWhere('zm.skipped = false OR zm.skipped IS NULL')
            ->select('o.order_id')
            ->setMaxResults(1);

        if ($createOrdersFrom > 0) {
            $qb->andWhere('o.orderNumber >= :orderNumber')
                ->setParameter('orderNumber', $createOrdersFrom);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }
}
