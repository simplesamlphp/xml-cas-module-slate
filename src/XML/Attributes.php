<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\CAS\Constants as C;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

/**
 * Class for CAS attributes
 *
 * @package simplesamlphp/cas
 */
final class Attributes extends AbstractCasElement
{
    use ExtendableElementTrait;


    /** @var string */
    final public const LOCALNAME = 'attributes';

    /** The namespace-attribute for the xs:any element */
    final public const XS_ANY_ELT_NAMESPACE = NS::ANY;

    /** The exclusions for the xs:any element */
    final public const XS_ANY_ELT_EXCLUSIONS = [
        [C::NS_CAS, 'authenticationDate'],
        [C::NS_CAS, 'longTermAuthenticationRequestTokenUsed'],
        [C::NS_CAS, 'isFromNewLogin'],
    ];


    /**
     * Initialize a cas:attributes element
     *
     * @param \SimpleSAML\CAS\XML\AuthenticationDate $authenticationDate
     * @param \SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed $longTermAuthenticationRequestTokenUsed
     * @param \SimpleSAML\CAS\XML\IsFromNewLogin $isFromNewLogin
     * @param list<\SimpleSAML\XML\SerializableElementInterface> $elts
     */
    final public function __construct(
        protected AuthenticationDate $authenticationDate,
        protected LongTermAuthenticationRequestTokenUsed $longTermAuthenticationRequestTokenUsed,
        protected IsFromNewLogin $isFromNewLogin,
        array $elts = [],
    ) {
        $this->setElements($elts);
    }


    /**
     * @return \SimpleSAML\CAS\XML\AuthenticationDate
     */
    public function getAuthenticationDate(): AuthenticationDate
    {
        return $this->authenticationDate;
    }


    /**
     * @return \SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed
     */
    public function getLongTermAuthenticationRequestTokenUsed(): LongTermAuthenticationRequestTokenUsed
    {
        return $this->longTermAuthenticationRequestTokenUsed;
    }


    /**
     * @return \SimpleSAML\CAS\XML\IsFromNewLogin
     */
    public function getIsFromNewLogin(): IsFromNewLogin
    {
        return $this->isFromNewLogin;
    }


    /**
     * Convert XML into a cas:attributes-element
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

        $authenticationDate = AuthenticationDate::getChildrenOfClass($xml);
        Assert::count(
            $authenticationDate,
            1,
            'Exactly one <cas:authenticationDate> must be specified.',
            MissingElementException::class,
        );

        $longTermAuthenticationRequestTokenUsed = LongTermAuthenticationRequestTokenUsed::getChildrenOfClass($xml);
        Assert::count(
            $longTermAuthenticationRequestTokenUsed,
            1,
            'Exactly one <cas:longTermAuthenticationRequestTokenUsed> must be specified.',
            MissingElementException::class,
        );

        $isFromNewLogin = IsFromNewLogin::getChildrenOfClass($xml);
        Assert::count(
            $isFromNewLogin,
            1,
            'Exactly least one <cas:isFromNewLogin> must be specified.',
            MissingElementException::class,
        );

        return new static(
            $authenticationDate[0],
            $longTermAuthenticationRequestTokenUsed[0],
            $isFromNewLogin[0],
            self::getChildElementsFromXML($xml),
        );
    }


    /**
     * Convert this Attributes to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Attributes to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->getAuthenticationDate()->toXML($e);
        $this->getLongTermAuthenticationRequestTokenUsed()->toXML($e);
        $this->getIsFromNewLogin()->toXML($e);

        /** @psalm-var \SimpleSAML\XML\SerializableElementInterface $elt */
        foreach ($this->elements as $elt) {
            if (!$elt->isEmptyElement()) {
                $elt->toXML($e);
            }
        }

        return $e;
    }
}
