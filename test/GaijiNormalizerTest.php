<?php
/**
 * Normalizer of external character.
 *
 * PHP 5.2
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/** GaijiNormalizer */
require dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'GaijiNormalizer.php';

class GaijiNormalizerTest extends PHPUnit_Framework_TestCase {

    private $before = null, $after = null;

    /**
     * Setup.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $savedDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'text';

        $this->before = file_get_contents($savedDirectory.DIRECTORY_SEPARATOR.'before.txt');
        $this->after  = file_get_contents($savedDirectory.DIRECTORY_SEPARATOR.'after.txt');
    }

    /**
     * Test convert characters.
     *
     * @return void
     */
    public function testConvert() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('fbfc', '8d82'); // ハシゴダカ
        $normalizer->addConversionRule('fab1', '8de8'); // タチザキ
        $normalizer->addConversionRule('faba', '93bf'); // 旧字の徳
        $normalizer->addConversionRule('fb7d', '97e7'); // 旧字の礼

        $result   = $normalizer->convert($this->before);
        $expected = $this->after;
        $this->assertEquals($expected, $result);
    }

    /**
     * Test an exception is thrown by convert that empty before character encode.
     *
     * @expectedException GaijiNormalizerException
     * @return void
     */
    public function testConvertWithEmptyBeforeCodeExtension() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('', '8d82');
        $normalized = $normalizer->convert('');
    }

    /**
     * Test an exception is thrown by convert that invalid before character encode.
     *
     * @expectedException GaijiNormalizerException
     * @return void
     */
    public function testConvertWithInvalidBeforeCodeExtension() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('ff', '8d82');
        $normalized = $normalizer->convert('');
    }

    /**
     * Test an exception is thrown by convert that empty after character encode.
     *
     * @expectedException GaijiNormalizerException
     * @return void
     */
    public function testConvertWithEmptyAfterCodeExtension() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('fbfc', '');
        $normalized = $normalizer->convert('');
    }

    /**
     * Test an exception is thrown by convert that invalid after character encode.
     *
     * @expectedException GaijiNormalizerException
     * @return void
     */
    public function testConvertWithInvalidAfterCodeExtension() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('fbfc', 'ff');
        $normalized = $normalizer->convert('');
    }

    /**
     * Test an exception is thrown by convert that failed to replaced.
     *
     * @expectedException GaijiNormalizerException
     * @return void
     */
    public function testConvertWithFailedToReplacedExtension() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('fbfc', 'ffff');
        $normalized = $normalizer->convert('');
    }

    /**
     * Test an support of ISO-2022-JP-MS.
     *
     * @return void
     */
    public function testConvertWithSupportOfIso2022JpMs() {
        $normalizer = new GaijiNormalizer;
        $normalizer->addConversionRule('eee0', '8d82'); // ハシゴダカ - NEC 選定 IBM 拡張文字
        $normalizer->addConversionRule('fab1', '8de8'); // タチザキ - NEC 選定 IBM 拡張文字

        $text = 'test of '.pack('H*', 'eee0').pack('H*', 'fab1');

        $result   = $normalizer->convert($text);
        $expected = mb_convert_encoding('test of 高崎', 'SJIS', 'UTF-8');
        $this->assertEquals($expected, $result);
    }
}
