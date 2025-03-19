<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Message extends \XC\VendorMessages\Model\Repo\Message
{
    public function findOrderMessageIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.zohoModel', 'zm')
            ->leftJoin('m.conversation', 'c')
            ->leftJoin('c.order', 'o')
            ->leftJoin('Iidev\ZohoCRM\Model\ZohoOrder', 'zo', 'WITH', 'o.order_id = zo.order_id')
            ->andWhere('m.conversation IS NOT NULL')
            ->andWhere('m.author IS NOT NULL')
            ->andWhere('zm.zoho_id IS NULL')
            ->andWhere('zm.skipped = false OR zm.skipped IS NULL')
            // ->andWhere('zo.zoho_id IS NOT NULL OR o.order_id IS NULL')
            ->andWhere('zo.zoho_id IS NOT NULL')
            ->select('m.id')
            ->setMaxResults(30)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
