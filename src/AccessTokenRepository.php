<?php

namespace Wanphp\Plugins\OAuth2Resource;


use Exception;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use LogicException;
use Psr\Cache\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
  private CacheInterface $storage;

  public function __construct(CacheInterface $storage)
  {
    $this->storage = $storage;
  }

  /**
   * @param ClientEntityInterface $clientEntity
   * @param array $scopes
   * @param null $userIdentifier
   * @return AccessTokenEntityInterface
   */
  public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
  {
    throw new LogicException('此方法在资源服务器上不需要实现。');
  }

  public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
  {
    throw new LogicException('此方法在资源服务器上不需要实现。');
  }

  public function revokeAccessToken($tokenId)
  {
    throw new LogicException('此方法在资源服务器上不需要实现。');
  }

  /**
   * @throws Exception
   * @throws InvalidArgumentException
   */
  public function isAccessTokenRevoked($tokenId): bool
  {
    // 资源服务器验证访问令牌时将调用此方法
    // 用于验证访问令牌是否已被删除
    // return true 已删除，false 未删除
    return !$this->storage->has($tokenId);
  }

}
