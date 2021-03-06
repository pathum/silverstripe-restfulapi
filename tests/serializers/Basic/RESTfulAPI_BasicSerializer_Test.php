<?php
/**
 * Basic Serializer Test suite
 * 
 * @author  Thierry Francois @colymba thierry@colymba.com
 * @copyright Copyright (c) 2013, Thierry Francois
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD Simplified
 * 
 * @package RESTfulAPI
 * @subpackage Tests
 */
class RESTfulAPI_BasicSerializer_Test extends RESTfulAPI_Tester
{
  protected $extraDataObjects = array(
    'ApiTest_Author',
    'ApiTest_Book',
    'ApiTest_Library'
  );

  protected function getSerializer()
  {
    $injector   = new Injector();
    $serializer = new RESTfulAPI_BasicSerializer();

    $injector->inject($serializer);

    return $serializer;
  }


  /* **********************************************************
   * TESTS
   * */


  /**
   * Checks serializer content type access
   */
  public function testContentType()
  {
    $serializer  = $this->getSerializer();
    $contentType = $serializer->getcontentType();

    $this->assertTrue(
      is_string($contentType),
      'Basic Serializer getcontentType() should return string'
    );
  }


  /**
   * Checks data serialization
   */
  public function testSerialize()
  {
    $serializer = $this->getSerializer();

    // test single dataObject serialization
    $dataObject = ApiTest_Author::get()->filter(array('Name' => 'Peter'))->first();
    $jsonString = $serializer->serialize($dataObject);
    $jsonObject = json_decode($jsonString);

    $this->assertEquals(
      JSON_ERROR_NONE,
      json_last_error(),
      "Basic Serialize dataObject should return valid JSON"
    );

    $this->assertEquals(
      $dataObject->Name,
      $jsonObject->Name,
      "Basic Serialize should return an object and not modify values"
    );

    // test datalist serialization
    $dataList   = ApiTest_Author::get();
    $jsonString = $serializer->serialize($dataList);
    $jsonArray  = json_decode($jsonString);

    $this->assertEquals(
      JSON_ERROR_NONE,
      json_last_error(),
      "Basic Serialize dataList should return valid JSON"
    );

    $this->assertTrue(
      is_array($jsonArray),
      "Basic Serialize dataObject should return an object"
    );
  }


  /**
   * Checks embedded records config
   */
  public function testEmbeddedRecords()
  {
    Config::inst()->update('RESTfulAPI', 'embedded_records', array(
      'ApiTest_Library' => array('Books')
    ));

    $serializer = $this->getSerializer();
    $dataObject = ApiTest_Library::get()->filter(array('Name' => 'Helsinki'))->first();


    // api access disabled
    Config::inst()->update('ApiTest_Book', 'api_access', false);
    $jsonString = $serializer->serialize($dataObject);
    $jsonObject = json_decode($jsonString);

    $this->assertTrue(
      is_numeric($jsonObject->Books[0]),
      "Basic Serialize should return ID list for embedded records without api access"
    );

    // api access enabled
    Config::inst()->update('ApiTest_Book', 'api_access', true);
    $jsonString = $serializer->serialize($dataObject);
    $jsonObject = json_decode($jsonString);

    $this->assertTrue(
      is_numeric($jsonObject->Books[0]->ID),
      "Basic Serialize should return a full record for embedded records"
    );
  }


  /**
   * Checks column name formatting
   */
  public function testFormatName()
  {
    $serializer = $this->getSerializer();

    $column = 'Name';

    $this->assertEquals(
      $column,
      $serializer->formatName($column),
      "Basic Serialize should not change name formatting"
    );
  }
}