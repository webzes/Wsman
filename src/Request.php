<?php
namespace c0py\Wsman;

use DOMDocument;
use Ramsey\Uuid\Uuid;

class Request
{
  /**
  * @var string
  */
  protected $method;

  /**
  * @var string
  */
  protected $resourceUri;

  /**
  * @var array
  */
  protected $options;

  /**
  * @var array
  */
  protected $params;

  /**
  * @var string
  */
  protected $command;

  public function __construct($method, $options, $resourceUri = '', $params = [], $command ='')
  {
    $this->method = $method;
    $this->resourceUri = $this->handleAlias($resourceUri);
    $this->options = $options;
    $this->params = $params;
    $this->command = $command;
  }

  public function build()
  {
    $doc = new DOMDocument('1.0', 'UTF-8');

    $xmlRoot = $doc->createElementNS('http://www.w3.org/2003/05/soap-envelope', 's:Envelope');
    $doc->appendChild($xmlRoot);

    if($this->method == "Identify") {
      return $this->buildIdentity($doc, $xmlRoot);
    }

    $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:a', 'http://schemas.xmlsoap.org/ws/2004/08/addressing'); //get,enumerate
    $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:w', 'http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd'); //get, enumerate
    $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:p', 'http://schemas.microsoft.com/wbem/wsman/1/wsman.xsd'); //get, enumerate

    if($this->method == "Enumerate") {
      $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:n', 'http://schemas.xmlsoap.org/ws/2004/09/enumeration');
      $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:b', 'http://schemas.dmtf.org/wbem/wsman/1/cimbinding.xsd');
    }

    if($this->method == "Pull") {
      $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:n', 'http://schemas.xmlsoap.org/ws/2004/09/enumeration');
    }

    $header = $doc->createElement("s:Header");
    $header = $xmlRoot->appendChild($header);

    $hTo = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/08/addressing', 'a:To', $this->options['location']);
    $hTo = $header->appendChild($hTo);

    $hResourceUri = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:ResourceURI', $this->resourceUri);
    $hResourceUri = $header->appendChild($hResourceUri);

    $rUriAttr = $doc->createAttributeNS('http://www.w3.org/2003/05/soap-envelope','mustUnderstand');
    $rUriAttr->value = "true";

    $doc->getElementsByTagName('ResourceURI')->item(0)->appendChild($rUriAttr);

    $hReplyTo = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/08/addressing', 'a:ReplyTo');
    $hReplyTo = $header->appendChild($hReplyTo);

    $hAddress = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/08/addressing', 'a:Address', 'http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous');
    $hAddress = $hReplyTo->appendChild($hAddress);

    $adrAttr = $doc->createAttributeNS('http://www.w3.org/2003/05/soap-envelope','mustUnderstand');
    $adrAttr->value = "true";

    $doc->getElementsByTagName('Address')->item(0)->appendChild($adrAttr);

    $hAction = $this->action($this->method, $doc);
    $hAction = $header->appendChild($hAction);

    $actionAttr = $doc->createAttributeNS('http://www.w3.org/2003/05/soap-envelope','mustUnderstand');
    $actionAttr->value = "true";

    $doc->getElementsByTagName('Action')->item(0)->appendChild($actionAttr);

    $hMaxSize = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:MaxEnvelopeSize', 512000);
    $hMaxSize = $header->appendChild($hMaxSize);

    $maxSizeAttr = $doc->createAttributeNS('http://www.w3.org/2003/05/soap-envelope','mustUnderstand');
    $maxSizeAttr->value = "true";

    $doc->getElementsByTagName('MaxEnvelopeSize')->item(0)->appendChild($maxSizeAttr);

    $hMessageId = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/08/addressing', 'a:MessageID', 'uuid:'.Uuid::uuid4());
    $hMessageId = $header->appendChild($hMessageId);

    $hLocale = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:Locale');
    $hLocale = $header->appendChild($hLocale);

    $hDataLocale = $doc->createElementNS('http://schemas.microsoft.com/wbem/wsman/1/wsman.xsd', 'p:DataLocale');
    $hDataLocale = $header->appendChild($hDataLocale);

    $localeAttr = $doc->createAttribute('xml:lang');
    $localeAttr->value = 'en-US';

    $dLocaleAttr = $doc->createAttribute('xml:lang');
    $dLocaleAttr->value = 'en-GB';

    $doc->getElementsByTagName('Locale')->item(0)->appendChild($localeAttr);
    $doc->getElementsByTagName('DataLocale')->item(0)->appendChild($dLocaleAttr);

    $hSessionId = $doc->createElementNS('http://schemas.microsoft.com/wbem/wsman/1/wsman.xsd', 'p:SessionId', 'uuid:'.Uuid::uuid4());
    $hSessionId = $header->appendChild($hSessionId);

    $hOpId = $doc->createElementNS('http://schemas.microsoft.com/wbem/wsman/1/wsman.xsd', 'p:OperationID', 'uuid:'.Uuid::uuid4());
    $hOpId = $header->appendChild($hOpId);

    $hSequenceId = $doc->createElementNS('http://schemas.microsoft.com/wbem/wsman/1/wsman.xsd', 'p:SequenceId', 1);
    $hSequenceId = $header->appendChild($hSequenceId);

    if($this->method == 'Get' AND $this->params) {
      $doc = $this->selectors($this->params, $doc, $header);
    }

    $hTimeout = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:OperationTimeout', 'PT60.000S');
    $hTimeout = $header->appendChild($hTimeout);

    $body = $doc->createElementNS('http://www.w3.org/2003/05/soap-envelope', 's:Body');
    $body = $xmlRoot->appendChild($body);

    if($this->method == "Enumerate") {
      $bEnum = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/09/enumeration', 'n:Enumerate');
      $bEnum = $body->appendChild($bEnum);

      $bOptimize = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:OptimizeEnumeration');
      $bOptimize = $bEnum->appendChild($bOptimize);

      $bMaxElements = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:MaxElements', 32000);
      $bMaxElements = $bEnum->appendChild($bMaxElements);
    }

    if($this->method == "Pull") {
      $bPull = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/09/enumeration', 'n:Pull');
      $bPull = $body->appendChild($bPull);

      $bContext = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/09/enumeration', 'n:EnumerationContext', (string)$this->params);
      $bContext = $bPull->appendChild($bContext);

      $bMaxElements = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/09/enumeration', 'n:MaxElements', 32000);
      $bMaxElements = $bPull->appendChild($bMaxElements);
    }

    if($this->method == "Invoke") {

      $bMethod = $doc->createElementNS($this->resourceUri, 'p:'.$this->command.'_INPUT');
      $bMethod = $body->appendChild($bMethod);

      foreach($this->params as $k => $v) {

        $k = $doc->createElementNS($this->resourceUri, 'p:'.$k, $v);
        $k = $bMethod->appendChild($k);
      }
    }

    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    $requestXml = $doc->saveXML();
    return $requestXml;
  }

  public function buildIdentity($doc, $xmlRoot)
  {
    $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:wsmid', 'http://schemas.dmtf.org/wbem/wsman/identity/1/wsmanidentity.xsd');

    $header = $doc->createElement("s:Header");
    $header = $xmlRoot->appendChild($header);

    $body = $doc->createElement("s:Body");
    $body = $xmlRoot->appendChild($body);

    $bIdentify = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/identity/1/wsmanidentity.xsd', 'wsmid:Identify');
    $bIdentify = $body->appendChild($bIdentify);

    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    $requestXml = $doc->saveXML();
    return $requestXml;
  }

  private function action($method, $doc)
  {
    $action = [
      'Get'       => 'http://schemas.xmlsoap.org/ws/2004/09/transfer/Get',
      'Enumerate' => 'http://schemas.xmlsoap.org/ws/2004/09/enumeration/Enumerate',
      'Pull'      => 'http://schemas.xmlsoap.org/ws/2004/09/enumeration/Pull',
      'Invoke'    => $this->resourceUri.'/'.$this->command
    ];

    $hAction = $doc->createElementNS('http://schemas.xmlsoap.org/ws/2004/08/addressing', 'a:Action', $action[$method]);

    return $hAction;
  }

  public function selectors($params, $doc, $header)
  {

    $hSelSet = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:SelectorSet');
    $hSelSet = $header->appendChild($hSelSet);

    $hSelector = $doc->createElementNS('http://schemas.dmtf.org/wbem/wsman/1/wsman.xsd', 'w:Selector', current($params));
    $hSelector = $hSelSet->appendChild($hSelector);

    $selAttr = $doc->createAttribute('Name');
    $selAttr->value = key($params);
    $doc->getElementsByTagName('Selector')->item(0)->appendChild($selAttr);

    return $doc;
  }

  private function handleAlias($resourceUri)
  {
    $aliases = [
        'wmi'      => 'http://schemas.microsoft.com/wbem/wsman/1/wmi',
        'wmicimv2' => 'http://schemas.microsoft.com/wbem/wsman/1/wmi/root/cimv2',
        'cimv2'    => 'http://schemas.dmtf.org/wbem/wscim/1/cim-schema/2',
        'winrm'    => 'http://schemas.microsoft.com/wbem/wsman/1',
        'wsman'    => 'http://schemas.microsoft.com/wbem/wsman/1',
        'shell'    => 'http://schemas.microsoft.com/wbem/wsman/1/windows/shell'
    ];

    $inAlias = explode('/', $resourceUri);
    if(array_key_exists( $inAlias[0], $aliases)) {
      $resourceUri = implode($aliases[$inAlias[0]], explode($inAlias[0], $resourceUri, 2));
    }
    return $resourceUri;
  }

}
