<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\Assert\Assert;
use SimpleSAML\CAS\XML\AbstractServiceResponse;
use SimpleSAML\CAS\XML\AuthenticationFailure;
use SimpleSAML\CAS\XML\ProxyFailure;
use SimpleSAML\CAS\XML\ProxySuccess;
use SimpleSAML\Slate\XML\AuthenticationSuccess;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function array_merge;

/**
 * Class for CAS serviceResponse
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
final class ServiceResponse extends AbstractServiceResponse implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Convert XML into a cas:serviceResponse-element
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

        $authenticationSuccess = AuthenticationSuccess::getChildrenOfClass($xml);
        $authenticationFailure = AuthenticationFailure::getChildrenOfClass($xml);
        $proxySuccess = ProxySuccess::getChildrenOfClass($xml);
        $proxyFailure = ProxyFailure::getChildrenOfClass($xml);

        $response = array_merge($authenticationSuccess, $authenticationFailure, $proxySuccess, $proxyFailure);
        Assert::notEmpty(
            $response,
            'The <cas:serviceResponse> must contain exactly one of <cas:authenticationSuccess>,'
            . ' <cas:authenticationFailure>, <cas:proxySuccess> or <cas:proxyFailure>.',
            MissingElementException::class,
        );
        Assert::count(
            $response,
            1,
            'The <cas:serviceResponse> must contain exactly one of <cas:authenticationSuccess>,'
            . ' <cas:authenticationFailure>, <cas:proxySuccess> or <cas:proxyFailure>.',
            TooManyElementsException::class,
        );

        return new static($response[0]);
    }
}
