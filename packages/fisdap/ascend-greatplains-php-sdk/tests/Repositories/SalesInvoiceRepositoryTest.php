<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Repositories;

use Fisdap\Ascend\Greatplains\Repositories\Repository;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\Repository as RepositoryInterface;
use Fisdap\Ascend\Greatplains\Repositories\SalesInvoiceRepository;
use Fisdap\Ascend\Greatplains\Repositories\SalesInvoiceRepository as SalesInvoiceRepositoryInterface;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class SalesInvoiceRepositoryTest
 *
 * Tests for sales invoice repository
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceRepositoryTest extends TestCase
{
    /**
     * Test sales invoice repository has correct contracts
     */
    public function testSalesInvoiceRepositoryHasCorrectContracts()
    {
        $repo = new SalesInvoiceRepository();

        $this->assertInstanceOf(SalesInvoiceRepositoryInterface::class, $repo);
        $this->assertInstanceOf(Repository::class, $repo);
        $this->assertInstanceOf(RepositoryInterface::class, $repo);
    }
}
