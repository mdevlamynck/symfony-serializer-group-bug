<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SerializerTest extends KernelTestCase
{
    public static function dataProvider(): iterable
    {
        yield 'without group, without accessor' => [
            ['isSomething' => true],
            new DataWithoutGroupWithoutAccessor(isSomething: true),
            false
        ];

        yield 'with group, without accessor' => [
            ['isSomething' => true],
            new DataWithGroupWithoutAccessor(isSomething: true),
            true
        ];

        yield 'without group, with accessor' => [
            ['isSomething' => true],
            new DataWithoutGroupWithAccessor(isSomething: true),
            false
        ];

        yield 'with group, with accessor' => [
            // Fails with 'something' instead of 'isSomething'
            ['isSomething' => true],
            new DataWithGroupWithAccessor(isSomething: true),
            true
        ];
    }

    #[DataProvider('dataProvider')]
    public function test($expected, $object, $withGroup): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $normalizer = $container->get(NormalizerInterface::class);

        $data = $normalizer->normalize(
            $object,
            context: $withGroup ? ['groups' => ['is_something']] : []
        );

        $this->assertEquals($expected, $data);
    }
}

class DataWithoutGroupWithoutAccessor
{
    public function __construct(
        public bool $isSomething = false,
    ) {
    }
}

class DataWithGroupWithoutAccessor
{
    public function __construct(
        #[Groups('is_something')]
        public bool $isSomething = false,
    ) {
    }
}

class DataWithoutGroupWithAccessor
{
    public function __construct(
        private bool $isSomething = false,
    ) {
    }

    public function isSomething(): bool
    {
        return $this->isSomething;
    }
}

class DataWithGroupWithAccessor
{
    public function __construct(
        private bool $isSomething = false,
    ) {
    }

    #[Groups('is_something')]
    public function isSomething(): bool
    {
        return $this->isSomething;
    }
}
