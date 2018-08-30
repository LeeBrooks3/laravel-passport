<?php

namespace LeeBrooks3\Laravel\Passport\Providers\Api;

use Laravel\Passport\Bridge\User as PassportUser;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use LeeBrooks3\Laravel\OAuth2\Providers\UserProvider as BaseUserProvider;

class UserProvider extends BaseUserProvider implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @param $username
     * @param $password
     * @param $grantType
     * @param ClientEntityInterface $clientEntity
     * @return PassportUser|null
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = $this->retrieveByCredentials([
            'email' => $username,
            'password' => $password,
        ]);

        if (!$user || !$this->validateCredentials($user, ['password' => $password])) {
            return null;
        }

        return new PassportUser($user->getAuthIdentifier());
    }
}
