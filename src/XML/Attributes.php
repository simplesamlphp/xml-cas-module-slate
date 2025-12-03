<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\XML\Chunk;
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
    /**
     * Return the local name for this element.
     *
     * The base CAS implementation uses "Attributes" (capital A), but Slate expects
     * the canonical CAS element name to be "attributes" (lowercase a).
     */
    public static function getLocalName(): string
    {
        return 'attributes';
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

        // Get all child elements, but drop the ones that are already handled
        // as dedicated constructor arguments to avoid duplicates.
        $elts = self::getChildElementsFromXML($xml);

        // Names of the standard CAS children we already expose as typed properties
        $standardNames = [
            'authenticationDate',
            'longTermAuthenticationRequestTokenUsed',
            'isFromNewLogin',
        ];

        // Remove those three from the generic list
        $elts = array_values(
            array_filter(
                $elts,
                static function (object $elt) use ($standardNames): bool {
                    if (!$elt instanceof Chunk) {
                        // Non-Chunk elements are fine
                        return true;
                    }

                    return !($elt->getNamespaceURI() === self::NS
                        && in_array($elt->getLocalName(), $standardNames, true));
                },
            ),
        );

        return new static(
            array_pop($authenticationDate),
            array_pop($longTermAuthenticationRequestTokenUsed),
            array_pop($isFromNewLogin),
            $elts,
        );
    }
}
