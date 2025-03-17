<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Profile extends \XLite\Model\Repo\Profile
{
    public function findProfileIdsToCreateInZoho()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.zohoModel', 'zm')
            ->join('p.addresses', 'a')
            ->where('p.order IS NULL')
            ->andWhere('zm.zoho_id IS NULL')
            ->andWhere('zm.skipped = false OR zm.skipped IS NULL')
            ->andWhere('a.address_id IS NOT NULL')
            ->select('DISTINCT p.profile_id')
            ->setMaxResults(30)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
