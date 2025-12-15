<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\Test\XML;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Slate\XML\AuthenticationSuccess;
use SimpleSAML\Slate\XML\ServiceResponse;

final class ServiceResponseTest extends TestCase
{
    public function testFromXMLWithAuthenticationSuccessReturnsSlateAuthenticationSuccess(): void
    {
        $doc = new DOMDocument('1.0', 'UTF-8');

        // Build <cas:serviceResponse> element with the correct namespace/localName
        $serviceResponseElement = $doc->createElementNS(
            ServiceResponse::getNamespaceURI(),
            'cas:' . ServiceResponse::getLocalName(),
        );
        $doc->appendChild($serviceResponseElement);

        // Build <cas:authenticationSuccess> child element using the same namespace
        $authSuccessElement = $doc->createElementNS(
            ServiceResponse::getNamespaceURI(),
            'cas:' . AuthenticationSuccess::getLocalName(),
        );
        $serviceResponseElement->appendChild($authSuccessElement);

        // Required child: <cas:user>some-user</cas:user>
        $userElement = $doc->createElementNS(
            ServiceResponse::getNamespaceURI(),
            'cas:user',
            'some-user',
        );
        $authSuccessElement->appendChild($userElement);

        // Required child: <cas:attributes>...</cas:attributes>
        // (Structure inside attributes is not constrained by the error,
        // so a minimal, possibly empty element is sufficient here.)
        $attributesElement = $doc->createElementNS(
            ServiceResponse::getNamespaceURI(),
            'cas:attributes',
        );
        $authSuccessElement->appendChild($attributesElement);

        // Act: parse XML into a ServiceResponse
        $serviceResponse = ServiceResponse::fromXML($serviceResponseElement);

        // AbstractServiceResponse usually exposes the wrapped response via a getter.
        // If the actual method name is different (e.g. getChild(), getInnerResponse()),
        // adjust the next line accordingly.
        $innerResponse = $serviceResponse->getResponse();

        // Assert: the wrapped response is our Slate-specific AuthenticationSuccess
        $this->assertInstanceOf(
            AuthenticationSuccess::class,
            $innerResponse,
            // phpcs:ignore Generic.Files.LineLength.TooLong
            'ServiceResponse should wrap a Slate AuthenticationSuccess instance when given <cas:authenticationSuccess> XML.',
        );
    }
}
