<?php
namespace KmbPuppetTest\Service;

use KmbPuppet\Service\ReportCollectorFactory;
use KmbPuppetTest\Bootstrap;

class ReportCollectorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new ReportCollectorFactory();

        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbPuppet\Service\ReportCollector', $service);
        $this->assertInstanceOf('KmbPuppetDb\Service\Report', $service->getReportService());
        $this->assertInstanceOf('KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface', $service->getReportsEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPermission\Service\EnvironmentInterface', $service->getPermissionEnvironmentService());
    }
}
