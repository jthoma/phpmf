<?php

/**
 * @package MariaFramework
 * @subpackage ddbwrapper plugin
 * @description helloworld
 */

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class ddbwrapper
{

  private function init()
  {
    $sdk = new Aws\Sdk([
      'endpoint'   => 'https://dynamodb.us-east-1.amazonaws.com',
      'region'   => 'us-east-1',
      'version'  => 'latest'
    ]);

    $dynamodb = $sdk->createDynamoDb();
    $marshaler = new Marshaler();

    return compact("sdk", "dynamodb", "marshaler");
  }

  public function create($entity)
  {

    $tmp = $this->init();
    extract($tmp);

    $item = $marshaler->marshalJson(json_encode($entity));

    $params = [
      'TableName' => ddb_table,
      'Item' => $item
    ];

    print_r($params);
    exit();

    try {
      $result = $dynamodb->putItem($params);
      console::log("Created " . $entity['type']);
      return true;
    } catch (DynamoDbException $e) {
      console::log("Unable to add item:");
      console::log($e->getMessage());
      return false;
    }
  }

  public function read($key)
  {

    $tmp = $this->init();
    extract($tmp);

    $item = $marshaler->marshalJson(json_encode($key));

    $params = [
      'TableName' => ddb_table,
      'Item' => $item
    ];


    try {
      $result = $dynamodb->getItem($params);
      console::log("Got " . $result['Item']['type']);
      return $result['Item'];
    } catch (DynamoDbException $e) {
      console::log("Unable to get item:");
      console::log($e->getMessage());
    }
  }

  public function update($key, $data)
  {
    $tmp = $this->init();
    extract($tmp);

    $key = $marshaler->marshalJson(json_encode($key));

    $eav = $marshaler->marshalJson(json_encode($data));

    $params = [
      'TableName' => $tableName,
      'Key' => $key,
      'UpdateExpression' =>
      'set info.rating = :r, info.plot=:p, info.actors=:a',
      'ExpressionAttributeValues' => $eav,
      'ReturnValues' => 'UPDATED_NEW'
    ];

    try {
      $result = $dynamodb->updateItem($params);
      echo "Updated item.\n";
      print_r($result['Attributes']);
    } catch (DynamoDbException $e) {
      echo "Unable to update item:\n";
      echo $e->getMessage() . "\n";
    }
  }

  public function increment($key, $field)
  {
    $tmp = $this->init();
    extract($tmp);

    $key = $marshaler->marshalJson(json_encode($key));

    $eav = $marshaler->marshalJson(json_encode([':val' => 1]));

    $params = [
      'TableName' => $tableName,
      'Key' => $key,
      'UpdateExpression' => 'set '.$field.' = '.$field.' + :val',
      'ExpressionAttributeValues' => $eav,
      'ReturnValues' => 'UPDATED_NEW'
    ];

    try {
      $result = $dynamodb->updateItem($params);
      return $result['Attributes'];
    } catch (DynamoDbException $e) {
      console::log("Unable to update item:");
      console::log($e->getMessage());
    }
  }


}
