<?php

namespace Fisdap\Api\Programs\Sites\Preceptors;

use Fisdap\Entity\PreceptorLegacy;
use Fisdap\Fractal\Transformer;

final class PreceptorTransformer extends Transformer
{
    /**
     * The response should contain camel case since that is how the data
     *
     * @param $preceptor
     * @return array
     */
    public function transform($preceptor)
    {
        if ($preceptor instanceof PreceptorLegacy) {
            $preceptor = $preceptor->toArray();
        }

        $preceptor['firstName'] = (isset($preceptor['first_name']) ? $preceptor['first_name'] : "");
        $preceptor['lastName'] = (isset($preceptor['last_name']) ? $preceptor['last_name'] : "");
        $preceptor['dateSelected'] = (isset($preceptor['date_selected']) ? $preceptor['date_selected'] : "");
        $preceptor['mainBase'] = (isset($preceptor['main_base']) ? $preceptor['main_base'] : "");
        $preceptor['homePhone'] = (isset($preceptor['home_phone']) ? $preceptor['home_phone'] : "");
        $preceptor['workPhone'] = (isset($preceptor['work_phone']) ? $preceptor['work_phone'] : "");
        $preceptor['advisorRnum'] = (isset($preceptor['advisor_rnum']) ? $preceptor['advisor_rnum'] : "");
        $preceptor['pdaPreceptorId'] = (isset($preceptor['pda_preceptor_id']) ? $preceptor['pda_preceptor_id'] : "");

        unset($preceptor['first_name']);
        unset($preceptor['last_name']);
        unset($preceptor['date_selected']);
        unset($preceptor['main_base']);
        unset($preceptor['home_phone']);
        unset($preceptor['work_phone']);
        unset($preceptor['advisor_rnum']);
        unset($preceptor['pda_preceptor_id']);

        return $preceptor;
    }
}
