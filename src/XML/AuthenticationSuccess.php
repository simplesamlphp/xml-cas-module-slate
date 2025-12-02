<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\CAS\XML\AbstractAttributes;
use SimpleSAML\CAS\XML\AbstractAuthenticationSuccess;
use SimpleSAML\CAS\XML\Proxies;
use SimpleSAML\CAS\XML\ProxyGrantingTicket;
use SimpleSAML\CAS\XML\User;
use SimpleSAML\Slate\Constants as C;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;

use function array_pop;

/**
 * Class for CAS authenticationSuccess
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
final class AuthenticationSuccess extends AbstractAuthenticationSuccess
{
    use ExtendableElementTrait;


    /** The namespace-attribute for the xs:any element */
    final public const XS_ANY_ELT_NAMESPACE = [C::NS_SLATE];


    /**
     * Initialize a cas:authenticationSuccess element
     *
     * @param \SimpleSAML\CAS\XML\User $user
     * @param \SimpleSAML\CAS\XML\AbstractAttributes $attributes
     * @param \SimpleSAML\CAS\XML\ProxyGrantingTicket|null $proxyGrantingTicket
     * @param \SimpleSAML\CAS\XML\Proxies|null $proxies
     * @param \SimpleSAML\XML\SerializableElementInterface[] $children
     */
    final public function __construct(
        protected User $user,
        protected AbstractAttributes $attributes,
        protected ?ProxyGrantingTicket $proxyGrantingTicket = null,
        protected ?Proxies $proxies = null,
        array $children = [],
    ) {
        parent::__construct($user, $attributes, $proxyGrantingTicket, $proxies);
        $this->setElements($children);
    }


    /**
     * Convert XML into a cas:authenticationSuccess-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::getNamespaceURI(), InvalidDOMElementException::class);

        $user = User::getChildrenOfClass($xml);
        Assert::count(
            $user,
            1,
            'Exactly one <cas:user> must be specified.',
            MissingElementException::class,
        );

        $attributes = Attributes::getChildrenOfClass($xml);
        Assert::count(
            $attributes,
            1,
            'Exactly one <cas:attributes> must be specified.',
            MissingElementException::class,
        );

        $proxyGrantingTicket = ProxyGrantingTicket::getChildrenOfClass($xml);
        $proxies = Proxies::getChildrenOfClass($xml);

        return new static(
            $user[0],
            $attributes[0],
            array_pop($proxyGrantingTicket),
            array_pop($proxies),
            self::getChildElementsFromXML($xml),
        );
    }


    /**
     * Convert this AuthenticationSuccess to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthenticationSuccess to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->getUser()->toXML($e);

        foreach ($this->getElements() as $elt) {
            if (!$elt->isEmptyElement()) {
                $elt->toXML($e);
            }
        }

        $this->getAttributes()->toXML($e);
        $this->getProxyGrantingTicket()?->toXML($e);
        $this->getProxies()?->toXML($e);

        return $e;
    }
}
