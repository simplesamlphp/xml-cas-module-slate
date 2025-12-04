<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\XML\AbstractResponse;
use SimpleSAML\CAS\XML\Proxies;
use SimpleSAML\CAS\XML\ProxyGrantingTicket;
use SimpleSAML\CAS\XML\User;

/**
 * Base class for Slate authenticationSuccess
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
abstract class AbstractAuthenticationSuccess extends AbstractResponse
{
    /** @var string */
    final public const LOCALNAME = 'authenticationSuccess';


    /**
     * Initialize a slate:authenticationSuccess element
     *
     * @param \SimpleSAML\CAS\XML\User $user
     * @param \SimpleSAML\Slate\XML\AbstractAttributes $attributes
     * @param \SimpleSAML\CAS\XML\ProxyGrantingTicket|null $proxyGrantingTicket
     * @param \SimpleSAML\CAS\XML\Proxies|null $proxies
     */
    public function __construct(
        protected User $user,
        protected AbstractAttributes $attributes,
        protected ?ProxyGrantingTicket $proxyGrantingTicket = null,
        protected ?Proxies $proxies = null,
    ) {
    }


    /**
     * @return \SimpleSAML\CAS\XML\User
     */
    public function getUser(): User
    {
        return $this->user;
    }


    /**
     * @return \SimpleSAML\Slate\XML\AbstractAttributes
     */
    public function getAttributes(): AbstractAttributes
    {
        return $this->attributes;
    }


    /**
     * @return \SimpleSAML\CAS\XML\ProxyGrantingTicket|null
     */
    public function getProxyGrantingTicket(): ?ProxyGrantingTicket
    {
        return $this->proxyGrantingTicket;
    }


    /**
     * @return \SimpleSAML\CAS\XML\Proxies|null
     */
    public function getProxies(): ?Proxies
    {
        return $this->proxies;
    }
}
