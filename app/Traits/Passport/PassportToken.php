<?php
namespace App\Traits\Passport;

use App\Models\User;
use DateTimeImmutable;
use Error;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Psr\Http\Message\ResponseInterface;
use TypeError;
use Laravel\Passport\Bridge\Scope;
use function time;

# https://github.com/laravel/passport/issues/71

/**
 * Trait PassportToken
 *
 * @package App\Traits
 */
trait PassportToken
{
    use ConvertsPsrResponses;

    /**
     * Generate a new unique identifier.
     *
     * @param int $length
     *
     * @return string
     * @throws OAuthServerException
     *
     */
    private function generateUniqueIdentifier($length = 40): string
    {
        try {
            return bin2hex(random_bytes($length));
            // @codeCoverageIgnoreStart
        } catch (TypeError $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (Error $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
        // @codeCoverageIgnoreEnd
    }

    private function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        $maxGenerationAttempts = 10;
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->getNewRefreshToken();
        $refreshToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $refreshTokenRepository->persistNewRefreshToken($refreshToken);

                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
    }

    protected function createPassportTokenByUser(User $user, $clientId, array $scopes = []): array
    {
        $clientRepository = app(ClientRepository::class);
        $client = $clientRepository->getClientEntity($clientId);

        $scopesObj = [];
        if (is_array($scopes)) {
            foreach ($scopes as $scope) {
                $scopesObj[] = new Scope($scope);
            }
        }
        $accessToken = new AccessToken($user->getAuthIdentifier(), $scopesObj, $client);
        $accessToken->setIdentifier($this->generateUniqueIdentifier());
        $accessToken->setClient(new Client($clientId, null, null));
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::tokensExpireIn()));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
        $accessTokenRepository->persistNewAccessToken($accessToken);
        $refreshToken = $this->issueRefreshToken($accessToken);

        $privateKey = new CryptKey('file://' . Passport::keyPath('oauth-private.key'), null, false);

        $accessToken->setPrivateKey($privateKey);

        $expireDateTime = $accessToken->getExpiryDateTime()->getTimestamp();

        return [
            'token_type'    => 'Bearer',
            'expires_in'    => $expireDateTime - time(),
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    protected function sendBearerTokenResponse($accessToken, $refreshToken): ResponseInterface
    {
        $response = new BearerTokenResponse();
        $response->setAccessToken($accessToken);
        $response->setRefreshToken($refreshToken);

        $privateKey = new CryptKey('file://' . Passport::keyPath('oauth-private.key'), null, false);

        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        return $response->generateHttpResponse(new Response);
    }

    /**
     * @param User $user
     * @param $clientId
     * @param array $scopes
     * @param bool $output
     * @return \Illuminate\Http\Response|mixed
     */
    protected function getBearerTokenByUser(User $user, $clientId, array $scopes = [], bool $output = true): mixed
    {
        $passportToken = $this->createPassportTokenByUser($user, $clientId, $scopes);
        $bearerToken = $this->sendBearerTokenResponse($passportToken['access_token'], $passportToken['refresh_token']);
        if ($output) {
            $bearerToken = $this->convertResponse($bearerToken);
        } else {
            $bearerToken = json_decode($bearerToken->getBody()->__toString(), true);
        }

        return $bearerToken;
    }
}
