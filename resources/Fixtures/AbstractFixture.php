<?php declare(strict_types=1);
namespace Tranquillity\Seeds\Fixtures;

// Library classes
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture as DoctrineAbstractFixture;

abstract class AbstractFixture extends DoctrineAbstractFixture implements FixtureInterface {};