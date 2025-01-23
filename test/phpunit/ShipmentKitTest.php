<?php
/* Copyright (C) 2025       Lionel Vessiller         <lvessiller@easya.solutions>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *      \file       test/phpunit/ShipmentKitTest.php
 *      \ingroup    test
 *      \brief      PHPUnit test
 *      \remarks    To run this script as CLI:  phpunit filename.php
 */

global $conf, $db, $langs, $user;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../htdocs/master.inc.php';
require_once dirname(__FILE__).'/../../htdocs/product/class/product.class.php';
require_once dirname(__FILE__).'/CommonClassTest.class.php';

if (empty($user->id)) {
	print "Load permissions for admin user nb 1\n";
	$user->fetch(1);
	$user->loadRights();
}
$conf->global->MAIN_DISABLE_ALL_MAILS = 1;

$langs->load('main');

/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class ShipmentKitTest extends CommonClassTest
{
	/**
	 * setUpBeforeClass
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		global $db;

		if (!isModEnabled('product') || !isModEnabled('service')) {
			print __METHOD__." Module Product or Service must be enabled.\n";
			die(1);
		}

		$db->begin(); // This is to have all actions inside a transaction even if test launched without suite.

		print __METHOD__."\n";
	}

	/**
	 * Create products
	 *
	 * @return array	List of products
	 */
	public function createProducts()
	{
		global $db, $user;

		$productList = [];
		$error = 0;
		$messageList = [];

		// P1 : product in sell
		$p1 = new Product($db);
		$p1->initAsSpecimen();
		$p1->type = Product::TYPE_PRODUCT;
		$p1->ref = 'P1';
		$p1->label = 'P1 in sell';
		$p1->status = 1;
		$resultP1 = $p1->create($user);
		if ($resultP1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP1=".$resultP1.", error=".$p1->errorsToString();
		}
		$productList['P1'] = $p1;

		// P2 : product not in sell
		$p2 = new Product($db);
		$p2->initAsSpecimen();
		$p2->type = Product::TYPE_PRODUCT;
		$p2->ref = 'P2';
		$p2->label = 'P2 not in sell';
		$p2->status = 0;
		$resultP2 = $p2->create($user);
		if ($resultP2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP2=".$resultP2.", error=".$p2->errorsToString();
		}
		$productList['P2'] = $p2;

		// P3L : product using lot
		$p3l = new Product($db);
		$p3l->initAsSpecimen();
		$p3l->type = Product::TYPE_PRODUCT;
		$p3l->ref = 'P3L';
		$p3l->label = 'P3L using lot';
		$p3l->status = 1;
		$p3l->status_batch = 1;
		$p3l->sell_or_eat_by_mandatory = Product::SELL_OR_EAT_BY_MANDATORY_ID_NONE;
		$resultP3L = $p3l->create($user);
		if ($resultP3L < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP3L=".$resultP3L.", error=".$p3l->errorsToString();
		}
		$productList['P3L'] = $p3l;

		// P4S : product with serial number
		$p4s = new Product($db);
		$p4s->initAsSpecimen();
		$p4s->type = Product::TYPE_PRODUCT;
		$p4s->ref = 'P4S';
		$p4s->label = 'P4S using serial number';
		$p4s->status = 1;
		$p4s->status_batch = 2;
		$p4s->sell_or_eat_by_mandatory = Product::SELL_OR_EAT_BY_MANDATORY_ID_SELL_AND_EAT;
		$resultP4S = $p4s->create($user);
		if ($resultP4S < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP4S=".$resultP4S.", error=".$p4s->errorsToString();
		}
		$productList['P4S'] = $p4s;

		// S1 : service in sell
		$s1 = new Product($db);
		$s1->initAsSpecimen();
		$s1->type = Product::TYPE_SERVICE;
		$s1->ref = 'S1';
		$s1->label = 'S1 in sell';
		$s1->status = 1;
		$resultS1 = $s1->create($user);
		if ($resultS1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultS1=".$resultS1.", error=".$s1->errorsToString();
		}
		$productList['S1'] = $s1;

		// S2 : service not in sell
		$s2 = new Product($db);
		$s2->initAsSpecimen();
		$s2->type = Product::TYPE_SERVICE;
		$s2->ref = 'S2';
		$s2->label = 'S2 in sell';
		$s2->status = 0;
		$resultS2 = $s2->create($user);
		if ($resultS2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultS2=".$resultS2.", error=".$s2->errorsToString();
		}
		$productList['S2'] = $s2;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $productList;
	}

	/**
	 * Create virtual products
	 *
	 * @return array	List of virtual products
	 */
	public function createKits()
	{
		global $db, $user;

		$kitList = [];
		$error = 0;
		$messageList = [];

		// K1 : kit with product components in sell
		$k1 = new Product($db);
		$k1->initAsSpecimen();
		$k1->type = Product::TYPE_PRODUCT;
		$k1->ref = 'K1';
		$k1->label = 'K1 with components in sell';
		$k1->status = 1;
		$resultK1 = $k1->create($user);
		if ($resultK1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK1=".$resultK1.", error=".$k1->errorsToString();
		}
		$kitList['K1'] = $k1;

		// KS1 : kit with services as components
		$ks1 = new Product($db);
		$ks1->initAsSpecimen();
		$ks1->type = Product::TYPE_SERVICE;
		$ks1->ref = 'KS1';
		$ks1->label = 'KS1 with services as components';
		$ks1->status = 1;
		$resultKS1 = $ks1->create($user);
		if ($resultKS1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultKS1=".$resultKS1.", error=".$ks1->errorsToString();
		}
		$kitList['KS1'] = $ks1;

		// KS2 : kit with services as components
		$ks2 = new Product($db);
		$ks2->initAsSpecimen();
		$ks2->type = Product::TYPE_SERVICE;
		$ks2->ref = 'KS2';
		$ks2->label = 'KS2 with services';
		$ks2->status = 1;
		$resultKS2 = $ks2->create($user);
		if ($resultKS2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultKS2=".$resultKS2.", error=".$ks2->errorsToString();
		}
		$kitList['KS2'] = $ks2;

		// K2 : kit with product components not in sell
		$k2 = new Product($db);
		$k2->initAsSpecimen();
		$k2->type = Product::TYPE_PRODUCT;
		$k2->ref = 'K2';
		$k2->label = 'K2 with components not in sell';
		$k2->status = 0;
		$resultK2 = $k2->create($user);
		if ($resultK2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK2=".$resultK2.", error=".$k2->errorsToString();
		}
		$kitList['K2'] = $k2;

		// K3 : kit with product components
		$k3 = new Product($db);
		$k3->initAsSpecimen();
		$k3->type = Product::TYPE_PRODUCT;
		$k3->ref = 'K3';
		$k3->label = 'K3 with product components';
		$k3->status = 1;
		$resultK3 = $k3->create($user);
		if ($resultK3 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK3=".$resultK3.", error=".$k3->errorsToString();
		}
		$kitList['K3'] = $k3;

		// K4 : service kit in sell
		$k4 = new Product($db);
		$k4->initAsSpecimen();
		$k4->type = Product::TYPE_SERVICE;
		$k4->ref = 'K4';
		$k4->label = 'K4 with services components in sell';
		$k4->status = 1;
		$resultK4 = $k4->create($user);
		if ($resultK4 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK4=".$resultK4.", error=".$k4->errorsToString();
		}
		$kitList['K4'] = $k4;

		// K5 : service kit not in sell
		$k5 = new Product($db);
		$k5->initAsSpecimen();
		$k5->type = Product::TYPE_SERVICE;
		$k5->ref = 'K5';
		$k5->label = 'K5 with services components not in sell';
		$k5->status = 0;
		$resultK5 = $k5->create($user);
		if ($resultK5 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK5=".$resultK5.", error=".$k5->errorsToString();
		}
		$kitList['K5'] = $k5;

		// K6 : service kit
		$k6 = new Product($db);
		$k6->initAsSpecimen();
		$k6->type = Product::TYPE_SERVICE;
		$k6->ref = 'K6';
		$k6->label = 'K6 with services components';
		$k6->status = 0;
		$resultK6 = $k6->create($user);
		if ($resultK6 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK6=".$resultK6.", error=".$k6->errorsToString();
		}
		$kitList['K6'] = $k6;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $kitList;
	}

	/**
	 * Add product to a virtual product (kit)
	 * @param 	array		$paramList		Array of parameters : [Product kit, Product component, flot qty, int incdec]
	 * @return	int			Return integer < 0 if KO, > 0 if OK
	 */
	public function addToKit($paramList)
	{
		/**
		 * @var Product $kit
		 */
		$kit = $paramList[0];
		/**
		 * @var Product $product
		 */
		$product = $paramList[1];
		$qty = $paramList[2];
		$incdec = $paramList[3];

		$result = $kit->add_sousproduit($kit->id, $product->id, $qty, $incdec);

		return $result;
	}

	/**
	 * Test to add product component in virtual product
	 *
	 * @return	int			Return integer < 0 if KO, > 0 if OK or 0 if nothing done
	 */
	public function testKitAddProductAsComponent()
	{
		global $conf, $db, $langs, $user;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;

		print "\n";

		$result = 0;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();

		$toTestList = [
			// add a simple product to kit with qty positive as integer
			'P1ToK1Qty5' => [
				'kit' => 'K1',
				'components' => [
					['product' => 'P1', 'qty' => 5, 'incdec' => 0],
				],
				'expected_components' => [
					['product' => 'P1', 'qty' => 5, 'incdec' => 0],
				],
			],
			// add a simple product and the same product with qty = 0
			'P1ToK1Qty3AndRemoved' => [
				'kit' => 'K1',
				'components' => [
					['product' => 'P1', 'qty' => 3, 'incdec' => 1],
					['product' => 'P1', 'qty' => 0, 'incdec' => 1],
				],
				'expected_components' => [
					['product' => 'P1', 'qty' => 0, 'incdec' => 1], // qty of "P1" is 0 and not added (standard behaviour)
				],
			],
			// add a simple service to kit with qty positive as integer
			'S1ToKS1Qty5' => [
				'kit' => 'KS1',
				'components' => [
					['product' => 'S1', 'qty' => 5, 'incdec' => 0],
				],
				'expected_components' => [
					['product' => 'S1', 'qty' => 5, 'incdec' => 0],
				],
			],
			// add a simple service and the same service with qty = 0
			'S1ToKS1Qty3AndRemoved' => [
				'kit' => 'KS1',
				'components' => [
					['product' => 'S1', 'qty' => 3, 'incdec' => 1],
					['product' => 'S1', 'qty' => 0, 'incdec' => 1],
				],
				'expected_components' => [
					['product' => 'S1', 'qty' => 0, 'incdec' => 1], // qty of "S1" is 0 and not added (standard behaviour)
				],
			],
			// add a simple product to kit with qty positive as float
			'P2ToK2QtyFloat' => [
				'kit' => 'K2',
				'components' => [
					['product' => 'P2', 'qty' => 3.25, 'incdec' => 1],
				],
				'expected_components' => [
					['product' => 'P2', 'qty' => 3.25, 'incdec' => 1],
				],
			],
		];

		foreach ($toTestList as $testKey => $testParamList) {
			$db->begin();

			$kitKey = $testParamList['kit'];
			$componentList = $testParamList['components'];
			$expectedComponentList = $testParamList['expected_components'];

			/**
			 * @var Product $kit
			 */
			$kit = $kitList[$kitKey];
			$kitRef = $kit->ref;
			foreach ($componentList as $component) {
				$productKey = $component['product'];
				$addQty = $component['qty'];
				$incdec = $component['incdec'];

				/**
				 * @var Product $productToAdd
				 */
				$productToAdd = $productList[$productKey];
				$productRef = $productToAdd->ref;
				$result = $this->addToKit([$kit, $productToAdd, $addQty, $incdec]);
				// success if result > 0
				$this->assertGreaterThan(0, $result, 'Test '.$testKey.' : add product [ref='.$productRef.'] to kit [ref='.$kitRef.'] with qty='.$addQty.' and incdec='.$incdec);
				print __METHOD__." result".$testKey."=".$result."\n";
			}

			//$kit->get_sousproduits_arbo(); // Load $object->sousprods
			//$kitAllSubComponentsArr = $kit->get_arbo_each_prod();
			//$kitAllSubComponentsCount = count($kitAllSubComponentsArr); // This includes all sub products into nb
			$kitComponentsArr = $kit->getChildsArbo($kit->id, 1);
			$kitComponentsCount = count($kitComponentsArr); // This includes only first level of children
			//print __METHOD__." kitComponentsArr=".var_export($kitComponentsArr, true)."\n";

			// check all components are in kit with expected quantity and incdec
			$foundComponentList = [];
			foreach ($kitComponentsArr as $kitComponentValue) {
				$foundComponentList[] = ['product' => $kitComponentValue[5], 'qty' => $kitComponentValue[1], 'incdec' => $kitComponentValue[4]];
			}
			//print __METHOD__." foundComponentList=".var_export($foundComponentList, true)."\n";
			$this->assertEqualsCanonicalizing($expectedComponentList, $foundComponentList, 'Test '.$testKey.': all components are not in kit [ref='.$kitRef.']');

			// check components count
			$this->assertEquals(count($expectedComponentList), $kitComponentsCount, 'Test '.$testKey.' : components count='.$kitComponentsCount.' for kit [ref='.$kitRef.']');

			$db->rollback();
		}

		$db->rollback();

		return $result;
	}
}
