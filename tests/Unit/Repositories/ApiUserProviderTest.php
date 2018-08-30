<?php

namespace LeeBrooks3\Laravel\Passport\Tests\Unit\Providers;

use Laravel\Passport\Bridge\User as PassportUser;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use LeeBrooks3\Laravel\OAuth2\Tests\Unit\Providers\UserProviderTest as BaseApiUserProviderTest;
use LeeBrooks3\Laravel\Passport\Providers\Api\UserProvider as ApiUserProvider;
use LeeBrooks3\Laravel\Tests\Examples\Models\ExampleUser;
use LeeBrooks3\Repositories\ModelRepositoryInterface;

/**
 * @property ApiUserProvider $userProvider
 */
class ApiUserProviderTest extends BaseApiUserProviderTest
{
    /**
     * Creates a mock model repository and client instance and the repository instance to test.
     */
    public function setUp()
    {
        parent::setUp();

        $model = new ExampleUser();
        $this->mockRepository = $this->createMock(ModelRepositoryInterface::class);

        $this->userProvider = new ApiUserProvider($model, $this->mockRepository, $this->mockClient);
    }

    /**
     * Tests that a passport user entity can be returned by credentials.
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
        ]);
        $credentials = [
            'password' => $password,
        ];

        $this->mockRepository->expects($this->once())
            ->method('get')
            ->with([
                'email' => $user->email,
            ])
            ->willReturn([
                $user,
            ]);

        $this->mockClient->expects($this->once())
            ->method('getUserToken')
            ->with($user->email, $credentials['password']);

        $result = $this->userProvider->getUserEntityByUserCredentials($email, $password, '', $mockClientEntity);

        $this->assertInstanceOf(PassportUser::class, $result);
    }
}
