<?php

namespace LeeBrooks3\Laravel\Passport\Providers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use LeeBrooks3\Laravel\OAuth2\Http\Clients\Client;
use LeeBrooks3\Laravel\Passport\Providers\Api\UserProvider as ApiUserProvider;
use LeeBrooks3\Laravel\Passport\Providers\Database\UserProvider as DatabaseUserProvider;
use LeeBrooks3\Models\ModelInterface;
use LeeBrooks3\Repositories\ModelRepositoryInterface;

class ServiceProvider extends PassportServiceProvider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register() : void
    {
        /** @var AuthManager $auth */
        $auth = $this->app->make(AuthManager::class);

        $auth->provider('passport_api_repository', function (Application $app, array $config) {
            return $this->makeApiUserProvider($app, $config);
        });

        $auth->provider('passport_database_repository', function (Application $app, array $config) {
            return $this->makeDatabaseUserProvider($app, $config);
        });

        parent::register();
    }

    /**
     * {@inheritdoc}
     *
     * @return \League\OAuth2\Server\Grant\PasswordGrant
     */
    protected function makePasswordGrant() : PasswordGrant
    {
        $grant = new PasswordGrant(
            $this->makeUserProvider(),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * Returns the user provider set in config.
     *
     * @return UserRepositoryInterface
     */
    protected function makeUserProvider() : UserRepositoryInterface
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $provider = $config->get('auth.providers.users.driver');

        if ($provider === 'passport_api_repository') {
            return $this->makeApiUserProvider($this->app, $config->get('auth.providers.users'));
        } elseif ($provider === 'passport_database_repository') {
            return $this->makeDatabaseUserProvider($this->app, $config->get('auth.providers.users'));
        }

        return null;
    }

    /**
     * Makes an API user provider instance.
     *
     * @param Application $app
     * @param array $config
     * @return ApiUserProvider
     */
    protected function makeApiUserProvider(Application $app, array $config) : ApiUserProvider
    {
        /**
         * @var ModelInterface $model
         * @var ModelRepositoryInterface $repository
         * @var Client $client
         */
        $model = $app->make($config['model']);
        $repository = $app->make($config['repository']);
        $client = $app->make(Client::class);

        return new ApiUserProvider($model, $repository, $client);
    }

    /**
     * Makes a database user provider instance.
     *
     * @param Application $app
     * @param array $config
     * @return DatabaseUserProvider
     */
    protected function makeDatabaseUserProvider(Application $app, array $config) : DatabaseUserProvider
    {
        /**
         * @var ModelInterface $model
         * @var ModelRepositoryInterface $repository
         * @var Hasher $hasher
         */
        $model = $app->make($config['model']);
        $repository = $app->make($config['repository']);
        $hasher = $app->make(Hasher::class);

        return new DatabaseUserProvider($model, $repository, $hasher);
    }
}
