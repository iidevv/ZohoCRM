<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XLite\Core\Config;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Order extends \XLite\Model\Repo\Order
{
    public function findOrderIdsToCreateInZoho()
    {
        $createOrdersFrom = (int) Config::getInstance()->Iidev->ZohoCRM->orders_from_number;

        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.zoho_id IS NULL')
            ->select('o.order_id')
            ->setMaxResults(1);

        if ($createOrdersFrom > 0) {
            $qb->andWhere('o.orderNumber >= :orderNumber')
                ->setParameter('orderNumber', $createOrdersFrom);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }
}
