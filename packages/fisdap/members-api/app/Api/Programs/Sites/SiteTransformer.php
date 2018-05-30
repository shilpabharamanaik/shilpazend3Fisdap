<?php namespace Fisdap\Api\Programs\Sites;

use Fisdap\Api\Programs\Sites\Bases\BaseTransformer;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Fractal\Transformer;

/**
 * Prepares site data for JSON output
 *
 * @package Fisdap\Api\Programs\Sites
 */
final class SiteTransformer extends Transformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'bases'
    ];


    /**
     * @param array $site
     *
     * @return array
     */
    public function transform($site)
    {
        if ($site instanceof SiteLegacy) {
            $site = $site->toArray();
        }

        $transformed = [
            'id'            => $site['id'],
            'name'          => $site['name'],
            'contact_name'  => $site['contact_name'],
            'contact_title' => $site['contact_title'],
            'contact_email' => $site['contact_email'],
            'address'       => $site['address'],
            'city'          => $site['city'],
            'zipcode'       => $site['zipcode'],
            'state'         => $site['state'],
            'country'       => $site['country'],
            'phone'         => $site['phone'],
            'fax'           => $site['fax'],
            'abbreviation'  => $site['abbreviation'],
            'type'          => $site['type'],
            'base_ids'      => $this->getIdsFromAssociation('bases', $site)
        ];

        if (isset($site['active'])) {
            $transformed['active'] = $site['active'];
        }

        return $transformed;
    }


    /**
     * @param array $site
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeBases(array $site)
    {
        $bases = $site['bases'];

        return $this->collection($bases, new BaseTransformer);
    }
}
