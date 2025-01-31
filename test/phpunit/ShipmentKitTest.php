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
require_once dirname(__FILE__).'/../../htdocs/societe/class/societe.class.php';
require_once dirname(__FILE__).'/../../htdocs/commande/class/commande.class.php';
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

		//print __METHOD__."\n";

		$list = [];
		$error = 0;
		$messageList = [];

		// P1 : product in sell
		$p1 = new Product($db);
		$p1->initAsSpecimen();
		$p1->type = Product::TYPE_PRODUCT;
		$p1->ref = 'P1';
		$p1->label = 'P1 in sell';
		$p1->status = 1;
		$resultP1 = $p1->fetch(0, $p1->ref);
		if ($resultP1 == 0) {
			$resultP1 = $p1->create($user);
		}
		if ($resultP1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP1=".$resultP1.", error=".$p1->errorsToString();
		}
		$list['P1'] = $p1;

		// P2 : product not in sell
		$p2 = new Product($db);
		$p2->initAsSpecimen();
		$p2->type = Product::TYPE_PRODUCT;
		$p2->ref = 'P2';
		$p2->label = 'P2 not in sell';
		$p2->status = 0;
		$resultP2 = $p2->fetch(0, $p2->ref);
		if ($resultP2 == 0) {
			$resultP2 = $p2->create($user);
		}
		if ($resultP2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP2=".$resultP2.", error=".$p2->errorsToString();
		}
		$list['P2'] = $p2;

		// P3L : product using lot
		$p3l = new Product($db);
		$p3l->initAsSpecimen();
		$p3l->type = Product::TYPE_PRODUCT;
		$p3l->ref = 'P3L';
		$p3l->label = 'P3L using lot';
		$p3l->status = 1;
		$p3l->status_batch = 1;
		$p3l->sell_or_eat_by_mandatory = Product::SELL_OR_EAT_BY_MANDATORY_ID_NONE;
		$resultP3L = $p3l->fetch(0, $p3l->ref);
		if ($resultP3L == 0) {
			$resultP3L = $p3l->create($user);
		}
		if ($resultP3L < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP3L=".$resultP3L.", error=".$p3l->errorsToString();
		}
		$list['P3L'] = $p3l;

		// P4S : product with serial number
		$p4s = new Product($db);
		$p4s->initAsSpecimen();
		$p4s->type = Product::TYPE_PRODUCT;
		$p4s->ref = 'P4S';
		$p4s->label = 'P4S using serial number';
		$p4s->status = 1;
		$p4s->status_batch = 2;
		$p4s->sell_or_eat_by_mandatory = Product::SELL_OR_EAT_BY_MANDATORY_ID_SELL_AND_EAT;
		$resultP4S = $p4s->fetch(0, $p4s->ref);
		if ($resultP4S == 0) {
			$resultP4S = $p4s->create($user);
		}
		if ($resultP4S < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP4S=".$resultP4S.", error=".$p4s->errorsToString();
		}
		$list['P4S'] = $p4s;

		// S1 : service in sell
		$s1 = new Product($db);
		$s1->initAsSpecimen();
		$s1->type = Product::TYPE_SERVICE;
		$s1->ref = 'S1';
		$s1->label = 'S1 in sell';
		$s1->status = 1;
		$resultS1 = $s1->fetch(0, $s1->ref);
		if ($resultS1 == 0) {
			$resultS1 = $s1->create($user);
		}
		if ($resultS1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultS1=".$resultS1.", error=".$s1->errorsToString();
		}
		$list['S1'] = $s1;

		// S2 : service not in sell
		$s2 = new Product($db);
		$s2->initAsSpecimen();
		$s2->type = Product::TYPE_SERVICE;
		$s2->ref = 'S2';
		$s2->label = 'S2 in sell';
		$s2->status = 0;
		$resultS2 = $s2->fetch(0, $s2->ref);
		if ($resultS2 == 0) {
			$resultS2 = $s2->create($user);
		}
		if ($resultS2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultS2=".$resultS2.", error=".$s2->errorsToString();
		}
		$list['S2'] = $s2;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $list;
	}

	/**
	 * Create virtual products
	 *
	 * @return array	List of virtual products
	 */
	public function createKits()
	{
		global $db, $user;

		//print __METHOD__."\n";

		$list = [];
		$error = 0;
		$messageList = [];

		// K1 : kit with product components in sell
		$k1 = new Product($db);
		$k1->initAsSpecimen();
		$k1->type = Product::TYPE_PRODUCT;
		$k1->ref = 'K1';
		$k1->label = 'K1 with components in sell';
		$k1->status = 1;
		$resultK1 = $k1->fetch(0, $k1->ref);
		if ($resultK1 == 0) {
			$resultK1 = $k1->create($user);
		}
		if ($resultK1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK1=".$resultK1.", error=".$k1->errorsToString();
		}
		$list['K1'] = $k1;

		// KS1 : kit with services as components
		$ks1 = new Product($db);
		$ks1->initAsSpecimen();
		$ks1->type = Product::TYPE_SERVICE;
		$ks1->ref = 'KS1';
		$ks1->label = 'KS1 with services as components';
		$ks1->status = 1;
		$resultKS1 = $ks1->fetch(0, $ks1->ref);
		if ($resultKS1 == 0) {
			$resultKS1 = $ks1->create($user);
		}
		if ($resultKS1 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultKS1=".$resultKS1.", error=".$ks1->errorsToString();
		}
		$list['KS1'] = $ks1;

		// KS2 : kit with services as components
		$ks2 = new Product($db);
		$ks2->initAsSpecimen();
		$ks2->type = Product::TYPE_SERVICE;
		$ks2->ref = 'KS2';
		$ks2->label = 'KS2 with services';
		$ks2->status = 1;
		$resultKS2 = $ks2->fetch(0, $ks2->ref);
		if ($resultKS2 == 0) {
			$resultKS2 = $ks2->create($user);
		}
		if ($resultKS2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultKS2=".$resultKS2.", error=".$ks2->errorsToString();
		}
		$list['KS2'] = $ks2;

		// K2 : kit with product components not in sell
		$k2 = new Product($db);
		$k2->initAsSpecimen();
		$k2->type = Product::TYPE_PRODUCT;
		$k2->ref = 'K2';
		$k2->label = 'K2 with components not in sell';
		$k2->status = 0;
		$resultK2 = $k2->fetch(0, $k2->ref);
		if ($resultK2 == 0) {
			$resultK2 = $k2->create($user);
		}
		if ($resultK2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK2=".$resultK2.", error=".$k2->errorsToString();
		}
		$list['K2'] = $k2;

		// K3 : kit with product components
		$k3 = new Product($db);
		$k3->initAsSpecimen();
		$k3->type = Product::TYPE_PRODUCT;
		$k3->ref = 'K3';
		$k3->label = 'K3 with product components';
		$k3->status = 1;
		$resultK3 = $k3->fetch(0, $k3->ref);
		if ($resultK3 == 0) {
			$resultK3 = $k3->create($user);
		}
		if ($resultK3 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK3=".$resultK3.", error=".$k3->errorsToString();
		}
		$list['K3'] = $k3;

		// K4 : service kit in sell
		$k4 = new Product($db);
		$k4->initAsSpecimen();
		$k4->type = Product::TYPE_SERVICE;
		$k4->ref = 'K4';
		$k4->label = 'K4 with services components in sell';
		$k4->status = 1;
		$resultK4 = $k4->fetch(0, $k4->ref);
		if ($resultK4 == 0) {
			$resultK4 = $k4->create($user);
		}
		if ($resultK4 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK4=".$resultK4.", error=".$k4->errorsToString();
		}
		$list['K4'] = $k4;

		// K5 : service kit not in sell
		$k5 = new Product($db);
		$k5->initAsSpecimen();
		$k5->type = Product::TYPE_SERVICE;
		$k5->ref = 'K5';
		$k5->label = 'K5 with services components not in sell';
		$k5->status = 0;
		$resultK5 = $k5->fetch(0, $k5->ref);
		if ($resultK5 == 0) {
			$resultK5 = $k5->create($user);
		}
		if ($resultK5 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK5=".$resultK5.", error=".$k5->errorsToString();
		}
		$list['K5'] = $k5;

		// K6 : service kit
		$k6 = new Product($db);
		$k6->initAsSpecimen();
		$k6->type = Product::TYPE_SERVICE;
		$k6->ref = 'K6';
		$k6->label = 'K6 with services components';
		$k6->status = 0;
		$resultK6 = $k6->fetch(0, $k6->ref);
		if ($resultK6 == 0) {
			$resultK6 = $k6->create($user);
		}
		if ($resultK6 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultK6=".$resultK6.", error=".$k6->errorsToString();
		}
		$list['K6'] = $k6;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $list;
	}

	/**
	 * Create companies
	 *
	 * @return	array	List of companies
	 */
	public function createCompanies()
	{
		global $db, $user;

		$list = [];
		$error = 0;
		$messageList = [];

		// T1 : customer company
		$company = new Societe($db);
		$company->initAsSpecimen();
		$company->ref = 'T1';
		$company->name = 'T1';
		$company->client = 1;
		$company->code_client = 'auto';
		$company->code_fournisseur = 'auto';
		$result = $company->fetch(0, $company->ref);
		if ($result == 0) {
			$result = $company->create($user);
		}
		if ($result < 0) {
			$error++;
			$messageList[] = __METHOD__." result=".$result.", error=".$company->errorsToString();
		}
		$list['T1'] = $company;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $list;
	}

	/**
	 * Create customers orders
	 *
	 * @return	array	List of customers orders
	 */
	public function createCustomerOrders()
	{
		global $db, $user;

		$list = [];
		$error = 0;
		$messageList = [];

		$companyList = $this->createCompanies();

		// C1 : customer order with company T1
		$company = $companyList['T1'];
		$order = new Commande($db);
		$order->initAsSpecimen();
		$order->ref = 'C1';
		$order->socid = $company->id;
		$result = $order->fetch(0, $order->ref);
		if ($result == 0) {
			$result = $order->create($user);
		}
		if ($result < 0) {
			$error++;
			$messageList[] = __METHOD__." result=".$result.", error=".$order->errorsToString();
		}
		$list['C1'] = $order;

		if ($error) {
			print implode("\n", $messageList)."\n";
		}

		return $list;
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

		print __METHOD__."\n";

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
			// add a simple product to kit with qty negative
			'P2ToK2QtyNegative' => [
				'kit' => 'K2',
				'components' => [
					['product' => 'P2', 'qty' => -1, 'incdec' => 1],
				],
				'expected_components' => [
					['product' => 'P2', 'qty' => -1, 'incdec' => 1],
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
				$result = $kit->add_sousproduit($kit->id, $productToAdd->id, $addQty, $incdec);
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

	/**
	 * Test to add a non exist product component in virtual product
	 *
	 * @return	int			Return integer < 0 if KO, > 0 if OK or 0 if nothing done
	 */
	public function testKitAddNonExistProductAsComponent()
	{
		global $conf, $db, $langs, $user;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;

		print __METHOD__."\n";

		$result = 0;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();

		$toTestList = [
			// add a product not exist in kit (id of product is higher than last product in database)
			'ProductNotExistToK1' => [
				'kit' => 'K1',
				'components' => [
					['product' => 999999999, 'qty' => 1, 'incdec' => 0],
				],
				'expected_components' => [],
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
				$productToAdd = new Product($db);
				$productToAdd->initAsSpecimen();
				$productToAdd->id = $productKey;
				$productRef = $productToAdd->ref;
				$result = $kit->add_sousproduit($kit->id, $productToAdd->id, $addQty, $incdec);
				// it shouldn't be possible to insert a non exist product into a kit (success if result < 0)
				$this->assertLessThan(0, $result, 'Test '.$testKey.' : add product [ref='.$productRef.'] to kit [ref='.$kitRef.'] with qty='.$addQty.' and incdec='.$incdec);
				print __METHOD__." result".$testKey."=".$result."\n";
			}

			$kitComponentsArr = $kit->getChildsArbo($kit->id, 1);
			$kitComponentsCount = count($kitComponentsArr); // This includes only first level of children

			// check all components are in kit with expected quantity and incdec
			$foundComponentList = [];
			foreach ($kitComponentsArr as $kitComponentValue) {
				$foundComponentList[] = ['product' => $kitComponentValue[5], 'qty' => $kitComponentValue[1], 'incdec' => $kitComponentValue[4]];
			}
			$this->assertEqualsCanonicalizing($expectedComponentList, $foundComponentList, 'Test '.$testKey.': all components are not in kit [ref='.$kitRef.']');

			// check components count
			$this->assertEquals(count($expectedComponentList), $kitComponentsCount, 'Test '.$testKey.' : components count='.$kitComponentsCount.' for kit [ref='.$kitRef.']');

			$db->rollback();
		}

		$db->rollback();

		return $result;
	}

	/**
	 * We want to test that a kit can contain another kit, and be nested on many levels.
	 */
	public function testNestedSubKits()
	{
		global $conf, $db, $langs, $user;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();

		$to_test = [
			// nesting on a level of 5
			'nesting_5' => [
				"nesting_level" => 5,
				"top_kit" => 'K1', // product kit that should contain products components
				"inner_kit" => 'K1',
				"base_product" => 'P1',
				"qty_each_level" => 1,
				"expected_nb_subsubproducts" => 5, // one product for each nesting level
				"expected_qty_of_base_product" => 1,
			],
			//  nesting on a level of 5, but qty is 2 at each level -> total qty is 2^(5) = 32
			'nesting_5_qty_2' => [
				"nesting_level" => 5,
				"top_kit" => 'K1', // product kit that should contain products components
				"inner_kit" => 'K1',
				"base_product" => 'P1',
				"qty_each_level" => 2,
				"expected_nb_subsubproducts" => 5, // one product for each nesting level
				"expected_qty_of_base_product" => 32,
			],
			// nesting on a level of 20
			'nesting_20' => [
				"nesting_level" => 20,
				"top_kit" => 'K1', // product kit that should contain products components
				"inner_kit" => 'K1',
				"base_product" => 'P1',
				"qty_each_level" => 1,
				"expected_nb_subsubproducts" => 20,
				"expected_qty_of_base_product" => 1,
			],
			// nesting on a level of 20 with qty = 10 at each level : expecting 10^20 base product
			'nesting_20_qty_10' => [
				"nesting_level" => 20,
				"top_kit" => 'K1', // product kit that should contain products components
				"inner_kit" => 'K1',
				"base_product" => 'P1',
				"qty_each_level" => 10,
				"expected_nb_subsubproducts" => 20,
				"expected_qty_of_base_product" => 100000000000000000000,
			],
		];

		foreach ($to_test as $case_name => $case) {
			$db->begin();

			$top_kit = clone $kitList[$case['top_kit']];
			$inner_kit = clone $kitList[$case['inner_kit']];
			// Having different ref is necessary so products stay separated in DB.
			// Having different labels make things easier to debug test
			$top_kit->ref .= '_'.$case_name;
			$top_kit->label .= '_'.$case_name;
			$top_kit->create($user);

			// Add component in lower kit
			$lower_kit = clone $inner_kit;
			$lower_kit->ref .= '_0'.$case_name;
			$lower_kit->label .= '_0'.$case_name;
			$lower_kit->create($user);
			$result = $lower_kit->add_sousproduit($lower_kit->id, $productList[$case['base_product']]->id, $case['qty_each_level'], 1);
			$this->assertGreaterThan(0, $result);

			// Build nested kits
			$n = 0;
			while ($n < $case['nesting_level'] - 2) { // -2 because we do one insert before (lowest insert) and one on top kit
				$n++;
				$current_kit = new Product($db);
				$current_kit = clone $inner_kit;
				$current_kit->ref .= '_'.$n.$case_name;
				$current_kit->label .= '_'.$n.$case_name;
				$current_kit->create($user);
				$result = $current_kit->add_sousproduit($current_kit->id, $lower_kit->id, $case['qty_each_level'], 1);
				$this->assertGreaterThan(0, $result);
				$lower_kit = clone $current_kit;
			}

			// Insert in top kit
			$result = $top_kit->add_sousproduit($top_kit->id, $lower_kit->id, $case['qty_each_level'], 1);
			$this->assertGreaterThan(0, $result);

			// Test content of kit
			$top_kit->get_sousproduits_arbo(); // Load $object->sousprods
			$prods_arbo = $top_kit->get_arbo_each_prod();
			// var_dump($prods_arbo);
			$nbofsubsubproducts = count($prods_arbo); // This includes all sub products into nb

			// Check there is one product for each product used in arbo
			$this->assertEquals($case['expected_nb_subsubproducts'], $nbofsubsubproducts, "Testing NUMBER of products in arbo for $case_name");
			// Check the number for base product is right
			foreach ($prods_arbo as $prod) {
				if ($prod['ref'] == $case['base_product']) {
					$this->assertEquals($case['expected_qty_of_base_product'], $prod['nb_total'], "Testing QUANTITY of products in arbo for $case_name");
				}
			}

			$db->rollback();
		}

		$db->rollback();
	}

	/**
	 * We want that inserting a kit into itself gives an error
	 */
	public function testKitsRecursivityFails()
	{
		global $conf, $db, $langs, $user;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();

		// kit in itself
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $kitList['K1']->id, 1, 1);
		$this->assertLessThan(0, $result);

		// kit in itself when kit already contains a pproduct
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $productList['P1']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $kitList['K1']->id, 1, 1);
		$this->assertLessThan(0, $result);

		// Kit in itself on nested level, without a product
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $kitList['K2']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K2']->add_sousproduit($kitList['K2']->id, $kitList['K3']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K3']->add_sousproduit($kitList['K3']->id, $kitList['K1']->id, 1, 1);
		$this->assertLessThan(0, $result);


		// Kit in itself on nested level, with a product
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $productList['P1']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K1']->add_sousproduit($kitList['K1']->id, $kitList['K2']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K2']->add_sousproduit($kitList['K2']->id, $kitList['K3']->id, 1, 1);
		$this->assertGreaterThan(0, $result);
		$result = $kitList['K3']->add_sousproduit($kitList['K3']->id, $kitList['K1']->id, 1, 1);
		$this->assertLessThan(0, $result);

		$db->rollback();
	}

	/**
	 * Test to create order and add a kit
	 *
	 * @return	int			Return integer < 0 if KO, > 0 if OK or 0 if nothing done
	 */
	public function testCustomerOrderCreateAndAddKit()
	{
		global $conf,$user,$langs,$db;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;

		print __METHOD__."\n";

		$result = 0;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();
		$customerOrderList = $this->createCustomerOrders();

		// add a kit un customer order
		/**
		 * @var Product $kitToAdd
		 */
		$kitToAdd = $kitList['K1'];

		/**
		 * @var Commande $customerOrder
		 */
		$customerOrder = $customerOrderList['C1'];
		$desc = $kitToAdd->label;
		$pu_ht = $kitToAdd->price;
		$pu_ttc = $kitToAdd->price_ttc;
		//$price_min = $kitToAdd->price_min;
		//$price_min_ttc = $kitToAdd->price_min_ttc;
		$price_base_type = $kitToAdd->price_base_type;
		$qty = 1.0;
		$tva_tx = 20.0;
		$txlocaltax1 = 0.0;
		$txlocaltax2 = 0.0;
		$idprod = $kitToAdd->id;
		$remise_percent = 0.0;
		$info_bits = 0;
		$fk_remise_percent = 0.0;
		$date_start = '';
		$date_end = '';
		$type = $kitToAdd->type;
		$rank = -1;
		$special_code = 0;
		$fk_parent_line = 0;
		$fournprice = null;
		$buyingprice = 0;
		$label = '';
		$array_options = array();
		$fk_unit = null;
		$pu_ht_devise = 0.0;
		$result = $customerOrder->addline(
			$desc,
			$pu_ht,
			$qty,
			$tva_tx,
			$txlocaltax1,
			$txlocaltax2,
			$idprod,
			$remise_percent,
			$info_bits,
			$fk_remise_percent,
			$price_base_type,
			$pu_ttc,
			$date_start,
			$date_end,
			$type,
			$rank,
			$special_code,
			$fk_parent_line,
			$fournprice,
			$buyingprice,
			$label,
			$array_options,
			$fk_unit,
			'',
			0,
			$pu_ht_devise
		);
		$this->assertGreaterThan(0, $result, $customerOrder->errorsToString());

		$db->rollback();

		return $result;
	}
}
