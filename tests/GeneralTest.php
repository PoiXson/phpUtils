<?php
/**
 * PoiXson phpUtils
 *
 * @copyright 2004-2015
 * @license GPL-3
 * @author lorenzo at poixson.com
 * @link http://poixson.com/
 */
namespace pxn\phpUtils\tests;

use pxn\phpUtils\General;

/**
 * @coversDefaultClass \pxn\phpUtils\General
 */
class GeneralTest extends \PHPUnit_Framework_TestCase {



	public function testArray() {
		$this->assertEmpty([]);
	}



	public function testTimestamp() {
		// all timings are in ms
		$this->PerformTimestampTest(
				10, // sleep time
				8,  // min expected time
				30  // max expected time
		);
	}
	/**
	 * @covers ::getTimestamp
	 * @covers ::Sleep
	 */
	private function PerformTimestampTest($sleepTime, $minExpected, $maxExpected) {
		$a = General::getTimestamp();
		\usleep($sleepTime * 1000);
		$b = General::getTimestamp();
		$c = $b - $a;
		// > 1
		$this->assertGreaterThan(1, $a);
		$this->assertGreaterThan(1, $b);
		// within 5-15ms
		$this->assertGreaterThan($minExpected / 1000, $c);
		$this->assertLessThan(   $maxExpected / 1000, $c);



	/**
	 * @covers ::GDSupported
	 */
	public function testGDSupported() {
		$this->assertEquals(
				\function_exists('imagepng'),
				General::GDSupported()
		);
	}



	const VALIDATE_CLASS_STRING = 'pxn\\phpUtils\\tests\\GeneralTest';



	public function testClassName() {
		$this->assertEquals(
				self::VALIDATE_CLASS_STRING,
				\get_class($this)
		);
	}



	/**
	 * @covers ::InstanceOfClass
	 */
	public function testInstanceOfClass() {
		$this->assertTrue(
				General::InstanceOfClass(
						self::VALIDATE_CLASS_STRING,
						$this
				)
		);
	}



	/**
	 * @covers ::ValidateClass
	 */
	public function testValidateClass() {
		General::ValidateClass(
				self::VALIDATE_CLASS_STRING,
				$this
		);
	}
	/**
	 * @covers ::ValidateClass
	 */
	public function testValidateClass_NullString() {
		try {
			General::ValidateClass(
					NULL,
					$this
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals(
					'classname not defined',
					$e->getMessage()
			);
			return;
		}
		$this->assertTrue(FALSE, 'Failed to throw expected exception');
	}
	/**
	 * @covers ::ValidateClass
	 */
	public function testValidateClass_BlankString() {
		try {
			General::ValidateClass(
					'',
					$this
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals(
					'classname not defined',
					$e->getMessage()
			);
			return;
		}
		$this->assertTrue(FALSE, 'Failed to throw expected exception');
	}
	/**
	 * @covers ::ValidateClass
	 */
	public function testValidateClass_NullObject() {
		try {
			General::ValidateClass(
					self::VALIDATE_CLASS_STRING,
					NULL
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals(
					'object not defined',
					$e->getMessage()
			);
			return;
		}
		$this->assertTrue(FALSE, 'Failed to throw expected exception');
	}
	/**
	 * @covers ::ValidateClass
	 */
	public function testValidateClass_ClassType() {
		try {
			General::ValidateClass(
					self::VALIDATE_CLASS_STRING.'_invalid',
					$this
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals(
					'Class object isn\'t of type '.
						self::VALIDATE_CLASS_STRING.'_invalid',
					$e->getMessage()
			);
			return;
		}
		$this->assertTrue(FALSE, 'Failed to throw expected exception');
	}



}
