<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;

/**
 * Interface EntityTransformer
 *
 * Entity transformer interface
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface EntityTransformer
{
    /**
     * Get the entity
     *
     * @return Entity
     */
    public function getEntity();
}
