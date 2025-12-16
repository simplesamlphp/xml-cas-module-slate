<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;

use function array_pop;

/**
 * Class for CAS attributes
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
final class Attributes extends AbstractAttributes
{
    final public const string LOCALNAME = 'attributes';


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
        Assert::maxCount(
            $authenticationDate,
            1,
            'A maximum of one <cas:authenticationDate> must be specified.',
            MissingElementException::class,
        );

        $longTermAuthenticationRequestTokenUsed = LongTermAuthenticationRequestTokenUsed::getChildrenOfClass($xml);
        Assert::maxCount(
            $longTermAuthenticationRequestTokenUsed,
            1,
            'A maximum of one <cas:longTermAuthenticationRequestTokenUsed> must be specified.',
            MissingElementException::class,
        );

        $isFromNewLogin = IsFromNewLogin::getChildrenOfClass($xml);
        Assert::maxCount(
            $isFromNewLogin,
            1,
            'A maximum of one <cas:isFromNewLogin> must be specified.',
            MissingElementException::class,
        );

        return new static(
            array_pop($authenticationDate),
            array_pop($longTermAuthenticationRequestTokenUsed),
            array_pop($isFromNewLogin),
            self::getChildElementsFromXML($xml),
        );
    }
}
