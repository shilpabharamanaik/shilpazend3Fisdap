<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;
use Fisdap\Data\Order\Permission\OrderPermissionRepository;

/**
 * Class CommerceAttributesSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CommerceAttributesSetter
{
    /**
     * @var OrderPermissionRepository
     */
    private $orderPermissionRepository;


    /**
     * CommerceAttributesSetter constructor.
     *
     * @param OrderPermissionRepository $orderPermissionRepository
     */
    public function __construct(OrderPermissionRepository $orderPermissionRepository)
    {
        $this->orderPermissionRepository = $orderPermissionRepository;
    }


    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        /** @noinspection PhpParamsInspection */
        $listener->getProgram()->setOrderPermission(
            $this->orderPermissionRepository->getOneById(
                $listener->getEvent()->getSettings()->commerce->orderPermissionId
            )
        );
    }
}
