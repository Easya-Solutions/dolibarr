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
			$messageList[] = __METHOD__." resultP1=".$resultP1;
		}
		$productList['P1'] = $p1;

		// P2 : product not in sell
		$p2 = new Product($db);
		$p2->initAsSpecimen();
		$p2->type = Product::TYPE_PRODUCT;
		$p1->ref = 'P2';
		$p1->label = 'P2 not in sell';
		$p2->status = 0;
		$resultP2 = $p2->create($user);
		if ($resultP2 < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP2=".$resultP2;
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
			$messageList[] = __METHOD__." resultP3L=".$resultP3L;
		}
		$productList['P3L'] = $p3l;

		// P4S : product with serial number
		$p4s = new Product($db);
		$p4s->initAsSpecimen();
		$p4s->type = Product::TYPE_PRODUCT;
		$p4s->ref = 'P4S';
		$p4s->label = 'P4S using serial number';
		$p4s->status = 1;
		$p3l->status_batch = 2;
		$p3l->sell_or_eat_by_mandatory = Product::SELL_OR_EAT_BY_MANDATORY_ID_SELL_AND_EAT;
		$resultP4S = $p4s->create($user);
		if ($resultP4S < 0) {
			$error++;
			$messageList[] = __METHOD__." resultP4S=".$resultP4S;
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
			$messageList[] = __METHOD__." resultS1=".$resultS1;
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
			$messageList[] = __METHOD__." resultS2=".$resultS2;
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
			$messageList[] = __METHOD__." resultK1=".$resultK1;
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
			$messageList[] = __METHOD__." resultKS1=".$resultKS1;
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
			$messageList[] = __METHOD__." resultKS2=".$resultKS2;
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
			$messageList[] = __METHOD__." resultK2=".$resultK2;
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
			$messageList[] = __METHOD__." resultK3=".$resultK3;
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
			$messageList[] = __METHOD__." resultK4=".$resultK4;
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
			$messageList[] = __METHOD__." resultK5=".$resultK5;
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
			$messageList[] = __METHOD__." resultK6=".$resultK6;
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
		$kit = $paramList[0];
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

		$to_test = [
			0 => [
				'kit' => 'K1',
				'products' => [
					['prod' => P1, 'qty' => 3],
					['prod' => P1, 'qty' => 0]
				]
        'expected_products' => []
    ],
    1 => [
		'kit' => 'K1',
		'products' => [
			['prod' => P1, 'qty' => 3, 'incdec' => 1],
			['prod' => P1, 'qty' => 5, 'incdec' => 0],
		]
        'expected_products' => [            // test on count(expected products)
		['prod' => P1, 'qty' => 5, 'incdec' => 0],
	]
    ],
];

		$result = 0;

		$db->begin();

		$productList = $this->createProducts();
		$kitList = $this->createKits();


		$addToKitList = [
			'P1ToK1Qty3Inc1' => [
				'kit' => $kitList['K1'],
				'products' => [
					[$productList['P1'], 3, 1],
					[$productList['P1'], 0, 1],
				],
				//'expected_products' =>
			], // add product "P1" to kit "K1" with qty = 3 and inc/dec = 1
			//'P1ToK1Qty0Inc1' => [$kitList['K1'], ], // add product "P1" to kit "K1" with qty = 0 and inc/dec = 1
		];
		foreach ($addToKitList as $addKey => $addToKit) {

			$db->begin();

			//foreach ($addToKit as  => ) {

			//}
			/**
			 * @var Product $kit
			 */
			$kit = $addToKit[0];
			/**
			 * @var Product $component
			 */
			$component = $addToKit[1];
			$kitRef = $kit->ref;
			$productRef = $component->ref;
			$addQty = $addToKit[2];
			$incdec = $addToKit[3];
			$result = $this->addToKit($addToKit);
			$this->assertGreaterThan(0, $result, 'Add product [ref='.$productRef.'] to kit [ref='.$kitRef.'] with qty='.$addQty.' and incdec='.$incdec);
			print __METHOD__." result".$addKey."=".$result."\n";


			if ($addQty >= 0) {
				$kit->get_sousproduits_arbo(); // Load $object->sousprods
				$prods_arbo = $kit->get_arbo_each_prod();
				$nbofsubsubproducts = count($prods_arbo); // This includes all sub products into nb
				$prodschild = $kit->getChildsArbo($kit->id, 1);
				$nbofsubproducts = count($prodschild); // This includes only first level of children

				// TODO : check P1 is in K1 if qty > 0
				// TODO : check P1 is not in K1 if qty = 0

				// TODO : check components count
				if ($addQty != $nbofsubproducts) {
					$result = -1;
				}
				//$this->assertEquals(count($expected_products), $nbofsubproducts, 'Add product [ref='.$productRef.'] to kit [ref='.$kitRef.'] with qty='.$addQty.' and incdec='.$incdec);
				//print __METHOD__." result=".$result."\n";
			}

			$db->rollback();
		}

		$db->rollback();

		return $result;
	}
}
