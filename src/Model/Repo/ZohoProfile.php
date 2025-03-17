<?php

namespace Iidev\ZohoCRM\Model\Repo;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class ZohoProfile extends \XLite\Model\Repo\ARepo
{
    /**
     * @return void
     */
    public function deleteEntities($ids)
    {
        if (empty($ids)) {
            return;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->createQueryBuilder('zm')
            ->where('zm.profile_id IN (:ids)')
            ->setParameter('ids', $ids)
            ->delete()
            ->getQuery()
            ->execute();
    }
}
