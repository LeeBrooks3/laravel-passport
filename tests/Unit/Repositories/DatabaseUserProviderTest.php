<?php

namespace LeeBrooks3\Laravel\Passport\Tests\Unit\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use LeeBrooks3\Laravel\Passport\Providers\Database\UserProvider as DatabaseUserProvider;
use LeeBrooks3\Laravel\Tests\Examples\Models\ExampleUser;
use LeeBrooks3\Laravel\Tests\Unit\Providers\DatabaseUserProviderTest as BaseDatabaseUserProviderTest;
use LeeBrooks3\Repositories\ModelRepositoryInterface;

/**
 * @property DatabaseUserProvider $userProvider
 */
class DatabaseUserProviderTest extends BaseDatabaseUserProviderTest
{
    /**
     * Creates a mock model repository and client instance and the repository instance to test.
     */
    public function setUp()
    {
        parent::setUp();

        $model = new ExampleUser();
        $this->mockRepository = $this->createMock(ModelRepositoryInterface::class);

        $this->userProvider = new DatabaseUserProvider($model, $this->mockRepository, $this->mockHasher);
    }

    /**
     * Tests that a passport user entity can not be returned by credentials.
     */
    public function testGetUserEntityByCredentials()
    {
        /** @var ClientEntityInterface $mockClientEntity */
        $email = $this->faker->email;
        $password = $this->faker->password;
        $mockClientEntity = $this->createMock(ClientEntityInterface::class);
        $user = new ExampleUser([
            'id' => $this->faker->uuid,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $this->mockRepository->expects($this->once())
            ->method('get')
            ->with([
                'email' => $user->email,
            ])
            ->willReturn([]);

        $result = $this->userProvider->getUserEntityByUserCredentials($email, $password, '', $mockClientEntity);

        $this->assertNull($result);
    }
}
