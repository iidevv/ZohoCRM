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
            ->join('p.addresses', 'a')
            ->where('p.order IS NULL')
            ->andWhere('p.zoho_id IS NULL')
            ->andWhere('a.address_id IS NOT NULL')
            ->select('DISTINCT p.profile_id')
            ->setMaxResults(30)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
