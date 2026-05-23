<?php

namespace Tests\Unit\Services\Application\Handlers\Admin;

use TitaKita\DomainObjects\AccountConfigurationDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use TitaKita\Services\Application\Handlers\Admin\DeleteConfigurationHandler;
use Mockery;
use Tests\TestCase;

class DeleteConfigurationHandlerTest extends TestCase
{
    private AccountConfigurationRepositoryInterface $repository;
    private DeleteConfigurationHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(AccountConfigurationRepositoryInterface::class);
        $this->handler = new DeleteConfigurationHandler($this->repository);
    }

    public function testHandleSuccessfullyDeletesConfiguration(): void
    {
        $configurationId = 123;
        $configuration = Mockery::mock(AccountConfigurationDomainObject::class);

        $configuration
            ->shouldReceive('getIsSystemDefault')
            ->once()
            ->andReturn(false);

        $this->repository
            ->shouldReceive('findById')
            ->with($configurationId)
            ->once()
            ->andReturn($configuration);

        $this->repository
            ->shouldReceive('deleteById')
            ->with($configurationId)
            ->once();

        $this->handler->handle($configurationId);

        $this->assertTrue(true);
    }

    public function testHandleThrowsExceptionWhenDeletingSystemDefault(): void
    {
        $configurationId = 1;
        $configuration = Mockery::mock(AccountConfigurationDomainObject::class);

        $configuration
            ->shouldReceive('getIsSystemDefault')
            ->once()
            ->andReturn(true);

        $this->repository
            ->shouldReceive('findById')
            ->with($configurationId)
            ->once()
            ->andReturn($configuration);

        $this->repository
            ->shouldNotReceive('deleteById');

        $this->expectException(CannotDeleteEntityException::class);
        $this->expectExceptionMessage('The system default configuration cannot be deleted.');

        $this->handler->handle($configurationId);
    }

    public function testHandleThrowsExceptionWhenConfigurationNotFound(): void
    {
        $configurationId = 999;

        $this->repository
            ->shouldReceive('findById')
            ->with($configurationId)
            ->once()
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->handler->handle($configurationId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
