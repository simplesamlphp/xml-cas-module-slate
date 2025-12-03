<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\Test\XML;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\CAS\Utils\XPath;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\Slate\XML\Attributes;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DateTimeValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\Slate\XML\AttributesTest
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase
{
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\XMLSchema\Type\DateTimeValue */
    private static DateTimeValue $authenticationDate;


    /**
     * Set up the test environment before the first test.
     *
     * This method initializes the test class with required XML representation and test data.
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Attributes::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/slate_attributes.xml',
        );

        self::$authenticationDate = DateTimeValue::fromString('2015-11-12T09:30:10Z');
    }


    public function testMarshalling(): void
    {
        $authenticationDate = new AuthenticationDate(self::$authenticationDate);
        $longTerm = new LongTermAuthenticationRequestTokenUsed(BooleanValue::fromString('true'));
        $isFromNewLogin = new IsFromNewLogin(BooleanValue::fromString('true'));

        /** @var \DOMElement $firstNameElt */
        $firstNameElt = DOMDocumentFactory::fromString(
            '<cas:firstname xmlns:cas="http://www.yale.edu/tp/cas">Example</cas:firstname>',
        )->documentElement;

        $firstName = new Chunk($firstNameElt);

        /** @var \DOMElement $lastNameElt */
        $lastNameElt = DOMDocumentFactory::fromString(
            '<cas:lastname xmlns:cas="http://www.yale.edu/tp/cas">User</cas:lastname>',
        )->documentElement;
        $lastName = new Chunk($lastNameElt);

        /** @var \DOMElement $emailElt */
        $emailElt = DOMDocumentFactory::fromString(
            '<cas:email xmlns:cas="http://www.yale.edu/tp/cas">example-user@technolutions.com</cas:email>',
        )->documentElement;
        $email = new Chunk($emailElt);

        $document = DOMDocumentFactory::fromString(
            '<cas:myAttribute xmlns:cas="http://www.yale.edu/tp/cas">myValue</cas:myAttribute>',
        );

        /** @var \DOMElement $elt */
        $elt = $document->documentElement;
        $myAttribute = new Chunk($elt);

        /** @var \DOMElement $customAttrElt */
        $customAttrElt = DOMDocumentFactory::fromString(
            '<slate:custom xmlns:slate="http://technolutions.com/slate">customAttribute</slate:custom>',
        )->documentElement;
        $customAttr = new Chunk($customAttrElt);

        $attributes = new Attributes(
            $authenticationDate,
            $longTerm,
            $isFromNewLogin,
            [$firstName, $lastName, $email, $myAttribute, $customAttr],
        );

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
        /** @var \DOMElement[] $attributesElements */
        $attributesElements = XPath::xpQuery(
            $attributesElement,
            './cas:authenticationDate/following-sibling::*',
            $xpCache,
        );

        $this->assertGreaterThanOrEqual(2, count($attributesElements));
        $this->assertEquals('cas:longTermAuthenticationRequestTokenUsed', $attributesElements[0]->tagName);
        $this->assertEquals('cas:isFromNewLogin', $attributesElements[1]->tagName);
    }


    /**
     * Ensure that fromXML() correctly populates the typed properties and
     * that the standard CAS children are not duplicated in the generic
     * extension elements.
     */
    public function testFromXMLPopulatesTypedPropertiesAndFiltersStandardChildren(): void
    {
        /** @var \DOMElement $element */
        $element = self::$xmlRepresentation->documentElement;

        $attributes = Attributes::fromXML($element);

        $this->assertInstanceOf(AuthenticationDate::class, $attributes->getAuthenticationDate());
        $this->assertInstanceOf(
            LongTermAuthenticationRequestTokenUsed::class,
            $attributes->getLongTermAuthenticationRequestTokenUsed(),
        );
        $this->assertInstanceOf(IsFromNewLogin::class, $attributes->getIsFromNewLogin());

        $elts = $attributes->getElements();

        // firstname, lastname, email, myAttribute, slate:custom
        $this->assertCount(5, $elts);

        $names = [];
        foreach ($elts as $c) {
            $this->assertInstanceOf(Chunk::class, $c);
            $names[] = $c->getLocalName();
        }

        $this->assertSame(
            ['firstname', 'lastname', 'email', 'myAttribute', 'custom'],
            $names,
        );
    }


    /**
     * Attributes with no standard CAS children should still parse and
     * serialize correctly, and should not gain any extra children.
     */
    public function testFromXMLWithOnlyExtensionAttributes(): void
    {
        $xml = <<<XML
<cas:attributes xmlns:cas="http://www.yale.edu/tp/cas" xmlns:slate="http://technolutions.com/slate">
  <cas:firstname>Example</cas:firstname>
  <cas:lastname>User</cas:lastname>
  <cas:email>example-user@technolutions.com</cas:email>
  <slate:custom>customAttribute</slate:custom>
</cas:attributes>
XML;

        /** @var \DOMElement $element */
        $element = DOMDocumentFactory::fromString($xml)->documentElement;

        $attributes = Attributes::fromXML($element);

        $this->assertNull($attributes->getAuthenticationDate());
        $this->assertNull($attributes->getLongTermAuthenticationRequestTokenUsed());
        $this->assertNull($attributes->getIsFromNewLogin());

        $elts = $attributes->getElements();
        $this->assertCount(4, $elts);

        $this->assertSame(
            $xml,
            strval($attributes),
        );
    }


    /**
     * If multiple instances of a standard CAS child are present, fromXML()
     * should enforce the maxCount() constraint and raise an exception.
     */
    public function testFromXMLWithDuplicateStandardChildThrows(): void
    {
        $xml = <<<XML
<cas:attributes xmlns:cas="http://www.yale.edu/tp/cas">
  <cas:authenticationDate>2015-11-12T09:30:10Z</cas:authenticationDate>
  <cas:authenticationDate>2015-11-12T10:30:10Z</cas:authenticationDate>
</cas:attributes>
XML;

        /** @var \DOMElement $element */
        $element = DOMDocumentFactory::fromString($xml)->documentElement;

        $this->expectException(MissingElementException::class);
        Attributes::fromXML($element);
    }
}
