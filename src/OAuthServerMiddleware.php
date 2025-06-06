<?php

namespace Wanphp\Plugins\OAuth2Resource;


use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class OAuthServerMiddleware implements MiddlewareInterface
{
  protected CacheItemPoolInterface $storage;
  private string $publicKeyPath;

  /**
   * @param array $config
   * @param CacheItemPoolInterface $storage
   */
  public function __construct(array $config, CacheItemPoolInterface $storage)
  {
    $this->storage = $storage;
    //授权服务器分发的公钥
    $this->publicKeyPath = realpath($config['publicKey']);
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $accessTokenRepository = new AccessTokenRepository($this->storage);

    $server = new ResourceServer($accessTokenRepository, $this->publicKeyPath);
    try {
      $request = $server->validateAuthenticatedRequest($request);
      return $handler->handle($request);
    } catch (OAuthServerException $exception) {
      return $exception->generateHttpResponse(new Response());
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return (new OAuthServerException($exception->getMessage(), 0, 'BadRequest'))
        ->generateHttpResponse(new Response());
      // @codeCoverageIgnoreEnd
    }
  }

}
