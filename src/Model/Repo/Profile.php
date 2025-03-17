<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class Profile extends \XLite\Model\Repo\Profile
{
    public const SEARCH_ZOHO_PROFILES = 'zoho_profiles';

    /**
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndZohoProfiles(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->leftJoin('p.zohoModel', 'zm')
            ->andWhere('zm.profile_id IS NOT NULL')
            ->andWhere('zm.skipped = true');
    }

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
