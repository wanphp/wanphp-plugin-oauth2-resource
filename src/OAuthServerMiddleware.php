<?php

namespace Wanphp\Plugins\OAuth2Resource;


use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Predis\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Wanphp\Libray\Slim\Setting;

class OAuthServerMiddleware implements MiddlewareInterface
{
  private Client $redis;
  private string $publicKeyPath;

  /**
   * @param Setting $setting
   */
  public function __construct(Setting $setting)
  {
    $config = $setting->get('oauth2Config');
    $this->redis = new Client($config['redis']['parameters'], $config['redis']['options']);
    //授权服务器分发的公钥
    $this->publicKeyPath = realpath($config['publicKey']);
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $accessTokenRepository = new AccessTokenRepository($this->redis);

    $server = new ResourceServer($accessTokenRepository, $this->publicKeyPath);
    try {
      $request = $server->validateAuthenticatedRequest($request);
      return $handler->handle($request);
    } catch (OAuthServerException $exception) {
      return $exception->generateHttpResponse(new Response());
      // @codeCoverageIgnoreStart
    } catch (\Exception $exception) {
      return (new OAuthServerException($exception->getMessage(), 0, 'BadRequest'))
        ->generateHttpResponse(new Response());
      // @codeCoverageIgnoreEnd
    }
  }

}
