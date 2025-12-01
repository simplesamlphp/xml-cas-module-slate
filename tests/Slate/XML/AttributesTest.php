<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\Test\XML;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\CAS\Utils\XPath;
use SimpleSAML\CAS\XML\AbstractCasElement;
use SimpleSAML\Slate\XML\Attributes;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DateTimeValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\Slate\XML\AttributesTest
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
#[CoversClass(XPath::class)]
#[CoversClass(Attributes::class)]
#[CoversClass(AbstractCasElement::class)]
final class AttributesTest extends TestCase
{
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\XMLSchema\Type\DateTimeValue */
    private static DateTimeValue $authenticationDate;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Attributes::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/slate_attributes.xml',
        );

        self::$authenticationDate = DateTimeValue::fromString('2015-11-12T09:30:10Z');
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authenticationDate = new AuthenticationDate(self::$authenticationDate);
        $longTerm = new LongTermAuthenticationRequestTokenUsed(BooleanValue::fromString('true'));
        $isFromNewLogin = new IsFromNewLogin(BooleanValue::fromString('true'));
        $document = DOMDocumentFactory::fromString(
            '<cas:myAttribute xmlns:cas="http://www.yale.edu/tp/cas">myValue</cas:myAttribute>',
        );

        /** @var \DOMElement $elt */
        $elt = $document->documentElement;
        $myAttribute = new Chunk($elt);
        $attributes = new Attributes($authenticationDate, $longTerm, $isFromNewLogin, [$myAttribute]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($attributes),
        );
    }


    public function testMarshallingElementOrdering(): void
    {
        $authenticationDate = new AuthenticationDate(self::$authenticationDate);
        $longTerm = new LongTermAuthenticationRequestTokenUsed(BooleanValue::fromString('true'));
        $isFromNewLogin = new IsFromNewLogin(BooleanValue::fromString('true'));
        $document = DOMDocumentFactory::fromString(
            '<cas:myAttribute xmlns:cas="http://www.yale.edu/tp/cas">myValue</cas:myAttribute>',
        );

        /** @var \DOMElement $elt */
        $elt = $document->documentElement;
        $myAttribute = new Chunk($elt);
        $attributes = new Attributes($authenticationDate, $longTerm, $isFromNewLogin, [$myAttribute]);

        $attributesElement = $attributes->toXML();

        // Test for an authenticationDate
        $xpCache = XPath::getXPath($attributesElement);
        $attributesElements = XPath::xpQuery($attributesElement, './cas:authenticationDate', $xpCache);
        $this->assertCount(1, $attributesElements);

        // Test ordering of cas:attributes contents
        /** @psalm-var \DOMElement[] $attributesElements */
        $attributesElements = XPath::xpQuery(
            $attributesElement,
            './cas:authenticationDate/following-sibling::*',
            $xpCache,
        );

        $this->assertGreaterThanOrEqual(2, count($attributesElements));
        $this->assertEquals('cas:longTermAuthenticationRequestTokenUsed', $attributesElements[0]->tagName);
        $this->assertEquals('cas:isFromNewLogin', $attributesElements[1]->tagName);
    }
}
