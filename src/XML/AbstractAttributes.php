<?php

declare(strict_types=1);

namespace SimpleSAML\Slate\XML;

use DOMElement;
use SimpleSAML\CAS\XML\AbstractCasElement;
use SimpleSAML\CAS\XML\AuthenticationDate;
use SimpleSAML\CAS\XML\IsFromNewLogin;
use SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XMLSchema\XML\Constants\NS;

/**
 * Class for CAS attributes
 *
 * @package simplesamlphp/xml-cas
 */
abstract class AbstractAttributes extends AbstractCasElement
{
    use ExtendableElementTrait;


    /** The namespace-attribute for the xs:any element */
    final public const XS_ANY_ELT_NAMESPACE = NS::ANY;


    /**
     * Initialize a cas:attributes element
     *
     * @param \SimpleSAML\CAS\XML\AuthenticationDate|null $authenticationDate
     * @param \SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed|null $longTermAuthenticationRequestTokenUsed
     * @param \SimpleSAML\CAS\XML\IsFromNewLogin|null $isFromNewLogin
     * @param list<\SimpleSAML\XML\SerializableElementInterface> $elts
     */
    final public function __construct(
        protected ?AuthenticationDate $authenticationDate = null,
        protected ?LongTermAuthenticationRequestTokenUsed $longTermAuthenticationRequestTokenUsed = null,
        protected ?IsFromNewLogin $isFromNewLogin = null,
        array $elts = [],
    ) {
        $this->setElements($elts);
    }


    /**
     * @return \SimpleSAML\CAS\XML\AuthenticationDate|null
     */
    public function getAuthenticationDate(): ?AuthenticationDate
    {
        return $this->authenticationDate;
    }


    /**
     * @return \SimpleSAML\CAS\XML\LongTermAuthenticationRequestTokenUsed|null
     */
    public function getLongTermAuthenticationRequestTokenUsed(): ?LongTermAuthenticationRequestTokenUsed
    {
        return $this->longTermAuthenticationRequestTokenUsed;
    }


    /**
     * @return \SimpleSAML\CAS\XML\IsFromNewLogin|null
     */
    public function getIsFromNewLogin(): ?IsFromNewLogin
    {
        return $this->isFromNewLogin;
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

        $this->getAuthenticationDate()?->toXML($e);
        $this->getLongTermAuthenticationRequestTokenUsed()?->toXML($e);
        $this->getIsFromNewLogin()?->toXML($e);

        foreach ($this->elements as $elt) {
            if (!$elt->isEmptyElement()) {
                $elt->toXML($e);
            }
        }

        return $e;
    }
}
