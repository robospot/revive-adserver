<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once LIB_PATH . '/Dal/Maintenance/Statistics/Factory.php';
require_once MAX_PATH . '/lib/OA/Dal/DataGenerator.php';

Language_Loader::load();

/**
 * A class for testing the summariseBucketsRawSupplementary() method of the
 * DB agnostic OX_Dal_Maintenance_Statistics class.
 *
 * @package    OpenXDal
 * @subpackage TestSuite
 */
class Test_OX_Dal_Maintenance_Statistics_summariseBucketsRawSupplementary extends UnitTestCase
{
    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * A method to test the summariseBucketsRawSupplementary() method.
     */
    public function testSummariseBucketsRawSupplementary()
    {
        $aConf = &$GLOBALS['_MAX']['CONF'];
        $aConf['maintenance']['operationInterval'] = 60;

        // Prepare standard test parameters
        $statisticsTableName = $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value';
        $aMigrationDetails = [
            'method' => 'rawSupplementary',
            'masterTable' => $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
            'masterTablePrimaryKeys' => [
                0 => 'data_intermediate_ad_connection_id'
            ],
            'bucketTablePrimaryKeys' => [
                0 => 'data_intermediate_ad_connection_id'
            ],
            'masterTableKeys' => [
                0 => 'server_raw_tracker_impression_id',
                1 => 'server_raw_ip'
            ],
            'bucketTableKeys' => [
                0 => 'server_conv_id',
                1 => 'server_ip'
            ],
            'masterDateTimeColumn' => 'tracker_date_time',
            'bucketTable' => $aConf['table']['prefix'] . 'data_bkt_a_var',
            'source' => [
                0 => 'tracker_variable_id',
                1 => 'value'
            ],
            'destination' => [
                0 => 'tracker_variable_id',
                1 => 'value'
            ]
        ];
        $aDates = [
            'start' => new Date('2008-08-21 09:00:00'),
            'end' => new Date('2008-08-21 09:59:59')
        ];

        // Prepare the DAL object
        $oFactory = new OX_Dal_Maintenance_Statistics_Factory();
        $oDalMaintenanceStatistics = $oFactory->factory();

        // Test 1: Test with an incorrect method name in the mapping array
        $savedValue = $aMigrationDetails['method'];
        $aMigrationDetails['method'] = 'foo';
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with migration map method 'foo' != 'rawSupplementary'.");
        $aMigrationDetails['method'] = $savedValue;

        // Test 2: Test with a different number of masterTablePrimaryKeys and bucketTablePrimaryKeys columns
        $savedValue = $aMigrationDetails['masterTablePrimaryKeys'][0];
        unset($aMigrationDetails['masterTablePrimaryKeys'][0]);
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with different number of 'masterTablePrimaryKeys' and 'bucketTablePrimaryKeys' columns.");
        $aMigrationDetails['masterTablePrimaryKeys'][0] = $savedValue;

        // Test 3: Test with a different number of masterTableKeys and bucketTableKeys columns
        $savedValue = $aMigrationDetails['masterTableKeys'][1];
        unset($aMigrationDetails['masterTableKeys'][1]);
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with different number of 'masterTableKeys' and 'bucketTableKeys' columns.");
        $aMigrationDetails['masterTableKeys'][1] = $savedValue;

        // Test 4: Test with a different number of source and destination columns
        $savedValue = $aMigrationDetails['destination'][1];
        unset($aMigrationDetails['destination'][1]);
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with different number of 'source' and 'destination' columns.");
        $aMigrationDetails['destination'][1] = $savedValue;

        // Test 5: Test with date parameters that are not really dates
        $savedValue = $aDates['start'];
        $aDates['start'] = 'foo';
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid start/end date parameters -- not Date objects.");
        $aDates['start'] = $savedValue;

        $savedValue = $aDates['end'];
        $aDates['end'] = 'foo';
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid start/end date parameters -- not Date objects.");
        $aDates['end'] = $savedValue;

        // Test 6: Test with invalid start/end dates
        $savedValue = $aDates['start'];
        $aDates['start'] = new Date('2008-08-21 08:00:00');
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid start/end date parameters -- not operation interval bounds.");
        $aDates['start'] = $savedValue;

        $savedValue = $aDates['end'];
        $aDates['end'] = new Date('2008-08-22 09:59:59');
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDARGS);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid start/end date parameters -- not operation interval bounds.");
        $aDates['end'] = $savedValue;

        // Test 7: Test with an invalid statistics table name
        $savedValue = $statisticsTableName;
        $statisticsTableName = 'foo';
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDREQUEST);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid statistics table 'foo'.");
        $statisticsTableName = $savedValue;

        // Test 8: Test with an invalid master statistics table name
        $savedValue = $aMigrationDetails['masterTable'];
        $aMigrationDetails['masterTable'] = 'foo';
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDREQUEST);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid master table 'foo'.");
        $aMigrationDetails['masterTable'] = $savedValue;

        // Test 9: Test with no data_bkt_a_var table in the database
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertTrue(is_a($result, 'PEAR_Error'));
        $this->assertEqual($result->code, MAX_ERROR_INVALIDREQUEST);
        $this->assertEqual($result->message, "OX_Dal_Maintenance_Statistics::summariseBucketsRawSupplementary() called with invalid bucket table '{$aConf['table']['prefix']}data_bkt_a_var'.");

        // Install the openXDeliveryLog plugin, which will create the
        // data_bkt_a table required for testing
        TestEnv::installPluginPackage('openXDeliveryLog', false);

        // Test 10: Test with all tables present, but no data
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertEqual($result, 0);

        // Insert a conversion into the data_intermediate_ad_connection table
        // in the incorrect operation interval
        $oData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $oData_intermediate_ad_connection->server_raw_tracker_impression_id = 1;
        $oData_intermediate_ad_connection->server_raw_ip = 'localhost';
        $oData_intermediate_ad_connection->tracker_id = 2;
        $oData_intermediate_ad_connection->tracker_date_time = '2008-08-21 08:15:00';
        $oData_intermediate_ad_connection->connection_date_time = '2008-08-21 07:15:00';
        $oData_intermediate_ad_connection->ad_id = 3;
        $oData_intermediate_ad_connection->zone_id = 4;
        $oData_intermediate_ad_connection->tracker_ip_address = '127.0.0.1';
        $oData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_CLICK;
        $oData_intermediate_ad_connection->connection_window = 3600;
        $oData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $oData_intermediate_ad_connection->inside_window = 1;
        $conversionId = DataGenerator::generateOne($oData_intermediate_ad_connection);

        // Test 11: Test with data in the incorrect operation interval
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertEqual($result, 0);

        // Insert two conversions into the data_intermediate_ad_connection table
        // in the correct operation interval
        $oData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $oData_intermediate_ad_connection->server_raw_tracker_impression_id = 2;
        $oData_intermediate_ad_connection->server_raw_ip = 'localhost';
        $oData_intermediate_ad_connection->tracker_id = 2;
        $oData_intermediate_ad_connection->tracker_date_time = '2008-08-21 09:15:00';
        $oData_intermediate_ad_connection->connection_date_time = '2008-08-21 08:15:00';
        $oData_intermediate_ad_connection->ad_id = 3;
        $oData_intermediate_ad_connection->zone_id = 4;
        $oData_intermediate_ad_connection->tracker_ip_address = '127.0.0.1';
        $oData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_CLICK;
        $oData_intermediate_ad_connection->connection_window = 3600;
        $oData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $oData_intermediate_ad_connection->inside_window = 1;
        $conversionId1 = DataGenerator::generateOne($oData_intermediate_ad_connection);

        $oData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $oData_intermediate_ad_connection->server_raw_tracker_impression_id = 3;
        $oData_intermediate_ad_connection->server_raw_ip = 'localhost';
        $oData_intermediate_ad_connection->tracker_id = 9;
        $oData_intermediate_ad_connection->tracker_date_time = '2008-08-21 09:16:00';
        $oData_intermediate_ad_connection->connection_date_time = '2008-08-21 08:16:00';
        $oData_intermediate_ad_connection->ad_id = 6;
        $oData_intermediate_ad_connection->zone_id = 7;
        $oData_intermediate_ad_connection->tracker_ip_address = '127.0.0.1';
        $oData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_IMPRESSION;
        $oData_intermediate_ad_connection->connection_window = 3600;
        $oData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $oData_intermediate_ad_connection->inside_window = 1;
        $conversionId2 = DataGenerator::generateOne($oData_intermediate_ad_connection);

        // Test 12: Test with data in the correct operation interval, but
        //          no supplementary data
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertEqual($result, 0);

        // Add some supplementary data for the conversions above!
        $oData_bkt_a_var = OA_Dal::factoryDO('data_bkt_a_var');
        $oData_bkt_a_var->server_conv_id = 2;
        $oData_bkt_a_var->server_ip = 'localhost';
        $oData_bkt_a_var->tracker_variable_id = 99;
        $oData_bkt_a_var->value = 'foo';
        DataGenerator::generateOne($oData_bkt_a_var);

        $oData_bkt_a_var = OA_Dal::factoryDO('data_bkt_a_var');
        $oData_bkt_a_var->server_conv_id = 2;
        $oData_bkt_a_var->server_ip = 'localhost';
        $oData_bkt_a_var->tracker_variable_id = 100;
        $oData_bkt_a_var->value = '156.99';
        DataGenerator::generateOne($oData_bkt_a_var);

        $oData_bkt_a_var = OA_Dal::factoryDO('data_bkt_a_var');
        $oData_bkt_a_var->server_conv_id = 3;
        $oData_bkt_a_var->server_ip = 'localhost';
        $oData_bkt_a_var->tracker_variable_id = 15;
        $oData_bkt_a_var->value = '123456789';
        DataGenerator::generateOne($oData_bkt_a_var);

        // Test 12: Test with data in the correct operation interval
        $result = $oDalMaintenanceStatistics->summariseBucketsRawSupplementary($statisticsTableName, $aMigrationDetails, $aDates);
        $this->assertEqual($result, 3);

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId1;
        $oData_intermediate_ad_variable_value->find();
        $rows = $oData_intermediate_ad_variable_value->getRowCount();
        $this->assertEqual($rows, 2);

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId1;
        $oData_intermediate_ad_variable_value->tracker_variable_id = 99;
        $oData_intermediate_ad_variable_value->find();
        $rows = $oData_intermediate_ad_variable_value->getRowCount();
        $this->assertEqual($rows, 1);
        $oData_intermediate_ad_variable_value->fetch();
        $this->assertEqual($oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id, $conversionId1);
        $this->assertEqual($oData_intermediate_ad_variable_value->tracker_variable_id, 99);
        $this->assertEqual($oData_intermediate_ad_variable_value->value, 'foo');

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId1;
        $oData_intermediate_ad_variable_value->tracker_variable_id = 100;
        $oData_intermediate_ad_variable_value->find();
        $rows = $oData_intermediate_ad_variable_value->getRowCount();
        $this->assertEqual($rows, 1);
        $oData_intermediate_ad_variable_value->fetch();
        $this->assertEqual($oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id, $conversionId1);
        $this->assertEqual($oData_intermediate_ad_variable_value->tracker_variable_id, 100);
        $this->assertEqual($oData_intermediate_ad_variable_value->value, '156.99');

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId2;
        $oData_intermediate_ad_variable_value->find();
        $rows = $oData_intermediate_ad_variable_value->getRowCount();
        $this->assertEqual($rows, 1);

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId2;
        $oData_intermediate_ad_variable_value->tracker_variable_id = 15;
        $oData_intermediate_ad_variable_value->find();
        $rows = $oData_intermediate_ad_variable_value->getRowCount();
        $this->assertEqual($rows, 1);
        $oData_intermediate_ad_variable_value->fetch();
        $this->assertEqual($oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id, $conversionId2);
        $this->assertEqual($oData_intermediate_ad_variable_value->tracker_variable_id, 15);
        $this->assertEqual($oData_intermediate_ad_variable_value->value, '123456789');

        // Clean up generated data
        DataGenerator::cleanUp();

        // Also clean up the data migrated into the statistics table
        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId1;
        $oData_intermediate_ad_variable_value->find();
        $oData_intermediate_ad_variable_value->delete();

        $oData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $oData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $conversionId2;
        $oData_intermediate_ad_variable_value->find();
        $oData_intermediate_ad_variable_value->delete();

        // Uninstall the installed plugin
        TestEnv::uninstallPluginPackage('openXDeliveryLog', false);

        // Restore the test environment configuration
        TestEnv::restoreConfig();
    }
}
