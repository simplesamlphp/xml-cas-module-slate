<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\Test\XML;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\CAS\Utils\XPath;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\CAS\XML\Proxies;
use SimpleSAML\CAS\XML\Proxy;
use SimpleSAML\CAS\XML\ProxyGrantingTicket;
use SimpleSAML\CAS\XML\User;
use SimpleSAML\Slate\XML\Attributes;
use SimpleSAML\Slate\XML\AuthenticationSuccess;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DateTimeValue;
use SimpleSAML\XMLSchema\Type\StringValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\Slate\Test\XML\AuthenticationSuccessTest
 *
 * @package simplesamlphp/xml-cas-module-slate
 */
#[CoversClass(AuthenticationSuccess::class)]
final class AuthenticationSuccessTest extends TestCase
{
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\XMLSchema\Type\DateTimeValue */
    private static DateTimeValue $authenticationDate;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthenticationSuccess::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/slate_authenticationSuccess.xml',
        );

        self::$authenticationDate = DateTimeValue::fromString('2015-11-12T09:30:10Z');
    }


    /**
     */
    public function testMarshalling(): void
    {
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

        /** @var \DOMElement $customAttrElt */
        $customAttrElt = DOMDocumentFactory::fromString(
            '<slate:custom xmlns:slate="http://technolutions.com/slate">customAttribute</slate:custom>',
        )->documentElement;
        $customAttr = new Chunk($customAttrElt);

        $authenticationDate = new AuthenticationDate(self::$authenticationDate);
        $longTerm = new LongTermAuthenticationRequestTokenUsed(BooleanValue::fromString('true'));
        $isFromNewLogin = new IsFromNewLogin(BooleanValue::fromString('true'));

        $user = new User(StringValue::fromString('example-user@technolutions.com'));
        $attributes = new Attributes(
            $authenticationDate,
            $longTerm,
            $isFromNewLogin,
            [$firstName, $lastName, $email, $customAttr],
        );
        $proxyGrantingTicket = new ProxyGrantingTicket(StringValue::fromString('PGTIOU-84678-8a9d...'));
        $proxies = new Proxies([
            new Proxy(StringValue::fromString('https://proxy2/pgtUrl')),
            new Proxy(StringValue::fromString('https://proxy1/pgtUrl')),
        ]);

        /** @var \DOMElement $personElt */
        $personElt = DOMDocumentFactory::fromString(
            // phpcs:ignore Generic.Files.LineLength
            '<slate:person xmlns:slate="http://technolutions.com/slate">345d2e1b-65de-419c-96ce-e1866d4c57cd</slate:person>',
        )->documentElement;

        $person = new Chunk($personElt);

        /** @var \DOMElement $roundElt */
        $roundElt = DOMDocumentFactory::fromString(
            '<slate:round xmlns:slate="http://technolutions.com/slate">Regular Decision</slate:round>',
        )->documentElement;

        $round = new Chunk($roundElt);

        /** @var \DOMElement $refElt */
        $refElt = DOMDocumentFactory::fromString(
            '<slate:ref xmlns:slate="http://technolutions.com/slate">774482874</slate:ref>',
        )->documentElement;

        $ref = new Chunk($refElt);

        $authenticationSuccess = new AuthenticationSuccess(
            $user,
            $attributes,
            $proxyGrantingTicket,
            $proxies,
            [$person, $round, $ref],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authenticationSuccess),
        );
    }


    public function testMarshallingElementOrdering(): void
    {
        /** @var \DOMElement $firstNameElt */
        $firstNameElt = DOMDocumentFactory::fromString(
            '<cas:firstname xmlns:cas="http://www.yale.edu/tp/cas">John</cas:firstname>',
        )->documentElement;

        $firstName = new Chunk($firstNameElt);

        /** @var \DOMElement $lastNameElt */
        $lastNameElt = DOMDocumentFactory::fromString(
            '<cas:lastname xmlns:cas="http://www.yale.edu/tp/cas">Doe</cas:lastname>',
        )->documentElement;
        $lastName = new Chunk($lastNameElt);

        /** @var \DOMElement $emailElt */
        $emailElt = DOMDocumentFactory::fromString(
            '<cas:email xmlns:cas="http://www.yale.edu/tp/cas">jdoe@example.org</cas:email>',
        )->documentElement;
        $email = new Chunk($emailElt);

        $authenticationDate = new AuthenticationDate(self::$authenticationDate);
        $longTerm = new LongTermAuthenticationRequestTokenUsed(BooleanValue::fromString('true'));
        $isFromNewLogin = new IsFromNewLogin(BooleanValue::fromString('true'));

        $user = new User(StringValue::fromString('username'));
        $attributes = new Attributes($authenticationDate, $longTerm, $isFromNewLogin, [$firstName, $lastName, $email]);
        $proxyGrantingTicket = new ProxyGrantingTicket(StringValue::fromString('PGTIOU-84678-8a9d...'));
        $proxies = new Proxies([
            new Proxy(StringValue::fromString('https://proxy2/pgtUrl')),
            new Proxy(StringValue::fromString('https://proxy1/pgtUrl')),
        ]);

        /** @var \DOMElement $personElt */
        $personElt = DOMDocumentFactory::fromString(
            // phpcs:ignore Generic.Files.LineLength
            '<slate:person xmlns:slate="http://technolutions.com/slate">345d2e1b-65de-419c-96ce-e1866d4c57cd</slate:person>',
        )->documentElement;

        $person = new Chunk($personElt);

        /** @var \DOMElement $roundElt */
        $roundElt = DOMDocumentFactory::fromString(
            '<slate:round xmlns:slate="http://technolutions.com/slate">Regular Decision</slate:round>',
        )->documentElement;

        $round = new Chunk($roundElt);

        /** @var \DOMElement $refElt */
        $refElt = DOMDocumentFactory::fromString(
            '<slate:ref xmlns:slate="http://technolutions.com/slate">774482874</slate:ref>',
        )->documentElement;

        $ref = new Chunk($refElt);

        $authenticationSuccess = new AuthenticationSuccess(
            $user,
            $attributes,
            $proxyGrantingTicket,
            $proxies,
            [$person, $round, $ref],
        );
        $authenticationSuccessElement = $authenticationSuccess->toXML();

        // Test for a user-element
        $xpCache = XPath::getXPath($authenticationSuccessElement);
        $authenticationSuccessElements = XPath::xpQuery($authenticationSuccessElement, './cas:user', $xpCache);
        $this->assertCount(1, $authenticationSuccessElements);

        // Test ordering of cas:authenticationSuccess contents
        /** @var \DOMElement[] $authenticationSuccessElements */
        $authenticationSuccessElements = XPath::xpQuery(
            $authenticationSuccessElement,
            './cas:user/following-sibling::*',
            $xpCache,
        );

        $this->assertCount(6, $authenticationSuccessElements);
        $this->assertEquals('slate:person', $authenticationSuccessElements[0]->tagName);
        $this->assertEquals('slate:round', $authenticationSuccessElements[1]->tagName);
        $this->assertEquals('slate:ref', $authenticationSuccessElements[2]->tagName);
        $this->assertEquals('cas:attributes', $authenticationSuccessElements[3]->tagName);
        $this->assertEquals('cas:proxyGrantingTicket', $authenticationSuccessElements[4]->tagName);
        $this->assertEquals('cas:proxies', $authenticationSuccessElements[5]->tagName);
    }
}
