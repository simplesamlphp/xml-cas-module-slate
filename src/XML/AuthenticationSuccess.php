<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;

use function array_pop;

/**
 * Class for CAS authenticationSuccess
 *
 * @package simplesamlphp/cas
 */
final class AuthenticationSuccess extends AbstractResponse
{
    /** @var string */
    final public const LOCALNAME = 'authenticationSuccess';


    /**
     * Non-CAS child elements directly under <cas:authenticationSuccess> to preserve round-trip.
     * @var array<\DOMElement>
     */
    protected array $authenticationSuccessMetadata = [];


    /**
     * Initialize a cas:authenticationSuccess element
     *
     * @param \SimpleSAML\CAS\XML\User $user
     * @param \SimpleSAML\Slate\XML\Attributes $attributes
     * @param \SimpleSAML\CAS\XML\ProxyGrantingTicket|null $proxyGrantingTicket
     * @param \SimpleSAML\CAS\XML\Proxies|null $proxies
     */
    final public function __construct(
        protected User $user,
        protected Attributes $attributes,
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
     * @return \SimpleSAML\Slate\XML\Attributes
     */
    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }


    /**
     * Get Non-CAS child elements directly under <cas:authenticationSuccess> to preserve round-trip.
     * @return array<\DOMElement>
     */
    public function getAuthenticationSuccessMetadata(): array
    {
        return $this->authenticationSuccessMetadata;
    }


    /**
     * @return \SimpleSAML\CAS\XML\ProxyGrantingTicket
     */
    public function getProxyGrantingTicket(): ?ProxyGrantingTicket
    {
        return $this->proxyGrantingTicket;
    }


    /**
     * @return \SimpleSAML\CAS\XML\Proxies
     */
    public function getProxies(): ?Proxies
    {
        return $this->proxies;
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

        $obj = new static(
            $user[0],
            $attributes[0],
            array_pop($proxyGrantingTicket),
            array_pop($proxies),
        );

        /*
         * Technolutions Slate’s SAMLValidate may emit vendor elements (e.g., slate:person, slate:round) directly under
         * cas:authenticationSuccess, not only inside cas:attributes. To interoperate without loosening CAS strictness,
         * we preserve and round‑trip only non‑CAS, namespaced children at that level and ignore unknown CAS‑namespace
         * elements.
         * This keeps vendor metadata intact for consumers (XPath, downstream mapping) while avoiding acceptance of
         * schema‑unknown CAS elements.
         */
        $metadata = [];
        foreach ($xml->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            // Skip all known CAS elements in the CAS namespace
            if ($child->namespaceURI === static::getNamespaceURI()) {
                // Known, schema-defined children
                if (
                    $child->localName === 'user' ||
                    $child->localName === 'attributes' ||
                    $child->localName === 'proxyGrantingTicket' ||
                    $child->localName === 'proxies'
                ) {
                    continue;
                }
                // Unknown elements in the CAS namespace are ignored to preserve strictness
                continue;
            }

            // Only keep vendor elements with a non-null namespace (exclude local/no-namespace)
            if ($child->namespaceURI === null) {
                continue;
            }

            $metadata[] = $child;
        }
        $obj->authenticationSuccessMetadata = $metadata;

        return $obj;
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
        $this->getAttributes()->toXML($e);
        $this->getProxyGrantingTicket()?->toXML($e);
        $this->getProxies()?->toXML($e);

        // Re-emit preserved non-CAS children (e.g., slate:* at top-level)
        foreach ($this->authenticationSuccessMetadata as $child) {
            $imported = $e->ownerDocument?->importNode($child, true);
            if ($imported !== null) {
                $e->appendChild($imported);
            }
        }

        return $e;
    }
}
