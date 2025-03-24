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
            ->leftJoin('o.zohoModel', 'zo')
            ->andWhere('zo.order_id IS NOT NULL')
            ->andWhere('zo.errors != :emptyString')
            ->setParameter('emptyString', '');
    }

    public function findOrderIdsToCreateInZoho()
    {
        $createOrdersFrom = (int) Config::getInstance()->Iidev->ZohoCRM->orders_from_number;

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.zohoModel', 'zo')
            ->leftJoin('o.zohoQuote', 'zq')
            ->andWhere('o.payment_method_name != :paymentMethod OR zq.zoho_id IS NOT NULL')
            ->andWhere('zo.zoho_id IS NULL')
            ->andWhere('zo.skipped = false OR zo.skipped IS NULL')
            ->setParameter('paymentMethod', 'Quote')
            ->select('o.order_id')
            ->setMaxResults(30);

        if ($createOrdersFrom > 0) {
            $qb->andWhere('o.orderNumber >= :orderNumber')
                ->setParameter('orderNumber', $createOrdersFrom);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function findOrderIdsToUpdateInZoho()
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.zohoModel', 'zo')
            ->andWhere('zo.zoho_id IS NOT NULL')
            ->andWhere('zo.synced = false')
            ->andWhere('zo.skipped = false OR zo.skipped IS NULL')
            ->select('o.order_id')
            ->setMaxResults(30)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findQuoteIdsToCreateInZoho()
    {
        $createOrdersFrom = (int) Config::getInstance()->Iidev->ZohoCRM->orders_from_number;

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.zohoQuote', 'zq')
            ->andWhere('o.payment_method_name = :paymentMethod')
            ->andWhere('zq.zoho_id IS NULL')
            ->andWhere('zq.skipped = false OR zq.skipped IS NULL')
            ->setParameter('paymentMethod', 'Quote')
            ->select('o.order_id')
            ->setMaxResults(30);

        if ($createOrdersFrom > 0) {
            $qb->andWhere('o.orderNumber >= :orderNumber')
                ->setParameter('orderNumber', $createOrdersFrom);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function findQuoteIdsToUpdateInZoho()
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.zohoQuote', 'zq')
            ->andWhere('zq.zoho_id IS NOT NULL')
            ->andWhere('zq.synced = false')
            ->andWhere('zq.skipped = false OR zq.skipped IS NULL')
            ->select('o.order_id')
            ->setMaxResults(30)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
